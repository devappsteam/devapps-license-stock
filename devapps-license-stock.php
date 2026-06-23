<?php
/*
Plugin Name: DevApps License Stock
Description: Exibe estoque de licenças por produto na tela do LMFWC.
Version: 1.0.1
Author: DevApps® Consultoria e Desenvolvimento de Software LTDA
*/

if (!defined("ABSPATH")) {
    exit();
}

class DevApps_License_Stock
{
    public function __construct()
    {
        add_action("admin_enqueue_scripts", [$this, "enqueue"]);
        add_action("wp_ajax_devapps_license_stock_data", [$this, "get_data"]);
    
        add_action(
            "wp_dashboard_setup",
            [$this, "register_dashboard_widget"]
        );
    }

    public function enqueue($hook)
    {
        if (!isset($_GET["post_type"]) || $_GET["post_type"] !== "product") {
            return;
        }

        if (!isset($_GET["page"]) || $_GET["page"] !== "lmfwc_licenses") {
            return;
        }

        wp_register_script(
            "devapps-license-stock",
            "",
            ["jquery"],
            "1.0.1",
            true
        );

        wp_enqueue_script("devapps-license-stock");

        wp_localize_script("devapps-license-stock", "devappsLicenseStock", [
            "ajax_url" => admin_url("admin-ajax.php"),
            "nonce" => wp_create_nonce("devapps_license_stock"),
        ]);

        wp_add_inline_script("devapps-license-stock", $this->javascript());
    }

    private function javascript()
    {
        return <<<'JS'

        jQuery(function($){
        
            function injectContainer() {
        
                const target = document.querySelector('.lmfwc-card');
        
                if (!target) {
                    setTimeout(injectContainer, 500);
                    return;
                }
        
                if ($('#devapps-license-stock').length) {
                    return;
                }
        
                const html = `
                    <div id="devapps-license-stock" class="lmfwc-card" style="margin-bottom:20px;">
                        <div class="lmfwc-card-header">
                            <h2 class="lmfwc-card-title">
                                Licenças Restantes por Produto
                            </h2>
                        </div>
        
                        <div class="lmfwc-card-content">
        
                            <table class="widefat striped">
                                <thead>
                                    <tr>
                                        <th width="80">Imagem</th>
                                        <th>Nome</th>
                                        <th>Cadastradas</th>
                                        <th>Utilizadas</th>
                                        <th>Restantes</th>
                                    </tr>
                                </thead>
        
                                <tbody id="devapps-license-stock-body">
                                    <tr>
                                        <td colspan="5">
                                            Carregando...
                                        </td>
                                    </tr>
                                </tbody>
        
                            </table>
        
                        </div>
                    </div>
                `;
        
                $(target).before(html);
        
                loadData();
            }
        
            function loadData() {
        
                $.post(
                    devappsLicenseStock.ajax_url,
                    {
                        action: 'devapps_license_stock_data',
                        nonce: devappsLicenseStock.nonce
                    },
                    function(response){
        
                        let rows = '';
        
                        if (
                            !response.success ||
                            !response.data.length
                        ) {
                            rows = `
                                <tr>
                                    <td colspan="5">
                                        Nenhum produto encontrado
                                    </td>
                                </tr>
                            `;
        
                            $('#devapps-license-stock-body').html(rows);
                            return;
                        }
        
                        response.data.forEach(function(item){
        
                            rows += `
                                <tr>
                                    <td>
                                        <img
                                            src="${item.image}"
                                            style="width:50px;height:50px;object-fit:cover;"
                                        >
                                    </td>
        
                                    <td>${item.name}</td>
        
                                    <td>${item.total}</td>
        
                                    <td>${item.used}</td>
        
                                    <td>
                                        <strong>${item.remaining}</strong>
                                    </td>
                                </tr>
                            `;
                        });
        
                        $('#devapps-license-stock-body').html(rows);
        
                    }
                );
            }
        
            injectContainer();
        
        });
        
        JS;
    }

