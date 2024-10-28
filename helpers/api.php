<?php
function giddhBulkProductCreateNotification() {
    $response = array();
    $result = true;

    global $wp_filesystem;
    if (empty($wp_filesystem)) {
        require_once (ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
    }

    $jsondata = $wp_filesystem->get_contents("php://input");
    if ($jsondata) {
        $dataobject = json_decode($jsondata);
        $parameters = json_decode(json_encode($dataobject), true);

        if(get_option('giddh_company_unique_name') == $parameters["companyUniqueName"] && get_option('giddh_company_auth_key') && get_option('giddh_shop_unique_name') == $parameters["storeUniqueName"]) {
            if($parameters["ecommerceStockDetails"] && count($parameters["ecommerceStockDetails"]) > 0) {
                $productsModel = new GiddhProductsModel();
                foreach($parameters["ecommerceStockDetails"] as $stock) {
                    if($stock["skuCode"]) {
                        if($stock["status"]["type"] == "FAILED") {
                            $productsModel->updateGiddhProductSavedStatusBySku(array("sku" => $stock["skuCode"], "is_saved" => 0));
                            $productsModel->updateWoocommerceProductSavedStatusBySku(array("sku" => $stock["skuCode"], "is_saved" => 0));
                        } else {
                            $productsModel->deleteGiddhProductBySku(array("sku" => $stock["skuCode"]));
                            $productsModel->deleteWoocommerceProductBySku(array("sku" => $stock["skuCode"]));
                        }
                    }
                }
                $result = true;
            } else {
                $result = false;
            }
        } else {
            $result = false;
        }
    } else {
        $result = false;
    }

    if($result) {
        http_response_code(200);
    } else {
        http_response_code(400);
    }
}

function giddhInventoryUpdate() {
    require_once("product.php");

    $response = array();
    $result = false;
    
    global $wp_filesystem;
    if (empty($wp_filesystem)) {
        require_once (ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
    }

    $jsondata = $wp_filesystem->get_contents("php://input");
    if ($jsondata) {
        $dataobject = json_decode($jsondata);
        $parameters = json_decode(json_encode($dataobject), true);

        if(get_option('giddh_company_unique_name') == $parameters["companyUniqueName"] && get_option('giddh_company_auth_key') && get_option('giddh_shop_unique_name') == $parameters["storeUniqueName"]) {
            foreach($parameters["stocks"] as $key => $stock) {
                if($stock["balanceType"] == "cr" || $stock["balanceType"] == "dr") {
                    $productId = wc_get_product_id_by_sku($stock['skuCode']);
                    if($productId) {
                        $productVariationDetails = wc_get_product($productId);
                        if($productVariationDetails) {
                            if($productVariationDetails->get_parent_id()) {
                                $productDetails = wc_get_product($productVariationDetails->get_parent_id());
                            } else {
                                $productDetails = $productVariationDetails;
                            }
                            $selectedVariant = false;

                            if($productDetails->get_type() === "variable") {
                                $variants = $productDetails->get_available_variations();
                                if($variants && count($variants) > 0) {
                                    foreach($variants as $variant) {
                                        if($variant["sku"] == $stock['skuCode']) {
                                            $availableInventory = $variant["max_qty"];
                                            $selectedVariant = $variant["variation_id"];
                                        }

                                        if($selectedVariant) {
                                            break;
                                        }
                                    }
                                } else {
                                    $availableInventory = $productDetails->get_stock_quantity();
                                }
                            } else {
                                $availableInventory = $productDetails->get_stock_quantity();
                            }

                            if($stock["balanceType"] == "cr") {
                                $availableInventory = $availableInventory + $stock["quantity"];
                            } else if($stock["balanceType"] == "dr") {
                                $availableInventory = $availableInventory - $stock["quantity"];
                            }

                            $result = giddhUpdateProductStock($availableInventory, $productId, $selectedVariant);
                            if(is_bool($result)) {
                                $parameters["stocks"][$key]["status"] = "success";
                                $parameters["stocks"][$key]["message"] = "Inventory has been updated successfully.";
                            } else {
                                $parameters["stocks"][$key]["status"] = "error";
                                $parameters["stocks"][$key]["message"] = $result;
                            }
                        } else {
                            $parameters["stocks"][$key]["status"] = "error";
                            $parameters["stocks"][$key]["message"] = "Invalid Sku Code!";
                        }
                    } else {
                        $parameters["stocks"][$key]["status"] = "error";
                        $parameters["stocks"][$key]["message"] = "Invalid Sku Code!";
                    }
                } else {
                    $parameters["stocks"][$key]["status"] = "error";
                    $parameters["stocks"][$key]["message"] = "Invalid Balance Type!";
                }
            }
            $response = $parameters;
            $result = true;
        } else {
            $response["status"] = "error";
            $response["message"] = "Invalid Company Unique Name/ Store Unique Name!";
        }
    } else {
        $response["status"] = "error";
        $response["message"] = "Invalid Json Provided!";
    }

    if($result) {
        http_response_code(200);
    } else {
        http_response_code(400);
    }
    echo json_encode($response);
    exit();
}
?>