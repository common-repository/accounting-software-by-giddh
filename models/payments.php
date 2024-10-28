<?php
class GiddhPaymentsModel {

    public function saveAvailablePaymentGateway($parameters) {
        try {
            global $wpdb;

            $wpdb->insert($wpdb->prefix."asg_available_payment_gateways", $parameters);
            if($wpdb->insert_id) {
                return true;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function checkIfPaymentGatewayExists($code) {
        try {
            global $wpdb;
            
            $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."asg_available_payment_gateways WHERE code=%s", $code), ARRAY_A);
            if($result) {
                return $result;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function deleteUnavailablePaymentGateway($codes) {
        try {
            global $wpdb;
            
            $result = $wpdb->query($wpdb->prepare("DELETE FROM ".$wpdb->prefix."asg_available_payment_gateways WHERE code NOT IN(%s)", $codes));
            if($result) {
                return true;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function deleteAllUnavailablePaymentGateway() {
        try {
            global $wpdb;
            
            $result = $wpdb->query($wpdb->prepare("TRUNCATE ".$wpdb->prefix."asg_available_payment_gateways"));
            if($result) {
                return true;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function savePaymentGateway($parameters) {
        try {
            global $wpdb;

            $wpdb->insert($wpdb->prefix."asg_payment_gateways", $parameters);
            if($wpdb->insert_id) {
                return true;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function updatePaymentGateway($parameters) {
        try {
            global $wpdb;

            $wpdb->update( 
                $wpdb->prefix."asg_payment_gateways", 
                array(
                    'woocommerce_payment_id' => $parameters["woocommerce_payment_id"],
                    'giddh_account_id' => $parameters['giddh_account_id']
                ), 
                array('id' => $parameters["id"])
            );

            return true;
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

    public function getPaymentGateway($parameters) {
        try {
            global $wpdb;
            
            $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."asg_payment_gateways WHERE woocommerce_payment_id=%s OR giddh_account_id=%s", $parameters["woocommerce_payment_id"], $parameters['giddh_account_id']), ARRAY_A);
            if($result) {
                return $result;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function getPaymentGatewayByWoocommercePaymentId($parameters) {
        try {
            $woocommercePaymentId = $this->getPaymentGatewayByCode(array("code" => $parameters["woocommerce_payment_id"]));
            if(!$woocommercePaymentId) {
                return false;
            }

            global $wpdb;
            
            $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."asg_payment_gateways where woocommerce_payment_id=%s", $woocommercePaymentId['id']), ARRAY_A);
            if($result) {
                return $result;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function deletePaymentGateway($parameters) {
        try {
            global $wpdb;

            $wpdb->delete($wpdb->prefix."asg_payment_gateways", $parameters);
            return true;
        } catch(Exception $e) {
            return false;
        }
    }

    public function getAllPaymentGateways() {
        try {
            global $wpdb;
            
            $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."asg_payment_gateways"), ARRAY_A);
            if($results) {
                return $results;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function getWoocommercePaymentGateways() {
        try {
            global $wpdb;
            
            $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."asg_available_payment_gateways ORDER BY name ASC"), ARRAY_A);
            if($results) {
                return $results;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function getPaymentGatewayByCode($parameters) {
        try {
            global $wpdb;
            
            $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."asg_available_payment_gateways WHERE code=%s", $parameters['code']), ARRAY_A);
            if($result) {
                return $result;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function getPaymentGatewayById($id) {
        try {
            global $wpdb;
            
            $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."asg_available_payment_gateways WHERE id=%d", $id), ARRAY_A);
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