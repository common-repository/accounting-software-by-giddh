<?php
include(GIDDH_PLUGIN_PATH."includes/header.php");
include(GIDDH_PLUGIN_PATH."includes/welcome.php");
?>
<section class="pt-5">
    <div class="connect-onboard">
        <p class="font-20 mb-5 tac">Enter following details to connect</p>

        <div class="form-group mb-3">
            <label>Auth Key<i class="text-danger">*</i></label>
            <input type="text" class="form-control" id="authKey" placeholder="Auth Key">
            <div class="text-right">
                <a href="<?php echo GIDDH_APP_URL; ?>/pages/user-details/auth-key" target="_blank">Get auth key</a>
            </div>
        </div>
        <div class="form-group mb-3">
            <label>Company Unique Name<i class="text-danger">*</i></label>
            <input type="text" class="form-control" id="companyUniqueName" placeholder="Company Unique Name" value="<?php echo esc_html(get_option('giddh_company_unique_name'));?>">
            <div class="text-right">
                <a href="<?php echo GIDDH_APP_URL; ?>/pages/settings/profile" target="_blank">Get company unique name</a>
            </div>
        </div>
        <div class="d-flex mt-4">
            <a href="<?php echo GIDDH_PLUGIN_URL;?>&view=onboarding" class="ripple-effect btn-gray-lg btn btn-lg mr-3">Back</a>
            <a href="javascript:;" id="connectGiddh" class="ripple-effect btn-primary-lg btn btn-lg width-100">Connect to Giddh</a>
        </div>
    </div>
</section>
<?php
include(GIDDH_PLUGIN_PATH."includes/footer.php");
?>