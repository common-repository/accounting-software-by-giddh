<?php
function giddhCreateAccount($order) {
    $customer = new WC_Customer($order->get_id());
    $billingAddress = $order->get_billing_address_1()." ".$order->get_billing_address_2();

    $customerName = (!$customer->get_first_name() && !$customer->get_last_name()) ? $order->get_formatted_billing_full_name() : $customer->get_first_name()." ".$customer->get_last_name();

    $parameters = array(
        "activeGroupUniqueName" => "sundrydebtors",
        "name" => $customerName,
        "uniqueName" => "",
        "openingBalanceType" => "CREDIT",
        "foreignOpeningBalance" => "0",
        "openingBalance" => "0",
        "mobileNo" => $order->get_billing_phone(),
        "mobileCode" => "",
        "email" => ($customer->get_email()) ? $customer->get_email() : $order->get_billing_email(),
        "companyName" => $order->get_billing_company(),
        "attentionTo" => "",
        "description" => $order->get_customer_note(),
        "addresses" => array(
            array(
                "gstNumber" => "",
                "address" => $billingAddress,
                "state" => array(
                    "code" => $order->get_billing_state(),
                    "name" => WC()->countries->get_states($order->get_billing_country())[$order->get_billing_state()],
                    "stateGstCode" => ""
                ),
                "stateCode" => $order->get_billing_state(),
                "isDefault" => 1,
                "isComposite" => false,
                "partyType" => "NOT APPLICABLE"
            )
        ),
        "country" => array(
            "countryCode" => $order->get_billing_country(),
        ),
        "currency" => $order->get_currency(),
        "closingBalanceTriggerAmountType" => "CREDIT"
    );

    $giddhApi = new GiddhApi();
    $response = $giddhApi->createAccount(get_option('giddh_company_unique_name'), get_option('giddh_company_auth_key'), $parameters);
    return $response;
}

function giddhCreateSalesInvoice($order, $accountUniqueName) {
    $parameters = giddhGetInvoiceArray($order, $accountUniqueName, "sales");

    giddhCreateLog('create-sales-invoice-request.log', json_encode($parameters));

    $giddhApi = new GiddhApi();
    $response = $giddhApi->createSalesInvoice(get_option('giddh_company_unique_name'), $accountUniqueName, get_option('giddh_company_auth_key'), $parameters);
    giddhCreateLog('create-sales-invoice-response.log', json_encode($response));
    return $response;
}

function giddhCreateCashInvoice($order, $accountUniqueName) {
    $parameters = giddhGetInvoiceArray($order, $accountUniqueName, "cash");

    giddhCreateLog('create-cash-invoice.log', json_encode($parameters));

    $giddhApi = new GiddhApi();
    $response = $giddhApi->createCashInvoice(get_option('giddh_company_unique_name'), $accountUniqueName, get_option('giddh_company_auth_key'), $parameters);
    giddhCreateLog('create-cash-invoice-response.log', json_encode($response));
    return $response;
}

