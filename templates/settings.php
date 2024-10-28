<?php
include(GIDDH_PLUGIN_PATH."includes/header.php");

$tabJs = "showActiveTab('".sanitize_text_field($_GET['tab'])."');";

wp_register_script('tab', '');
wp_enqueue_script('tab');
wp_add_inline_script('tab', $tabJs);
?>
<section class="settings-main">
    <div class="container-fluid">
        <div class="setting-tabs">
            <ul class="nav nav-pills custom-tabs" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" id="invoice-tab" data-toggle="pill" href="#invoice" role="tab" aria-controls="invoice" aria-selected="true">
                    <span class="icon-invoice"></span>  
                    Invoice</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="category-tab" data-toggle="pill" href="#category" role="tab" aria-controls="category" aria-selected="false">
                    <span class="icon-category"></span>  
                    Category</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="inventory-tab" data-toggle="pill" href="#inventory" role="tab" aria-controls="inventory" aria-selected="false">
                    <span class="icon-inventory"></span>  
                    Inventory</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="payment-tab" data-toggle="pill" href="#payment" role="tab" aria-controls="payment" aria-selected="false">
                    <span class="icon-payment"></span>
                    Payment</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="my-account-tab" data-toggle="pill" href="#my-account" role="tab" aria-controls="my-account" aria-selected="false">
                    <span class="icon-my-account"></span>  
                    My Account</a>
                </li>
            </ul>
            <div class="tab-content setting-tabs" id="pills-tabContent">
                <div class="tab-pane fade show active" id="invoice" role="tabpanel" aria-labelledby="invoice-tab">
                    <?php require(GIDDH_PLUGIN_PATH."includes/settings_invoice.php"); ?>
                </div>
                <div class="tab-pane fade show" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
                    <?php require(GIDDH_PLUGIN_PATH."includes/settings_inventory.php"); ?>
                </div>
                <div class="tab-pane fade" id="category" role="tabpanel" aria-labelledby="category-tab">
                    <?php require(GIDDH_PLUGIN_PATH."includes/settings_category.php"); ?>
                </div>
                <div class="tab-pane fade" id="payment" role="tabpanel" aria-labelledby="payment-tab">
                    <?php require(GIDDH_PLUGIN_PATH."includes/settings_payment.php"); ?>
                </div>
                <div class="tab-pane fade" id="my-account" role="tabpanel" aria-labelledby="my-account-tab">
                    <?php require(GIDDH_PLUGIN_PATH."includes/settings_account.php"); ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
include(GIDDH_PLUGIN_PATH."includes/footer.php");
?>