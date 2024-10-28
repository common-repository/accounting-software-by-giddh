<?php
function giddhSaveProduct($id) {
    if(get_option('giddh_company_unique_name') && get_option('giddh_company_auth_key') && get_option('giddh_shop_unique_name')) {
        $product = wc_get_product($id);

        $productsModel = new GiddhProductsModel();
        $getWoocommerceTempProduct = $productsModel->getWoocommerceTempProduct($product->get_sku());

        if(!$getWoocommerceTempProduct || $getWoocommerceTempProduct["tags"] != "giddhdnc") {
            $tags = ($getWoocommerceTempProduct) ? $getWoocommerceTempProduct["tags"] : "";
            if($product) {
                giddhAddProduct($product, $tags);
            }
        }
    }
}

function giddhUpdateProduct($id) {
    if(get_option('giddh_company_unique_name') && get_option('giddh_company_auth_key') && get_option('giddh_shop_unique_name')) {
        $product = wc_get_product($id);

        $productsModel = new GiddhProductsModel();
        
        $parameters = array();
        $parameters["product_id"] = $id;

        $existingProduct = $productsModel->getWoocommerceProduct($parameters);
        if($existingProduct) {
            if($product) {
                giddhEditProduct($product, $existingProduct);
            }
        } else {
            $getWoocommerceTempProduct = $productsModel->getWoocommerceTempProduct($product->get_sku());
            if(!$getWoocommerceTempProduct || $getWoocommerceTempProduct["tags"] != "giddhdnc") {
                $tags = ($getWoocommerceTempProduct) ? $getWoocommerceTempProduct["tags"] : "";
                $product = wc_get_product($id);
                if($product) {
                    giddhAddProduct($product, $tags);
                }
            }
        }
    }
}

function giddhTrashPost($id) {
    // global $wpdb;
    
    // $post = get_post($id);
    // if($post->post_type == "product") {

    // }
}

function giddhRestorePost($id) {
    // global $wpdb;
    // $post = get_post($id);
    // if($post->post_type == "product") {

    // }
}

