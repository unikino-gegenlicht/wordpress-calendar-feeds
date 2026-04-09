<?php

function ggl_cf__calendar_feed_url( $_ ) {
	if ( ! is_user_logged_in() ) {
		return home_url( "/calendar.php" );
	}
	return home_url("/calendar.php") . "?calendar_token= " . ggl_cf__generate_access_token(wp_get_current_user());
}