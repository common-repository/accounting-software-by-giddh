var loading = false;
var currentSelectedPaymentGateway = false;
var timeoutObj = "";
var bulkFailedProductsCount = 0;
var giddhShippingAccount = {};
var adminAjaxUrl = ajaxurl;

jQuery(document).ready(function() {
    jQuery('.dropdowns').select2();
    disableSelectedLocations();

    jQuery(document).on("click", "#connectGiddh", function() {
        if(loading) {
            return false;
        }
        
        var authKey = jQuery.trim(jQuery("#authKey").val());
        var companyUniqueName = jQuery.trim(jQuery("#companyUniqueName").val());

        if(!authKey) {
            flashError("<p>Please enter Auth Key!</p>");
            return false;
        }

        if(!companyUniqueName) {
            flashError("<p>Please enter Company Unique Name!</p>");
            return false;
        }

        jQuery.ajax({
            url: adminAjaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {authKey: authKey, companyUniqueName: companyUniqueName, action: "giddh_connect"},
            beforeSend: function() {
                loading = true;
                jQuery("#connectGiddh").html("Verifying...");
            },
            success: function(response) {
                if(response.status == "success") {
                    jQuery("#connectGiddh").html("Verified");
                    setTimeout(function() {
                        window.location.href = "admin.php?page=giddh&view="+response.next+"&action=connected&tab=1";
                    }, 1500);
                } else {
                    jQuery("#connectGiddh").html("Connect to Giddh");
                    loading = false;
                    flashError("<p>"+response.message+"</p>");
                }
            },
            error: function() {
                jQuery("#connectGiddh").html("Connect to Giddh");
                loading = false;
                flashError("<p>Something went wrong! Please try again.</p>");
            }
        });
    });

    jQuery(document).on("click", "#saveSettings", function() {
        if(loading) {
            return false;
        }

        jQuery.ajax({
            url: adminAjaxUrl,
            type: 'POST',
            dataType: 'json',
            data: jQuery("#settingsForm").serializeArray(),
            beforeSend: function() {
                loading = true;
                jQuery("#saveSettings").text("Loading...");
            },
            success: function(response) {
                loading = false;
                jQuery("#saveSettings").text("Save");

                if(response.status == "success") {
                    flashNotice("<p>"+response.message+"</p>");
                } else if(response.status == "connect") {
                    window.location.href = "admin.php?page=giddh&view=connect";
                } else {
                    flashError("<p>"+response.message+"</p>");
                }
            },
            error: function() {
                jQuery("#saveSettings").text("Save");
                loading = false;
                flashError("<p>Something went wrong! Please try again.</p>");
            }
        });
    });

    jQuery(document).on("click", "#mapPaymentGateways", function() {
        if(loading) {
            return false;
        }

        var woocommerceTempPayments = [];
        jQuery(".woocommercePaymentGateways").each(function() {
            if(jQuery(this).val()) {
                woocommerceTempPayments.push(jQuery(this).val());
            }
        });

        if(woocommerceTempPayments.length == 0 && !savedPayments) {
            flashError("<p>Please select atleast 1 WooCommerce payment to map with Giddh.</p>");
            return false;
        }

        var giddhTempAccounts = [];
        jQuery(".giddhAccounts").each(function() {
            if(jQuery(this).val()) {
                giddhTempAccounts.push(jQuery(this).val());
            }
        });

        if(giddhTempAccounts.length == 0 && !savedPayments) {
            flashError("<p>Please select atleast 1 Giddh payment to map with WooCommerce.</p>");
            return false;
        }

        var errorMessages = [];

        jQuery(".woocommercePaymentGateways").each(function(i, v) {
            if(jQuery(this).val()) {
                if(!jQuery(".giddhAccounts:eq("+i+")").val()) {
                    errorMessages.push("Select a Giddh account to link with the chosen Woocommerce payment " + jQuery(".woocommercePaymentGateways:eq("+i+") option:selected").text());
                }
            } else if(jQuery(".giddhAccounts:eq("+i+")").val()) {
                errorMessages.push("Select a WooCoomerce payment to link with the chosen Giddh Account " + jQuery(".giddhAccounts:eq("+i+") option:selected").text());
            }
        });

        if(errorMessages.length > 0) {
            flashError("<p>"+errorMessages.join("<br>")+"</p>");
            return false;
        }

        jQuery.ajax({
            url: adminAjaxUrl,
            type: 'POST',
            dataType: 'json',
            data: jQuery("#paymentsForm").serializeArray(),
            beforeSend: function() {
                loading = true;
                jQuery("#mapPaymentGateways").text("Loading...");
            },
            success: function(response) {
                loading = false;
                jQuery("#mapPaymentGateways").text("Save");

                if(response.status == "success") {
                    window.location.href = "admin.php?page=giddh&view=settings&action=payment&tab=4";
                } else if(response.status == "connect") {
                    window.location.href = "admin.php?page=giddh&view=connect";
                } else {
                    flashError("<p>"+response.message+"</p>");
                }
            },
            error: function() {
                jQuery("#mapPaymentGateways").text("Save");
                loading = false;
                flashError("<p>Something went wrong! Please try again.</p>");
            }
        });
    });

    // Add minus icon for collapse element which is open by default
    jQuery(".collapse.show").each(function(){
        jQuery(this).prev(".card-header").find(".fa").addClass("fa-caret-down").removeClass("fa-caret-right");
    });

    // Toggle plus minus icon on show hide of collapse element
    jQuery(".collapse").on('show.bs.collapse', function(){
        jQuery(this).prev(".card-header").find(".fa").removeClass("fa-caret-right").addClass("fa-caret-down");
    }).on('hide.bs.collapse', function(){
        jQuery(this).prev(".card-header").find(".fa").removeClass("fa-caret-down").addClass("fa-caret-right");
    });

    jQuery(document).on("click", "#addMorePaymentGateway", function() {
        var html = '<div class="payment-mapping-section"><div class="row">';
        html += '<div class="col-md-5 col-10">';
        html += '<div class="row">';
        html += '<div class="col-sm-6">';
        html += '<div class="form-group">';

        html += '<select class="dropdowns woocommercePaymentGateways unsaved" name="woocommercePaymentGateways[]">';
        html += '<option selected value="">Select Payment</option>';

        if(woocommercePaymentGateways) {
            jQuery.each(woocommercePaymentGateways, function(i, v) {
                html += '<option value="'+v.id+'">'+v.name+'</option>';
            });
        }

        html += '</select>';
        html += '</div>';
        html += '</div>';

        html += '<div class="col-sm-6">';
        html += '<div class="form-group">';
        html += '<select class="dropdowns giddhAccounts" name="giddhAccounts[]">';
        html += '<option selected value="">Select Payment</option>';
        html += '<option value="create">Create Payment</option>';

        if(giddhBankAccounts) {
            jQuery.each(giddhBankAccounts, function(i, v) {
                html += '<option value="'+v.uniqueName+'">'+v.name+'</option>';
            });
        }

        html += '</select>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '<div class="col-sm-2 col-2 pl-0 pt-1">';
        html += '<input type="hidden" name="mappingId[]">';
        html += '<input type="hidden" name="removedMappings[]" class="removedMappings" value="0">';
        html += '<a href="javascript:;" class="remove-payment-mapping icon-remove" data=""><i class="fa fa-times"></i></a>';
        html += '</div>';

        html += '</div>';
        html += '</div>';

        jQuery("#payment-mappings").append(html);
        jQuery('.dropdowns').select2();
    });

    jQuery(document).on("select2:open", ".woocommercePaymentGateways", function() {
        currentSelectedPaymentGateway = jQuery(this).val();
    });

    jQuery(document).on("change", ".woocommercePaymentGateways", function() {
        if(savedPayments[jQuery(this).val()] !== undefined) {
            jQuery(this).find("option[value='"+currentSelectedPaymentGateway+"']").prop("selected", true);

            jQuery.alert({
                title: "Warning!",
                content: "This payment gateway is already mapped."
            });
            jQuery('.dropdowns').select2();
        } else {
            refreshSavedPaymentGateways();
        }
    });

    jQuery(document).on("click", ".remove-payment-mapping", function() {
        var objIndex = jQuery(this).parents(".payment-mapping-section").index();
        var obj = jQuery(this);

        if(jQuery(".payment-mapping-section:eq("+objIndex+")").find(".woocommercePaymentGateways").val() || jQuery(".payment-mapping-section:eq("+objIndex+")").find(".giddhAccounts").val() || jQuery(".payment-mapping-section:eq("+objIndex+")").find(".paymentMappingId").val()) {
            jQuery.confirm({
                title: "Delete mapping?",
                content: "Do you want to delete mapping?",
                buttons: {
                    yes: function() {
                        obj.prev(".removedMappings").val(1);
                        obj.parents(".payment-mapping-section").fadeOut(500);
                        refreshSavedPaymentGateways();
                    },
                    no: function() {

                    }
                }
            });
        } else {
            obj.prev(".removedMappings").val(1);
            obj.parents(".payment-mapping-section").fadeOut(500);
        }
    });

    jQuery(document).on("click", "#mapCategories", function() {
        if(loading) {
            return false;
        }

        var thisObj = jQuery(this);

        jQuery.ajax({
            url: adminAjaxUrl,
            type: 'POST',
            dataType: 'json',
            data: thisObj.parents("form").serializeArray(),
            beforeSend: function() {
                loading = true;
                thisObj.text("Loading...");
            },
            success: function(response) {
                loading = false;
                thisObj.text("Save");

                if(response.status == "success") {
                    window.location.href = "admin.php?page=giddh&view=settings&action=category&tab=2";
                } else if(response.status == "connect") {
                    window.location.href = "admin.php?page=giddh&view=connect";
                } else {
                    flashError("<p>"+response.message+"</p>");
                }
            },
            error: function() {
                thisObj.text("Save");
                loading = false;
                flashError("<p>Something went wrong! Please try again.</p>");
            }
        });
    });

    jQuery(document).on("click", ".ripple-effect", function(e){
        var rippler = jQuery(this);
        if(rippler.find(".teffect").length === 0) {
            rippler.append("<span class='teffect'></span>");
        }
        var teffect = rippler.find(".teffect");
        teffect.removeClass("animate");
        if(!teffect.height() && !teffect.width())
        {
            var d = Math.max(rippler.outerWidth(), rippler.outerHeight());
            teffect.css({height: d, width: d});
        }
        var x = e.pageX - rippler.offset().left - teffect.width()/2;
        var y = e.pageY - rippler.offset().top - teffect.height()/2;
        teffect.css({
            top: y+'px',
            left:x+'px'
        }).addClass("animate");
    });

    jQuery(document).on("click", "#categoryList .page-link", function() {
        var page = jQuery(this).attr("page");
        getCategories(page);
    });

    jQuery(document).on("click", "#skumatchedList .page-link,#woocommerceunmatchedList .page-link,#giddhunmatchedList .page-link", function() {
        var type = jQuery(this).parents("ul").attr("type");
        var page = jQuery(this).attr("page");
        getProducts(type, page);
    });

    jQuery(document).on("click", "#mapProducts", function() {
        if(loading) {
            return false;
        }

        var thisObj = jQuery(this);
        var newProductsToCreate = 0;
        var newProductsWithSku = 0;
        thisObj.parents("form").find(".selectedProduct").each(function(i, v) {
            if(jQuery(this).val() == "create") {
                newProductsToCreate++;
            }

            if(jQuery.trim(jQuery(".selectedProductSku:eq("+i+")").val())) {
                newProductsWithSku++;
            }
        });

        if(newProductsWithSku > 0) {
            if(newProductsToCreate > 0) {
                var platform = "";
                var currentTab = thisObj.parents(".tab-pane").attr("id");
                if(currentTab == "unmatched-giddh") {
                    platform = "WooCommerce";
                } else {
                    platform = "Giddh";
                }

                var message = "";

                if(newProductsWithSku != newProductsToCreate) {
                    message += " We have found "+newProductsWithSku+" out of "+newProductsToCreate+" products with SKU number. Please fill SKU number of remaining products individually.";
                }

                message += " Are you sure you want to create "+newProductsWithSku+" products in "+platform+"?";

                jQuery.confirm({
                    title: "Confirmation",
                    content: message,
                    buttons: {
                        yes: function() {
                            saveProducts(thisObj);
                        },
                        no: function() {

                        }
                    }
                });
            } else {
                saveProducts(thisObj);
            }
        } else {
            flashError("<p>SKU is not available in any product. Please add SKU in the products to save them.</p>");
        }
    });

    jQuery(document).on("click", "#saveAccount", function() {
        if(loading) {
            return false;
        }

        if(!jQuery.trim(jQuery("#notification_email").val())) {
            flashError("<p>Please enter the notification email to receive notifications.</p>");
            return false;
        }

        var thisObj = jQuery(this);

        jQuery.ajax({
            url: adminAjaxUrl,
            type: 'POST',
            dataType: 'json',
            data: jQuery("#accountForm").serializeArray(),
            beforeSend: function() {
                loading = true;
                thisObj.text("Loading...");
            },
            success: function(response) {
                loading = false;
                thisObj.text("Save");

                if(response.status == "success") {
                    flashNotice("<p>"+response.message+"</p>");
                } else if(response.status == "connect") {
                    window.location.href = "admin.php?page=giddh&view=connect&tab=5";
                } else {
                    flashError("<p>"+response.message+"</p>");
                }
            },
            error: function() {
                thisObj.text("Save");
                loading = false;
                flashError("<p>Something went wrong! Please try again.</p>");
            }
        });
    });

    jQuery(document).on("click", ".createallproducts", function() {
        if(loading) {
            return false;
        }

        var thisObj = jQuery(this);
        var platform = "";
        var action = "";
        var totalProducts = thisObj.attr("total");
        var invalidProducts = thisObj.attr("invalid");

        if(parseInt(totalProducts) > parseInt(invalidProducts)) {
            var currentTab = thisObj.parents(".tab-pane").attr("id");
            if(currentTab == "unmatched-giddh") {
                platform = "WooCommerce";
                action = "giddh_create_giddh_unmatched_to_woocommerce";
            } else {
                platform = "Giddh";
                action = "giddh_create_woocommerce_unmatched_to_giddh";
            }

            var message = " Are you sure you want to create all products in "+platform+"?";

            jQuery.confirm({
                title: "Confirmation",
                content: message,
                buttons: {
                    yes: function() {
                        flashNotice("<p>Please wait while we create all products.</p>");
                        bulkFailedProductsCount = 0;
                        createBulkProducts(action, 1, thisObj);
                    },
                    no: function() {

                    }
                }
            });
        } else {
            flashError("<p>SKU is not available in any product. Please add SKU in the products to save them.</p>");
        }
    });

    // Gets the video src from the data-src on each button
    var videoSrc;  
    jQuery(document).on('click', '.video-btn', function() {
        videoSrc = jQuery(this).data("src");
    });

    jQuery('#myModal').on('shown.bs.modal', function (e) {
        jQuery("#video").attr('src', videoSrc + "?autoplay=1&amp;modestbranding=1&amp;showinfo=0"); 
    });

    // stop playing the youtube video when I close the modal
    jQuery('#myModal').on('hide.bs.modal', function (e) {
        jQuery("#video").attr('src', videoSrc); 
    });

    jQuery(document).on("mouseenter", ".notice", function() {
        if(timeoutObj) {
            clearTimeout(timeoutObj);
        }
    });

    jQuery(document).on("mouseleave", ".notice", function() {
        if(timeoutObj) {
            hideNotificationMessage();
        }
    });

    jQuery(document).on("change", "input[name='email_method']", function() {
        if(jQuery(this).val() == "sendgrid") {
            jQuery("#sendgrid_key").removeClass("hide-sendgrid-key");
        } else {
            jQuery("#sendgrid_key").addClass("hide-sendgrid-key");
        }
    });

    jQuery(document).on("click", "#mapServices", function() {
        if(loading) {
            return false;
        }

        var thisObj = jQuery(this);

        jQuery.ajax({
            url: adminAjaxUrl,
            type: 'POST',
            dataType: 'json',
            data: jQuery("#servicesForm").serializeArray(),
            beforeSend: function() {
                loading = true;
                thisObj.text("Loading...");
            },
            success: function(response) {
                loading = false;
                thisObj.text("Save");

                if(response.status == "success") {
                    giddhShippingAccount = {name: jQuery("#giddhSalesAccountsName-shipping").val(), uniqueName: jQuery("#giddhSalesAccounts-shipping").val()};

                    flashNotice("<p>"+response.message+"</p>");
                } else if(response.status == "connect") {
                    window.location.href = "admin.php?page=giddh&view=connect";
                } else {
                    flashError("<p>"+response.message+"</p>");
                }
            },
            error: function() {
                thisObj.text("Save");
                loading = false;
                flashError("<p>Something went wrong! Please try again.</p>");
            }
        });
    });

    jQuery(document).on("keydown keyup", ".giddhSalesAccountsAutocomplete", function() {
        jQuery("#giddhSalesAccounts-shipping").val("");
        jQuery("#giddhSalesAccountsName-shipping").val("");
    });

    jQuery(document).on("blur", ".giddhSalesAccountsAutocomplete", function() {
        if(jQuery("#giddhSalesAccounts-shipping").val() == "" && giddhShippingAccount && giddhShippingAccount.uniqueName) {
            jQuery("#giddhSalesAccounts-shipping").val(giddhShippingAccount.uniqueName);
            jQuery("#giddhSalesAccountsName-shipping,.giddhSalesAccountsAutocomplete").val(giddhShippingAccount.name);
        }
    });

    jQuery(document).on("click", "#pills-tab li a", function() {
        var href = jQuery(this).attr("href");
        window.location.hash = href.replace("#", "");
    });
});

