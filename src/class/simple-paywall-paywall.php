<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

if ( ! class_exists( 'Simple_Paywall_Paywall' ) ) {
	class Simple_Paywall_Paywall {

		private static $_instance = null;

		private
					$_data,
					$_id;

		public function __construct() {}

		public static function get_instance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}

		/**
		 * Add content to paywall
		 * @since 0.6.0
		 * @return void
		 */
		public function add_content( $paywall_id, $post_ids ) {

			$body = array(
				'content' => $post_ids
			);

			// Make API call only if no form errors up to this point
			$api = new Simple_Paywall_API();
			$api->method( 'POST' )
				->endpoint( '/paywalls/' . $paywall_id . '/add-content' )
				->body( $body )
				->call();

			// Check if $api call experienced any issues
			if ( isset( $api ) ) {

				// if ( $api->get_error() ) {
				// 	$this->_form_errors->add_error( '', $api->get_message() );
				// }

				// Ensure a 200 response was received--if no other errors have been caught
				// if ( ! $this->_form_errors->has_errors() && $api->get_code() !== 200 ) {
				// 	$this->_form_errors->add_error( '', 'There was some issue on our end that prevented us from updating the paywall. Please try again.' );
				// }

			}

		}

		/**
		 * Create a new paywall
		 * @return null
		 */
		public function create() {

			// Verify nonce or die
			Simple_Paywall_Validate::nonce( 'simple-paywall-add-paywall' );

			// Get and set id of resource
			$this->set_id();

			// Instantiate form errors
			$this->_form_errors = Simple_Paywall_Form_Error::get_instance();

			/** Get, sanitize, and set relevant $_POST values */

			/** @var array $body The array containing sanitized values to submit only validated values in API call */
			$body = array(
				'description' => $this->get_post_value_paywall_description(),
				'limit_count' => $this->get_post_value_paywall_limit_count(),
				'limit_interval' => $this->get_post_value_paywall_limit_cycle_interval(),
				'limit_interval_count' => $this->get_post_value_paywall_limit_cycle_interval_count(),
				'name' => $this->get_post_value_paywall_name(),
				'type' => $this->get_post_value_paywall_type()
			);

			/**
			 * Filter out null values
			 * This approach requires php >= 5.3
			 */
			$body = array_filter( $body, function( $value ) { return ! is_null( $value ); } );

			// Make API call only if no form errors up to this point
			if ( ! $this->_form_errors->has_errors() ) {
				$api = new Simple_Paywall_API();
				$api->method( 'POST' )
					->endpoint( '/paywalls' )
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
					$this->_form_errors->add_error( '', 'There was some issue on our end that prevented us from updating the paywall. Please try again.' );
				}

			}

		}

		/**
		 * Return data for paywall
		 * @return array The paywall data
		 */
		public function get() {
			$this->set_id();
			$api = new Simple_Paywall_API();
			$api->call_legacy( '/paywalls/' . $this->_id );
			$this->set_data( $api->get_data() );
			return $api->get_data();
		}

		public function get_collection() {
			$api = new Simple_Paywall_API();
			$api->method( 'GET' )
				->endpoint( '/paywalls' )
				->call();
			$this->set_data( $api->get_data() );
			return $api->get_data();
		}

		private function get_post_value( $key ) {
			return isset( $_POST[$key] ) ? $_POST[$key] : null;
		}

		/**
		 * Get the content assigned to paywall
		 * @todo Return empty string as emptry string instead of null and send to API
		 * @param array $options Optional settings that can be applied to this function
		 * @return null|array Null if not set or array containing items to assign to paywall
		 */
		private function get_post_value_paywall_content( $options = null ) {

			// Return null if element is not detected in $_POST
			if ( ! array_key_exists( 'simple_paywall_paywall_content_submitted', $_POST ) ) {
				return null;
			}

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_paywall_content' ) ) ) {
				return array();
			}

			/** @var array $valueArray The content selected to be assigned to paywall */
			$valueArray = $this->get_post_value( 'simple_paywall_paywall_content' );

			if ( isset( $valueArray ) ) {
				// Convert each id to int type
				foreach ( $valueArray as $key => $value ) {
					$valueArray[$key] = (int) sanitize_text_field( $value );
				}
				return $valueArray;
			}

			return array();

		}

		private function get_post_value_paywall_default_notice_type( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_paywall_default_notice_type' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_paywall_default_notice_type' ) );

			if ( isset( $value ) ) {

				// If field is marked as required
				if ( $options['is_required'] ) {
					// Check that value is not empty
					if ( empty( $value ) ) {
						$this->_form_errors->add_error( '', 'The field "Notice Type" is required.' );
					}
				}

				// Check that value is not empty
				if ( empty( $value ) ) {
					$this->_form_errors->add_error( '', 'The field "Notice Type" is required.' );
				}

				// Check that value matches one of the allowed values
				if ( ! Simple_Paywall_Validate::is_value( $value, array( 'sticky_floating_footer_bar' ) ) ) {
					$this->_form_errors->add_error( '', 'Invalid value for "Notice Type" field' );
				}

				// Check if value is same--no change in value should return null
				if ( isset( $this->_data->notice->type ) ) {
					if ( $value == $this->_data->notice->type ) {
						return null;
					}
				}

				return $value;

			}

			return null;

		}

		private function get_post_value_paywall_default_notice_content( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_paywall_default_notice_content' ) ) ) {
				return null;
			}

			$value = wp_kses_post( $this->get_post_value( 'simple_paywall_paywall_default_notice_content' ) );

			// Reverse escaping of double quotes from wp_kses_post() - to make compatible with json_encode()
			$value = str_replace( "\\\"", "\"", $value );

			// Reverse escaping of single quotes from wp_kses_post() - to make compatible with json_encode()
			$value = str_replace( "\'", "'", $value );

			if ( isset( $value ) ) {
				if ( empty( $value ) ) {
					$this->_form_errors->add_error( '', 'The field "Default Notice Content" is required and cannot be empty.' );
				}
				return $value;
			}

			return null;

		}

		private function get_post_value_paywall_default_final_notice_content( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_paywall_default_final_notice_content' ) ) ) {
				return null;
			}

			$value = wp_kses_post( $this->get_post_value( 'simple_paywall_paywall_default_final_notice_content' ) );

			// Reverse escaping of double quotes from wp_kses_post() - to make compatible with json_encode()
			$value = str_replace( "\\\"", "\"", $value );

			// Reverse escaping of single quotes from wp_kses_post() - to make compatible with json_encode()
			$value = str_replace( "\'", "'", $value );

			if ( isset( $value ) ) {
				if ( $options['is_required'] ) {
					if ( Simple_Paywall_Validate::is_empty( $value ) ) {
						$this->_form_errors->add_error( '', 'The field "Default Final Notice Content" is required and cannot be empty.' );
					}
				}
				return $value;
			}

			return null;

		}

		private function get_post_value_paywall_default_restriction_type( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_paywall_default_restriction_type' ) ) ) {
				return null;
			}

			$value = $this->get_post_value( 'simple_paywall_paywall_default_restriction_type' );

			if ( isset( $value ) ) {
				// Check that value is one of the restriction types ones allowed
				if ( ! Simple_Paywall_Validate::is_value( $value, array( 'overlay' ) ) ) {
					$this->_form_errors->add_error( '', 'Invalid value for "Restriction Type" field' );
				}
				return $value;
			}

			return null;

		}

		private function get_post_value_paywall_default_user_restriction_content( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_paywall_user_restriction_content' ) ) ) {
				return null;
			}

			$value = wp_kses_post( $this->get_post_value( 'simple_paywall_paywall_user_restriction_content' ) );

			// Revert escaping of single quotes from wp_kses_post()
			$value = str_replace( "\'", "'", $value );

			if ( isset( $value ) ) {
				if ( empty( $value ) ) {
					$this->_form_errors->add_error( '', 'The required field "Restriction Content for Users" cannot be empty.' );
				}
				return $value;
			}

			return null;

		}

		private function get_post_value_paywall_default_visitor_restriction_content( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_paywall_visitor_restriction_content' ) ) ) {
				return null;
			}

			$value = wp_kses_post( $this->get_post_value( 'simple_paywall_paywall_visitor_restriction_content' ) );

			// Revert escaping of single quotes from wp_kses_post()
			$value = str_replace( "\'", "'", $value );

			if ( isset( $value ) ) {
				if ( empty( $value ) ) {
					$this->_form_errors->add_error( '', 'The required field "Restriction Content for Visitors" cannot be empty.' );
				}
				return $value;
			}

		}

		private function get_post_value_paywall_description( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_paywall_description' ) ) ) {
				return null;
			}

			$value = sanitize_textarea_field( $this->get_post_value( 'simple_paywall_paywall_description' ) );

			// Revert escaping of single quotes from sanitize_textarea_field()
			$value = str_replace( "\'", "'", $value );

			if ( isset( $value ) ) {

				// Check that value does not exceed the maximum length allowed
				if ( ! Simple_Paywall_Validate::is_max_length( $value, 240 ) ) {
					$this->_form_errors->add_error( '', 'The field "Description" cannot exceed 240 characters in length.' );
				}

				// Check if value is same--no change in value should return null
				if ( isset( $this->_data ) ) {
					if ( $value == $this->_data->description ) {
						return null;
					}
				}

				return $value;

			}

		}

		private function get_post_value_paywall_is_user_only( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_paywall_is_user_only' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_paywall_is_user_only' ) );

			if ( isset( $value ) ) {
				return ( $value == 1 ) ? true : false;
			}

			return null;

		}

		private function get_post_value_paywall_limit_count( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_paywall_limit_count' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_paywall_limit_count' ) );

			if ( isset( $value ) ) {

				// If field marked as required
				if ( $options['is_required'] ) {
					// Check that value is not an empty
					if ( empty( $value ) ) {
						$this->_form_errors->add_error( '', 'The field "Limit Count" is required.' );
					}
				}

				// Get the paywall's type
				$type = $this->get_post_value_paywall_type() ? $this->get_post_value_paywall_type() : $this->_data->type;

				// Validate the following if this is a soft paywall
				if ( isset( $type ) && $type === 'soft' ) {

					// Check that limit count is set
					if ( empty( $this->get_post_value_paywall_limit_cycle_interval_count() ) ) {
						$this->_form_errors->add_error( '', 'The field "Limit Count" is required.' );
					}

					// Check that value is within the allowed range
					if ( ! Simple_Paywall_Validate::is_within_range( $value, 1, 100 ) ) {
						$this->_form_errors->add_error( '', 'The field "Limit Count" must be somewhere between 1 and 100.' );
					}

					return $value;

				}

				// Validate the following is this is a hard paywall
				if ( isset( $type ) && $type === 'hard' ) {
					return $value;
				}

				// Check if value is same--no change in value should return null
				if ( isset( $this->_data ) ) {
					if ( $value == $this->_data->limit_count ) {
						return null;
					}
				}

				return null;

			}

			return null;

		}

		private function get_post_value_paywall_limit_cycle_interval( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_paywall_limit_cycle_interval' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_paywall_limit_cycle_interval' ) );

			if ( isset( $value ) ) {

				// If field marked as required
				if ( $options['is_required'] ) {
					// Check that value is not an empty
					if ( empty( $value ) ) {
						$this->_form_errors->add_error( '', 'The field "Limit Cycle" is required.' );
					}
				}

				// Check that value is set if paywall type is "soft"
				$type = $this->get_post_value_paywall_type();
				if ( isset( $type ) && $type === 'soft' ) {
					if ( empty( $value ) ) {
						$this->_form_errors->add_error( '', 'The field "Limit Cycle" is required.' );
					}
				}

				// Check that value does not exceed 100
				if ( ! Simple_Paywall_Validate::is_max_length( $value, 100 ) ) {
					$this->_form_errors->add_error( '', 'The field "Name" cannot exceed the maximum 100 characters allowed.' );
				}

				return $value;

			}

			return null;

		}

		private function get_post_value_paywall_limit_cycle_interval_count( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_paywall_limit_cycle_interval_count' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_paywall_limit_cycle_interval_count' ) );

			if ( isset( $value ) ) {

				// If field marked as required
				if ( $options['is_required'] ) {
					// Check that value is not an empty
					if ( empty( $value ) ) {
						$this->_form_errors->add_error( '', 'The field "Limit Cycle" is required.' );
					}
				}

				// Check that value is set if paywall type is "soft"
				$type = $this->get_post_value_paywall_type() ? $this->get_post_value_paywall_type() : $this->_data->type;
				if ( isset( $type ) && $type === 'soft' ) {
					if ( empty( $value ) ) {
						$this->_form_errors->add_error( '', 'The field "Limit Cycle" is required.' );
					}
				}

				// Check that value does not exceed 100
				if ( ! Simple_Paywall_Validate::is_max_length( $value, 100 ) ) {
					$this->_form_errors->add_error( '', 'The field "Name" cannot exceed the maximum 100 characters allowed.' );
				}

				// Check if value is same--no change in value should return null
				if ( isset( $this->_data ) ) {
					if ( $value == $this->_data->limit_interval ) {
						return null;
					}
				}

				return $value;

			}

			return null;

		}

		private function get_post_value_paywall_name( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_paywall_name' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_paywall_name' ) );

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

		private function get_post_value_paywall_notice_style_background_color( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_paywall_notice_style_background_color' ) ) ) {
				return null;
			}

			$value = sanitize_hex_color( $this->get_post_value( 'simple_paywall_paywall_notice_style_background_color' ) );

			if ( isset( $value ) ) {
				return $value;
			}

			return null;

		}

		private function get_post_value_paywall_notice_style_close_button_color( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_paywall_notice_style_close_button_color' ) ) ) {
				return null;
			}

			$value = sanitize_hex_color( $this->get_post_value( 'simple_paywall_paywall_notice_style_close_button_color' ) );

			if ( isset( $value ) ) {
				return $value;
			}

			return null;

		}

		private function get_post_value_paywall_notice_style_max_width( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_paywall_notice_style_max_width' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_paywall_notice_style_max_width' ) );

			if ( isset( $value ) ) {
				return $value;
			}

			return null;

		}

		private function get_post_value_paywall_is_rolling_limit( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_paywall_is_rolling_limit' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_paywall_is_rolling_limit' ) );

			// limit_is_rolling
			if ( isset( $value ) ) {
				$type = $this->get_post_value_paywall_type();
				if ( isset( $type ) && $type === 'soft' ) {
					return ( $value == 1 ) ? true : false;
				}
			}

			return null;

		}

		private function get_post_value_paywall_type( $options = null ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_paywall_type' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_paywall_type' ) );

			if ( isset( $value ) ) {

				// Check if field is marked as required
				if ( $options['is_required'] ) {
					// Check that value is set
					if ( empty( $value ) ) {
						$this->_form_errors->add_error( '', 'The field "Notice Type" is required.' );
					}
				}

				// Check that value is only one of the ones allowed
				if ( ! Simple_Paywall_Validate::is_value( $value, array( 'hard', 'soft' ) ) ) {
					$this->_form_errors->add_error( '', 'Invalid value for "Notice Type" field' );
				}

				// Check if value is same--no change in value should return null
				if ( isset( $this->_data ) ) {
					if ( $value == $this->_data->type ) {
						return null;
					}
				}

				return $value;

			}

			return null;

		}

		public function set_id( $id = null ) {
			if ( is_null( $id ) ) {
				if ( isset( $_GET['paywall'] ) ) {
					$this->_id = sanitize_text_field( $_GET['paywall'] );
				}
			} else {
				$this->_id = $id;
			}
		}

		public function set_data( $data ) {
			$this->_data = $data;
		}

		public function get_id() {
			return $this->_id;
		}

		public function get_data() {
			return $this->_data;
		}

		/**
		 * Remove posts from their assigned paywall
		 */
		public function remove_content( $post_ids ) {

			$body = array(
				'content' => $post_ids
			);

			// Make API call only if no form errors up to this point
			$api = new Simple_Paywall_API();
			$api->method( 'POST' )
				->endpoint( '/paywalls/remove-content' )
				->body( $body )
				->call();

			// Check if $api call experienced any issues
			if ( isset( $api ) ) {

				// if ( $api->get_error() ) {
				// 	$this->_form_errors->add_error( '', $api->get_message() );
				// }

				// Ensure a 200 response was received--if no other errors have been caught
				// if ( ! $this->_form_errors->has_errors() && $api->get_code() !== 200 ) {
				// 	$this->_form_errors->add_error( '', 'There was some issue on our end that prevented us from updating the paywall. Please try again.' );
				// }

			}
		}

		/**
		 * Update paywall
		 * @return null
		 */
		public function update() {

			// Verify nonce or die
			Simple_Paywall_Validate::nonce( 'simple-paywall-update-paywall' );

			// Set id of resource we are updating
			$this->set_id();

			// Get the paywall we are updating to compare data
			$this->get();

			// Instantiate form errors
			$this->_form_errors = Simple_Paywall_Form_Error::get_instance();

			/** @var array $body The array containing sanitized values to submit only validated values in API call */
			$body = array(
				'content' => $this->get_post_value_paywall_content(),
				'default_notice_content' => $this->get_post_value_paywall_default_notice_content(),
				'default_notice_type' => $this->get_post_value_paywall_default_notice_type(),
				'default_final_notice_content' => $this->get_post_value_paywall_default_final_notice_content(),
				'default_restriction_type' => $this->get_post_value_paywall_default_restriction_type(),
				'default_user_restriction_content' => $this->get_post_value_paywall_default_user_restriction_content(),
				'default_visitor_restriction_content' => $this->get_post_value_paywall_default_visitor_restriction_content(),
				'description' => $this->get_post_value_paywall_description(),
				'limit_count' => $this->get_post_value_paywall_limit_count(),
				'limit_interval' => $this->get_post_value_paywall_limit_cycle_interval(),
				'limit_interval_count' => $this->get_post_value_paywall_limit_cycle_interval_count(),
				// 'limit_is_rolling' => $this->get_post_value_paywall_is_rolling_limit(),
				'name' => $this->get_post_value_paywall_name(),
				'notice_style_background_color' => $this->get_post_value_paywall_notice_style_background_color(),
				'notice_style_close_button_color' => $this->get_post_value_paywall_notice_style_close_button_color(),
				'notice_style_max_width' => $this->get_post_value_paywall_notice_style_max_width(),
				'type' => $this->get_post_value_paywall_type(),
				'user_only' => $this->get_post_value_paywall_is_user_only(),
			);

			/**
			 * Filter out null values
			 * This approach requires php >= 5.3
			 */
			$body = array_filter( $body, function( $value ) { return ! is_null( $value ); } );

			// Make API call only if no form errors up to this point
			if ( ! $this->_form_errors->has_errors() ) {
				// Check that there are changes to be submitted
				if ( ! empty( $body ) ) {
					$api = new Simple_Paywall_API();
					$api->method( 'POST' )
						->endpoint( '/paywalls/' . $this->_id )
						->body( $body )
						->call();
				}
			}

			// Check if $api call experienced any issues
			if ( isset( $api ) ) {

				if ( $api->get_error() ) {
					$this->_form_errors->add_error( '', $api->get_message() );
				}

				// Ensure a 201 response was received--if no other errors have been caught
				if ( ! $this->_form_errors->has_errors() && $api->get_code() !== 200 ) {
					$this->_form_errors->add_error( '', 'There was some issue on our end that prevented us from updating the paywall. Please try again.' );
				}

			}

		}

	}
}