function giddhGetInvoiceArray($order, $accountUniqueName, $type) {
    $entries = array();
    $entriesLoop = 0;
    $isInclusiveTax = (!wc_tax_enabled() || wc_prices_include_tax()) ? 'true' : 'false';

    foreach($order->get_items() as $item) {
        $product = wc_get_product($item->get_product_id());

        $entrySku = $product->get_sku();
        $amountForAccount = giddhFormatPrice($item->get_total());

        $amountForAccount += giddhFormatPrice($item->get_subtotal() - $item->get_total()); // Discount

        $rate = giddhFormatPrice($amountForAccount/$item->get_quantity());

        if($item->get_variation_id()) {
            $variants = $product->get_available_variations();
            if($variants && count($variants) > 0) {
                foreach($variants as $variant) {
                    if($variant["variation_id"] == $item->get_variation_id()) {
                        $itemPrice = $variant["display_price"];
                        $entrySku = $variant["sku"];
                    }
                }
            } else {
                $itemPrice = $product->get_price();
            }
        } else {
            $itemPrice = $product->get_price();
        }

        $taxes = array();

        if(wc_tax_enabled() && $item->get_total_tax() > 0) {
            $tax = new WC_Tax();
            $itemTaxes = $tax->get_rates($item->get_tax_class());
            if($itemTaxes && count($itemTaxes) > 0) {
                foreach($itemTaxes as $tax_line) {
                    $taxes[] = array(
                        "taxPercent" => number_format($tax_line['rate'], 0),
                        "calculationMethod" => "OnTaxableAmount"
                    );
                }
            }
        }

        $entries[$entriesLoop] = array(
            "transactions" => array(
                array(
                    "account" => array(
                        "uniqueName" => "sales",
                        "name" => "Sales"
                    ),
                    "amount" => array(
                        "type" => "DEBIT",
                        "amountForAccount" => giddhFormatPrice($amountForAccount)
                    ),
                    "stock" => array(
                        "quantity" => $item->get_quantity(),
                        "sku" => $entrySku,
                        "name" => $item->get_name(),
                        "uniqueName" => "",
                        "rate" => array(
                            "amountForAccount" => giddhFormatPrice($rate)
                        ),
                        "stockUnit" => array(
                            "code" => "nos"
                        )
                    )
                )
            ),
            "date" => date("d-m-Y"),
            "taxes" => $taxes,
            "hsnNumber" => null,
            "sacNumber" => null,
            "description" => "",
            "voucherNumber" => "",
            "voucherType" => "sales",
            "discounts" => array(
                array(
                    "calculationMethod" => "FIX_AMOUNT",
                    "amount" => array(
                        "type" => "DEBIT",
                        "amountForAccount" => giddhFormatPrice($item->get_subtotal() - $item->get_total())
                    ),
                    "discountValue" => giddhFormatPrice($item->get_subtotal() - $item->get_total()),
                    "name" => "",
                    "particular" => ""
                )
            )
        );

        $entriesLoop++;
    }

    $shippingTotal = $order->get_shipping_total();
    if($shippingTotal > 0 && get_option('giddh_shipping_account')) {
        if($isInclusiveTax == 'true' && $order->get_shipping_tax() > 0) {
            $shippingTotal += $order->get_shipping_tax();
        }

        $entries[$entriesLoop] = array(
            "transactions" => array(
                array(
                    "account" => array(
                        "uniqueName" => get_option('giddh_shipping_account'),
                        "name" => get_option('giddh_shipping_account_name')
                    ),
                    "amount" => array(
                        "type" => "DEBIT",
                        "amountForAccount" => giddhFormatPrice($shippingTotal)
                    )
                )
            ),
            "date" => date("d-m-Y"),
            "taxes" => array(
                
            ),
            "hsnNumber" => null,
            "sacNumber" => null,
            "description" => "",
            "voucherNumber" => "",
            "voucherType" => "sales",
            "discounts" => array(
                array(
                    "calculationMethod" => "FIX_AMOUNT",
                    "amount" => array(
                        "type" => "DEBIT",
                        "amountForAccount" => 0
                    ),
                    "discountValue" => 0,
                    "name" => "",
                    "particular" => ""
                )
            )
        );
    }

    $paymentsModel = new GiddhPaymentsModel();
    $giddhPaymentAccount = $paymentsModel->getPaymentGatewayByWoocommercePaymentId(array("woocommerce_payment_id" => $order->get_payment_method()));

    $locationDetails = false;

    $billingAddress = $order->get_billing_address_1()." ".$order->get_billing_address_2();
    $shippingAddress = $order->get_shipping_address_1()." ".$order->get_shipping_address_2();

    $customer = new WC_Customer($order->get_id());

    $customerName = (!$customer->get_first_name() && !$customer->get_last_name()) ? $order->get_formatted_billing_full_name() : $customer->get_first_name()." ".$customer->get_last_name();

    $parameters = array(
        "account" => array(
            "currencySymbol" => "",
            "currency" => array(
                "code" => $order->get_currency()
            ),
            "billingDetails" => array(
                "address" => array(
                    trim($billingAddress)
                ),
                "state" => array(
                    "code" => $order->get_billing_state(),
                    "name" => WC()->countries->get_states($order->get_billing_country())[$order->get_billing_state()]
                ),
                "gstNumber" => "",
                "panNumber" => "",
                "countryName" => WC()->countries->countries[$order->get_billing_country()],
                "stateCode" => $order->get_billing_state(),
                "stateName" => WC()->countries->get_states($order->get_billing_country())[$order->get_billing_state()]
            ),
            "shippingDetails" => array(
                "address" => array(
                    trim($shippingAddress)
                ),
                "state" => array(
                    "code" => $order->get_shipping_state(),
                    "name" => WC()->countries->get_states($order->get_shipping_country())[$order->get_shipping_state()]
                ),
                "gstNumber" => "",
                "panNumber" => "",
                "countryName" => WC()->countries->countries[$order->get_shipping_country()],
                "stateCode" => $order->get_shipping_state(),
                "stateName" => WC()->countries->get_states($order->get_shipping_country())[$order->get_shipping_state()]
            ),
            "name" => trim($customerName),
            "customerName" => trim($customerName),
            "uniqueName" => $accountUniqueName,
            "email" => ($customer->get_email()) ? $customer->get_email() : $order->get_billing_email(),
            "attentionTo" => "",
            "contactNumber" => "",
            "mobileNumber" => $order->get_billing_phone(),
            "country" => array(
                "countryName" => WC()->countries->countries[$order->get_billing_country()],
                "countryCode" => $order->get_billing_country()
            )
        ),
        "updateAccountDetails" => false,
        "entries" => $entries,
        "date" => date("d-m-Y"),
        "type" => $type,
        "exchangeRate" => "1",
        "dueDate" => date("d-m-Y"),
        "templateDetails" => array(
            "other" => array(
                "shippingDate" => "",
                "shippedVia" => null,
                "trackingNumber" => null,
                "customField1" => null,
                "customField2" => null,
                "customField3" => null,
                "message2" => "Woocommerce Order ID: #".$order->get_id()."\n".$order->get_customer_note()
            )
        ),
        "deposit" => array(
            "type" => "DEBIT",
            "accountUniqueName" => ($giddhPaymentAccount) ? $giddhPaymentAccount["giddh_account_id"] : "",
            "amountForAccount" => ($order->get_status() == "pending" || !$giddhPaymentAccount || $type === "cash") ? 0 : giddhFormatPrice($order->get_total())
        ),
        "warehouse" => array(
            "name" => "",
            "uniqueName" => ""
        ),
        "number" => $order->get_id(),
        "ecommerceDetails" => array(
            "uniqueName" => get_option('giddh_shop_unique_name')
        ),
        "roundOffApplicable" => false,
        "applyApplicableTaxes" => true
    );

    return $parameters;
}

