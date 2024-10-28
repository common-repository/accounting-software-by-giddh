<?php
class GiddhCategoriesModel {

    public function saveCategory($parameters) {
        try {
            global $wpdb;

            $wpdb->insert($wpdb->prefix."asg_categories", $parameters);
            if($wpdb->insert_id) {
                return true;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function updateCategory($parameters) {
        try {
            global $wpdb;

            $wpdb->update( 
                $wpdb->prefix."asg_categories", 
                array(
                    'giddh_stock_group_id' => $parameters["giddh_stock_group_id"],
                    'giddh_stock_group_name' => $parameters["giddh_stock_group_name"]
                ), 
                array('id' => $parameters["id"])
            );

            return true;
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

    public function getCategory($parameters) {
        try {
            global $wpdb;
            
            $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."asg_categories WHERE woocommerce_category_id=%s or giddh_stock_group_id=%s", $parameters["woocommerce_category_id"], $parameters['giddh_stock_group_id']), ARRAY_A);
            if($result) {
                return $result;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function getCategoryByWoocommerceCategoryId($parameters) {
        try {
            global $wpdb;
            
            $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."asg_categories WHERE woocommerce_category_id=%s", $parameters["woocommerce_category_id"]), ARRAY_A);
            if($result) {
                return $result;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function getCategoryByGiddhStockGroupId($parameters) {
        try {
            global $wpdb;
            
            $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."asg_categories WHERE giddh_stock_group_id=%s", $parameters["giddh_stock_group_id"]), ARRAY_A);
            if($result) {
                return $result;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function deleteCategory($parameters) {
        try {
            global $wpdb;

            $wpdb->delete($wpdb->prefix."asg_categories", $parameters);
            return true;
        } catch(Exception $e) {
            return false;
        }
    }

    public function getAllCategories($parameters) {
        try {
            global $wpdb;
            
            $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."asg_categories ORDER BY woocommerce_category_name ASC LIMIT %d OFFSET %d", GIDDH_PAGINATION_LIMIT, $parameters["offset"]), ARRAY_A);
            if($results) {
                return $results;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    public function updateCategoryById($parameters) {
        try {
            global $wpdb;

            $wpdb->update( 
                $wpdb->prefix."asg_categories", 
                array(
                    'woocommerce_category_name' => $parameters["woocommerce_category_name"]
                ), 
                array('woocommerce_category_id' => $parameters["woocommerce_category_id"])
            );

            return true;
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

    public function getAllCategoriesCount($parameters) {
        try {
            global $wpdb;
            
            $result = $wpdb->get_row($wpdb->prepare("SELECT count(*) as total FROM ".$wpdb->prefix."asg_categories"), ARRAY_A);
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