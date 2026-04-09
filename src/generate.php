<?php

defined( 'ABSPATH' ) || exit();

require_once "CalendarFactory.php";

use Base64Url\Base64Url;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\Entity\TimeZone;
use Eluceo\iCal\Domain\ValueObject\EmailAddress;
use Eluceo\iCal\Domain\ValueObject\GeographicPosition;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\ValueObject\Organizer;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Domain\ValueObject\UniqueIdentifier;
use Eluceo\iCal\Domain\ValueObject\Uri;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use UnikinoGegenlicht\WordpressCalendarFeeds\CalendarFactory;


function ggl_cf__generate_access_token( WP_User $user ): string {
	if ( ! WP_Application_Passwords::application_name_exists_for_user( $user->ID, GGL_CF__APPLICATION_NAME ) ) {
		[ $app_password, $details ] = WP_Application_Passwords::create_new_application_password( $user->ID, [
			"name"   => GGL_CF__APPLICATION_NAME,
			"app_id" => GGL_CF__APPLICATION_ID,
		] );

		$password_uuid = $details["uuid"];

		add_user_meta( $user->ID, "calendar_password", $app_password, true );
		add_user_meta( $user->ID, "calendar_password_uuid", $password_uuid, true );
	} else {
		$app_password  = get_user_meta( $user->ID, "calendar_password", true );
		$password_uuid = get_user_meta( $user->ID, "calendar_password_uuid", true );
	}

	$credentials = Base64Url::encode( "$user->user_login:$app_password:$password_uuid", true );

	return $credentials;
}

function ggl_cf__generate_single( WP_Post $post, bool $public = true ): Event|null {
	if ( ! in_array( $post->post_type, [ 'event', "movie" ] ) ) {
		return null;
	}

	$id    = new UniqueIdentifier( get_post_permalink( $post->ID ) );
	$event = new Event( $id );

	$event->setSummary( ( $post->post_type == "movie" ? "🎬 " : "🔮 " ) . ggl_get_localized_title( $post ) );
	$event->setDescription( strip_tags(ggl_get_summary( $post )) );
	$event->setUrl(new Uri($id));

	$post_location = ggl_get_assigned_location( $post );
	$location_schema  = ggl_get_location_schema_markup_data( $post_location );
	$latitude    = get_post_meta( $post_location->ID, "lat", true );
	$longitude   = get_post_meta( $post_location->ID, "long", true );


	$address_data  = $location_schema["address"];
	$address       = "$location_schema[name] $address_data[streetAddress], $address_data[postalCode] $address_data[locality]";
	$location      = new Location( $address, $location_schema["name"] )->withGeographicPosition(new GeographicPosition($latitude, $longitude));

	$event->setLocation( $location );

	$organizer = new Organizer( new EmailAddress( "noreply@gegenlicht.net" ), "Unikino GEGENLICHT" );
	$event->setOrganizer( $organizer );

	$running_time = ggl_get_running_time($post);

	$event->setOccurrence(new TimeSpan(
		new DateTime(ggl_get_starting_time($post), true),
		new DateTime(ggl_get_starting_time($post)->add(new DateInterval("PT{$running_time}M")), true)
	));

	return $event;
}

function ggl_cf__generate_feed_content( bool $public = true ): string {
	$query = new WP_Query( [
		"post_type"      => [ "movie", "event" ],
		"posts_per_page" => - 1,
	] );

	$events = [];
	while ( $query->have_posts() ): $query->the_post();
		$events[] = ggl_cf__generate_single( $query->post );
	endwhile;

	$calendar = new Calendar( $events );
	$calendar->addTimeZone( new TimeZone( "Europe/Berlin" ) );

	return new CalendarFactory()->createCalendar( $calendar );
}