function giddhPayInvoice($order, $invoiceDetails) {
    $paymentsModel = new GiddhPaymentsModel();
    $giddhPaymentAccount = $paymentsModel->getPaymentGatewayByWoocommercePaymentId(array("woocommerce_payment_id" => $order->get_payment_method()));

    $customersModel = new GiddhCustomersModel();
    $getCustomer = $customersModel->getCustomer(array("woocommerce_customer_id" => $order->get_customer_id()));

    $parameters = array(
        "invoiceNumber" => $invoiceDetails["giddh_voucher_number"],
        "voucherType" => $invoiceDetails["invoice_type"]
    );

    $giddhApi = new GiddhApi();
    $giddhInvoiceDetails = $giddhApi->getInvoiceDetails(get_option('giddh_company_unique_name'), $getCustomer['giddh_account_id'], get_option('giddh_company_auth_key'), $parameters);

    $amount = $giddhInvoiceDetails['body']['balanceTotal']['amountForAccount'];

    if($amount > 0) {
        $parameters = array(
            "paymentDate" => date("d-m-Y"),
            "amount" => $amount,
            "accountUniqueName" => ($giddhPaymentAccount) ? $giddhPaymentAccount["giddh_account_id"] : "",
            "exchangeRate" => 1,
            "action" => "paid"
        );

        giddhCreateLog('pay-invoice-request.log', json_encode($parameters));

        $response = $giddhApi->actionSalesInvoice(get_option('giddh_company_unique_name'), $invoiceDetails["giddh_invoice_id"], get_option('giddh_company_auth_key'), $parameters);

        giddhCreateLog('pay-invoice-response.log', json_encode($response));

        return $response;
    } else {
        return array("status" => "success");
    }
}

function giddhEditSalesInvoice($order, $accountUniqueName) {
    $parameters = giddhGetGiddhInvoice($order, $accountUniqueName, "sales");

    giddhCreateLog('edit-sales-invoice-request.log', json_encode($parameters));

    $giddhApi = new GiddhApi();
    $response = $giddhApi->updateSalesInvoice(get_option('giddh_company_unique_name'), $accountUniqueName, get_option('giddh_company_auth_key'), $parameters);
    giddhCreateLog('edit-sales-invoice-response.log', json_encode($response));
    return $response;
}

