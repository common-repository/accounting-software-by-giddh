<div class="white-box">
    <form id="settingsForm">
        <div class="setting-height">
            <h4 class="heading">Invoice</h4>
            <div class="mb-5">
                <p>When do you want to create Customer in Giddh? <span class="text-gray font-12">(Note: we will automatically create Customer when below selected stage fulfils)</span></p>
                <div>
                    <input id="create_customer_account_1" class="radio-custom" name="create_customer_account" type="radio" value="yes" <?php if(get_option('giddh_create_customer_account') == "yes") { echo "checked"; } ?>>
                    <label for="create_customer_account_1" class="radio-custom-label">Always create customer ledger/account in Giddh.</label>
                </div>
                <div>
                    <input id="create_customer_account_2" class="radio-custom" name="create_customer_account" type="radio" value="no" <?php if(get_option('giddh_create_customer_account') == "no") { echo "checked"; } ?>>
                    <label for="create_customer_account_2" class="radio-custom-label">Do not create customer account automatically. (Recommended)
                    <span class="d-block font-12 text-gray pl-4">(Note: Generates Cash/Bank Invoice)</span>
                    </label>
                </div>
            </div>
            <div class="mb-2">
                <p>When to create Invoice in Giddh? <span class="text-gray font-12">(Note: we will automatically create invoice when below selected stage fulfils)</span></p>
                <div>
                    <input id="create_invoice_1" class="radio-custom" name="create_invoice" type="radio" value="ready_to_ship" <?php if(get_option('giddh_create_invoice') == "ready_to_ship") { echo "checked"; } ?>>
                    <label for="create_invoice_1" class="radio-custom-label">When order is ready to ship. (Recommended)</label>
                </div>
                <div>
                    <input id="create_invoice_2" class="radio-custom" name="create_invoice" type="radio" value="order_paid" <?php if(get_option('giddh_create_invoice') == "order_paid") { echo "checked"; } ?>>
                    <label for="create_invoice_2" class="radio-custom-label">At the time payment received.
                    </label>
                </div>
                <div>
                    <input id="create_invoice_3" class="radio-custom" name="create_invoice" type="radio" value="order_create" <?php if(get_option('giddh_create_invoice') == "order_create") { echo "checked"; } ?>>
                    <label for="create_invoice_3" class="radio-custom-label">When order confirms.</label>
                </div>
            </div>
        </div>
        <div class="clearfix">
            <input type="hidden" name="action" value="giddh_settings_invoice">
            <button type="button" id="saveSettings" class="btn btn-custom">Save</button>
        </div>
    </form>    
</div>