function giddhSaveOrder($id) {
    if(get_option('giddh_company_unique_name') && get_option('giddh_company_auth_key') && get_option('giddh_shop_unique_name')) {
        $customersModel = new GiddhCustomersModel();
        $invoiceModel = new GiddhInvoiceModel();

        $order = wc_get_order($id);
        $customer = new WC_Customer($id);

        $customerName = (!$customer->get_first_name() && !$customer->get_last_name()) ? $order->get_formatted_billing_full_name() : $customer->get_first_name()." ".$customer->get_last_name();

        $invoiceDetails = $invoiceModel->getInvoice(array("woocommerce_order_id" => $id));
        if(!$invoiceDetails) {
            if((get_option('giddh_create_invoice') == "order_create" && $order->get_status() == "pending") || (get_option('giddh_create_invoice') == "ready_to_ship" && $order->get_status() == "completed")) {
                if(get_option('giddh_create_customer_account') == "yes") {
                    $getCustomer = $customersModel->getCustomer(array("woocommerce_customer_id" => $order->get_customer_id()));
                    if($getCustomer) {
                        $invoiceResponse = giddhCreateSalesInvoice($order, $getCustomer['giddh_account_id']);
                        if(!$invoiceResponse || $invoiceResponse['status'] != "success") {
                            giddhSendEmail(array("subject" => GIDDH_CREATE_INVOICE_ERROR_EMAIL_SUBJECT, "message" => "The invoice for #".$order->get_id()." could not be created because of an internal error.".$invoiceResponse['message']));
                        } else {
                            $invoiceModel->saveInvoice(array("woocommerce_order_id" => $id, "giddh_invoice_id" => $invoiceResponse["body"]["uniqueName"], "giddh_voucher_number" => $invoiceResponse["body"]["number"], "invoice_type" => "sales", "date_created" => date("Y-m-d H:i:s")));
                        }
                    } else {
                        $accountResponse = giddhCreateAccount($order);
                        if($accountResponse && $accountResponse['status'] == "success") {
                            $customersModel->saveCustomer(array("woocommerce_customer_id" => $order->get_customer_id(), "giddh_account_id" => $accountResponse['body']['uniqueName']));
                            $invoiceResponse = giddhCreateSalesInvoice($order, $accountResponse['body']['uniqueName']);
                            if(!$invoiceResponse || $invoiceResponse['status'] != "success") {
                                giddhSendEmail(array("subject" => GIDDH_CREATE_INVOICE_ERROR_EMAIL_SUBJECT, "message" => "The invoice for #".$order->get_id()." could not be created because of an internal error.".$invoiceResponse['message']));
                            } else {
                                $invoiceModel->saveInvoice(array("woocommerce_order_id" => $id, "giddh_invoice_id" => $invoiceResponse["body"]["uniqueName"], "giddh_voucher_number" => $invoiceResponse["body"]["number"], "invoice_type" => "sales", "date_created" => date("Y-m-d H:i:s")));
                            }
                        } else {
                            giddhSendEmail(array("subject" => GIDDH_CREATE_ACCOUNT_ERROR_EMAIL_SUBJECT, "message" => "The account for ".$customerName." could not be created because of an internal error."));
                        }
                    }
                } else {
                    $paymentsModel = new GiddhPaymentsModel();
                    $giddhPaymentAccount = $paymentsModel->getPaymentGatewayByWoocommercePaymentId(array("woocommerce_payment_id" => $order->get_payment_method()));
                    $accountUniqueName = ($giddhPaymentAccount) ? $giddhPaymentAccount["giddh_account_id"] : "cash";

                    $invoiceResponse = giddhCreateCashInvoice($order, $accountUniqueName);
                    if(!$invoiceResponse || $invoiceResponse['status'] != "success") {
                        giddhSendEmail(array("subject" => GIDDH_CREATE_INVOICE_ERROR_EMAIL_SUBJECT, "message" => "The invoice for #".$order->get_id()." could not be created because of an internal error.".$invoiceResponse['message']));
                    } else {
                        $invoiceModel->saveInvoice(array("woocommerce_order_id" => $id, "giddh_invoice_id" => $invoiceResponse["body"]["uniqueName"], "giddh_voucher_number" => $invoiceResponse["body"]["number"], "invoice_type" => "cash", "date_created" => date("Y-m-d H:i:s")));
                    }
                }
            } else if(get_option('giddh_create_invoice') == "order_paid" && ($order->get_status() == "processing" || $order->get_status() == "completed")) {
                giddhPaidOrder($id);
            }
        }
    }
}