function giddhEditCashInvoice($order, $accountUniqueName) {
    $parameters = giddhGetGiddhInvoice($order, $accountUniqueName, "sales");

    giddhCreateLog('edit-cash-invoice-request.log', json_encode($parameters));

    $giddhApi = new GiddhApi();
    $response = $giddhApi->updateCashInvoice(get_option('giddh_company_unique_name'), $accountUniqueName, get_option('giddh_company_auth_key'), $parameters);
    giddhCreateLog('edit-cash-invoice-response.log', json_encode($response));
    return $response;
}

function giddhGetGiddhInvoice($order, $accountUniqueName, $type) {
    $invoiceModel = new GiddhInvoiceModel();
    $getInvoice = $invoiceModel->getInvoice(array("woocommerce_order_id" => $order->get_id()));

    $parameters = array(
        "invoiceNumber" => $getInvoice["giddh_voucher_number"],
        "voucherType" => $type
    );

    $giddhApi = new GiddhApi();
    $invoiceDetails = $giddhApi->getInvoiceDetails(get_option('giddh_company_unique_name'), $accountUniqueName, get_option('giddh_company_auth_key'), $parameters);

    if(!$invoiceDetails || $invoiceDetails["status"] == "error") {
        return false;
    }

    $invoiceDetails = $invoiceDetails["body"];
    $isInclusiveTax = (!wc_tax_enabled() || wc_prices_include_tax()) ? 'true' : 'false';
    $processedItemKeys = array();

    if($order->get_items() && count($order->get_items()) > 0) {
        foreach($order->get_items() as $item) {
            $itemFound = false;

            $product = wc_get_product($item->get_product_id());

            $entrySku = $product->get_sku();

            if($item->get_variation_id()) {
                $variants = $product->get_available_variations();
                if($variants && count($variants) > 0) {
                    foreach($variants as $variant) {
                        if($variant["variation_id"] == $item->get_variation_id()) {
                            $itemPrice = $variant["display_price"];
                            $entrySku = $variant["sku"];
                        }
                    }
                } else {
                    $itemPrice = $product->get_price();
                }
            } else {
                $itemPrice = $product->get_price();
            }

            if($invoiceDetails['entries'] && count($invoiceDetails['entries']) > 0) {
                foreach($invoiceDetails['entries'] as $key => $entry) {
                    if($entry['skuCode'] == $entrySku && !in_array($key, $processedItemKeys)) {
                        $itemFound = $key;
                        $processedItemKeys[] = $itemFound;
                        break;
                    }
                }
            }

            if($itemFound === false) {
                $amountForAccount = giddhFormatPrice($item->get_total());

                $amountForAccount += giddhFormatPrice($item->get_subtotal() - $item->get_total()); // Discount

                $rate = giddhFormatPrice($amountForAccount/$item->get_quantity());

                $taxes = array();

                if(wc_tax_enabled() && $item->get_total_tax() > 0) {
                    $tax = new WC_Tax();
                    $itemTaxes = $tax->get_rates($item->get_tax_class());
                    if($itemTaxes && count($itemTaxes) > 0) {
                        foreach($itemTaxes as $tax_line) {
                            $taxes[] = array(
                                "taxPercent" => number_format($tax_line['rate'], 0),
                                "calculationMethod" => "OnTaxableAmount"
                            );
                        }
                    }
                }

                $invoiceDetails['entries'][count($invoiceDetails['entries'])] = array(
                    "transactions" => array(
                        array(
                            "account" => array(
                                "uniqueName" => "sales",
                                "name" => "Sales"
                            ),
                            "amount" => array(
                                "type" => "DEBIT",
                                "amountForAccount" => giddhFormatPrice($amountForAccount)
                            ),
                            "stock" => array(
                                "quantity" => $item->get_quantity(),
                                "sku" => $entrySku,
                                "name" => $item->get_name(),
                                "uniqueName" => "",
                                "rate" => array(
                                    "amountForAccount" => giddhFormatPrice($rate)
                                ),
                                "stockUnit" => array(
                                    "code" => "nos"
                                )
                            )
                        )
                    ),
                    "date" => $invoiceDetails["date"],
                    "taxes" => $taxes,
                    "hsnNumber" => null,
                    "sacNumber" => null,
                    "description" => "",
                    "voucherNumber" => "",
                    "voucherType" => "sales",
                    "discounts" => array(
                        array(
                            "calculationMethod" => "FIX_AMOUNT",
                            "amount" => array(
                                "type" => "DEBIT",
                                "amountForAccount" => giddhFormatPrice($item->get_subtotal() - $item->get_total())
                            ),
                            "discountValue" => giddhFormatPrice($item->get_subtotal() - $item->get_total()),
                            "name" => "",
                            "particular" => ""
                        )
                    )
                );
            } else {
                $amountForAccount = giddhFormatPrice($item->get_total());

                $amountForAccount += giddhFormatPrice($item->get_subtotal() - $item->get_total()); // Discount

                if($item->get_variation_id()) {
                    $variants = $product->get_available_variations();
                    if($variants && count($variants) > 0) {
                        foreach($variants as $variant) {
                            if($variant["variation_id"] == $item->get_variation_id()) {
                                $itemPrice = $variant["display_price"];
                            }
                        }
                    } else {
                        $itemPrice = $product->get_price();
                    }
                } else {
                    $itemPrice = $product->get_price();
                }

                $invoiceDetails['entries'][$itemFound]["transactions"] = array(
                    array(
                        "account" => array(
                            "uniqueName" => "sales",
                            "name" => "Sales"
                        ),
                        "amount" => array(
                            "type" => "DEBIT",
                            "amountForAccount" => $amountForAccount
                        ),
                        "stock" => array(
                            "quantity" => $item->get_quantity(),
                            "sku" => $entrySku,
                            "name" => $item->get_name(),
                            "uniqueName" => $invoiceDetails['entries'][$itemFound]["transactions"][0]["stock"]["uniqueName"],
                            "rate" => array(
                                "amountForAccount" => giddhFormatPrice($amountForAccount/$item->get_quantity())
                            ),
                            "stockUnit" => array(
                                "code" => "nos"
                            )
                        )
                    )
                );

                $invoiceDetails['entries'][$itemFound]["discounts"] = array(
                    array(
                        "calculationMethod" => "FIX_AMOUNT",
                        "amount" => array(
                            "type" => "DEBIT",
                            "amountForAccount" => giddhFormatPrice($item->get_subtotal() - $item->get_total())
                        ),
                        "discountValue" => giddhFormatPrice($item->get_subtotal() - $item->get_total()),
                        "name" => "",
                        "particular" => ""
                    )
                );

                $taxes = array();

                if(wc_tax_enabled() && $item->get_total_tax() > 0) {
                    $tax = new WC_Tax();
                    $itemTaxes = $tax->get_rates($item->get_tax_class());
                    if($itemTaxes && count($itemTaxes) > 0) {
                        foreach($itemTaxes as $tax_line) {
                            $taxes[] = array(
                                "taxPercent" => number_format($tax_line['rate'], 0),
                                "calculationMethod" => "OnTaxableAmount"
                            );
                        }
                    }
                }

                $invoiceDetails['entries'][$itemFound]["taxes"] = $taxes;
            }
        }

        if($invoiceDetails['entries'] && count($invoiceDetails['entries']) > 0) {
            $removeItems = array();
            foreach($invoiceDetails['entries'] as $key => $entry) {
                $itemFound = false;

                foreach($order->get_items() as $item) {
                    $product = wc_get_product($item->get_product_id());

                    $entrySku = $product->get_sku();

                    if($item->get_variation_id()) {
                        $variants = $product->get_available_variations();
                        if($variants && count($variants) > 0) {
                            foreach($variants as $variant) {
                                if($variant["variation_id"] == $item->get_variation_id()) {
                                    $entrySku = $variant["sku"];
                                }
                            }
                        }
                    }

                    if($entry['skuCode'] == $entrySku) {
                        $itemFound = $key;
                        break;
                    }
                }

                if($entry['skuCode'] && $itemFound === false) {
                    $removeItems[] = $key;
                }
            }

            if($removeItems && count($removeItems) > 0) {
                foreach($removeItems as $removeItem) {
                    unset($invoiceDetails['entries'][$removeItem]);
                }
            }
        }
    } else {
        $invoiceDetails['entries'] = null;
    }

    $shippingTotal = $order->get_shipping_total();
    if($shippingTotal > 0 && get_option('giddh_shipping_account')) {
        $itemFound = false;

        if($invoiceDetails['entries'] && count($invoiceDetails['entries']) > 0) {
            foreach($invoiceDetails['entries'] as $key => $entry) {
                foreach($entry['transactions'] as $transaction) {
                    if($transaction['account']['uniqueName'] == get_option('giddh_shipping_account')) {
                        $itemFound = $key;
                        break;
                    }
                }

                if($itemFound) {
                    break;
                }
            }
        }

        if($itemFound) {
            $invoiceDetails['entries'][$itemFound]["isInclusiveTax"] = $isInclusiveTax;
            $invoiceDetails['entries'][$itemFound]["transactions"] = array(
                array(
                    "account" => array(
                        "uniqueName" => get_option('giddh_shipping_account'),
                        "name" => get_option('giddh_shipping_account_name')
                    ),
                    "amount" => array(
                        "type" => "DEBIT",
                        "amountForAccount" => giddhFormatPrice($shippingTotal)
                    )
                )
            );
        }
    }

    $invoiceDetails["templateDetails"] = array(
        "other" => array(
            "shippingDate" => "",
            "shippedVia" => null,
            "trackingNumber" => null,
            "customField1" => null,
            "customField2" => null,
            "customField3" => null,
            "message2" => "Woocommerce Order ID: #".$order->get_id()."\n".$order->get_customer_note()
        )
    );

    $invoiceDetails["ecommerceDetails"] = array(
        "uniqueName" => get_option('giddh_shop_unique_name')
    );

    $invoiceDetails["roundOffApplicable"] = false;
    $invoiceDetails["applyApplicableTaxes"] = true;

    return $invoiceDetails;
}

