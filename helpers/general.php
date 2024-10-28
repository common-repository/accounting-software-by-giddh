<?php
function giddhCreateLog($file, $data) {
    if(GIDDH_DEBUG_MODE) {
        $upload_dir = wp_upload_dir();
        file_put_contents($upload_dir['basedir'].'/'.$file, date("Y-m-d H:i:s").'::'.$data."\n", FILE_APPEND);
    }
}

function giddhActivationError($message) {
    ?>
    <!doctype html>
    <html>
    <head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <style>
        * {
            margin:0px;
            padding:0px;
        }
        p {
            font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
            font-weight: 600;
            font-size:14px;
        }
    </style>
    </head>
    <body>
    <p><?php echo esc_html($message); ?></p>
    </body>
    </html>
    <?php
}

function giddhFormatPrice($price) {
    $price = trim(strip_tags(wc_price($price, array("currency" => " "))));
    return str_replace(",", "", $price);
}

function giddhCheckNestedGroups($group, $results, $accounts) {
    if($group['accounts'] && count($group['accounts']) > 0) {
        foreach($group['accounts'] as $account) {
            if(count($results) < 10 && !in_array($account["uniqueName"], $accounts)) {
                $results[] = array("label" => $account["name"], "value" => $account["name"], "uniqueName" => $account["uniqueName"]);
                $accounts[] = $account["uniqueName"];
            }
        }
    }

    if($group['groups'] && count($group['groups']) > 0) {
        foreach($group['groups'] as $innerGroup) {
            if(count($results) < 10) {
                $results = giddhCheckNestedGroups($innerGroup, $results, $accounts);
            }
        }
    }
    return $results;
}

function giddhLoadScripts() {
    wp_enqueue_style('bootstrap', plugins_url('assets/css/vendor/bootstrap.min.css', dirname(__FILE__)));
    wp_enqueue_style('select2', plugins_url('assets/css/vendor/select2.min.css', dirname(__FILE__)));
    wp_enqueue_style('confirm', plugins_url('assets/css/jquery-confirm.min.css', dirname(__FILE__)));
    wp_enqueue_style('font-awesome', plugins_url('assets/css/vendor/font-awesome-4.7.0/css/font-awesome.min.css', dirname(__FILE__)));
    wp_enqueue_style('icomoon', plugins_url('assets/fonts/icomoon/style.css', dirname(__FILE__)));
    wp_enqueue_style('custom', plugins_url('assets/css/style.css', dirname(__FILE__)));

    wp_enqueue_script('bootstrap', plugins_url('assets/js/vendor/bootstrap.min.js', dirname(__FILE__)));
    wp_enqueue_script('popper', plugins_url('assets/js/vendor/popper.min.js', dirname(__FILE__)));
    wp_enqueue_script('select2', plugins_url('assets/js/vendor/select2.min.js', dirname(__FILE__)));
    wp_enqueue_script('confirm', plugins_url('assets/js/jquery-confirm.min.js', dirname(__FILE__)));
    wp_enqueue_script('jquery-ui-autocomplete');
    wp_enqueue_script('custom', plugins_url('assets/js/app.js?t='.time(), dirname(__FILE__)));
}
?>