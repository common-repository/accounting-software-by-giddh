<?php
class GiddhCustomersModel {

    public function saveCustomer($parameters) {
        try {
            global $wpdb;

            $wpdb->insert($wpdb->prefix."asg_customers", $parameters);
            if($wpdb->insert_id) {
                return true;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function getCustomer($parameters) {
        try {
            global $wpdb;
            
            $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."asg_customers WHERE woocommerce_customer_id=%s", $parameters["woocommerce_customer_id"]), ARRAY_A);
            if($result) {
                return $result;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }
}
?>