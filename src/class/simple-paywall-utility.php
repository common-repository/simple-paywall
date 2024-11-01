<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

if ( ! class_exists( 'Simple_Paywall_Utility' ) ) {
	class Simple_Paywall_Utility {

		/**
		 * Convert ISO 8601 datetime format to local WP time settings
		 * @param string $datetime The time to convert to local time
		 * @return string $datetime The new time set using WP settings
		 */
		public static function get_wp_datetime( $datetime ) {
			$dateObj = new DateTime( $datetime );
			if ( self::get_wp_timezone() ) {
				$dateObj->setTimezone( new DateTimeZone( self::get_wp_timezone() ) );
			}
			if ( self::get_wp_timezone() ) {
				$dateObj->setTimezone( new DateTimeZone( self::get_wp_timezone() ) );
			}
			$datetime = $dateObj->format( self::get_wp_date_format() . ' ' . self::get_wp_time_format() );
			return $datetime;
		}

		public static function get_wp_timezone() {
			return get_option( 'timezone_string' );
		}

		public static function get_wp_date_format() {
			return get_option( 'date_format' );
		}

		public static function get_wp_time_format() {
			return get_option( 'time_format' );
		}

		/**
		 * Format price to non-decimal formatting
		 *
		 * Tests and (expected) results:
		 * 9.99 => 999
		 * $9.99 => 999
		 * 999.999 => 99999
		 * 456.9997564861684 => 45699
		 * 3.14578945612378945613 => 314
		 * 0009.99 => 999
		 * 00000999.999 => 99999
		 * .12345 => 12
		 */
		public static function format_price( $price, $currency ) {

			if ( $currency !== 'usd' ) {
				throw new \InvalidArgumentException( 'Invalid currency' );
			}

			// Remove any leading zeroes in string
			$price = ltrim( $price, '0' );

			// Truncate anything after two places if decimal point present
			if ( strpos( $price, '.' ) !== false ) {
				$length = strlen( $price ) - 1; // Subtract 1 to make compatible with index count
				$decimalPos = strpos( $price, '.' );
				if ( ( $decimalPos + 2 ) - $length !== 0 ) {
					$price = substr( $price, 0, ( $decimalPos + 2 ) - $length );
				}
			}

			// Remove all non-numerical characters from string
			$price = preg_replace( '/[^0-9]/', '', $price );

			// Return newly formatted price at int type
			return (int) $price;

		}

	}
}
