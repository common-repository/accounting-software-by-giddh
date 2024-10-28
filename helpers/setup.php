<?php
function giddhSaveWoocommerceCategories() {
    try {
        if(get_option('giddh_save_woocommerce_categories') === 'pending') {
            update_option('giddh_save_woocommerce_categories', 'inprogress');

            $categoriesModel = new GiddhCategoriesModel();

            global $wpdb;

            $args = array(
                'orderby'    => 'name',
                'order'      => 'asc',
                'hide_empty' => false,
                'limit' => -1
            );
            
            $categories = get_terms('product_cat', $args);

            if($categories && count($categories) > 0) {
                foreach($categories as $category) {
                    $parameters = array();
                    $parameters["woocommerce_category_id"] = $category->term_id;

                    $categoryExists = $categoriesModel->getCategoryByWoocommerceCategoryId($parameters);
                    if(!$categoryExists) {
                        $parameters["woocommerce_category_name"] = $category->name;
                        $parameters["giddh_stock_group_id"] = "";
                        $parameters["giddh_stock_group_name"] = "";
                        $categoriesModel->saveCategory($parameters);
                    }
                }
            }

            update_option('giddh_save_woocommerce_categories', 'completed');
        }

        echo json_encode(array("result" => "success"));
        wp_die();
    } catch(Exception $e) {
        update_option('giddh_save_woocommerce_categories', 'pending');

        echo json_encode(array("result" => "error", "message" => $e->getMessage()));
        wp_die();
    }
}

function giddhSaveWoocommerceProducts() {
    try {
        if(get_option('giddh_save_woocommerce_products') === 'pending') {
            update_option('giddh_save_woocommerce_products', 'inprogress');
    
            global $wpdb;
    
            $args = array('status' => 'publish', 'limit' => -1);
            $products = wc_get_products($args);
    
            foreach($products as $product) {
                giddhAddProduct($product, '');
            }
    
            update_option('giddh_save_woocommerce_products', 'completed');
        }
    
        echo json_encode(array("result" => "success"));
        wp_die();
    } catch(Exception $e) {
        update_option('giddh_save_woocommerce_products', 'pending');
    
        echo json_encode(array("result" => "error", "message" => $e->getMessage()));
        wp_die();
    }      
}

function giddhSaveWoocommercePaymentGateways() {
    try {
        if(class_exists('WooCommerce')) {
            if(get_option('giddh_save_woocommerce_payment_gateways') === 'pending') {
                update_option('giddh_save_woocommerce_payment_gateways', 'inprogress');
    
                $paymentGatewaysObj = new WC_Payment_Gateways(); 
                $enabledPaymentGateways = $paymentGatewaysObj->payment_gateways();
    
                $paymentsModel = new GiddhPaymentsModel();
                $paymentsModel->deleteAllUnavailablePaymentGateway();
    
                if($enabledPaymentGateways && count($enabledPaymentGateways) > 0) {
                    foreach($enabledPaymentGateways as $paymentGateway) {
                        $checkIfPaymentGatewayExists = $paymentsModel->checkIfPaymentGatewayExists($paymentGateway->id);
                        if(!$checkIfPaymentGatewayExists) {
                            $paymentsModel->saveAvailablePaymentGateway(array("name" => $paymentGateway->title, "code" => $paymentGateway->id));
                        }
                    }
                }
    
                update_option('giddh_save_woocommerce_payment_gateways', 'completed');
            }
    
            echo json_encode(array("result" => "success"));
            wp_die();
        } else {
            update_option('giddh_save_woocommerce_payment_gateways', 'pending');

            echo json_encode(array("result" => "error", "message" => "WooCommerce Plugin is not installed/active."));
            wp_die();
        }
    } catch(Exception $e) {
        update_option('giddh_save_woocommerce_payment_gateways', 'pending');
        
        echo json_encode(array("result" => "error", "message" => $e->getMessage()));
        wp_die();
    }
}

