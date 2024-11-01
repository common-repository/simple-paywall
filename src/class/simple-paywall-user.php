<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

if ( ! class_exists( 'Simple_Paywall_User' ) ) {
	class Simple_Paywall_User {

		private static $_instance = null;

		private $_id,
				$_data;

		public function __construct() {
			$this->set_id();
		}

		public static function get_instance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}

		/**
		 * Create a new user for Simple Paywall
		 *
		 * Simple Paywall documentation reference:
		 * List of parameters allowed in body request for /users endpoint:
		 * 	birthday_day
		 * 	birthday_month
		 * 	birthday_year
		 * 	city
		 * 	email
		 * 	first_name
		 * 	gender
		 * 	last_name
		 * 	password
		 * 	state
		 * 	street_address
		 * 	street_address_2
		 * 	zip
		 * @return void
		 */
		public function create() {

			// Verify nonce or die
			Simple_Paywall_Validate::nonce( 'simple-paywall-add-user' );

			// Instantiate form errors
			$this->_form_errors = Simple_Paywall_Form_Error::get_instance();

			/** Get, sanitize, set, and validate $_POST values from form */

			/** @var array $body The array containing sanitized values to submit only validated values in API call */
			$body = array(
				'birthday' => $this->get_post_value_birthday(),
				'city' => $this->get_post_value_city(),
				'country' => $this->get_post_value_country(),
				'email' => $this->get_post_value_email(),
				'first_name' => $this->get_post_value_first_name(),
				'last_name' => $this->get_post_value_last_name(),
				'postal_code' => $this->get_post_value_postal_code(),
				'region' => $this->get_post_value_region(),
				'street_address' => $this->get_post_value_street_address_line_one(),
				'street_address2' => $this->get_post_value_street_address_line_two()
			);

			// Filter out null values
			// This requires php >= 5.3
			$body = array_filter( $body, function( $value ) { return ! is_null( $value ); } );

			// Make API call only if no form errors exist up to this point
			if ( ! $this->_form_errors->has_errors() ) {
				$api = new Simple_Paywall_API();
				$api->method( 'POST' )
					->endpoint( '/users' )
					->body( $body )
					->call();
			}

			// Check if $api call experienced any issues
			if ( isset( $api ) ) {
				if ( $api->get_error() ) {
					$this->_form_errors->add_error( '', $api->get_message() );
				}
				// Ensure a 201 response was received--if no other errors have been caught
				if ( ! $this->_form_errors->has_errors() && $api->get_code() !== 201 ) {
					$this->_form_errors->add_error( '', 'There was some issue on our end that prevented us from adding this user. Please try again.' );
				}
			}

		}

		/**
		 * Get data for user
		 * return object $this->_data
		 */
		public function get() {
			$api = new Simple_Paywall_API();
			$api->method( 'GET' )
				->endpoint( '/users/' . $this->_id )
				->call();
			$this->_data = $api->get_data();
			return $this->_data;
		}

		public function update() {

			// Verify nonce or die
			Simple_Paywall_Validate::nonce( 'simple-paywall-update-user' );

			// Instantiate form errors
			$this->_form_errors = Simple_Paywall_Form_Error::get_instance();

			/** Get, sanitize, and set relevant $_POST values */

			/** @var array $body The array containing sanitized values to submit only validated values in API call */
			$body = array(
				'birthday' => $this->get_post_value_birthday(),
				'city' => $this->get_post_value_city(),
				'country' => $this->get_post_value_country(),
				'email' => $this->get_post_value_email(),
				'first_name' => $this->get_post_value_first_name(),
				'last_name' => $this->get_post_value_last_name(),
				'postal_code' => $this->get_post_value_postal_code(),
				'region' => $this->get_post_value_region(),
				'street_address' => $this->get_post_value_street_address_line_one(),
				'street_address_2' => $this->get_post_value_street_address_line_two()
			);

			// Filter out null values
			// This requires php >= 5.3
			$body = array_filter( $body, function( $value ) { return ! is_null( $value ); } );

			// Make API call only if no form errors up to this point
			if ( ! $this->_form_errors->has_errors() ) {
				$api = new Simple_Paywall_API();
				$api->method( 'POST' )
					->endpoint( '/users/' . $this->_id )
					->body( $body )
					->call();
			}

			// Check if $api call experienced any issues
			if ( isset( $api ) ) {

				if ( $api->get_error() ) {
					$this->_form_errors->add_error( '', $api->get_message() );
				}

				// Ensure a 201 response was received--if no other errors have been caught
				if ( ! $this->_form_errors->has_errors() && $api->get_code() !== 201 ) {
					$this->_form_errors->add_error( '', 'There was some issue on our end that prevented us from updating the user. Please try again.' );
				}

			}

		}

		/**
		 * Delete user account
		 * @param string $user The id of the user we are deleting.
		 * @return self
		 */
		public function delete( $user ) {
			$api = new Simple_Paywall_API();
			$api->method( 'DELETE' )->endpoint( '/users/' . $this->_id )->call();
			?>
			<script>
				window.location.replace( '?page=simple-paywall-users');
			</script>
			<?php
		}

		private function get_post_value_birthday() {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_birthday_year' ) ) )  {
				return null;
			}

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_birthday_month' ) ) )  {
				return null;
			}

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_birthday_day' ) ) )  {
				return null;
			}

			// simple_paywall_birthday_month
			$birthday_month = sanitize_text_field( $this->get_post_value( 'simple_paywall_birthday_month' ) );

			// simple_paywall_birthday_year
			$birthday_year = sanitize_text_field( $this->get_post_value( 'simple_paywall_birthday_year' ) );

			// simple_paywall_birthday_day
			$birthday_day = sanitize_text_field( $this->get_post_value( 'simple_paywall_birthday_day' ) );

			// birthday
			if ( isset( $birthday_day ) && isset( $birthday_month ) && isset( $birthday_year ) ) {

				// Check if it's an attempt to use birthday field and one field or more fields were missed
				if ( ! empty( $birthday_day ) || ! empty( $birthday_month ) || ! empty( $birthday_year ) ) {

					// Check that no fields are actually empty
					if ( ! empty( $birthday_day ) && ! empty( $birthday_month )	&& ! empty( $birthday_year ) ) {

						// Check that day is a number
						if ( ! Simple_Paywall_Validate::is_number( $birthday_day ) ) {
							$form_errors->add_error( '', 'The value for birthday day is invalid.' );
						}

						// Check that month is a number
						if ( ! Simple_Paywall_Validate::is_number( $birthday_month ) ) {
							$form_errors->add_error( '', 'The value for birthday month is invalid.' );
						}

						// Check that year is a number
						if ( ! Simple_Paywall_Validate::is_number( $birthday_year ) ) {
							$form_errors->add_error( '', 'The value for birthday year is invalid.' );
						}

						// Check that year correct length
						if ( ! Simple_Paywall_Validate::is_length( $birthday_year, 4 ) ) {
							$form_errors->add_error( '', 'Please insert a valid year for "Birthday".' );
						}

						// Check that year is within range
						if ( ! Simple_Paywall_Validate::is_within_range( $birthday_year, 1900, (int) ( date( 'Y' ) - 14 ) ) ) {
							$form_errors->add_error( '', 'Please insert a valid year for "Birthday".' );
						}

						// Check that day is within range
						if ( ! Simple_Paywall_Validate::is_within_range( $birthday_day, 1, 31 ) ) {
							$form_errors->add_error( '', 'Please insert a valid day (0 - 31) for "Birthday".' );
						}

						$value = $birthday_year . '-' . $birthday_month . '-' . $birthday_day;

						return $value;

					} else {
						$this->_form_errors->add_error( '', 'Please insert a valid date into the "Birthday" field.' );
					}
				}

				$this->_form_errors->add_error( '', 'It looks like something is not set properly in the Birthday field.' );

			}

		}

		private function get_post_value_city() {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_city' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_city' ) );

			if ( isset( $value )	) {

				// Check that value does not exceed 50 characters in length
				if ( ! Simple_Paywall_Validate::is_max_length( $value, 50 ) ) {
					$this->_form_errors->add_error( '', 'The field "City" should not exceed the maximum 50 characters allowed.' );
				}

				return $value;

			}
			return null;
		}

		private function get_post_value_country() {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_country' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_country' ) );

			if ( isset( $value ) ) {

				// Check that value does not exceed 20 characters in length
				if ( ! Simple_Paywall_Validate::is_max_length( $value, 20 ) ) {
					$this->_form_errors->add_error( '', 'The field "Country" exceeds the maximum 20 characters allowed.' );
				}

				return $value;

			}

			return null;

		}

		/**
		 * Get $_POST value from email field.
		 * This is a required field.
		 * @return string|null $value The $value we are returning from input provided
		 */
		private function get_post_value_email( $is_required = false ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_email' ) ) ) {
				return null;
			}

			$value = sanitize_email( $this->get_post_value( 'simple_paywall_email' ) );

			if ( $is_required ) {
				if ( isset( $value ) ) {
					// Check that value is not empty
					if ( Simple_Paywall_Validate::is_empty( $value ) ) {
						$this->_form_errors->add_error( '', 'The required field "Email" cannot be empty.' );
					}
					// Check that value is a valid email address
					if ( ! Simple_Paywall_Validate::is_email( $value ) ) {
						$this->_form_errors->add_error( '', 'Please insert a valid email address.' );
					}
					return $value;
				}
				return null;
			}

			if ( isset( $value ) ) {
				// Check that it's not empty
				if ( Simple_Paywall_Validate::is_empty( $value ) ) {
					$this->_form_errors->add_error( '', 'The required field "Email" cannot be empty.' );
				}
				// Check that it's a valid email address
				if ( ! Simple_Paywall_Validate::is_email( $value ) ) {
					$this->_form_errors->add_error( '', 'Please insert a valid email address.' );
				}
				return $value;
			}
			return null;
		}

		private function get_post_value_first_name( $is_required = false ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_first_name' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_first_name' ) );

			if ( isset( $value ) ) {

				// Check that value is not empty
				if ( Simple_Paywall_Validate::is_empty( $value ) ) {
					$this->_form_errors->add_error( '', 'The required field "First Name" cannot be empty.' );
				}

				// Check that value is not unusually long
				if ( ! Simple_Paywall_Validate::is_max_length( $value, 120 ) ) {
					$this->_form_errors->add_error( '', 'The field "First Name" cannot exceed 120 characters length.' );
				}

				return $value;

			}

			return null;

		}

		private function get_post_value_last_name( $is_required = false ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_last_name' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_last_name' ) );

			if ( isset( $value ) ) {
				// Check that field is not empty
				if ( Simple_Paywall_Validate::is_empty( $value ) ) {
					$this->_form_errors->add_error( '', 'The required field "Last Name" cannot be empty.' );
				}

				return $value;

			}

			return null;

		}

		private function get_post_value_postal_code() {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_postal_code' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_postal_code' ) );

			if ( isset( $value ) ) {
				if ( ! Simple_Paywall_Validate::is_alpha_numeric( $value ) ) {
					$this->_form_errors->add_error( '', 'The field "Postal Code" is not valid.' );
				}
				return $value;
			}

			return null;

		}

		private function get_post_value_region() {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_region' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_region' ) );

			if ( isset( $value ) ) {
				return $value;
			}

		}

		private function get_post_value_street_address_line_one() {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_street_address_line_one' ) ) )  {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_street_address_line_one' ) );

			if ( isset( $value ) ) {
				if ( ! Simple_Paywall_Validate::is_max_length( $value, 240 ) ) {
					$this->_form_errors->add_error( '', 'The field "Street Address - Line One" exceeds to the maximum 240 characters allowed.' );
				}
				return $value;
			}

		}

		private function get_post_value_street_address_line_two() {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_street_address_line_two' ) ) )  {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_street_address_line_two' ) );

			if ( isset( $value ) ) {
				return $value;
			}

			return null;

		}

		private function get_post_value_verify_account() {}

		public function set_id( $id = null ) {
			if ( is_null( $id ) ) {
				if ( isset( $_GET['user'] ) ) {
					$this->_id = sanitize_text_field( $_GET['user'] );
				}
			} else {
				$this->_id = $id;
			}
		}

		public function set_data( $data ) {
			$this->_data = $data;
		}

		public function get_data() {
			return $this->_data;
		}

		private function get_post_value( $key ) {
			return isset( $_POST[$key] ) ? $_POST[$key] : null;
		}

	}
}
