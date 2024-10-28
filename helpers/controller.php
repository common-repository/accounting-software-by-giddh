<?php
function giddhConnect() {
    if(!trim(sanitize_text_field($_POST['authKey']))) {
        echo json_encode(array("status" => "error", "message" => "Please enter Auth Key!"));
        wp_die();
    }
    if(!trim(sanitize_text_field($_POST['companyUniqueName']))) {
        echo json_encode(array("status" => "error", "message" => "Please enter Company Unique Name!"));
        wp_die();
    }
    
    if(!get_option('giddh_company_unique_name') || !get_option('giddh_company_auth_key') || !get_option('giddh_shop_unique_name')) {
        $giddhApi = new GiddhApi();
        $response = $giddhApi->verifyAccount(trim(sanitize_text_field($_POST['companyUniqueName'])), array("authKey" => trim(sanitize_text_field($_POST['authKey'])), "domain" => site_url(), "source" => "wordpress"));
        if($response['status'] == "success") {
            $nextStep = "setup";
            if(get_option('giddh_company_unique_name')) {
                $nextStep = "settings";
            }
            update_option('giddh_company_auth_key', trim(sanitize_text_field($_POST['authKey'])));
            update_option('giddh_company_unique_name', trim(sanitize_text_field($_POST['companyUniqueName'])));
            update_option('giddh_shop_unique_name', $response["body"]["uniqueName"]);
    
            if(get_option('giddh_shop_unique_name')) {
                if($nextStep == "setup") {
                    giddhInitSetup();
                }
    
                update_option('giddh_email_method', 'php');
                update_option('giddh_create_customer_account', 'no');
                update_option('giddh_create_invoice', 'ready_to_ship');
    
                echo json_encode(array("status" => "success", "next" => $nextStep, "message" => $response['body']["message"]));
                wp_die();
            } else {
                echo json_encode(array("status" => "error", "message" => "Something went wrong! Please try again."));
                wp_die();
            }
        } else {
            echo json_encode(array("status" => "error", "message" => $response['message']));
            wp_die();
        }
    } else {
        echo json_encode(array("status" => "error", "message" => "Giddh Account is already connected!"));
        wp_die();
    }
}

function giddhSettingsCategory() {
    try {
        if(get_option('giddh_company_unique_name') && get_option('giddh_company_auth_key') && get_option('giddh_shop_unique_name')) {
            if(!$_POST['woocommerceCategories']) {
                echo json_encode(array("status" => "error", "message" => "No categories are available in Woocommerce!"));
                wp_die();
            }
    
            $errors = array();
            $loop = 0;
            $categoriesModel = new GiddhCategoriesModel();
    
            $giddhApi = new GiddhApi();
    
            if($_POST['woocommerceCategories']) {
                foreach($_POST['woocommerceCategories'] as $woocommerceCategory) {
                    $parameters = array();
                    $parameters['woocommerce_category_id'] = sanitize_text_field($woocommerceCategory);
                    $parameters['giddh_stock_group_id'] = sanitize_text_field($_POST['giddhStockGroups'][$loop]);
                    $parameters['giddh_stock_group_name'] = sanitize_text_field($_POST['giddhStockGroupsName'][$loop]);
    
                    if($parameters['giddh_stock_group_id']) {
                        if($parameters['giddh_stock_group_id'] == "create") {
                            $apiParams = array();
                            $apiParams["name"] = sanitize_text_field($_POST["woocommerceCategoriesName"][$loop]);
                            $apiParams["isSubGroup"] = false;
    
                            $response = $giddhApi->createStockGroup(get_option('giddh_company_unique_name'), get_option('giddh_company_auth_key'), $apiParams);
                            if($response["status"] == "success" && $response["body"]["uniqueName"]) {
                                $parameters['giddh_stock_group_id'] = $response["body"]["uniqueName"];
                                $parameters['giddh_stock_group_name'] = $response["body"]["name"];
                            } else {
                                $errors[] = $parameters['woocommerce_category_id'];
                            }
                        }
    
                        if(!in_array($parameters['woocommerce_category_id'], $errors)) {
                            $parameters['id'] = sanitize_text_field($_POST['id'][$loop]);
                            $result = $categoriesModel->updateCategory($parameters);
    
                            if(is_bool($result)) {
                                if(!$result) {
                                    $errors[] = sanitize_text_field($_POST['giddhStockGroups'][$loop]);
                                }
                            } else {
                                $errors[] = sanitize_text_field($_POST['giddhStockGroups'][$loop]);
                            }
                        }
                    } else {
                        $getCategory = $categoriesModel->getCategoryByWoocommerceCategoryId($parameters);
                        if($getCategory) {
                            $categoriesModel->updateCategory($parameters);
                        }
                    }
    
                    $loop++;
                }
            }
            
            if($errors && count($errors) > 0) {
                echo json_encode(array("status" => "error", "message" => "Error in mapping categories(s)! Please try again."));
                wp_die();
            } else {
                echo json_encode(array("status" => "success", "message" => "Categories mapped successfully."));
                wp_die();
            }
        } else {
            echo json_encode(array("status" => "connect", "message" => "Please connect the plugin with Giddh!"));
            wp_die();
        }
    } catch(Exception $e) {
        echo json_encode(array("status" => "error", "message" => $e->getMessage()));
        wp_die();
    }
}