function giddhCancelInvoice($invoiceUniqueName) {
    $parameters = array(
        "action" => "cancel"
    );

    $giddhApi = new GiddhApi();
    $response = $giddhApi->actionSalesInvoice(get_option('giddh_company_unique_name'), $invoiceUniqueName, get_option('giddh_company_auth_key'), $parameters);
    return $response;
}

function giddhSendEmail($parameters) {
    $apiParams = array();
    $apiParams["subject"] = $parameters["subject"];
    $apiParams["sendFrom"] = GIDDH_DEFAULT_EMAIL_FROM;
    $apiParams["sendTo"]["recipients"][0] = get_option("giddh_notification_email");
    $apiParams["sendCc"]["recipients"][0] = "";
    $apiParams["content"] = giddhGetErrorTemplate(array("customerName" => "Admin", "errorMessage" => $parameters["message"]));

    $giddhApi = new GiddhApi();
    $response = $giddhApi->sendEmail(get_option('giddh_company_unique_name'), get_option('giddh_company_auth_key'), $apiParams);
    return $response;
}

function giddhUpdateStock($stockUniqueName, $stockGroupUniqueName, $skuCode) {
    $giddhApi = new GiddhApi();

    $stockDetails = $giddhApi->getStock(get_option('giddh_company_unique_name'), $stockGroupUniqueName, $stockUniqueName, get_option('giddh_company_auth_key'));

    $parameters = $stockDetails["body"];
    $parameters["skuCode"] = $skuCode;
    
    $response = $giddhApi->updateStock(get_option('giddh_company_unique_name'), $stockGroupUniqueName, $stockUniqueName, get_option('giddh_company_auth_key'), $parameters);
    return $response;
}

function giddhGetInvoiceDetails($order, $accountUniqueName, $type) {
    $invoiceModel = new GiddhInvoiceModel();
    $getInvoice = $invoiceModel->getInvoice(array("woocommerce_order_id" => $order->get_id()));

    $parameters = array(
        "invoiceNumber" => $getInvoice["giddh_voucher_number"],
        "voucherType" => $type
    );

    $giddhApi = new GiddhApi();
    $invoiceDetails = $giddhApi->getInvoiceDetails(get_option('giddh_company_unique_name'), $accountUniqueName, get_option('giddh_company_auth_key'), $parameters);

    return $invoiceDetails;
}

function giddhGetElectronVersion() {
    $response = wp_remote_get("https://s3-ap-south-1.amazonaws.com/giddh-app-builds/latest.yml");
    $body = $response['body'];
    $versionString = explode("files", $body);
    $version = explode(" ", $versionString[0]);
    return $version[1];
}
?>