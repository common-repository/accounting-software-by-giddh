<?php
$currentUser = wp_get_current_user();
?>
<section class="welcome-top text-center">
    <h2 class="text-blue">Welcome <?php echo ($currentUser->display_name) ? esc_html($currentUser->display_name) : esc_html($currentUser->user_login);?>!</h2>
</section>