function giddhSaveGiddhProducts() {
    try {
        if(get_option('giddh_save_giddh_products') === 'pending') {
            update_option('giddh_save_giddh_products', 'inprogress');
    
            $productsModel = new GiddhProductsModel();
            $giddhApi = new GiddhApi();
    
            $giddhProducts = $giddhApi->getAllStock(get_option('giddh_company_unique_name'), get_option('giddh_company_auth_key'), 1);
            if($giddhProducts["status"] == "success" && $giddhProducts["body"] && $giddhProducts["body"]["totalItems"] > 0) {
                $pages = ceil($giddhProducts["body"]["totalItems"] / GIDDH_PAGINATION_LIMIT);
    
                for($loop = 1; $loop <= $pages; $loop++) {
                    if($loop > 1) {
                        $giddhProducts = $giddhApi->getAllStock(get_option('giddh_company_unique_name'), get_option('giddh_company_auth_key'), $loop);
                    }
    
                    if($giddhProducts && $giddhProducts["body"] && $giddhProducts["body"]["results"] && count($giddhProducts["body"]["results"]) > 0) {
                        foreach($giddhProducts["body"]["results"] as $giddhProduct) {
                            $parameters = array();
                            $parameters["unique_name"] = $giddhProduct["uniqueName"];
    
                            $productExists = $productsModel->getGiddhProduct($parameters);
                            if(!$productExists) {
                                $parameters["title"] = $giddhProduct["name"];
                                $parameters["stock_unit_code"] = ($giddhProduct["stockUnit"] && $giddhProduct["stockUnit"]["code"]) ? $giddhProduct["stockUnit"]["code"] : "";
                                $parameters["amount"] = ($giddhProduct["openingQuantity"]) ? ($giddhProduct["amount"] / $giddhProduct["openingQuantity"]) : $giddhProduct["amount"];
                                $parameters["quantity"] = $giddhProduct["openingQuantity"];
                                $parameters["sku"] = $giddhProduct["skuCode"];
                                $parameters["hsn"] = $giddhProduct["hsnNumber"];
                                $parameters["stock_group"] = ($giddhProduct["stockGroup"] && $giddhProduct["stockGroup"]["uniqueName"]) ? $giddhProduct["stockGroup"]["uniqueName"] : "";
                                $productsModel->saveGiddhProduct($parameters);
                            }
                        }
                    }
                }
            }
    
            update_option('giddh_save_giddh_products', 'completed');
        }
    
        echo json_encode(array("result" => "success"));
        wp_die();
    } catch(Exception $e) {
        update_option('giddh_save_giddh_products', 'pending');
    
        echo json_encode(array("result" => "error", "message" => $e->getMessage()));
        wp_die();
    }
}

function giddhCreateGiddhUnmatchedToWoocommerce() {
    try {
        $productsModel = new GiddhProductsModel();
        $categoriesModel = new GiddhCategoriesModel();
        $failedProductsCount = 0;
        $parameters = array();
    
        $productsCount = $productsModel->getUnmatchedGiddhProductCount($parameters);
        if($productsCount && $productsCount["total"] > 0) {
            $totalPages = ceil($productsCount["total"] / GIDDH_PAGINATION_LIMIT);
    
            $offset = (sanitize_text_field($_POST['page']) - 1) * GIDDH_PAGINATION_LIMIT;
            if($offset < 0) {
                $offset = 0;
            }
    
            $parameters['offset'] = $offset;
            $products = $productsModel->getUnmatchedGiddhProduct($parameters);
            if($products && count($products) > 0) {
                foreach($products as $product) {
                    if(trim($product["sku"])) {
                        if($product["stock_group"]) {
                            $categoryDetails = $categoriesModel->getCategoryByGiddhStockGroupId(array("giddh_stock_group_id" => $product["stock_group"]));
                        } else {
                            $categoryDetails = array();
                        }
    
                        $productsModel->saveWoocommerceTempProduct(array("product_sku" => $product["sku"], "tags" => "giddhdnc"));
    
                        $objProduct = new WC_Product();
                        $objProduct->set_name($product["title"]);
                        $objProduct->set_status("publish");
                        $objProduct->set_catalog_visibility('visible');
                        $objProduct->set_sku($product["sku"]);
                        $objProduct->set_price($product["amount"]);
                        $objProduct->set_manage_stock(true);
                        $objProduct->set_stock_quantity($product["quantity"]);
                        $objProduct->set_stock_status('instock');
                        $objProduct->set_category_ids(array($categoryDetails["woocommerce_category_id"]));
                        $product_id = $objProduct->save();
    
                        $productsModel->deleteGiddhProductBySku(array("sku" => $product["sku"]));
                    } else {
                        $failedProductsCount++;
                    }
                }
            }
            
            if(sanitize_text_field($_POST['page']) == $totalPages) {
                echo json_encode(array("result" => "completed", "failed" => $failedProductsCount));
                wp_die();
            } else {
                echo json_encode(array("result" => "true", "pages" => $totalPages, "failed" => $failedProductsCount));
                wp_die();
            }
        } else {
            echo json_encode(array("result" => "error", "message" => "No Products Found."));
            wp_die();
        }
    } catch(Exception $e) {
        echo json_encode(array("result" => "error", "message" => $e->getMessage()));
        wp_die();
    }
}

