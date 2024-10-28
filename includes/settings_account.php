<div class="white-box">
    <form id="accountForm">
        <div class="setting-height">
            <h4 class="heading">My Account</h4>
            <div class="form-group row">
                <label class="col-md-2 col-sm-4 col-form-label">Notification email</label>
                <div class="col-md-3 col-sm-6">
                    <input type="text" class="form-control" id="notification_email" name="notification_email" value="<?php echo esc_html(get_option("giddh_notification_email")); ?>">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-2 col-sm-4 col-form-label">Email Method</label>
                <div class="col-md-3 col-sm-6">
                    <span>
                        <input type="radio" class="form-control" id="email_method_php" name="email_method" value="php" <?php if(get_option("giddh_email_method") == "php") { echo "checked"; } ?>>&nbsp;<label class="email_method" for="email_method_php">PHP</label>
                    </span>
                    <span class="ml-1">
                        <input type="radio" class="form-control" id="email_method_sendgrid" name="email_method" value="sendgrid" <?php if(get_option("giddh_email_method") == "sendgrid") { echo "checked"; } ?>>&nbsp;<label class="email_method" for="email_method_sendgrid">Sendgrid</label>
                    </span>
                </div>
            </div>
            <div class="form-group row <?php if(get_option("giddh_email_method") == "sendgrid") { echo "show-sendgrid-key"; } else { echo "hide-sendgrid-key"; } ?>" id="sendgrid_key">
                <label class="col-md-2 col-sm-4 col-form-label">Sendgrid Api Key</label>
                <div class="col-md-3 col-sm-6">
                    <input type="text" class="form-control" id="sendgrid_api_key" name="sendgrid_api_key" value="<?php echo esc_html(get_option("giddh_sendgrid_api_key")); ?>">
                </div>
            </div>
        </div>
        <div class="clearfix">
            <input type="hidden" name="action" value="giddh_settings_account">
            <button type="button" id="saveAccount" class="btn btn-custom">Save</button>
        </div>
    </form>
</div>