    public function get_data()
    {
        check_ajax_referer("devapps_license_stock", "nonce");

        global $wpdb;

        $license_stats = $wpdb->get_results(
            "
            SELECT
                product_id,
                COUNT(*) AS total,
                SUM(CASE WHEN order_id IS NULL THEN 1 ELSE 0 END) AS remaining,
                SUM(CASE WHEN order_id IS NOT NULL THEN 1 ELSE 0 END) AS used
            FROM {$wpdb->prefix}lmfwc_licenses
            GROUP BY product_id
            ",
            ARRAY_A
        );

        if (empty($license_stats)) {
            wp_send_json_success([]);
        }

        $product_ids = array_map(
            "intval",
            array_column($license_stats, "product_id")
        );

        $products = get_posts([
            "post_type" => "product",
            "post_status" => "publish",
            "posts_per_page" => -1,
            "post__in" => $product_ids,
            "orderby" => "title",
            "order" => "ASC",
        ]);

        $stats_map = [];

        foreach ($license_stats as $stat) {
            $stats_map[(int) $stat["product_id"]] = [
                "total" => (int) $stat["total"],
                "used" => (int) $stat["used"],
                "remaining" => (int) $stat["remaining"],
            ];
        }

        $data = [];

        foreach ($products as $product) {
            $product_id = (int) $product->ID;

            if (!isset($stats_map[$product_id])) {
                continue;
            }

            $image = get_the_post_thumbnail_url($product_id, "thumbnail");

            if (!$image && function_exists("wc_placeholder_img_src")) {
                $image = wc_placeholder_img_src();
            }

            $data[] = [
                "id" => $product_id,
                "name" => $product->post_title,
                "image" => $image,
                "total" => $stats_map[$product_id]["total"],
                "used" => $stats_map[$product_id]["used"],
                "remaining" => $stats_map[$product_id]["remaining"],
            ];
        }

        wp_send_json_success($data);
    }
    
    public function register_dashboard_widget()
{
    wp_add_dashboard_widget(
        "devapps_license_stock_widget",
        "Estoque de Licenças",
        [$this, "render_dashboard_widget"]
    );
}

public function render_dashboard_widget()
{
    global $wpdb;

    $license_stats = $wpdb->get_results(
        "
        SELECT
            product_id,
            COUNT(*) AS total,
            SUM(CASE WHEN order_id IS NULL THEN 1 ELSE 0 END) AS remaining
        FROM {$wpdb->prefix}lmfwc_licenses
        GROUP BY product_id
        ORDER BY remaining ASC
        ",
        ARRAY_A
    );

    if (empty($license_stats)) {
        echo '<p>Nenhuma licença encontrada.</p>';
        return;
    }

    echo '
    <style>
        .devapps-license-item{
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:8px 10px;
            margin-bottom:6px;
            border-radius:4px;
            background:#f6f7f7;
        }

        .devapps-license-item.low{
            background:#ffe5e5;
            border-left:4px solid #d63638;
        }

        .devapps-license-item.warning{
            background:#fff8e1;
            border-left:4px solid #dba617;
        }

        .devapps-license-count{
            font-weight:700;
            font-size:14px;
        }

        .devapps-license-name{
            flex:1;
            margin-right:10px;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
            font-weight:bold;
        }
    </style>
    ';

    $has_alerts = false;

    foreach ($license_stats as $stat) {

        $remaining = (int) $stat["remaining"];

        $product = wc_get_product(
            (int) $stat["product_id"]
        );

        if (!$product) {
            continue;
        }

    
        if($remaining <= 2){
            $class = "low";
        }else if($remaining <= 10){
            $class = "warning";
        }else{
            $class = "";
        }
        
        echo '
        <div class="devapps-license-item '.$class.'">
            <span class="devapps-license-name">
                '.esc_html($product->get_name()).'
            </span>

            <span class="devapps-license-count">
                '.$remaining.'
            </span>
        </div>';
    }

}
}

new DevApps_License_Stock();
