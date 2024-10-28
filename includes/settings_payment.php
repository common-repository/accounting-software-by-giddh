<?php
$paymentJs = "var giddhBankAccounts, woocommercePaymentGateways, savedPayments = false;";

$giddhApi = new GiddhApi();
$giddhBankAccounts = $giddhApi->getBankAccounts(get_option('giddh_company_unique_name'), get_option('giddh_company_auth_key'));
if($giddhBankAccounts && $giddhBankAccounts['status'] == "success" && $giddhBankAccounts['body'] && count($giddhBankAccounts['body']) > 0) {
    $paymentJs .= " giddhBankAccounts = ".json_encode($giddhBankAccounts['body']).";";
}

$paymentsModel = new GiddhPaymentsModel();
$woocommercePaymentGateways = $paymentsModel->getWoocommercePaymentGateways();
if($woocommercePaymentGateways && count($woocommercePaymentGateways) > 0) {
    $paymentJs .= "woocommercePaymentGateways = ".json_encode($woocommercePaymentGateways).";";
}

$getAllPaymentGateways = $paymentsModel->getAllPaymentGateways();
if($getAllPaymentGateways) {
    $paymentsArray = array();
    foreach($getAllPaymentGateways as $getAllPaymentGateway) {
        $paymentsArray[$getAllPaymentGateway['woocommerce_payment_id']] = $getAllPaymentGateway['giddh_account_id'];
    }
    $paymentJs .= "savedPayments = ".json_encode($paymentsArray).";";
}

wp_register_script('payment', '');
wp_enqueue_script('payment');
wp_add_inline_script('payment', $paymentJs);
?>
<div class="white-box">
    <form id="paymentsForm">
        <div class="setting-height">
            <h4 class="heading">Payment</h4>
            <p>Link Woocommerce Payment with Giddh.</p>
            <div class="row">
                <div class="col-md-5">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="font-12 text-light d-block mb-1">Woocommerce Payment Method </p>
                        </div>
                        <div class="col-md-6">
                            <p class="font-12 text-light d-block mb-1">Giddh Bank Account </p>
                        </div>
                    </div>
                </div>
            </div>
            <div id="payment-mappings">
            <?php
            if($getAllPaymentGateways) {
                foreach($getAllPaymentGateways as $getAllPaymentGateway) {
            ?>
                <div class="payment-mapping-section clearfix">
                    <div class="row">
                        <div class="col-md-5 col-10">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <select class="dropdowns woocommercePaymentGateways js-data-example-ajax" name="woocommercePaymentGateways[]">
                                        <?php
                                        if($woocommercePaymentGateways && count($woocommercePaymentGateways) > 0) {
                                            foreach($woocommercePaymentGateways as $woocommercePaymentGateway) {
                                        ?>
                                                <option value="<?php echo esc_html($woocommercePaymentGateway['id']);?>" <?php if($getAllPaymentGateway['woocommerce_payment_id'] == $woocommercePaymentGateway['id']) { echo "selected"; } ?>><?php echo esc_html($woocommercePaymentGateway['name']);?></option>
                                        <?php
                                            }
                                        }
                                        ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <select class="dropdowns giddhAccounts" name="giddhAccounts[]">
                                            <option selected value="">Select Payment</option>
                                            <option value="create">Create Payment</option>
                                            <?php
                                            if($giddhBankAccounts && $giddhBankAccounts['status'] == "success" && $giddhBankAccounts['body'] && count($giddhBankAccounts['body']) > 0) { 
                                                foreach($giddhBankAccounts['body'] as $giddhBankAccount) {    
                                            ?>
                                                    <option value="<?php echo esc_html($giddhBankAccount['uniqueName']);?>" <?php if($getAllPaymentGateway['giddh_account_id'] == $giddhBankAccount['uniqueName']) { echo "selected"; } ?>><?php echo esc_html(stripslashes($giddhBankAccount['name']));?></option>
                                            <?php
                                                }
                                            }
                                            ?>  
                                        </select>  
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-2 col-2 pl-0 pt-1">
                            <input type="hidden" name="mappingId[]" class="paymentMappingId" value="<?php echo esc_html($getAllPaymentGateway["id"]); ?>">
                            <input type="hidden" name="removedMappings[]" class="removedMappings" value="0">
                            <a href="javascript:;" class="remove-payment-mapping icon-remove"><i class="fa fa-times"></i></a>
                        </div>
                    </div>
                </div> 
            <?php
                }
            }
            ?>
                <div class="payment-mapping-section">
                    <div class="row">
                        <div class="col-md-5 col-10">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <select class="dropdowns woocommercePaymentGateways unsaved" name="woocommercePaymentGateways[]">
                                        <option selected value="">Select Payment</option>
                                        <?php
                                        if($woocommercePaymentGateways && count($woocommercePaymentGateways) > 0) {
                                            foreach($woocommercePaymentGateways as $woocommercePaymentGateway) {
                                        ?>
                                                <option value="<?php echo esc_html($woocommercePaymentGateway['id']);?>"><?php echo esc_html($woocommercePaymentGateway['name']);?></option>
                                        <?php
                                            }
                                        }
                                        ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <select class="dropdowns giddhAccounts" name="giddhAccounts[]">
                                            <option selected value="">Select Payment</option>
                                            <option value="create">Create Payment</option>
                                            <?php
                                            if($giddhBankAccounts && $giddhBankAccounts['status'] == "success" && $giddhBankAccounts['body'] && count($giddhBankAccounts['body']) > 0) { 
                                                foreach($giddhBankAccounts['body'] as $giddhBankAccount) {    
                                            ?>
                                                    <option value="<?php echo esc_html($giddhBankAccount['uniqueName']);?>"><?php echo esc_html(stripslashes($giddhBankAccount['name']));?></option>
                                            <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-2 col-2 pl-0 pt-1">
                            <div class="pl-0">
                                <input type="hidden" name="mappingId[]" class="paymentMappingId">
                                <input type="hidden" name="removedMappings[]" class="removedMappings" value="0">
                                <a href="javascript:;" class="remove-payment-mapping icon-remove" data=""><i class="fa fa-times"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix">
            <input type="hidden" name="action" value="giddh_settings_payment">                             
            <button type="button" id="addMorePaymentGateway" class="btn btn-outline mr-2">Add More</button>
            <button type="button" id="mapPaymentGateways" class="btn btn-custom">Save</button>
        </div>
    </form>    
</div>