function createBulkProducts(action, page, thisObj) {
    jQuery.ajax({
        url: adminAjaxUrl,
        type: 'POST',
        dataType: 'json',
        data: {page: page, action: action},
        beforeSend: function() {
            loading = true;
            thisObj.text("Loading...");
        },
        success: function(response) {
            if(response.result == "true") {
                page++;

                if(response.failed) {
                    bulkFailedProductsCount += response.failed;
                }

                createBulkProducts(action, page, thisObj);
            } else if(response.result == "completed") {
                if(response.failed) {
                    bulkFailedProductsCount += response.failed;
                }

                if(bulkFailedProductsCount > 0) {
                    thisObj.text("Create All");
                    loading = false;
                    flashError("<p>"+bulkFailedProductsCount+" product(s) couldn't be created. Please add missing SKU in products.</p>");
                } else {
                    flashNotice("<p>Products have been created successfully.</p>");
                    setTimeout(function() {
                        window.location.href = "admin.php?page=giddh&view=settings&tab=3";
                    }, 1500);
                }
            } else if(response.result == "error") {
                thisObj.text("Create All");
                loading = false;
                flashError("<p>"+response.message+"</p>");
            }
        },
        error: function() {
            thisObj.text("Create All");
            loading = false;
            flashError("<p>Something went wrong! Please try again.</p>");
        }
    });
}

