<?php
function giddhGetProducts() {
    require_once(GIDDH_PLUGIN_PATH."classes/pagination.php");

    if(get_option("giddh_save_woocommerce_products") == "completed" && get_option("giddh_save_giddh_products") == "completed" && !get_option("giddh_create_woocommerce_unmatched_to_giddh") && !get_option("giddh_create_giddh_unmatched_to_woocommerce")) {
        $offset = (sanitize_text_field($_POST["page"]) - 1) * GIDDH_PAGINATION_LIMIT;
        if($offset < 0) {
            $offset = 0;
        }

        $productsModel = new GiddhProductsModel();
        $parameters = array();
        $parameters['offset'] = $offset;
        $_POST['type'] = sanitize_text_field($_POST['type']);

        if($_POST["type"] == "skumatched") {
            $notes = "Note: We have found following products with same SKU in both the applications. If you do not want to link with selected product you can edit and change its SKU. Click below button to save products in application.";
            $buttonId = "";

            $productsCount = $productsModel->getMatchedProductCount($parameters);
            $products = $productsModel->getMatchedProduct($parameters);
            $totalProductsCount = $productsModel->getTotalWoocommerceProductsCount($parameters);
        } else if($_POST["type"] == "woocommerceunmatched") {
            $notes = "Note: We have found following products with unmatched SKU in Giddh. You can either map the woocommerce products with Giddh product or create new. Click below button to save products in application.";
            $buttonId = "createwoocommerceunmatchedtogiddh";

            $productsCount = $productsModel->getUnmatchedWoocommerceProductCount($parameters);
            $products = $productsModel->getUnmatchedWoocommerceProduct($parameters);
            $totalProductsCount = $productsModel->getTotalWoocommerceProductsCount($parameters);
            $totalProductsCountWithoutSku = $productsModel->getUnmatchedWoocommerceProductCountWithoutSku();
        } else if($_POST["type"] == "giddhunmatched") {
            $notes = "Note: We have found following products with unmatched SKU in Woocommerce. You can either map the Giddh products with Woocommerce product or create new. Click below button to save products in application.";
            $buttonId = "creategiddhunmatchedtowoocommerce";

            $productsCount = $productsModel->getUnmatchedGiddhProductCount($parameters);
            $products = $productsModel->getUnmatchedGiddhProduct($parameters);
            $totalProductsCount = $productsModel->getTotalGiddhProductsCount($parameters);
            $totalProductsCountWithoutSku = $productsModel->getUnmatchedGiddhProductCountWithoutSku();
        }
        
        if($products && count($products) > 0) {
            ?>
            <form id="productForm">
                <div class="clearfix mb-5 setting-inventor">
                    <div class="row">
                        <div class="col-md-7 mb-4">
                            <p class="text-gray"><?php echo esc_html($notes); ?></p>
                            <input type="hidden" name="action" value="giddh_settings_inventory">
                            <button type="button" id="mapProducts" class="btn btn-custom">Save</button>
                            <?php if($buttonId) { ?>
                                <button type="button" id="<?php echo esc_html($buttonId);?>" total="<?php echo esc_html($productsCount["total"]);?>" invalid="<?php echo esc_html($totalProductsCountWithoutSku["total"]);?>" class="btn btn-primary createallproducts">Create All</button>
                            <?php } ?>
                        </div>
                        <div class="col-md-5 text-right mb-2 xs-left">
                            <h4 class="text-blue"><?php echo esc_html($productsCount["total"]);?>/<?php echo esc_html($totalProductsCount["total"]);?></h4>
                            <p class="font-16">Total Inventory</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-9">
                            <div class="row">
                                <div class="col-lg-3 col-md-4">
                                    <span class="font-12 text-light d-block mb-1"><?php echo ($_POST["type"] == "giddhunmatched") ? esc_html("Giddh") : esc_html("Woocommerce"); ?> Inventory</span>
                                </div>
                                <div class="col-lg-3 col-md-4">
                                    <span class="font-12 text-light d-block mb-1"><?php echo ($_POST["type"] == "giddhunmatched") ? esc_html("Woocommerce") : esc_html("Giddh"); ?> Inventory</span>
                                </div>
                                <div class="col-lg-2 col-md-4">
                                    <span class="font-12 te pl-5 xs-pl-0xt-light d-block mb-1 xs-pl-0">SKU Code</span>
                                </div>
                            </div>
                            <?php
                            foreach($products as $product) {
                            ?>
                                <div class="row">
                                    <label class="col-lg-3 col-md-4 d-flex align-items-center"><?php echo esc_html($product['title']);?></label>
                                    <div class="col-lg-3 col-md-4">
                                        <div class="form-group">
                                            <input type="hidden" name="<?php echo esc_html($_POST["type"]);?>[]" value="<?php echo esc_html($product["id"]); ?>">
                                            <input type="hidden" class="selectedProduct" name="<?php echo esc_html($_POST["type"]);?>Id[]" id="productid-<?php echo esc_html($_POST["type"].$product['id']); ?>" value="<?php if($product["matched_unique_name"]) { echo esc_html($product["matched_unique_name"]); } else { echo esc_html("create"); } ?>">
                                            <input class="form-control basic <?php echo ($_POST["type"] == "giddhunmatched") ? esc_html("woocommerce") : esc_html("giddh"); ?>ProductAutocomplete" id="<?php echo esc_html($_POST["type"].$product['id']); ?>" type="text" autocomplete="off" value="<?php if($product["matched_title"]) { echo esc_html($product["matched_title"]); } else { echo esc_html("Create New Stock"); } ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-md-4">
                                        <div class="form-group pl-5 xs-pl-0">
                                            <input type="text" class="form-control selectedProductSku" name="sku[]" value="<?php echo esc_html($product['sku']);?>" <?php if($product['sku']) { echo esc_html('readonly'); }?>>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </form>    
            <?php
            $pagination = new GiddhPagination();
            $paginationResult = $pagination->getPageLinks($productsCount["total"]);
            if($paginationResult) {
                ?>
                <div class="pagination-main text-center">
                    <nav class="d-inline-block">
                        <ul class="pagination custom-pagination" type="<?php echo esc_html($_POST["type"]);?>">
                            <?php echo $paginationResult; ?>
                        </ul>
                    </nav>
                </div>
                <?php
            }
        } else {
            ?>
            <p>No products available.</p>
            <?php
        }
    } else {
    ?>
        <p>
            <?php
            if(get_option("giddh_save_woocommerce_products") != "completed") {
                echo esc_html("Please wait while we fetch all products from Woocommerce!");
            } else if(get_option("giddh_save_giddh_products") != "completed") {
                echo esc_html("Please wait while we fetch all products from Giddh!");
            } else if(get_option("giddh_create_giddh_unmatched_to_woocommerce") && get_option("giddh_create_giddh_unmatched_to_woocommerce") != "completed") {
                echo esc_html("Please wait while we create all products in Woocommerce!");
            } else if(get_option("giddh_create_woocommerce_unmatched_to_giddh") && get_option("giddh_create_woocommerce_unmatched_to_giddh") != "completed") {
                echo esc_html("Please wait while we create all products in Giddh!");
            } else {
                echo esc_html("Please wait!");
            }
            ?>
        </p>
    <?php
    }
    wp_die();
}

function giddhGetCategories() {
    require_once(GIDDH_PLUGIN_PATH."classes/pagination.php");

    if(get_option("giddh_save_woocommerce_categories") == "completed") {
        $offset = (sanitize_text_field($_POST["page"]) - 1) * GIDDH_PAGINATION_LIMIT;
        if($offset < 0) {
            $offset = 0;
        }

        $categoriesModel = new GiddhCategoriesModel();
        $parameters = array();
        $parameters['offset'] = $offset;
        $categoriesCount = $categoriesModel->getAllCategoriesCount($parameters);
        $categories = $categoriesModel->getAllCategories($parameters);

        if($categories && count($categories) > 0) {
            ?>
            <form id="categoryForm">
                <div class="collections-main">
                    <h4 class="heading">Category</h4>
                    <p>Link Woocommerce Category with Giddh.</p>
                    <div class="row">
                        <div class="col-md-2 col-sm-4">
                            <span class="font-12 text-light d-block mb-1">Woocommerce Category</span>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <span class="font-12 text-light d-block mb-1">Giddh Stock Group</span>
                        </div>
                    </div>
            <?php
            foreach($categories as $category) {
            ?>
                <div class="form-group row">
                    <label class="col-md-2 col-sm-4 col-form-label"><?php echo esc_html(stripslashes($category['woocommerce_category_name'])); ?></label>
                    <div class="col-md-3 col-sm-6">
                        <input type="hidden" name="id[]" value="<?php echo esc_html($category['id']); ?>">
                        <input type="hidden" name="woocommerceCategories[]" value="<?php echo esc_html($category['woocommerce_category_id']); ?>">
                        <input type="hidden" name="woocommerceCategoriesName[]" value="<?php echo esc_html($category['woocommerce_category_name']); ?>">
                        <input type="hidden" name="giddhStockGroups[]" id="giddhStockGroups-<?php echo esc_html($category['woocommerce_category_id']); ?>" value="<?php if($category['giddh_stock_group_id']) { echo esc_html(stripslashes($category['giddh_stock_group_id'])); } else { echo esc_html("create"); } ?>">
                        <input type="hidden" name="giddhStockGroupsName[]" id="giddhStockGroupsName-<?php echo esc_html($category['woocommerce_category_id']); ?>" value="<?php if($category['giddh_stock_group_name']) { echo esc_html(stripslashes($category['giddh_stock_group_name'])); } else { echo esc_html("Create New Stock Group"); } ?>">
                        <input class="form-control basic giddhStockGroupAutocomplete" id="<?php echo esc_html($category['woocommerce_category_id']); ?>" value="<?php if($category['giddh_stock_group_name']) { echo esc_html(stripslashes($category['giddh_stock_group_name'])); } else { echo esc_html("Create New Stock Group"); } ?>" type="text" autocomplete="off">
                    </div>
                </div>
            <?php
            }
            ?>
                </div>
                <div class="clearfix saveBtnDiv">
                    <input type="hidden" name="action" value="giddh_settings_category">
                    <button type="button" id="mapCategories" class="btn btn-custom">Save</button>
                </div>
            </form>
            <?php
            $pagination = new GiddhPagination();
            $paginationResult = $pagination->getPageLinks($categoriesCount["total"]);
            if($paginationResult) {
                ?>
                <div class="pagination-main text-center">
                    <nav class="d-inline-block">
                        <ul class="pagination custom-pagination">
                            <?php echo $paginationResult; ?>
                        </ul>
                    </nav>
                </div>
                <?php
            }
        } else {
        ?>
            <p>No categories are available in Woocommerce.</p>
        <?php 
        }
    } else {
        ?>
            <p>Please wait while we fetch all categories from your woocommerce store!</p>
        <?php 
    }
    wp_die();
}

function giddhGetSalesAccounts() {
    if(get_option('giddh_company_unique_name') && get_option('giddh_company_auth_key') && $_REQUEST["term"]) {
        $giddhApi = new GiddhApi();
        $giddhSalesAccounts = $giddhApi->getSalesAccounts(get_option('giddh_company_unique_name'), get_option('giddh_company_auth_key'), urlencode($_REQUEST["term"]));
    
        if($giddhSalesAccounts && $giddhSalesAccounts['status'] == "success" && $giddhSalesAccounts['body'] && count($giddhSalesAccounts['body']) > 0) {
            $results = array();
            $accounts = array();
            foreach($giddhSalesAccounts['body'] as $groups) {
                if($groups['groups'] && count($groups['groups']) > 0) {
                    foreach($groups['groups'] as $group) {
                        if($group['uniqueName'] === "sales" && (($group['accounts'] && count($group['accounts']) > 0) || ($group['groups'] && count($group['groups']) > 0)) && count($results) < 10) {
                            $results = giddhCheckNestedGroups($group, $results, $accounts);
                        }
                    }
                }
            }
            
            echo json_encode($results);
            wp_die();
        } else {
            echo json_encode(array());
            wp_die();
        }
    } else {
        echo json_encode(array());
        wp_die();    
    }
}

function giddhGetStockGroups() {
    if(get_option('giddh_company_unique_name') && get_option('giddh_company_auth_key') && $_REQUEST["term"]) {
        $giddhApi = new GiddhApi();
        $giddhStockGroups = $giddhApi->getStockGroups(get_option('giddh_company_unique_name'), get_option('giddh_company_auth_key'), urlencode($_REQUEST["term"]));
    
        $createOption = array(array("uniqueName" => "create", "value" => "Create New Stock Group", "label" => "Create New Stock Group"));
    
        if($giddhStockGroups && $giddhStockGroups['status'] == "success" && $giddhStockGroups['body']['results'] && count($giddhStockGroups['body']['results']) > 0) {
            $results = array();
            foreach($giddhStockGroups['body']['results'] as $result) {
                $results[] = array("label" => $result["name"], "value" => $result["name"], "uniqueName" => $result["uniqueName"]);
            }
            
            echo json_encode(array_merge($createOption, $results));
            wp_die();
        } else {
            echo json_encode($createOption);
            wp_die();
        }
    } else {
        echo json_encode($createOption);
        wp_die();
    }
}

function giddhGetStocks() {
    if(get_option('giddh_company_unique_name') && get_option('giddh_company_auth_key') && $_REQUEST["term"]) {
        $productsModel = new GiddhProductsModel();
        $parameters = array();
        $parameters['q'] = sanitize_text_field($_REQUEST["term"]);
    
        $products = $productsModel->searchGiddhProducts($parameters);
    
        $createOption = array(array("uniqueName" => "create", "value" => "Create New Stock", "label" => "Create New Stock"));
    
        if($products && count($products) > 0) {
            echo json_encode(array_merge($createOption, $products));
            wp_die();
        } else {
            echo json_encode($createOption);
            wp_die();
        }
    } else {
        echo json_encode($createOption);
        wp_die();
    }
}

function giddhGetWoocommerceStocks() {
    if(get_option('giddh_company_unique_name') && get_option('giddh_company_auth_key') && $_REQUEST["term"]) {
        $productsModel = new GiddhProductsModel();
        $parameters = array();
        $parameters['q'] = sanitize_text_field($_REQUEST["term"]);
    
        $products = $productsModel->searchWoocommerceProducts($parameters);
    
        $createOption = array(array("uniqueName" => "create", "value" => "Create New Stock", "label" => "Create New Stock"));
    
        if($products && count($products) > 0) {
            echo json_encode(array_merge($createOption, $products));
            wp_die();
        } else {
            echo json_encode($createOption);
            wp_die();
        }
    } else {
        echo json_encode($createOption);
        wp_die();
    }
}
?>