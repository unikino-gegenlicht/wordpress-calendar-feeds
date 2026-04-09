<div align="center">
<h3>wordpress-calendar-feeds</h3>
<p>📆 a plugin for generating ical files for semesters and single screenings</p>
<a href="LICENSE">
<img alt="License" src="https://flat.badgen.net/github/license/unikino-gegenlicht/wordpress-calendar-feeds?cache=300&label=License">
</a>
<img alt="Current Release Badge" src="https://flat.badgen.net/github/release/unikino-gegenlicht/wordpress-calendar-feeds/stable?cache=300&label=Latest%20Version">
</div>

This WordPress plugin allows us to generate calendar feeds for the semesters and single screenings.

It exposes the following functions which allow other plugins and themes to interact with this plugin to generate the
iCalendar files:

* `ggl_cal_entry(WP_Post $post): string`
* `ggl_cal_entry_uri(WP_Post $post): string`