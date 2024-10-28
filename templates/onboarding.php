<?php
include(GIDDH_PLUGIN_PATH."includes/header.php");
include(GIDDH_PLUGIN_PATH."includes/welcome.php");
?>
<section class="onboard-bottom pt-5">
    <div class="text-center">
        <p class="font-20 mb-5">Connect your Giddh account to WooCommerce</p>
        <ul class="list-inline mb-5 pb-3">
            <li><img src="<?php echo plugins_url('assets/images/woocommerce.png', dirname(__FILE__)); ?>" alt="WooCommerce"></li>
            <li><img src="<?php echo plugins_url('assets/images/arrow.svg', dirname(__FILE__)); ?>" alt="Connect"></li>
            <li><img src="<?php echo plugins_url('assets/images/logo.svg', dirname(__FILE__)); ?>" alt="Giddh"></li>
        </ul>
        <a href="<?php echo GIDDH_PLUGIN_URL;?>&view=connect" class="ripple-effect btn-primary-lg btn btn-lg">Connect to Giddh</a>
        <!-- <div class="clearfix mt-3">
        <span class="font-16 videos-popup"><a href="javascript:void(0)" class="video-btn video-btn-2" data-toggle="modal" data-src="https://www.youtube.com/embed/owxO6p3z-nM" data-target="#myModal"><img src="<?php //echo plugins_url('assets/images/watch.svg', dirname(__FILE__)); ?>" alt="" class="middle mr-1"> Watch Onboarding </a></span>
        </div> -->
    </div>
</section>
<?php
include(GIDDH_PLUGIN_PATH."includes/footer.php");
?>