function disableSelectedLocations() {
    var giddhTempWarehouses = [];
    jQuery(".giddhWarehouses").each(function() {
        if(jQuery(this).val() && jQuery(this).val() != "create") {
            giddhTempWarehouses.push(jQuery(this).val());
        }
    });

    jQuery(".giddhWarehouses").each(function() {
        var loopOptionObj = jQuery(this);
        loopOptionObj.find("option[disabled='disabled']").removeAttr("disabled");
    });

    if(giddhTempWarehouses.length > 0) {
        for(var loop = 0; loop < giddhTempWarehouses.length; loop++) {
            jQuery(".giddhWarehouses").each(function() {
                var loopOptionObj = jQuery(this);
                if(loopOptionObj.val() && jQuery(this).val() != "create" && loopOptionObj.val() == giddhTempWarehouses[loop]) {
                    loopOptionObj.find("option[value='"+giddhTempWarehouses[loop]+"']").removeAttr("disabled");
                } else {
                    loopOptionObj.find("option[value='"+giddhTempWarehouses[loop]+"']").attr("disabled", "disabled");
                }
            });
        }
    }

    jQuery('.giddhWarehouses').select2('destroy');
    jQuery('.giddhWarehouses').select2();
}

function refreshSavedPaymentGateways() {
    savedPayments = [];

    jQuery(".woocommercePaymentGateways").each(function(i, v) {
        if(jQuery(".removedMappings:eq("+i+")").val() != "1") {
            var wooCommercePaymentGateway = jQuery(this).val();
            var giddhAccount = jQuery(".giddhAccounts:eq("+i+")").val();

            savedPayments[wooCommercePaymentGateway] = [];
            savedPayments[wooCommercePaymentGateway] = giddhAccount;
        }
    });
}