function giddhCreateWoocommerceUnmatchedToGiddh() {
    try {
        $giddhApi = new GiddhApi();
        $productsModel = new GiddhProductsModel();
        $categoriesModel = new GiddhCategoriesModel();
        $failedProductsCount = 0;
        $parameters = array();
    
        $productsCount = $productsModel->getUnmatchedWoocommerceProductCount($parameters);
        if($productsCount && $productsCount["total"] > 0) {
            $totalPages = ceil($productsCount["total"] / GIDDH_PAGINATION_LIMIT);
    
            $offset = (sanitize_text_field($_POST['page']) - 1) * GIDDH_PAGINATION_LIMIT;
            if($offset < 0) {
                $offset = 0;
            }
    
            $parameters['offset'] = $offset;
            $products = $productsModel->getUnmatchedWoocommerceProduct($parameters);
            if($products && count($products) > 0) {
                $productData = array();
    
                foreach($products as $product) {
                    if($product["category_id"]) {
                        $categoryDetails = $categoriesModel->getCategoryByWoocommerceCategoryId(array("woocommerce_category_id" => $product["category_id"]));
                    } else {
                        $categoryDetails = array();
                    }
    
                    if(trim($product["sku"])) {
                        $productData[] = array(
                            "name" => $product["title"],
                            "uniqueName" => $product["title"],
                            "stockUnitCode" => "nos",
                            "openingAmount" => $product["amount"] * $product["quantity"],
                            "openingQuantity" => $product["quantity"],
                            "skuCode" => $product["sku"],
                            "salesAccountDetails" => array(
                                "accountUniqueName" => "sales",
                                "unitRates" => array(
                                    array(
                                        "rate" => $product["amount"],
                                        "stockUnitCode" => "nos"
                                    )
                                )
                            ),
                            "purchaseAccountDetails" => array(
                                "accountUniqueName" => "purchases",
                                "unitRates" => array(
                                    
                                )
                            ),
                            "stockGroupUniqueName" => ($categoryDetails && $categoryDetails["giddh_stock_group_id"]) ? $categoryDetails["giddh_stock_group_id"] : ""
                        );
                    } else {
                        $failedProductsCount++;
                    }
                }
    
                if($productData && count($productData) > 0) {
                    $response = $giddhApi->createBulkStock(get_option('giddh_company_unique_name'), get_option('giddh_shop_unique_name'), get_option('giddh_company_auth_key'), $productData);
                    if($response["status"] == "success") {
                        foreach($products as $product) {
                            $updateParameters = array();
                            $updateParameters['sku'] = $product["sku"];
                            $updateParameters['id'] = $product["id"];
                            $productsModel->updateWoocommerceProductSkuById($updateParameters);
                        }
                    } else {
                        $errorMessage = ($response["message"]) ? $response["message"] : "Something went wrong! Please try again";
                        echo json_encode(array("result" => "error", "message" => $errorMessage));
                        wp_die();
                    }
                }
            }
    
            if(sanitize_text_field($_POST['page']) == $totalPages) {
                echo json_encode(array("result" => "completed", "failed" => $failedProductsCount));
                wp_die();
            } else {
                echo json_encode(array("result" => "true", "pages" => $totalPages, "failed" => $failedProductsCount));
                wp_die();
            }
        } else {
            echo json_encode(array("result" => "error", "message" => "No Products Found."));
            wp_die();
        }
    } catch(Exception $e) {
        echo json_encode(array("result" => "error", "message" => $e->getMessage()));
        wp_die();
    }
}
?>