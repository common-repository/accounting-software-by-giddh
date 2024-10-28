<?php
class GiddhInvoiceModel {

    public function saveInvoice($parameters) {
        try {
            global $wpdb;

            $wpdb->insert($wpdb->prefix."asg_invoices", $parameters);
            if($wpdb->insert_id) {
                return true;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function getInvoice($parameters) {
        try {
            global $wpdb;
            
            $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."asg_invoices WHERE woocommerce_order_id=%s", $parameters["woocommerce_order_id"]), ARRAY_A);
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