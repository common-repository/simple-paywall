<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

if ( ! class_exists( 'Simple_Paywall_Plan' ) ) {
	class Simple_Paywall_Plan {

		private static $_instance = null;

		private $_id,
				$_data;

		public function __construct() {}

		public static function get_instance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}

		/**
		 * Add a new plan
		 */
		public function create() {

			// Verify nonce or die
			Simple_Paywall_Validate::nonce( 'simple-paywall-add-plan' );

			// Set id of resource we are updating
			$this->set_id();

			// Instantiate form errors
			$this->_form_errors = Simple_Paywall_Form_Error::get_instance();

			/** Get, sanitize, and set relevant $_POST values */

			/** @var array $body The array containing sanitized values to submit only validated values in API call */
			$body = array(
				'currency' => $this->get_post_value_plan_currency( array( 'is_required' => true ) ),
				'description' => $this->get_post_value_plan_description(),
				'interval' => $this->get_post_value_plan_duration_interval(),
				'interval_count' => $this->get_post_value_plan_duration_interval_count(),
				'name' => $this->get_post_value_plan_name( array( 'is_required' => true ) ),
				'price' => $this->get_post_value_plan_price( array( 'is_required' => true ) ),
				'product_id' => $this->get_post_value_plan_product( array( 'is_required' => true ) ),
				'receipt_name' => $this->get_post_value_plan_receipt_name(),
				'recurring' => $this->get_post_value_plan_is_recurring( array( 'is_required' => true ) ),
			);

			// Filter out null values
			// This requires php >= 5.3
			$body = array_filter( $body, function( $value ) { return ! is_null( $value ); } );

			// Make API call only if no form errors up to this point
			if ( ! $this->_form_errors->has_errors() ) {
				$api = new Simple_Paywall_API();
				$api->method( 'POST' )
					->endpoint( '/plans' )
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
					$this->_form_errors->add_error( '', 'There was some issue on our end that prevented us from creating a new plan. Please try again.' );
				}
			}

		}

		/**
		 * Return data for plan
		 * @return array The plan data
		 */
		public function get() {
			$this->set_id();
			$api = new Simple_Paywall_API();
			$api->method( 'GET' )->endpoint( '/plans/' . $this->_id )->call();
			$this->_data = $api->get_data();
			return $this->_data;
		}

		public function get_data() {
			return $this->_data;
		}

		public function get_id() {
			return $this->_id;
		}

		private function get_post_value( $key ) {
			return isset( $_POST[$key] ) ? $_POST[$key] : null;
		}

		private function get_post_value_plan_currency( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_plan_currency' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_plan_currency' ) );

			if ( isset( $value ) ) {

				if ( $options['is_required'] ) {
					// Check that value is not an empty
					if ( empty( $value ) ) {
						$this->_form_errors->add_error( '', 'The field "Currency" is required.' );
					}
				}

				// Check that value is not an empty
				if ( empty( $value ) ) {
					$this->_form_errors->add_error( '', 'The field "Currency" is required.' );
				}

				// Force USD currency (temporarily)
				if ( $value !== 'usd' ) {
					$this->_form_errors->add_error( '', 'USD is the only currency allowed at this time.' );
				}

				return $value;

			}

			return null;

		}

		private function get_post_value_plan_description( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_plan_description' ) ) ) {
				return null;
			}

			$value = sanitize_textarea_field( $this->get_post_value( 'simple_paywall_plan_description' ) );

			if ( isset( $value ) ) {
				// Check that value does not exceed 240 characters in length
				if ( ! Simple_Paywall_Validate::is_max_length( $value, 240 ) ) {
					$this->_form_errors->add_error( '', 'The field "Description" cannot exceed the maximum 100 characters allowed.' );
				}
				return $value;
			}

			return null;

		}

		private function get_post_value_plan_duration_interval( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_plan_duration_interval' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_plan_duration_interval' ) );

			if ( isset( $value ) ) {
				// Check that value does not exceed 2 characters in length
				if ( ! Simple_Paywall_Validate::is_max_length( $value, 5 ) ) {
					$this->_form_errors->add_error( '', 'The field "Duration Interval" cannot exceed the maximum 100 characters allowed.' );
				}
				return $value;
			}

			return null;

		}

		private function get_post_value_plan_duration_interval_count( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_plan_duration_interval_count' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_plan_duration_interval_count' ) );

			if ( isset( $value ) ) {
				// Check that value does not exceed 2 characters in length
				if ( ! Simple_Paywall_Validate::is_max_length( $value, 2 ) ) {
					$this->_form_errors->add_error( '', 'The field "Duration Interval Count" cannot exceed the maximum 2 characters allowed.' );
				}
				return $value;
			}

			return null;

		}

		private function get_post_value_plan_name( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_plan_name' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_plan_name' ) );

			if ( isset( $value ) ) {
				if ( $options['is_required'] ) {
					// Check that value is not an empty
					if ( empty( $value ) ) {
						$this->_form_errors->add_error( '', 'The field "Name" is required.' );
					}
				}
				// Check that value does not exceed 100 characters in length
				if ( ! Simple_Paywall_Validate::is_max_length( $value, 100 ) ) {
					$this->_form_errors->add_error( '', 'The field "Name" cannot exceed the maximum 100 characters allowed.' );
				}
				return $value;
			}

			return null;

		}

		private function get_post_value_plan_price( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_plan_price' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_plan_price' ) );

			if ( isset( $value ) ) {

				if ( $options['is_required'] ) {
					// Check that value is not an empty
					if ( empty( $value ) ) {
						$this->_form_errors->add_error( '', 'The field "Price" is required.' );
					}
				}

				// Check that value does not exceed 10 characters in length
				if ( ! Simple_Paywall_Validate::is_max_length( $value, 10 ) ) {
					$this->_form_errors->add_error( '', 'The field "Name" cannot exceed the maximum 100 characters allowed.' );
				}

				$value = Simple_Paywall_Utility::format_price( $value, $this->get_post_value_plan_currency() );

				// Check that price is properly formatted
				if ( strpos( $value, '.' ) !== false ) {
					$this->_form_errors->add_error( '', 'Price is not properly formatted.' );
				}

				// Check that price is not empty
				if ( Simple_Paywall_Validate::is_empty( $value ) ) {
					$this->_form_errors->add_error( '', 'The required field "Price" cannot be empty.' );
				}

				// Check that price is a minimum value
				if ( ! $value >= 50 && $value !== 0 ) {
					$this->_form_errors->add_error( '', 'Price must be greater than $0.50 for the USD currency.' );
				}

				return $value;

			}

			return null;

		}

		private function get_post_value_plan_product( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_plan_product' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_plan_product' ) );

			if ( isset( $value ) ) {
				if ( $options['is_required'] ) {
					// Check that value is not an empty
					if ( empty( $value ) ) {
						$this->_form_errors->add_error( '', 'The field "Product" is required.' );
					}
				}

				// Check that value is 19 characters in length
				if ( strlen( $value ) !== 19 ) {
					$this->_form_errors->add_error( '', 'The field "Product" is required.' );
				}

				return $value;

			}

			return null;

		}

		private function get_post_value_plan_receipt_name( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_plan_receipt_name' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_plan_receipt_name' ) );

			if ( isset( $value ) ) {
				// Check that value does not exceed 100 characters in length
				if ( ! Simple_Paywall_Validate::is_max_length( $value, 100 ) ) {
					$this->_form_errors->add_error( '', 'The field "Name" cannot exceed the maximum 100 characters allowed.' );
				}
				return $value;
			}

			return null;

		}

		private function get_post_value_plan_is_recurring( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_plan_is_recurring' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_plan_is_recurring' ) );

			if ( isset( $value ) ) {

				// Check that value does not exceed 1 character in length
				if ( ! Simple_Paywall_Validate::is_max_length( $value, 1 ) ) {
					$this->_form_errors->add_error( '', 'The field "Recurring" cannot exceed the maximum 100 characters allowed.' );
				}

				return ( $value === '1' ) ? true : false;

			}

			return null;

		}

		public function set_data( $data ) {
			$this->_data = $data;
		}

		public function set_id( $id = null ) {
			if ( is_null( $id ) ) {
				if ( isset( $_GET['plan'] ) ) {
					$this->_id = sanitize_text_field( $_GET['plan'] );
				}
			} else {
				$this->_id = $id;
			}
		}

		public function update() {

			// Set id of resource we are updating
			$this->set_id();

			// Verify nonce or die
			Simple_Paywall_Validate::nonce( 'simple-paywall-update-plan' );

			// Instantiate form errors
			$this->_form_errors = Simple_Paywall_Form_Error::get_instance();

			/** Get, sanitize, and set relevant $_POST values */

 			/** @var array $body The array containing sanitized values to submit only validated values in API call */
 			$body = array(
 				'description' => $this->get_post_value_plan_description(),
 				'name' => $this->get_post_value_plan_name( array( 'is_required' => true ) ),
 				'receipt_name' => $this->get_post_value_plan_receipt_name()
 			);

			// Make API call only if no form errors up to this point
			if ( ! $this->_form_errors->has_errors() ) {
				$api = new Simple_Paywall_API();
				$api->method( 'POST' )
					->endpoint( '/plans/' . $this->_id )
					->body( $body )
					->call();
			}

			// Check if $api call experienced any issues
			if ( isset( $api ) ) {
				if ( $api->get_error() ) {
					$this->_form_errors->add_error( '', $api->get_message() );
				}

				// Ensure a 201 response was received--if no other errors have been caught
				if ( ! $this->_form_errors->has_errors() && $api->get_code() !== 200 ) {
					$this->_form_errors->add_error( '', 'There was some issue on our end that prevented us from updating the product. Please try again.' );
				}
			}

		}

	}
}