function initCategoryAutocomplete() {
    jQuery(".giddhStockGroupAutocomplete").autocomplete({
        source: adminAjaxUrl + "?action=giddh_get_stock_groups",
        minLength: 1,
        select: function(event, ui) {
            jQuery("#giddhStockGroups-"+event.target.id).val(ui.item.uniqueName);
            jQuery("#giddhStockGroupsName-"+event.target.id).val(ui.item.value);
        }
    });
}

function getCategories(page) {
    jQuery.ajax({
        url: adminAjaxUrl,
        type: 'POST',
        data: {page: page, action: "giddh_get_categories"},
        beforeSend: function() {
            loading = true;
            jQuery("#categoryList").html(giddhGetLoader());
        },
        success: function(response) {
            loading = false;
            jQuery("#categoryList").html(response);
            initCategoryAutocomplete();
        },
        error: function() {
            loading = false;
            flashError("<p>Something went wrong! Please try again.</p>");
        }
    });
}

function initProductAutocomplete() {
    jQuery(".giddhProductAutocomplete").autocomplete({
        source: adminAjaxUrl + "?action=giddh_get_stocks",
        minLength: 1,
        select: function(event, ui) {
            jQuery("#productid-"+event.target.id).val(ui.item.uniqueName);
        }
    });

    jQuery(".woocommerceProductAutocomplete").autocomplete({
        source: adminAjaxUrl + "?action=giddh_get_woocommerce_stocks",
        minLength: 1,
        select: function(event, ui) {
            jQuery("#productid-"+event.target.id).val(ui.item.uniqueName);
        }
    });
}

