<?php
add_action( "admin_menu", "wpcal_feed__add_settings_page" );
function wpcal_feed__add_settings_page() {
    add_options_page( __( "Calendar Feeds", "wordpress-calendar-feeds" ), __( "Calendar Feeds", "wordpress-calendar-feeds" ), "manage_options", "wpcal_feed", "wpcal_feed_options_page" );
}

function wpcal_feed_options_page() {
    ?>
    <h1><?= esc_html__( "Calendar Feed Settings", "wordpress-calendar-feeds" ) ?></h1>
    <form action="options.php" method="post">
        <?php
        settings_fields( "wpcal_feed_options" );
        do_settings_sections( "wpcal_feed_options" );
        ?>
    </form>
    <input name="submit" class="button button-primary" type="submit"
           value="<?php esc_attr_e( 'Save', "wordpress-calendar-feeds" ) ?>"/>
    <?php
}

add_action("init", "wpcal_feed__add_ical_endpoint");
function wpcal_feed__add_ical_endpoint() {
    add_rewrite_rule("^ical/([^/]+)/([^/]+)", WP_PLUGIN_DIR.'wordpress-calendar-feeds/src/generate.php?post_type=$matches[1]&id=$matches[2]', "top");
    flush_rewrite_rules(false);
}