<?php
class GiddhProductsModel {

    public function saveWoocommerceProduct($parameters) {
        try {
            global $wpdb;

            $wpdb->insert($wpdb->prefix."asg_woocommerce_products", $parameters);
            if($wpdb->insert_id) {
                return true;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function getWoocommerceProduct($parameters) {
        global $wpdb;

        try {
            $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."asg_woocommerce_products WHERE product_id=%d ORDER BY id ASC", $parameters["product_id"]), ARRAY_A);
            if($results) {
                return $results;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function deleteWoocommerceProductByVariantId($parameters) {
        try {
            global $wpdb;

            $wpdb->delete($wpdb->prefix."asg_woocommerce_products", $parameters);
            return true;
        } catch(Exception $e) {
            return false;
        }
    }

    public function updateWoocommerceProductByVariantId($parameters, $variant_id) {
        try {
            global $wpdb;

            $wpdb->update( 
                $wpdb->prefix."asg_woocommerce_products", 
                $parameters, 
                array('variant_id' => $variant_id)
            );

            return true;
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

    public function saveGiddhProduct($parameters) {
        try {
            global $wpdb;

            $wpdb->insert($wpdb->prefix."asg_giddh_products", $parameters);
            if($wpdb->insert_id) {
                return true;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function getGiddhProduct($parameters) {
        global $wpdb;

        try {
            $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."asg_giddh_products WHERE unique_name=%s", $parameters["unique_name"]), ARRAY_A);
            if($result) {
                return $result;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function getMatchedProductCount($parameters) {
        try {
            global $wpdb;

            $result = $wpdb->get_row($wpdb->prepare("SELECT count(*) AS total FROM ".$wpdb->prefix."asg_woocommerce_products AS woocommerce_product LEFT JOIN ".$wpdb->prefix."asg_giddh_products AS giddh_products ON giddh_products.sku = woocommerce_product.sku AND giddh_products.is_saved=0 WHERE giddh_products.sku IS NOT NULL AND woocommerce_product.is_saved=0"), ARRAY_A);
            if($result) {
                return $result;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function getUnmatchedGiddhProductCount($parameters) {
        try {
            global $wpdb;

            $result = $wpdb->get_row($wpdb->prepare("SELECT count(*) as total FROM ".$wpdb->prefix."asg_giddh_products AS giddh_products LEFT JOIN ".$wpdb->prefix."asg_woocommerce_products AS woocommerce_product ON woocommerce_product.sku = giddh_products.sku AND woocommerce_product.is_saved=0 WHERE woocommerce_product.sku IS NULL AND giddh_products.is_saved=0"), ARRAY_A);
            if($result) {
                return $result;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function getUnmatchedWoocommerceProductCount($parameters) {
        try {
            global $wpdb;

            $result = $wpdb->get_row($wpdb->prepare("SELECT count(*) as total FROM ".$wpdb->prefix."asg_woocommerce_products AS woocommerce_product LEFT JOIN ".$wpdb->prefix."asg_giddh_products AS giddh_products ON giddh_products.sku = woocommerce_product.sku AND giddh_products.is_saved=0 WHERE giddh_products.sku IS NULL AND woocommerce_product.is_saved=0"), ARRAY_A);
            if($result) {
                return $result;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function getMatchedProduct($parameters) {
        try {
            global $wpdb;

            $results = $wpdb->get_results($wpdb->prepare("SELECT woocommerce_product.*,giddh_products.title as matched_title,giddh_products.unique_name as matched_unique_name FROM ".$wpdb->prefix."asg_woocommerce_products AS woocommerce_product LEFT JOIN ".$wpdb->prefix."asg_giddh_products AS giddh_products ON giddh_products.sku = woocommerce_product.sku AND giddh_products.is_saved=0 WHERE giddh_products.sku IS NOT NULL AND woocommerce_product.is_saved=0 ORDER BY woocommerce_product.title ASC LIMIT %d OFFSET %d", GIDDH_PAGINATION_LIMIT, $parameters["offset"]), ARRAY_A);
            if($results) {
                return $results;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function getUnmatchedGiddhProduct($parameters) {
        try {
            global $wpdb;

            $results = $wpdb->get_results($wpdb->prepare("SELECT giddh_products.* FROM ".$wpdb->prefix."asg_giddh_products AS giddh_products LEFT JOIN ".$wpdb->prefix."asg_woocommerce_products AS woocommerce_product ON woocommerce_product.sku = giddh_products.sku AND woocommerce_product.is_saved=0 WHERE woocommerce_product.sku IS NULL AND giddh_products.is_saved=0 ORDER BY giddh_products.title ASC LIMIT %d OFFSET %d", GIDDH_PAGINATION_LIMIT, $parameters["offset"]), ARRAY_A);
            if($results) {
                return $results;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function getUnmatchedWoocommerceProduct($parameters) {
        try {
            global $wpdb;

            $results = $wpdb->get_results($wpdb->prepare("SELECT woocommerce_product.* FROM ".$wpdb->prefix."asg_woocommerce_products AS woocommerce_product LEFT JOIN ".$wpdb->prefix."asg_giddh_products AS giddh_products ON giddh_products.sku = woocommerce_product.sku AND giddh_products.is_saved=0 WHERE giddh_products.sku IS NULL AND woocommerce_product.is_saved=0 ORDER BY woocommerce_product.title ASC LIMIT %d OFFSET %d", GIDDH_PAGINATION_LIMIT, $parameters["offset"]), ARRAY_A);
            if($results) {
                return $results;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function getTotalWoocommerceProductsCount($parameters) {
        try {
            global $wpdb;

            $result = $wpdb->get_row($wpdb->prepare("SELECT count(*) as total FROM ".$wpdb->prefix."asg_woocommerce_products WHERE is_saved=0"), ARRAY_A);
            if($result) {
                return $result;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function getTotalGiddhProductsCount($parameters) {
        try {
            global $wpdb;

            $result = $wpdb->get_row($wpdb->prepare("SELECT count(*) as total FROM ".$wpdb->prefix."asg_giddh_products WHERE is_saved=0"), ARRAY_A);
            if($result) {
                return $result;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function searchWoocommerceProducts($parameters) {
        try {
            global $wpdb;

            $results = $wpdb->get_results($wpdb->prepare("SELECT id as uniqueName,title as label,title as value FROM ".$wpdb->prefix."asg_woocommerce_products WHERE LOWER(title) LIKE %s AND is_saved=0 ORDER BY title ASC LIMIT 10 OFFSET 0", '%'.strtolower($parameters['q']).'%'), ARRAY_A);
            if($results) {
                return $results;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function searchGiddhProducts($parameters) {
        try {
            global $wpdb;

            $results = $wpdb->get_results($wpdb->prepare("SELECT unique_name as uniqueName,title as label,title as value FROM ".$wpdb->prefix."asg_giddh_products WHERE LOWER(title) LIKE %s AND is_saved=0 ORDER BY title ASC LIMIT 10 OFFSET 0", '%'.strtolower($parameters['q']).'%'), ARRAY_A);
            if($results) {
                return $results;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function updateWoocommerceProductSkuById($parameters) {
        try {
            global $wpdb;

            $wpdb->update( 
                $wpdb->prefix."asg_woocommerce_products", 
                array(
                    'sku' => $parameters["sku"],
                    'is_saved' => 1
                ), 
                array('id' => $parameters["id"])
            );

            return true;
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

    public function updateGiddhProductSkuByUniqueName($parameters) {
        // try {
        //     $this->con = $this->db->connect();
        //     $query = $this->con->prepare("UPDATE giddh_products SET sku=:sku,is_saved=1 WHERE shop_id=:shop_id AND unique_name=:unique_name");
        //     $query->bindParam(':sku', $parameters['sku']);
        //     $query->bindParam(':shop_id', $parameters['shop_id']);
        //     $query->bindParam(':unique_name', $parameters['unique_name']);
        //     $query->execute();
        //     if($query && $query->errorCode() == "00000") {
        //         $this->con = null;
        //         return true;
        //     } else {
        //         $this->con = null;
        //         return false;
        //     }
        // } catch(Exception $e) {
        //     $this->con = null;
        //     return $e->getMessage();
        // }
    }

    public function updateGiddhProductSkuById($parameters) {
        try {
            global $wpdb;

            $wpdb->update( 
                $wpdb->prefix."asg_giddh_products", 
                array(
                    'sku' => $parameters["sku"],
                    'is_saved' => 1
                ), 
                array('id' => $parameters["id"])
            );

            return true;
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

    public function getWoocommerceProductById($parameters) {
        try {
            global $wpdb;

            $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."asg_woocommerce_products WHERE id=%d", $parameters['id']), ARRAY_A);
            if($result) {
                return $result;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function getGiddhProductById($parameters) {
        try {
            global $wpdb;

            $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."asg_giddh_products WHERE id=%d", $parameters['id']), ARRAY_A);
            if($result) {
                return $result;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function getWoocommerceProductByVariantId($variant_id) {
        try {
            global $wpdb;

            $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."asg_woocommerce_products WHERE variant_id=%s", $variant_id), ARRAY_A);
            if($result) {
                return $result;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function updateGiddhProductSavedStatusBySku($parameters) {
        try {
            global $wpdb;

            $wpdb->update( 
                $wpdb->prefix."asg_giddh_products", 
                array("is_saved" => $parameters['is_saved']), 
                array('sku' => $parameters['sku'])
            );

            return true;
        } catch(Exception $e) {
            $this->con = null;
            return $e->getMessage();
        }
    }

    public function updateWoocommerceProductSavedStatusBySku($parameters) {
        try {
            global $wpdb;

            $wpdb->update( 
                $wpdb->prefix."asg_woocommerce_products", 
                array("is_saved" => $parameters['is_saved']), 
                array('sku' => $parameters['sku'])
            );

            return true;
        } catch(Exception $e) {
            $this->con = null;
            return $e->getMessage();
        }
    }

    public function deleteGiddhProductBySku($parameters) {
        try {
            global $wpdb;

            $wpdb->delete($wpdb->prefix."asg_giddh_products", $parameters);
            return true;
        } catch(Exception $e) {
            return false;
        }
    }

    public function deleteWoocommerceProductBySku($parameters) {
        try {
            global $wpdb;

            $wpdb->delete($wpdb->prefix."asg_woocommerce_products", $parameters);
            return true;
        } catch(Exception $e) {
            return false;
        }
    }

    public function deleteGiddhProductByUniqueName($parameters) {
        try {
            global $wpdb;

            $wpdb->delete($wpdb->prefix."asg_giddh_products", $parameters);
            return true;
        } catch(Exception $e) {
            return false;
        }
    }

    public function saveWoocommerceTempProduct($parameters) {
        try {
            global $wpdb;

            $wpdb->insert($wpdb->prefix."asg_woocommerce_temp_products", $parameters);
            if($wpdb->insert_id) {
                return true;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function getWoocommerceTempProduct($productSku) {
        global $wpdb;

        try {
            $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."asg_woocommerce_temp_products WHERE product_sku=%s", $productSku), ARRAY_A);
            if($result) {
                return $result;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function deleteWoocommerceProductById($parameters) {
        try {
            global $wpdb;

            $wpdb->delete($wpdb->prefix."asg_woocommerce_products", $parameters);
            return true;
        } catch(Exception $e) {
            return false;
        }
    }

    public function updateWoocommerceProductById($parameters, $id) {
        try {
            global $wpdb;

            $wpdb->update( 
                $wpdb->prefix."asg_woocommerce_products", 
                $parameters, 
                array('product_id' => $id)
            );

            return true;
        } catch(Exception $e) {
            echo $e->getMessage(); die;
        }
    }

    public function getUnmatchedGiddhProductCountWithoutSku() {
        try {
            global $wpdb;

            $result = $wpdb->get_row($wpdb->prepare("SELECT count(*) as total FROM ".$wpdb->prefix."asg_giddh_products AS giddh_products LEFT JOIN ".$wpdb->prefix."asg_woocommerce_products AS woocommerce_product ON woocommerce_product.sku = giddh_products.sku AND woocommerce_product.is_saved=0 WHERE woocommerce_product.sku IS NULL AND giddh_products.is_saved=0 AND giddh_products.sku IS NULL"), ARRAY_A);
            if($result) {
                return $result;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function getUnmatchedWoocommerceProductCountWithoutSku() {
        try {
            global $wpdb;

            $result = $wpdb->get_row($wpdb->prepare("SELECT count(*) as total FROM ".$wpdb->prefix."asg_woocommerce_products AS woocommerce_product LEFT JOIN ".$wpdb->prefix."asg_giddh_products AS giddh_products ON giddh_products.sku = woocommerce_product.sku AND giddh_products.is_saved=0 WHERE giddh_products.sku IS NULL AND woocommerce_product.is_saved=0 AND woocommerce_product.sku IS NULL"), ARRAY_A);
            if($result) {
                return $result;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function updateWoocommerceProductVariationStock($parameters, $variant_id) {
        try {
            global $wpdb;

            $wpdb->update( 
                $wpdb->prefix."postmeta", 
                $parameters,
                array('post_id' => $variant_id, "meta_key" => "_stock")
            );

            return true;
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }
}
?>