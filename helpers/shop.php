<?php
function giddhDisconnectApp() {
    global $wpdb;

    if(get_option('giddh_company_unique_name') && get_option('giddh_shop_unique_name')) {
        $giddhApi = new GiddhApi();
        $giddhApi->disconnectAccount(get_option('giddh_company_unique_name'), get_option('giddh_shop_unique_name'), array("source" => "wordpress"));
    }

    update_option('giddh_company_auth_key', '');
    update_option('giddh_shop_unique_name', '');

    $currentUser = wp_get_current_user();
    $customerName = ($currentUser->display_name) ? $currentUser->display_name : $currentUser->user_login;

    $template = giddhGetDisconnectTemplate(array("customerName" => $customerName));
    giddhSendMail(array("to" => $currentUser->user_email, "toName" => $customerName, "subject" => GIDDH_DISCONNECT_EMAIL_SUBJECT, "message" => $template));

    wp_redirect(add_query_arg(array('page' => 'giddh', 'view' => 'onboarding')));
    exit();
}
?>