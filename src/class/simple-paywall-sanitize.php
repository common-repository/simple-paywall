<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

if ( ! class_exists( 'Simple_Paywall_Sanitize' ) ) {
	class Simple_Paywall_Sanitize {

		/**
		 * Ensures that only the fields listed will be accepted. Everything else will be unset.
		 */
		public static function post( $post_keys_allowed, $post_keys ) {
			// var_dump( $post_keys ); die(); // Test
			foreach ( $post_keys as $key => $value ) {
				if ( ! in_array( $key, $post_keys_allowed ) ) {
					unset( $post_keys[$key] );
				}
			}
			// var_dump( $post_keys ); die(); // Test
			return $post_keys;
		}

		public static function convert_date_to_iso_8601( $date ) {}
			
		/**
		 * Trims either a string or array
		 * @param  string|array $mixed
		 * @return string|array $mixed
		 */
		public static function trim( $mixed ) {
			// array
			if ( is_array( $mixed ) ) {
				foreach ( $mixed as $key => $value ) {
					$mixed[$key] = trim( $mixed[$key] );
				}
			}
			// string
			if ( is_string( $mixed ) ) {
				trim( $mixed );
			}
			return $mixed;
		}

		/**
		 * Sets any variable or key in an array with an empty string to null
		 * @param  string|array $mixed
		 * @return string|array $mixed
		 */
		public static function set_empty_string_to_null( $mixed ) {
			// array
			if ( is_array( $mixed ) ) {
				foreach ( $mixed as $key => $value ) {
					if ( empty( $value ) ) {
						$mixed[$key] = null;
					}
				}
			}
			// string
			if ( empty ( $mixed ) ) {
				$mixed = null;
			}
			return $mixed;
		}

	}
}
