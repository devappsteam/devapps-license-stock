# DevApps License Stock

## Descrição

O **DevApps License Stock** é uma extensão para WordPress desenvolvida para complementar o ecossistema do **License Manager for WooCommerce (LMFWC)**, fornecendo uma visão clara e centralizada do estoque de licenças digitais disponíveis por produto.

O plugin adiciona uma interface administrativa que permite acompanhar, em tempo real, a quantidade de licenças cadastradas, vendidas e disponíveis para cada produto do WooCommerce, facilitando o controle operacional e prevenindo rupturas de estoque de licenças.

Além disso, disponibiliza um widget no Dashboard do WordPress com indicadores visuais de alerta para produtos com baixo estoque, permitindo uma gestão proativa das licenças digitais.

---

## Principais Recursos

### 📊 Controle de Estoque de Licenças por Produto

Adiciona uma nova seção na área administrativa do LMFWC exibindo:

* Imagem do produto;
* Nome do produto;
* Quantidade total de licenças cadastradas;
* Quantidade de licenças já utilizadas/vendidas;
* Quantidade de licenças restantes disponíveis para venda.

### ⚡ Atualização Dinâmica via AJAX

Os dados são carregados dinamicamente utilizando AJAX, garantindo melhor desempenho e atualização rápida das informações sem necessidade de recarregar a página.

### 📦 Widget de Monitoramento no Dashboard

Cria um widget nativo no painel administrativo do WordPress exibindo o saldo atual de licenças por produto.

### 🚨 Alertas Visuais de Baixo Estoque

O widget utiliza indicadores visuais para destacar produtos que exigem atenção:

| Situação | Critério                        |
| -------- | ------------------------------- |
| Crítico  | Até 2 licenças restantes        |
| Atenção  | Entre 3 e 10 licenças restantes |
| Normal   | Acima de 10 licenças restantes  |

Essa funcionalidade permite identificar rapidamente produtos que precisam de reposição de licenças.

---

## Requisitos

* WordPress 5.8+
* WooCommerce
* License Manager for WooCommerce (LMFWC)
* PHP 7.4+

---

## Como Funciona

O plugin consulta diretamente a tabela de licenças do LMFWC para calcular:

* Total de licenças cadastradas;
* Licenças já vinculadas a pedidos;
* Licenças ainda disponíveis.

As informações são agrupadas por produto e apresentadas em uma interface amigável dentro do painel administrativo.

---

## Benefícios

* Melhor controle do estoque de licenças digitais;
* Redução do risco de vendas sem disponibilidade de licenças;
* Monitoramento rápido através do Dashboard do WordPress;
* Integração transparente com WooCommerce e LMFWC;
* Interface simples e intuitiva para equipes operacionais.

---

## Autor

**DevApps® Consultoria e Desenvolvimento de Software LTDA**

Especialistas em desenvolvimento de soluções corporativas, automações e integrações para WordPress, WooCommerce e plataformas de e-commerce.
