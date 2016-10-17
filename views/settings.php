<div class="wrap">
    <?php    echo "<h2>" . __( 'Tattler settings', 'tattler_trdom' ) . "</h2>"; ?>

    <form name="tattler_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        <input type="hidden" name="tattler_hidden" value="Y">
        <p><?php _e("Server: " ); ?><input type="text" name="server" value="<?php echo $settings['server']; ?>" size="20"><?php _e(" ex: tattler.yourdomain.com" ); ?></p>
        <p><?php _e("Secure: " ); ?><input type="checkbox" name="secure" <?php if($settings['secure'] == true) { echo ' checked '; }; ?>" size="20"><?php _e(" accessible by https" ); ?></p>
        <p><?php _e("Login: " ); ?><input type="text" name="login" value="<?php echo $settings['login']; ?>" size="20"><?php _e(" ex: ".$_SERVER['SERVER_NAME'] ); ?></p>

        <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Update Options', 'tattler_trdom' ) ?>" />
        </p>
    </form>
</div>