function giddhUpdateOrder($id) {
	if(get_option('giddh_company_unique_name') && get_option('giddh_company_auth_key') && get_option('giddh_shop_unique_name')) {
        $customersModel = new GiddhCustomersModel();
        $invoiceModel = new GiddhInvoiceModel();

        $order = wc_get_order($id);

        if($order->get_status() !== "cancelled" && $order->get_status() !== "refunded") {
            $customer = new WC_Customer($id);

            $customerName = (!$customer->get_first_name() && !$customer->get_last_name()) ? $order->get_formatted_billing_full_name() : $customer->get_first_name()." ".$customer->get_last_name();

            $invoiceDetails = $invoiceModel->getInvoice(array("woocommerce_order_id" => $id));
            if($invoiceDetails) {
                if($invoiceDetails["invoice_type"] == "sales") {
                    $getCustomer = $customersModel->getCustomer(array("woocommerce_customer_id" => $order->get_customer_id()));
                    $giddhInvoiceDetails = giddhGetInvoiceDetails($order, $getCustomer['giddh_account_id'], "sales");
                    if($giddhInvoiceDetails && $giddhInvoiceDetails["status"] != "error") {
                        if($giddhInvoiceDetails["body"]["balanceStatus"] != "CANCEL") {
                            $invoiceResponse = giddhEditSalesInvoice($order, $getCustomer['giddh_account_id']);
                            if(!$invoiceResponse || $invoiceResponse['status'] != "success") {
                                giddhSendEmail(array("subject" => GIDDH_EDIT_INVOICE_ERROR_EMAIL_SUBJECT, "message" => "The invoice for #".$order->get_id()." could not be updated because of an internal error.".$invoiceResponse['message']));
                            }
                        } else {
                            giddhSendEmail(array("subject" => GIDDH_EDIT_CANCEL_INVOICE_ERROR_EMAIL_SUBJECT, "message" => "The recent change made by you (reactivation of a cancelled invoice #".$order->get_id().") in your WooCommerce account will not reflect in Giddh Accounting Software. To keep a record of the order in Giddh, manually create a new invoice there and keep updating its progress."));
                        }
                    }
                } else if($invoiceDetails["invoice_type"] == "cash") {
                    $paymentsModel = new GiddhPaymentsModel();
                    $giddhPaymentAccount = $paymentsModel->getPaymentGatewayByWoocommercePaymentId(array("woocommerce_payment_id" => $order->get_payment_method()));
                    $accountUniqueName = ($giddhPaymentAccount) ? $giddhPaymentAccount["giddh_account_id"] : "cash";
                    $giddhInvoiceDetails = giddhGetInvoiceDetails($order, $accountUniqueName, "sales");
                    if($giddhInvoiceDetails && $giddhInvoiceDetails["status"] != "error") {
                        if($giddhInvoiceDetails["body"]["balanceStatus"] != "CANCEL") {
                            $invoiceResponse = giddhEditCashInvoice($order, $accountUniqueName);
                            if(!$invoiceResponse || $invoiceResponse['status'] != "success") {
                                giddhSendEmail(array("subject" => GIDDH_EDIT_INVOICE_ERROR_EMAIL_SUBJECT, "message" => "The invoice for #".$order->get_id()." could not be updated because of an internal error.".$invoiceResponse['message']));
                            }
                        } else {
                            giddhSendEmail(array("subject" => GIDDH_EDIT_CANCEL_INVOICE_ERROR_EMAIL_SUBJECT, "message" => "The recent change made by you (reactivation of a cancelled invoice #".$order->get_id().") in your WooCommerce account will not reflect in Giddh Accounting Software. To keep a record of the order in Giddh, manually create a new invoice there and keep updating its progress."));
                        }
                    }
                }
            }
        }
    }
}

// function giddhRefundOrder($id) {
	
// }

function giddhPaidOrder($id) {
	if(get_option('giddh_company_unique_name') && get_option('giddh_company_auth_key') && get_option('giddh_shop_unique_name')) {
        $customersModel = new GiddhCustomersModel();
        $invoiceModel = new GiddhInvoiceModel();

        $order = wc_get_order($id);

        $invoiceDetails = $invoiceModel->getInvoice(array("woocommerce_order_id" => $id));
        if(!$invoiceDetails) {
            if(get_option('giddh_create_invoice') == "order_paid") {
                $customer = new WC_Customer($id);
                $customerName = (!$customer->get_first_name() && !$customer->get_last_name()) ? $order->get_formatted_billing_full_name() : $customer->get_first_name()." ".$customer->get_last_name();

                if(get_option('giddh_create_customer_account') == "yes") {
                    $getCustomer = $customersModel->getCustomer(array("woocommerce_customer_id" => $order->get_customer_id()));
                    if($getCustomer) {
                        $invoiceResponse = giddhCreateSalesInvoice($order, $getCustomer['giddh_account_id']);
                        if(!$invoiceResponse || $invoiceResponse['status'] != "success") {
                            giddhSendEmail(array("subject" => GIDDH_CREATE_INVOICE_ERROR_EMAIL_SUBJECT, "message" => "The invoice for #".$order->get_id()." could not be created because of an internal error. ".$invoiceResponse['message']));
                        } else {
                            $invoiceModel->saveInvoice(array("woocommerce_order_id" => $id, "giddh_invoice_id" => $invoiceResponse["body"]["uniqueName"], "giddh_voucher_number" => $invoiceResponse["body"]["number"], "invoice_type" => "sales", "date_created" => date("Y-m-d H:i:s")));
                        }
                    } else {
                        $accountResponse = giddhCreateAccount($order);
                        if($accountResponse && $accountResponse['status'] == "success") {
                            $customersModel->saveCustomer(array("woocommerce_customer_id" => $order->get_customer_id(), "giddh_account_id" => $accountResponse['body']['uniqueName']));
                            $invoiceResponse = giddhCreateSalesInvoice($order, $accountResponse['body']['uniqueName']);
                            if(!$invoiceResponse || $invoiceResponse['status'] != "success") {
                                giddhSendEmail(array("subject" => GIDDH_CREATE_INVOICE_ERROR_EMAIL_SUBJECT, "message" => "The invoice for #".$order->get_id()." could not be created because of an internal error. ".$invoiceResponse['message']));
                            } else {
                                $invoiceModel->saveInvoice(array("woocommerce_order_id" => $id, "giddh_invoice_id" => $invoiceResponse["body"]["uniqueName"], "giddh_voucher_number" => $invoiceResponse["body"]["number"], "invoice_type" => "sales", "date_created" => date("Y-m-d H:i:s")));
                            }
                        } else {
                            giddhSendEmail(array("subject" => GIDDH_CREATE_ACCOUNT_ERROR_EMAIL_SUBJECT, "message" => "The account for ".$customerName." could not be created because of an internal error."));
                        }
                    }
                } else {
                    $paymentsModel = new GiddhPaymentsModel();
                    $giddhPaymentAccount = $paymentsModel->getPaymentGatewayByWoocommercePaymentId(array("woocommerce_payment_id" => $order->get_payment_method()));
                    $accountUniqueName = ($giddhPaymentAccount) ? $giddhPaymentAccount["giddh_account_id"] : "cash";

                    $invoiceResponse = giddhCreateCashInvoice($order, $accountUniqueName);
                    if(!$invoiceResponse || $invoiceResponse['status'] != "success") {
                        giddhSendEmail(array("subject" => GIDDH_CREATE_INVOICE_ERROR_EMAIL_SUBJECT, "message" => "The invoice for #".$order->get_id()." could not be created because of an internal error. ".$invoiceResponse['message']));
                    } else {
                        $invoiceModel->saveInvoice(array("woocommerce_order_id" => $id, "giddh_invoice_id" => $invoiceResponse["body"]["uniqueName"], "giddh_voucher_number" => $invoiceResponse["body"]["number"], "invoice_type" => "cash", "date_created" => date("Y-m-d H:i:s")));
                    }
                }
            }
        }
    }
}

