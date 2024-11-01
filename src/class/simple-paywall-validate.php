<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

if ( ! class_exists( 'Simple_Paywall_Validate' ) ) {
	class Simple_Paywall_Validate {

		/**
		 * Checks that all keys in an array are present
		 * @param  array $required_keys An array of keys to
		 * @return array $array The array to check keys in
		 */
		public static function has_required_keys( $required_keys, $array ) {
			foreach ( $required_keys as $key ) {
				if ( ! array_key_exists( $key, $array ) ) {
					return false;
				}
			}
			return true;
		}

		/**
		 * Checks that string is valid email address
		 * @param  string  $string The email address to evaluate
		 * @return boolean Returns true/false for whether or not it is a valid email address
		 * @uses filter_var
		 * @see http://php.net/manual/en/filter.filters.validate.php
		 */
		public static function is_email( $string ) {
			if ( ! filter_var( $string, FILTER_VALIDATE_EMAIL ) ) {
				return false;
			}
			return true;
		}

		/**
		 * Checks if string is empty or not
		 * @param string $string The string that we are evaluating
		 * @return boolean True if empty, false if not
		 */
		public static function is_empty( $string ) {
			if ( $string === '' ) {
				return true;
			}
			return false;
		}

		/**
		 * Checks if string is alphanumeric
		 * @param  string $string The string that we are evaluating
		 * @return boolean Returns true is string contains only alphanumeric characters
		 */
		public static function is_alpha_numeric( $string ) {
			if ( ! preg_match( '/[^A-Za-z0-9]/', $string ) ) {
				return true;
			}
			return false;
		}

		// Check that value does not exceed a maximum amount
		public static function is_max( $string, $length ) {
			// Cast string as int
			if ( is_string( $value ) ) {
				$value = (int) $value;
			}
			if ( $value > $max ) {
				return false;
			}
			return true;
		}

		// Check that value does not exceed the maximum length
		public static function is_max_length( $string, $length ) {
			if ( strlen( $string ) <= $length ) {
				return true;
			}
			return false;
		}

		public static function is_number( $string ) {
			if ( ctype_digit( $string ) ) {
				return true;
			}
			return false;
		}

		public static function is_length( string $string, int $length ) {
			if ( strlen( $string ) === $length ) {
				return true;
			}
			return false;
		}

		public static function is_value( $value, $array ) {
			if ( in_array( $value, $array ) ) {
				return true;
			}
			return false;
		}

		public static function is_value_or_die( $value, $array ) {
			if ( in_array( $value, $array ) ) {
				return $value;
			}
			die( esc_html( '"' . $value . '" is not an acceptable value.' ) );
		}

		public static function is_within_range( $value, $min, $max ) {
			// Cast string as int
			if ( is_string( $value ) ) {
				$value = (int) $value;
			}
			// Eval
			if ( $value >= $min && $value <= $max ) {
				return true;
			}
			return false;
		}

		/**
		 * Verify nonce or die
		 * @param string $nonce_name The name of the nonce we are verifying
		 * @return void
		 */
		public static function nonce( $nonce_name ) {
			if ( ! isset( $_POST['simple_paywall_wp_nonce'] ) || ! wp_verify_nonce( $_POST['simple_paywall_wp_nonce'], $nonce_name ) ) {
				die( 'Sorry, the nonce didn\'t verify' );
			}
		}

	}
}
