<?php
/**
 * Calendar Feed Generator
 *
 * @package wordpress-calendar-feeds
 * @author Jan Eike Suchard <jan@gegenlicht.net>
 * @copyright 2025 Unikino GEGENLICHT
 * @license EUPL-1.2
 *
 * @wordpress-plugin
 * Plugin Name: Calendar Feed Generator
 * Plugin URI: https://github.com/unikino-gegenlicht/wordpress-calendar-feeds
 * Description: A plugin which provides additional functions to generate single event calendar files or multi-event files for movies, events and taxonomies.
 * Requires PHP: 8.4
 * Author: Jan Eike Suchard
 * Author URI: https://github.com/dr4hcu5-jan
 * License: EUPL-1.2
 * License URI: https://interoperable-europe.ec.europa.eu/sites/default/files/custom-page/attachment/2020-03/EUPL-1.2%20EN.txt
 * Text Domain: wordpress-calendar-feeds
 * Domain Path: /languages
 * Version: GGL_PLUGIN_VERSION
 */

/**
 * This file acts as loader entrypoint to the `src/index.php` file which fully bootstraps the plugin
 */

use Base64Url\Base64Url;

defined( "ABSPATH" ) || exit;
require_once "const.php";
require_once "src/index.php";
require_once "vendor/autoload.php";
require_once "src/shortcode.php";
require_once "src/generate.php";
require_once ABSPATH . WPINC . '/class-wp-application-passwords.php';

add_shortcode( "calendar_feed_url", "ggl_cf__calendar_feed_url" );

add_filter( "query_vars", function ( $current ) {
	$current[] = "calendar_token";

	return $current;
} );

add_action( "parse_request", function ( WP $wp ) {
	if ( preg_match( '/^(ical\.php)|(calendar\.php)/', $wp->request ) ) {
		$credentials = mb_trim( $wp->query_vars["calendar_token"] ?? "" );
		if ( $credentials === "" ) {
			$content = ggl_cf__generate_feed_content();
			goto output;
		}
		try {
			[ $username, $password, $password_uuid ] = explode( ":", Base64Url::decode( $credentials ) );
		} catch ( Exception $e ) {
			$content = ggl_cf__generate_feed_content();
			goto output;
		}
		$user = get_user_by( 'login', $username );
		if ( ! $user || $password === null || $password_uuid === null ) {
			$content = ggl_cf__generate_feed_content();
			goto output;
		}

		$possible_password = WP_Application_Passwords::get_user_application_password( $user->ID, $password_uuid );
		if ($possible_password === null) {
			$content = ggl_cf__generate_feed_content();
			goto output;
		}
		if ( ! WP_Application_Passwords::check_password( $password, $possible_password["password"] ) ) {
			$content = ggl_cf__generate_feed_content();
			goto output;
		}

		WP_Application_Passwords::record_application_password_usage($user->ID, $password_uuid);

		add_filter("ggl__show_full_details", function (bool $display_details, WP_Post $post) {
			return true;
		}, 80, 2);


		$content = ggl_cf__generate_feed_content();
output:
		header("Content-Type: text/calendar");
		header('Content-Disposition: attachment; filename="Unikino GEGENLICHT.ics"');
		echo $content;
		die();
	}
} );

