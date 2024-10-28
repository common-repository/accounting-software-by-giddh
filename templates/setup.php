<?php
include(GIDDH_PLUGIN_PATH."includes/header.php");

wp_register_script('setup', '');
wp_enqueue_script('setup');
wp_add_inline_script('setup', 'jQuery(document).ready(function() { initSetup(); });');
?>
<section class="faq-main mt-2">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-6">
                    <h2 class="heading-2">Setup</h2>
                </div>
            </div>
            <div class="white-box faq-height">
                <div class="row">
                    <div class="col-md-10">
                        <ul class="setup">
                            <li class="s1">Save Woocommerce Categories</li>
                            <li class="s2">Save Woocommerce Products</li>
                            <li class="s3">Save Woocommerce Payment Gateways</li>
                            <li class="s4">Save Giddh Products</li>
                        </ul>
                    </div>
                </div>
                <div class="copyright">
                    <p class="text-light font-12">Powered by <a href="https://giddh.com" rel="nofollow" target="_blank">giddh</a> | <span class="font-16">&copy;</span> <?php echo date("Y"); ?> . All Rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
include(GIDDH_PLUGIN_PATH."includes/footer.php");
?>