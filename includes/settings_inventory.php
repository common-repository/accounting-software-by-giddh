<section class="settings-main">
    <div class="white-box mt-2">
        <div class="clearfix mb-4">
            <ul class="nav nav-tabs custom-tab-2" id="collectionTab" role="tablist">
                <li class="nav-item"><a class="nav-link active" id="SKU-matched-tab" data-toggle="tab" href="#SKU-matched" role="tab" aria-controls="SKU-matched" aria-selected="true">SKU Matched</a></li>
                <li class="nav-item"><a class="nav-link" id="unmatched-woocommerce-tab" data-toggle="tab" href="#unmatched-woocommerce" role="tab" aria-controls="unmatched-woocommerce" aria-selected="false">Unmatched Woocommerce</a></li>
                <li class="nav-item"><a class="nav-link" id="unmatched-giddh-tab" data-toggle="tab" href="#unmatched-giddh" role="tab" aria-controls="unmatched-giddh" aria-selected="false">Unmatched Giddh</a></li>
                <li class="nav-item"><a class="nav-link" id="service-tab" data-toggle="tab" href="#service-giddh" role="tab" aria-controls="service-giddh" aria-selected="false">Service</a></li>
            </ul>
            <div class="tab-content" id="CollectionTabContent">
                <div class="tab-pane fade show active" id="SKU-matched" role="tabpanel" aria-labelledby="SKU-matched-tab">
                    <?php include(GIDDH_PLUGIN_PATH.'includes/inventory/sku_matched.php') ?>
                </div>
                <div class="tab-pane fade" id="unmatched-woocommerce" role="tabpanel" aria-labelledby="unmatched-woocommerce-tab">
                    <?php include(GIDDH_PLUGIN_PATH.'includes/inventory/unmatched_woocommerce.php') ?>
                </div>
                <div class="tab-pane fade" id="unmatched-giddh" role="tabpanel" aria-labelledby="unmatched-giddh">
                    <?php include(GIDDH_PLUGIN_PATH.'includes/inventory/unmatched_giddh.php') ?>
                </div>
                <div class="tab-pane fade" id="service-giddh" role="tabpanel" aria-labelledby="service-giddh">
                    <?php include(GIDDH_PLUGIN_PATH.'includes/inventory/service_giddh.php') ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
wp_register_script('inventory', '');
wp_enqueue_script('inventory');
wp_add_inline_script('inventory', 'giddhShippingAccount = {name: "'.get_option('giddh_shipping_account_name').'", uniqueName: "'.get_option('giddh_shipping_account').'" }; jQuery(document).ready(function() { initServiceAccountsAutocomplete(); getProducts("skumatched", 1); getProducts("giddhunmatched", 1); getProducts("woocommerceunmatched", 1); });');
include(GIDDH_PLUGIN_PATH."includes/footer.php");
?>