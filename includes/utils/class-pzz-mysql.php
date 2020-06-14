<?php
/**
 * MySql Utils class
 *
 * @since 1.1.1
 * @package WordPress
 * @subpackage JSON API
 */
class PZZ_MySql_Helper extends DateTime {
	/**
	 * Parses and formats a MySQL datetime (Y-m-d H:i:s) for ISO8601/RFC3339
	 *
	 * Explicitly strips timezones, as datetimes are not saved with any timezone
	 * information. Including any information on the offset could be misleading.
	 *
	 * @since 1.1.1
	 * @param string $date_string
	 * @return mixed
	 */
	public static function mysql_to_rfc3339( $date_string ) {
		$formatted = mysql2date( 'c', $date_string, false );

		// Strip timezone information
		return preg_replace( '/(?:Z|[+-]\d{2}(?::\d{2})?)$/', '', $formatted );
	}
}