function giddhChangeOrderStatus($id) {
    if(get_option('giddh_company_unique_name') && get_option('giddh_company_auth_key') && get_option('giddh_shop_unique_name')) {
        $invoiceModel = new GiddhInvoiceModel();
        $invoiceDetails = $invoiceModel->getInvoice(array("woocommerce_order_id" => $id));
        if($invoiceDetails) {
            $order = wc_get_order($id);
            if($order->get_status() === "cancelled" || $order->get_status() === "refunded") {
                if($invoiceDetails["giddh_invoice_id"]) {
                    $response = giddhCancelInvoice($invoiceDetails["giddh_invoice_id"]);
                    if($response["status"] != "success") {
                        giddhSendEmail(array("subject" => GIDDH_CANCEL_INVOICE_ERROR_EMAIL_SUBJECT, "message" => "The invoice for #".$order->get_id()." could not be cancelled because of an internal error. ".$response['message']));
                    }
                }
            }

            if($invoiceDetails["invoice_type"] === "sales" && ($order->get_status() == "processing" || $order->get_status() == "completed")) {
                $payResponse = giddhPayInvoice($order, $invoiceDetails);
                if(!$payResponse || $payResponse['status'] != "success") {
                    giddhSendEmail(array("subject" => GIDDH_PAY_INVOICE_ERROR_EMAIL_SUBJECT, "message" => "The invoice for #".$order->get_id()." could not be paid because of an internal error. ".$payResponse['message']));
                }
            }
        } else {
            giddhSaveOrder($id);
        }
    }
}

function giddhSaveCategory($id) {
    $getTerm = get_term($id);
    if($getTerm->taxonomy == "product_cat") {
        $categoryModel = new GiddhCategoriesModel();
        $data = $categoryModel->getCategoryByWoocommerceCategoryId(array("woocommerce_category_id" => $id));
        if(!$data) {
            $categoryModel->saveCategory(array("woocommerce_category_name" => $getTerm->name, "woocommerce_category_id" => $id));
        } else {
            $categoryModel->updateCategoryById(array("woocommerce_category_name" => $getTerm->name, "woocommerce_category_id" => $id));
        }
    }
}
?>