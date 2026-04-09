<?php

namespace UnikinoGegenlicht\WordpressCalendarFeeds;

use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Presentation\Component\Property;
use Eluceo\iCal\Presentation\Component\Property\Value\DurationValue;
use Eluceo\iCal\Presentation\Component\Property\Value\TextValue;

class CalendarFactory extends \Eluceo\iCal\Presentation\Factory\CalendarFactory {

	#[\Override]
	protected function getProperties( Calendar $calendar): \Generator
	{
		/* @see https://www.ietf.org/rfc/rfc5545.html#section-3.7.3 */
		yield new Property('PRODID', new TextValue($calendar->getProductIdentifier()));
		/* @see https://www.ietf.org/rfc/rfc5545.html#section-3.7.4 */
		yield new Property('VERSION', new TextValue('2.0'));
		/* @see https://www.ietf.org/rfc/rfc5545.html#section-3.7.1 */
		yield new Property('CALSCALE', new TextValue('GREGORIAN'));
		$publishedTTL = $calendar->getPublishedTTL();
		if ($publishedTTL) {
			/* @see http://msdn.microsoft.com/en-us/library/ee178699(v=exchg.80).aspx */
			yield new Property('X-PUBLISHED-TTL', new DurationValue($publishedTTL));
		}

		yield new Property('X-WR-CALNAME', new TextValue('Vorstellungskalender Unikino GEGENLICHT'));
		yield new Property("X-WR-CALDESC", new TextValue('Dieser Kalender beinhaltet alle Vorstellungen des Unikino GEGENLICHT'));
	}

}