function getProducts(type, page) {
    jQuery.ajax({
        url: adminAjaxUrl,
        type: 'POST',
        data: {type: type, page:page, action: "giddh_get_products"},
        dataType: 'html',
        beforeSend: function() {
            loading = true;
            jQuery("#"+type+"List").html(giddhGetLoader());
        },
        success: function(response) {
            loading = false;
            jQuery("#"+type+"List").html(response);
            initProductAutocomplete();
        },
        error: function() {
            loading = false;
            flashError("<p>Something went wrong! Please try again.</p>");
        }
    });
}

function saveProducts(thisObj) {
    jQuery.ajax({
        url: adminAjaxUrl,
        type: 'POST',
        dataType: 'json',
        data: thisObj.parents("form").serializeArray(),
        beforeSend: function() {
            loading = true;
            thisObj.text("Loading...");
        },
        success: function(response) {
            if(response.status == "success") {
                flashNotice("<p>"+response.message+"</p>");
                setTimeout(function() {
                    window.location.href = "admin.php?page=giddh&view=settings&action=product&tab=3";
                }, 2000);
            } else if(response.status == "connect") {
                window.location.href = "admin.php?page=giddh&view=connect";
            } else {
                loading = false;
                thisObj.text("Save");
                flashError("<p>"+response.message+"</p>");
            }
        },
        error: function() {
            thisObj.text("Save");
            loading = false;
            flashError("<p>Something went wrong! Please try again.</p>");
        }
    });
}

