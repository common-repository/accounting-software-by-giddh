<?php
function giddhSendMail($parameters) {
    if(get_option("giddh_email_method") == "sendgrid" && get_option("giddh_sendgrid_api_key")) {
        $params = array(
            "personalizations" => array(
                array(
                    "to" => array(
                        array(
                            "email" => $parameters['to']
                        )
                    ),
                    "subject" => $parameters['subject']
                )
            ),
            "from" => array(
                "email" => "support@giddh.com"
            ),
            "content" => array(
                array(
                    "type" => "text/html",
                    "value" => $parameters['message']
                )
            )
        );

        wp_remote_post('https://api.sendgrid.com/v3/mail/send', array(
            'body' => json_encode($params),
            'headers' => array('Content-Type' => 'application/json', 'Content-Length' => strlen(json_encode($params)), 'Authorization' => 'Bearer ' . get_option("giddh_sendgrid_api_key"))
        ));
    } else {
        $to = $parameters['to'];
        $subject = $parameters['subject'];
        $message = $parameters['message'];
        
        $headers = "From:support@giddh.com \r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html\r\n";
        
        mail($to, $subject, $message, $headers);
    }
}

function giddhGetWelcomeTemplate($parameters) {
    $electronVersion = giddhGetElectronVersion();
    $template = wp_remote_get(GIDDH_SITE_URL.'/emailtemplates/welcome.html');
    $templateBody = $template['body'];
    $templateBody = str_replace(array("{{CUSTOMER_NAME}}", "{{GIDDH_SITE_URL}}", "{{ELECTRON_VERSION}}"), array($parameters['customerName'], GIDDH_SITE_URL, $electronVersion), $templateBody);
    return $templateBody;
}

function giddhGetUninstallTemplate($parameters) {
    $electronVersion = giddhGetElectronVersion();
    $template = wp_remote_get(GIDDH_SITE_URL.'/emailtemplates/uninstall.html');
    $templateBody = $template['body'];
    $templateBody = str_replace(array("{{CUSTOMER_NAME}}", "{{WORDPRESS_APP_URL}}", "{{GIDDH_SITE_URL}}", "{{ELECTRON_VERSION}}"), array($parameters['customerName'], GIDDH_WORDPRESS_APP_URL, GIDDH_SITE_URL, $electronVersion), $templateBody);
    return $templateBody;
}

function giddhGetErrorTemplate($parameters) {
    $electronVersion = giddhGetElectronVersion();
    $template = wp_remote_get(GIDDH_SITE_URL.'/emailtemplates/error.html');
    $templateBody = $template['body'];
    $templateBody = str_replace(array("{{CUSTOMER_NAME}}", "{{ERROR_MESSAGE}}", "{{GIDDH_SITE_URL}}", "{{ELECTRON_VERSION}}"), array($parameters['customerName'], $parameters['errorMessage'], GIDDH_SITE_URL, $electronVersion), $templateBody);
    return $templateBody;
}

function giddhGetDisconnectTemplate($parameters) {
    $electronVersion = giddhGetElectronVersion();
    $template = wp_remote_get(GIDDH_SITE_URL.'/emailtemplates/disconnected.html');
    $templateBody = $template['body'];
    $templateBody = str_replace(array("{{CUSTOMER_NAME}}", "{{GIDDH_SITE_URL}}", "{{ELECTRON_VERSION}}"), array($parameters['customerName'], GIDDH_SITE_URL, $electronVersion), $templateBody);
    return $templateBody;
}
?>