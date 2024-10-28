<?php
function giddhAddProduct($product, $tags) {
	$productsModel = new GiddhProductsModel();

	$categories = $product->get_category_ids();

	if($tags == "giddhauto") {
        $is_saved = 1;
    } else {
        $is_saved = 0;
    }

	if($product->get_type() === "variable") {
		$variants = $product->get_available_variations();
		if($variants && count($variants) > 0) {
			foreach($variants as $variant) {
				$attributeName = "";

				if($variant["attributes"] && count($variant["attributes"]) > 0) {
					foreach($variant["attributes"] as $key => $value) {
						$attributeName .= " ".$value;
					}
				}

				$parameters = array();
				$parameters["product_id"] = $product->get_id();
				$parameters["title"] = $product->get_name().$attributeName;
			    $parameters["variant_id"] = $variant["variation_id"];
			    $parameters["stock_unit_code"] = "kg";
			    $parameters["amount"] = $variant["display_price"];
			    $parameters["quantity"] = $variant["max_qty"];
			    $parameters["sku"] = $variant["sku"];
			    $parameters["is_saved"] = $is_saved;
			    $parameters["category_id"] = $categories[0];

			    $productsModel->saveWoocommerceProduct($parameters);
			}
		} else {
			$parameters = array();
			$parameters["product_id"] = $product->get_id();
			$parameters["title"] = $product->get_name();
		    $parameters["variant_id"] = "";
		    $parameters["stock_unit_code"] = "kg";
		    $parameters["amount"] = $product->get_price();
		    $parameters["quantity"] = $product->get_stock_quantity();
		    $parameters["sku"] = $product->get_sku();
		    $parameters["is_saved"] = $is_saved;
		    $parameters["category_id"] = $categories[0];

		    $productsModel->saveWoocommerceProduct($parameters);
		}
	} else {
		$parameters = array();
		$parameters["product_id"] = $product->get_id();
		$parameters["title"] = $product->get_name();
	    $parameters["variant_id"] = "";
	    $parameters["stock_unit_code"] = "kg";
	    $parameters["amount"] = $product->get_price();
	    $parameters["quantity"] = $product->get_stock_quantity();
	    $parameters["sku"] = $product->get_sku();
	    $parameters["is_saved"] = $is_saved;
	    $parameters["category_id"] = $categories[0];

	    $productsModel->saveWoocommerceProduct($parameters);
	}
}

function giddhEditProduct($product, $existingProduct) {
    $productsModel = new GiddhProductsModel();

    $categories = $product->get_category_ids();

    $newVariants = array();

    if($product->get_type() === "variable") {
		$variants = $product->get_available_variations();
		if($variants && count($variants) > 0) {
			foreach($variants as $variant) {
				$attributeName = "";

				if($variant["attributes"] && count($variant["attributes"]) > 0) {
					foreach($variant["attributes"] as $key => $value) {
						$attributeName .= " ".$value;
					}
                }
                
                $newVariants[] = $variant["variation_id"];

				$parameters = array();
				$parameters["product_id"] = $product->get_id();
				$parameters["title"] = $product->get_name().$attributeName;
			    $parameters["variant_id"] = $variant["variation_id"];
			    $parameters["stock_unit_code"] = "kg";
			    $parameters["amount"] = $variant["display_price"];
			    $parameters["quantity"] = $variant["max_qty"];
			    $parameters["sku"] = $variant["sku"];
                $parameters["category_id"] = $categories[0];
                
                $getWoocommerceProductByVariantId = $productsModel->getWoocommerceProductByVariantId($variant["variation_id"]);
                if(!$getWoocommerceProductByVariantId) {
                    $parameters["is_saved"] = 0;
                    $productsModel->saveWoocommerceProduct($parameters);
                } else {
                    $productsModel->updateWoocommerceProductByVariantId($parameters, $variant["variation_id"]);
                }
            }

            foreach($existingProduct as $variant) {
                $parameters = array();

                if(!$variant["variant_id"]) {
                    $parameters["id"] = $variant["id"];
                    $productsModel->deleteWoocommerceProductById($parameters);
                } else {
                    if(!in_array($variant["variant_id"], $newVariants)) {
                        $parameters["variant_id"] = $variant["variant_id"];
                        $productsModel->deleteWoocommerceProductByVariantId($parameters);
                    }
                }
            }
		} else {
            foreach($existingProduct as $variant) {
                $parameters = array();

                if($variant["variant_id"]) {
                    $parameters["variant_id"] = $variant["variant_id"];
                    $productsModel->deleteWoocommerceProductByVariantId($parameters);
                }
            }

			$parameters = array();
			$parameters["title"] = $product->get_name();
		    $parameters["variant_id"] = "";
		    $parameters["stock_unit_code"] = "kg";
		    $parameters["amount"] = $product->get_price();
		    $parameters["quantity"] = $product->get_stock_quantity();
		    $parameters["sku"] = $product->get_sku();
		    $parameters["category_id"] = $categories[0];

            $existingProduct = $productsModel->getWoocommerceProduct(array("product_id" => $product->get_id()));
            if(!$existingProduct) {
                $parameters["product_id"] = $product->get_id();
                $parameters["is_saved"] = 0;
                $productsModel->saveWoocommerceProduct($parameters);
            } else {
                $productsModel->updateWoocommerceProductById($parameters, $product->get_id());
            }
		}
	} else {
		foreach($existingProduct as $variant) {
            $parameters = array();

            if($variant["variant_id"]) {
                $parameters["variant_id"] = $variant["variant_id"];
                $productsModel->deleteWoocommerceProductByVariantId($parameters);
            }
        }

        $parameters = array();
        $parameters["title"] = $product->get_name();
        $parameters["variant_id"] = "";
        $parameters["stock_unit_code"] = "kg";
        $parameters["amount"] = $product->get_price();
        $parameters["quantity"] = $product->get_stock_quantity();
        $parameters["sku"] = $product->get_sku();
        $parameters["category_id"] = $categories[0];

        $existingProduct = $productsModel->getWoocommerceProduct(array("product_id" => $product->get_id()));
        if(!$existingProduct) {
            $parameters["product_id"] = $product->get_id();
            $parameters["is_saved"] = 0;
            $productsModel->saveWoocommerceProduct($parameters);
        } else {
            $productsModel->updateWoocommerceProductById($parameters, $product->get_id());
        }
	}
}

function giddhUpdateProductStock($availableInventory, $productId, $selectedVariant) {
    try {
        if($selectedVariant) {
            $productsModel = new GiddhProductsModel();
            $productsModel->updateWoocommerceProductVariationStock(array("meta_value" => $availableInventory), $selectedVariant);
        } else {
            $objProduct = new WC_Product($productId);
            $objProduct->set_manage_stock(true);
            $objProduct->set_stock_quantity($availableInventory);
            $objProduct->save();
        }
        return true;
    } catch(Exception $e) {
        return $e->getMessage();
    }
}
?>