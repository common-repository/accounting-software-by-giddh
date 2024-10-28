<form id="servicesForm">
    <div class="setting-height">
        <div class="row">
            <div class="col-md-5">
                <div class="row">
                    <div class="col-md-6">
                        <p class="font-12 text-light d-block mb-1">Woocommerce Service </p>
                    </div>
                    <div class="col-md-6">
                        <p class="font-12 text-light d-block mb-1">Giddh Shipping Account </p>
                    </div>
                </div>
            </div>
        </div>
        <div id="service-mappings">
            <div class="payment-mapping-section clearfix">
                <div class="row">
                    <div class="col-md-5 col-10">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    Shipping Charges
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="hidden" name="giddh_shipping_account" id="giddhSalesAccounts-shipping" value="<?php echo esc_html(get_option('giddh_shipping_account'));?>">
                                    <input type="hidden" name="giddh_shipping_account_name" id="giddhSalesAccountsName-shipping" value="<?php echo esc_html(get_option('giddh_shipping_account_name'));?>">
                                    <input class="form-control basic giddhSalesAccountsAutocomplete" id="shipping" placeholder="Search" type="text" autocomplete="off" value="<?php echo esc_html(get_option('giddh_shipping_account_name'));?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> 
        </div>
    </div>
    <div class="clearfix">
        <input type="hidden" name="action" value="giddh_settings_service">
        <button type="button" id="mapServices" class="btn btn-custom">Save</button>
    </div>
</form>    