function giddhSettingsInventory() {
    try {
        if(get_option('giddh_company_unique_name') && get_option('giddh_company_auth_key') && get_option('giddh_shop_unique_name')) {
            if(!$_POST['skumatched'] && !$_POST['woocommerceunmatched'] && !$_POST['giddhunmatched']) {
                echo json_encode(array("status" => "error", "message" => "No products available for mapping!"));
                wp_die();
            }
    
            $errors = array();
            $loop = 0;
            $createParameters = array();
            $categoriesModel = new GiddhCategoriesModel();
            $productsModel = new GiddhProductsModel();
    
            if($_POST['skumatched']) {
                foreach($_POST['skumatched'] as $product) {
                    if(sanitize_text_field($_POST["skumatchedId"][$loop]) == "create") {
                        $woocommerceProduct = $productsModel->getWoocommerceProductById(array("id" => sanitize_text_field($product)));
                        if($woocommerceProduct) {
                            if($woocommerceProduct["category_id"]) {
                                $categoryDetails = $categoriesModel->getCategoryByWoocommerceCategoryId(array("woocommerce_category_id" => $woocommerceProduct["category_id"]));
                            } else {
                                $categoryDetails = array();
                            }
    
                            $createParameters[] = array(
                                "name" => $woocommerceProduct["title"],
                                "uniqueName" => $woocommerceProduct["title"],
                                "stockUnitCode" => "nos",
                                "openingAmount" => $woocommerceProduct["amount"] * $woocommerceProduct["quantity"],
                                "openingQuantity" => $woocommerceProduct["quantity"],
                                "skuCode" => sanitize_text_field($_POST["sku"][$loop]),
                                "salesAccountDetails" => array(
                                    "accountUniqueName" => "sales",
                                    "unitRates" => array(
                                        array(
                                            "rate" => $woocommerceProduct["amount"],
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
    
                            $parameters = array();
                            $parameters['sku'] = sanitize_text_field($_POST["sku"][$loop]);
                            $parameters['id'] = $product;
                            $resultWoocommerce = $productsModel->updateWoocommerceProductSkuById($parameters);
    
                            if(is_bool($resultWoocommerce)) {
                                if(!$resultWoocommerce) {
                                    $errors[] = sanitize_text_field($_POST['skumatchedName'][$loop]);
                                }
                            } else {
                                $errors[] = sanitize_text_field($_POST['skumatchedName'][$loop]);
                            }
                        }
                    } else {
                        $productDetails = $productsModel->getGiddhProduct(array("unique_name" => sanitize_text_field($_POST["skumatchedId"][$loop])));
                        if($productDetails["sku"] == sanitize_text_field($_POST["sku"][$loop])) {
                            $productsModel->deleteWoocommerceProductBySku(array("sku" => sanitize_text_field($_POST["sku"][$loop])));
                            $productsModel->deleteGiddhProductBySku(array("sku" => sanitize_text_field($_POST["sku"][$loop])));
                        } else {
                            $response = giddhUpdateStock($productDetails["unique_name"], $productDetails["stock_group"], sanitize_text_field($_POST["sku"][$loop]));
                            if($response["status"] != "success") {
                                $errors[] = $response["message"];
                            } else {
                                $productsModel->deleteWoocommerceProductBySku(array("sku" => sanitize_text_field($_POST["sku"][$loop])));
                                $productsModel->deleteGiddhProductByUniqueName(array("unique_name" => $productDetails["unique_name"]));
                            }
                        }
                    }
                    $loop++;
                }
    
                if($createParameters && count($createParameters) > 0) {
                    $giddhApi = new GiddhApi();
                    $response = $giddhApi->createBulkStock(get_option('giddh_company_unique_name'), get_option('giddh_shop_unique_name'), get_option('giddh_company_auth_key'), $createParameters);
                    if($response["status"] != "success") {
                        $errors[] = $response["message"];
                    }
                }
            } else if($_POST['woocommerceunmatched']) {
                foreach($_POST['woocommerceunmatched'] as $product) {
                    if(sanitize_text_field($_POST["sku"][$loop])) {
                        if(sanitize_text_field($_POST["woocommerceunmatchedId"][$loop]) == "create") {
                            $woocommerceProduct = $productsModel->getWoocommerceProductById(array("id" => sanitize_text_field($product)));
                            if($woocommerceProduct) {
                                if($woocommerceProduct["category_id"]) {
                                    $categoryDetails = $categoriesModel->getCategoryByWoocommerceCategoryId(array("woocommerce_category_id" => $woocommerceProduct["category_id"]));
                                } else {
                                    $categoryDetails = array();
                                }
    
                                $createParameters[] = array(
                                    "name" => $woocommerceProduct["title"],
                                    "uniqueName" => $woocommerceProduct["title"],
                                    "stockUnitCode" => "nos",
                                    "openingAmount" => $woocommerceProduct["amount"] * $woocommerceProduct["quantity"],
                                    "openingQuantity" => $woocommerceProduct["quantity"],
                                    "skuCode" => sanitize_text_field($_POST["sku"][$loop]),
                                    "salesAccountDetails" => array(
                                        "accountUniqueName" => "sales",
                                        "unitRates" => array(
                                            array(
                                                "rate" => $woocommerceProduct["amount"],
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
    
                                $parameters = array();
                                $parameters['sku'] = sanitize_text_field($_POST["sku"][$loop]);
                                $parameters['id'] = $product;
                                $resultWoocommerce = $productsModel->updateWoocommerceProductSkuById($parameters);
    
                                if(is_bool($resultWoocommerce)) {
                                    if(!$resultWoocommerce) {
                                        $errors[] = sanitize_text_field($_POST['woocommerceunmatchedName'][$loop]);
                                    }
                                } else {
                                    $errors[] = sanitize_text_field($_POST['woocommerceunmatchedName'][$loop]);
                                }
                            }
                        } else {
                            $productDetails = $productsModel->getGiddhProduct(array("unique_name" => sanitize_text_field($_POST["woocommerceunmatchedId"][$loop])));
                            $response = giddhUpdateStock($productDetails["unique_name"], $productDetails["stock_group"], sanitize_text_field($_POST["sku"][$loop]));
                            if($response["status"] != "success") {
                                $errors[] = $response["message"];
                            } else {
                                $productsModel->deleteWoocommerceProductBySku(array("sku" => sanitize_text_field($_POST["sku"][$loop])));
                                $productsModel->deleteGiddhProductByUniqueName(array("unique_name" => $productDetails["unique_name"]));
                            }
                        }
                    }
    
                    $loop++;
                }
    
                if($createParameters && count($createParameters) > 0) {
                    $giddhApi = new GiddhApi();
                    $response = $giddhApi->createBulkStock(get_option('giddh_company_unique_name'), get_option('giddh_shop_unique_name'), get_option('giddh_company_auth_key'), $createParameters);
                    if($response["status"] != "success") {
                        $errors[] = $response["message"];
                    }
                }
            } else if($_POST['giddhunmatched']) {
                foreach($_POST['giddhunmatched'] as $product) {
                    if(sanitize_text_field($_POST["sku"][$loop])) {
                        if(sanitize_text_field($_POST["giddhunmatchedId"][$loop]) == "create") {
                            $giddhProduct = $productsModel->getGiddhProductById(array("id" => sanitize_text_field($product)));
                            if($giddhProduct) {
                                if($giddhProduct["stock_group"]) {
                                    $categoryDetails = $categoriesModel->getCategoryByGiddhStockGroupId(array("giddh_stock_group_id" => $giddhProduct["stock_group"]));
                                } else {
                                    $categoryDetails = array();
                                }
    
                                $createParameters[] = array(
                                    "title" => $giddhProduct["title"],
                                    "price" => $giddhProduct["amount"],
                                    "inventory_quantity" => $giddhProduct["quantity"],
                                    "sku" => sanitize_text_field($_POST["sku"][$loop]),
                                    "category_id" => $categoryDetails["woocommerce_category_id"]
                                );
    
                                $parameters = array();
                                $parameters['sku'] = sanitize_text_field($_POST["sku"][$loop]);
                                $parameters['id'] = $product;
                                $resultGiddh = $productsModel->updateGiddhProductSkuById($parameters);
    
                                if(is_bool($resultGiddh)) {
                                    if(!$resultGiddh) {
                                        $errors[] = sanitize_text_field($_POST['giddhunmatchedName'][$loop]);
                                    }
                                } else {
                                    $errors[] = sanitize_text_field($_POST['giddhunmatchedName'][$loop]);
                                }
                            }
                        } else {
                            $productDetails = $productsModel->getWoocommerceProductById(array("id" => sanitize_text_field($_POST["giddhunmatchedId"][$loop])));
                            if($productDetails["variant_id"]) {
                                update_post_meta($productDetails["variant_id"], 'sku', sanitize_text_field($_POST["sku"][$loop]));
                            } else {
                                update_post_meta($productDetails["product_id"], 'sku', sanitize_text_field($_POST["sku"][$loop]));
                            }
    
                            $productsModel->deleteGiddhProductBySku(array("sku" => sanitize_text_field($_POST["sku"][$loop])));
                            $productsModel->deleteWoocommerceProductBySku(array("sku" => sanitize_text_field($_POST["sku"][$loop])));
                        }
                    }
    
                    $loop++;
                }
    
                if($createParameters && count($createParameters) > 0) {
                    foreach($createParameters as $createParameter) {
                        $productsModel->saveWoocommerceTempProduct(array("product_sku" => $createParameter["sku"], "tags" => "giddhauto"));
    
                        $objProduct = new WC_Product();
                        $objProduct->set_name($createParameter["title"]);
                        $objProduct->set_status("publish");
                        $objProduct->set_catalog_visibility('visible');
                        $objProduct->set_sku($createParameter["sku"]);
                        $objProduct->set_price($createParameter["amount"]);
                        $objProduct->set_manage_stock(true);
                        $objProduct->set_stock_quantity($createParameter["quantity"]);
                        $objProduct->set_stock_status('instock');
                        $objProduct->set_category_ids(array($createParameter["category_id"]));
                        $objProduct->save();
                    }
                }
            }
            
            if($errors && count($errors) > 0) {
                echo json_encode(array("status" => "error", "message" => "Error in mapping product(s)! Please try again.", "error" => $errors));
                wp_die();
            } else {
                echo json_encode(array("status" => "success", "message" => "Products mapped successfully."));
                wp_die();
            }
        } else {
            echo json_encode(array("status" => "error", "message" => "Please connect the plugin with Giddh!"));
            wp_die();
        }
    } catch(Exception $e) {
        echo json_encode(array("status" => "error", "message" => $e->getMessage()));
        wp_die();
    }
}

function giddhSettingsAccount() {
    if(get_option('giddh_company_unique_name') && get_option('giddh_company_auth_key') && get_option('giddh_shop_unique_name')) {
        if(!is_email(trim(sanitize_email($_POST['notification_email'])))) {
            echo json_encode(array("status" => "error", "message" => "Please enter valid notification email to receive notifications."));
            wp_die();
        }
    
        update_option('giddh_notification_email', trim(sanitize_email($_POST['notification_email'])));
        update_option('giddh_email_method', trim(sanitize_text_field($_POST['email_method'])));
        update_option('giddh_sendgrid_api_key', trim(sanitize_text_field($_POST['sendgrid_api_key'])));
    
        echo json_encode(array("status" => "success", "message" => "Account updated successfully."));
        wp_die();
    } else {
        echo json_encode(array("status" => "connect", "message" => "Please connect the plugin with Giddh!"));
        wp_die();
    }
}

function giddhSettingsInvoice() {
    if(get_option('giddh_company_unique_name') && get_option('giddh_company_auth_key') && get_option('giddh_shop_unique_name')) {
        if(!trim(sanitize_text_field($_POST['create_customer_account']))) {
            echo json_encode(array("status" => "error", "message" => "Please choose when do you want to create Customer in Giddh?"));
            wp_die();
        }
        if(!trim(sanitize_text_field($_POST['create_invoice']))) {
            echo json_encode(array("status" => "error", "message" => "Please choose when to create Invoice in Giddh"));
            wp_die();
        }
    
        update_option('giddh_create_customer_account', sanitize_text_field($_POST['create_customer_account']));
        update_option('giddh_create_invoice', sanitize_text_field($_POST['create_invoice']));
    
        echo json_encode(array("status" => "success", "message" => "Settings saved successfully."));
        wp_die();
    } else {
        echo json_encode(array("status" => "connect", "message" => "Please connect the plugin with Giddh!"));
        wp_die();
    }
}

function giddhSettingsPayment() {
    require_once(GIDDH_PLUGIN_PATH."models/payments.php");

    try {
        if(get_option('giddh_company_unique_name') && get_option('giddh_company_auth_key') && get_option('giddh_shop_unique_name')) {
            $errors = array();
            $loop = 0;
            $paymentsModel = new GiddhPaymentsModel();

            if(!$_POST['woocommercePaymentGateways']) {
                echo json_encode(array("status" => "error", "message" => "Please choose atleast 1 payment gateway in Woocommerce!"));
                wp_die();
            }
            if(!$_POST['giddhAccounts']) {
                $parameters = array();
                $getAllPaymentGateways = $paymentsModel->getAllPaymentGateways($parameters);
                if(!$getAllPaymentGateways) {
                    echo json_encode(array("status" => "error", "message" => "Please choose atleast 1 payment gateway in Giddh!"));
                    wp_die();
                }
            }

            $giddhApi = new GiddhApi();
            $countryDetails = array();

            foreach($_POST['woocommercePaymentGateways'] as $woocommercePaymentGateway) {
                $parameters = array();
                $parameters['woocommerce_payment_id'] = sanitize_text_field($woocommercePaymentGateway);
                $parameters['giddh_account_id'] = sanitize_text_field($_POST['giddhAccounts'][$loop]);

                if($parameters['woocommerce_payment_id'] && $parameters['giddh_account_id']) {
                    if(sanitize_text_field($_POST['removedMappings'][$loop])) {
                        if(sanitize_text_field($_POST['mappingId'][$loop])) {
                            $paymentsModel->deletePaymentGateway(array("id" => sanitize_text_field($_POST['mappingId'][$loop])));
                        }
                    } else {
                        if($parameters['giddh_account_id'] == "create") {
                            $paymentDetails = $paymentsModel->getPaymentGatewayById($parameters['woocommerce_payment_id']);

                            $apiParams = array();
                            $apiParams["activeGroupUniqueName"] = "bankaccounts";
                            $apiParams["name"] = $paymentDetails["name"];
                            $apiParams["uniqueName"] = "";
                            $apiParams["openingBalanceType"] = "CREDIT";
                            $apiParams["foreignOpeningBalance"] = 0;
                            $apiParams["openingBalance"] = 0;
                            $apiParams["mobileNo"] = "";
                            $apiParams["mobileCode"] = "";
                            $apiParams["email"] = "";
                            $apiParams["companyName"] = "";
                            $apiParams["attentionTo"] = "";
                            $apiParams["description"] = "";
                            $apiParams["addresses"] = array(
                                array(
                                    "gstNumber" => "",
                                    "address" => "",
                                    "state" => array(
                                        "code" => "",
                                        "name" => "",
                                        "stateGstCode" => ""
                                    ),
                                    "stateCode" => "",
                                    "isDefault" => true,
                                    "isComposite" => false,
                                    "partyType" => "NOT APPLICABLE"
                                )
                            );

                            $apiParams["country"] = array(
                                "countryCode" => ""
                            );

                            $apiParams["currency"] = get_option('woocommerce_currency');
                            $apiParams["closingBalanceTriggerAmountType"] = "CREDIT";

                            $response = $giddhApi->createBankAccount(get_option('giddh_company_unique_name'), get_option('giddh_company_auth_key'), $apiParams);
                            if($response["status"] == "success" && $response["body"]["uniqueName"]) {
                                $parameters['giddh_account_id'] = $response["body"]["uniqueName"];
                            } else {
                                $errors[] = $parameters['woocommerce_payment_id'];
                            }
                        }

                        $getPaymentGateway = $paymentsModel->getPaymentGateway($parameters);
                        if($getPaymentGateway) {
                            $parameters['id'] = $getPaymentGateway['id'];
                            $result = $paymentsModel->updatePaymentGateway($parameters);
                        } else {
                            $result = $paymentsModel->savePaymentGateway($parameters);
                        }

                        if(is_bool($result)) {
                            if(!$result) {
                                $errors[] = sanitize_text_field($_POST['giddhAccounts'][$loop]);
                            }
                        } else {
                            $errors[] = sanitize_text_field($_POST['giddhAccounts'][$loop]);
                        }
                    }
                } else if(sanitize_text_field($_POST['mappingId'][$loop])) {
                    $paymentsModel->deletePaymentGateway(array("id" => sanitize_text_field($_POST['mappingId'][$loop])));
                }

                $loop++;
            }
            
            if($errors && count($errors) > 0) {
                echo json_encode(array("status" => "error", "message" => "Error in mapping payment gateway(s)! Please try again."));
                wp_die();
            } else {
                echo json_encode(array("status" => "success", "message" => "Payment Gateways mapped successfully."));
                wp_die();
            }
        } else {
            echo json_encode(array("status" => "connect", "message" => "Please connect the plugin with Giddh!"));
            wp_die();
        }
    } catch(Exception $e) {
        echo json_encode(array("status" => "error", "message" => $e->getMessage()));
        wp_die();
    }
}

function giddhSettingsService() {
    if(get_option('giddh_company_unique_name') && get_option('giddh_company_auth_key') && get_option('giddh_shop_unique_name')) {
        update_option('giddh_shipping_account', trim(sanitize_text_field($_POST['giddh_shipping_account'])));
        update_option('giddh_shipping_account_name', trim(sanitize_text_field($_POST['giddh_shipping_account_name'])));
        echo json_encode(array("status" => "success", "message" => "Service updated successfully."));
        wp_die();
    } else {
        echo json_encode(array("status" => "connect", "message" => "Please connect the plugin with Giddh!"));
        wp_die();
    }
}
?>