function flashError(message, hideMessage = true) {
    jQuery(".updated.notice").html("").hide();
    jQuery(".error.notice").html(message).show();
    jQuery('html, body').animate({
        scrollTop: jQuery(".error.notice").offset().top - 100
    }, 500);

    if(hideMessage) {
        hideNotificationMessage();
    }
}

function flashNotice(message) {
    jQuery(".error.notice").html("").hide();
    jQuery(".updated.notice").html(message).show();
    jQuery('html, body').animate({
        scrollTop: jQuery(".updated.notice").offset().top - 100
    }, 500);

    hideNotificationMessage();
}

function hideNotificationMessage() {
    timeoutObj = setTimeout(function() {
        jQuery(".notice").html("").hide();
        timeoutObj = "";
    }, 3000);
}

function initSetup() {
    saveWoocommerceCategories();
}

function saveWoocommerceCategories() {
    jQuery.ajax({
        url: adminAjaxUrl,
        type: 'POST',
        data: {action: 'giddh_save_woocommerce_categories'},
        dataType: 'json',
        beforeSend: function() {
            jQuery(".s1").addClass("setup-active");
        },
        success: function(response) {
            if(response.result === "success") {
                jQuery(".s1").addClass("setup-completed");
            } else {
                flashError("<p>"+response.message+"</p>", false);
                jQuery(".s1").addClass("setup-error");
            }
            saveWoocommerceProducts();
        },
        error: function(error) {
            flashError("<p>"+error+"</p>", false);
            jQuery(".s1").addClass("setup-error").attr("title", error);
            saveWoocommerceProducts();
        }
    });
}

