<?php
wp_register_script('get-categories', '');
wp_enqueue_script('get-categories');
wp_add_inline_script('get-categories', 'jQuery(document).ready(function() { getCategories(1); });');
?>
<div class="white-box">
    <div class="setting-height">
        <div class="clearfix mb-4">
            <div id="categoryList">
                <div class="no-data mrT2 loader-main">
                    <div class="spinner2">
                        <div class="cube1"></div>
                        <div class="cube2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>