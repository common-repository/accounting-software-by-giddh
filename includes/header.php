<?php
$giddhConnected = false;
if(get_option('giddh_company_unique_name') && get_option('giddh_company_auth_key') && get_option('giddh_shop_unique_name')) { 
    $giddhConnected = true;
}
?>
<header class="header-top">
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between">
            <a href="<?php echo GIDDH_PLUGIN_URL;?>&view=settings" class="logo">
                <img src="<?php echo plugins_url('assets/images/white-logo.svg', dirname(__FILE__)); ?>" alt="Giddh">
            </a>
        </div>
    </div>
</header>

<div class="header d-flex align-items-center">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <ul class="nav nav-pills custom-nav font-semibold">
                <?php
                if($giddhConnected) {
                ?>
                <li>
                    <a <?php if(!$_GET['view'] || $_GET['view'] == "settings") { ?>class="active"<?php } ?> href="<?php echo GIDDH_PLUGIN_URL;?>&view=settings">Settings</a>
                </li>
                <?php } else { ?>
                <li>
                    <a <?php if($_GET['view'] == "connect") { ?>class="active"<?php } ?> href="<?php echo GIDDH_PLUGIN_URL;?>&view=connect">Connect</a>
                </li>
                <?php } ?>
                <li>
                    <a <?php if($_GET['view'] == "faq") { ?>class="active"<?php } ?> href="<?php echo GIDDH_PLUGIN_URL;?>&view=faq">FAQ</a>
                </li>
            </ul>
        </div>
    </div>
</div>
<div class="updated notice">
    &nbsp;
</div>
<div class="error notice">
    &nbsp;
</div>