function saveWoocommerceProducts() {
    jQuery.ajax({
        url: adminAjaxUrl,
        type: 'POST',
        data: {action: 'giddh_save_woocommerce_products'},
        dataType: 'json',
        beforeSend: function() {
            jQuery(".s2").addClass("setup-active");
        },
        success: function(response) {
            if(response.result === "success") {
                jQuery(".s2").addClass("setup-completed");
            } else {
                flashError("<p>"+jQuery(".error.notice").html()+response.message+"</p>", false);
                jQuery(".s2").addClass("setup-error");
            }
            saveWoocommercePaymentGateways();
        },
        error: function(error) {
            flashError("<p>"+jQuery(".error.notice").html()+error+"</p>", false);
            jQuery(".s2").addClass("setup-error").attr("title", error);
            saveWoocommercePaymentGateways();
        }
    });
}

function saveWoocommercePaymentGateways() {
    jQuery.ajax({
        url: adminAjaxUrl,
        type: 'POST',
        data: {action: 'giddh_save_woocommerce_payment_gateways'},
        dataType: 'json',
        beforeSend: function() {
            jQuery(".s3").addClass("setup-active");
        },
        success: function(response) {
            if(response.result === "success") {
                jQuery(".s3").addClass("setup-completed");
            } else {
                flashError("<p>"+jQuery(".error.notice").html()+response.message+"</p>", false);
                jQuery(".s3").addClass("setup-error");
            }
            saveGiddhProducts();
        },
        error: function(error) {
            flashError("<p>"+jQuery(".error.notice").html()+error+"</p>", false);
            jQuery(".s3").addClass("setup-error").attr("title", error);
            saveGiddhProducts();
        }
    });
}

function saveGiddhProducts() {
    jQuery.ajax({
        url: adminAjaxUrl,
        type: 'POST',
        data: {action: 'giddh_save_giddh_products'},
        dataType: 'json',
        beforeSend: function() {
            jQuery(".s4").addClass("setup-active");
        },
        success: function(response) {
            if(response.result === "success") {
                jQuery(".s4").addClass("setup-completed");
            } else {
                flashError("<p>"+jQuery(".error.notice").html()+response.message+"</p>", false);
                jQuery(".s4").addClass("setup-error");
            }
            setupCompleted();
        },
        error: function(error) {
            flashError("<p>"+jQuery(".error.notice").html()+error+"</p>", false);
            jQuery(".s4").addClass("setup-error").attr("title", error);
            setupCompleted();
        }
    });
}

function setupCompleted() {
    setTimeout(function() {
        if(jQuery.trim(jQuery(".error.notice").html()) == "" || jQuery.trim(jQuery(".error.notice").html()) == "&nbsp;") {
            window.location.href = "admin.php?page=giddh&view=settings&action=connected&tab=1";
        }
    }, 500);
}

function showActiveTab(tab) {
    var hash = window.location.hash;
    if(hash) {
        hash = hash.replace("#", "");
        tab = (hash == "invoice") ? 1 : (hash == "category") ? 2 : (hash == "inventory") ? 3 : (hash == "payment") ? 4 : (hash == "my-account") ? 5 : tab;
    }

    if(tab) {
        tab = parseInt(tab) - 1;
    }

    var mainTabList = [].slice.call(document.querySelectorAll('#pills-tab a'));
    var tabList = [];
    mainTabList.forEach(function (element) {
        tabList.push(new bootstrap.Tab(element));
    });
    tabList[tab].show();
}

function initServiceAccountsAutocomplete() {
    jQuery(".giddhSalesAccountsAutocomplete").autocomplete({
        source: adminAjaxUrl + "?action=giddh_get_sales_accounts",
        minLength: 1,
        select: function(event, ui) {
            jQuery("#giddhSalesAccounts-"+event.target.id).val(ui.item.uniqueName);
            jQuery("#giddhSalesAccountsName-"+event.target.id).val(ui.item.value);
        }
    });
}

function giddhGetLoader() {
    var html = '<div class="no-data mrT2 loader-main"><div class="spinner2"><div class="cube1"></div><div class="cube2"></div></div></div>';
    return html;
}