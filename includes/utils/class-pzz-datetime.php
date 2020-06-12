<?php
/**
 * DateTime compatibility class
 *
 * @since 2.0.0
 * @package WordPress
 * @subpackage JSON API
 */
class PZZ_DateTime_Helper extends DateTime {
	/**
	 * This function override DateTime createFromFormat function.
	 * 
	 * Workaround for DateTime::createFromFormat on PHP > 5.2
	 *
	 * @link http://stackoverflow.com/a/17084893/717643
	 *
	 * @since 2.0.0
	 * @param  string       $format   The format that the passed in string should be in.
	 * @param  string       $string   String representing the time.
	 * @param  DateTimeZone $timezone A DateTimeZone object representing the desired time zone.
	 * @return Datetime
	 */
	public static function createFromFormat( $format, $time, $timezone = null ) {
		if ( is_null( $timezone ) ) {
			$timezone = new DateTimeZone( date_default_timezone_get() );
		}

		if ( method_exists( 'DateTime', 'createFromFormat' ) ) {
			return parent::createFromFormat( $format, $time, $timezone );
		}

		return new DateTime( date( $format, strtotime( $time ) ), $timezone );
	}

	/**
	 * Get the timezone object for the site.
	 *
	 * @since 2.0.0
	 * @return DateTimeZone DateTimeZone instance.
	 */
	public static function get_timezone() {
		static $zone = null;

		if ( $zone !== null ) {
			return $zone;
		}

		$tzstring = get_option( 'timezone_string' );

		if ( ! $tzstring ) {
			// Create a UTC+- zone if no timezone string exists
			$current_offset = get_option( 'gmt_offset' );
			if ( 0 == $current_offset ) {
				$tzstring = 'UTC';
			} elseif ( $current_offset < 0 ) {
				$tzstring = 'Etc/GMT' . $current_offset;
			} else {
				$tzstring = 'Etc/GMT+' . $current_offset;
			}
		}
		$zone = new DateTimeZone( 'Asia/Tehran' );

		return $zone;
	}
}
