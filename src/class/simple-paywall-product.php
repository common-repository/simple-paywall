<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

if ( ! class_exists( 'Simple_Paywall_Product' ) ) {
	class Simple_Paywall_Product {

		private static $_instance = null;

		private
					$_id,
					$_data;

		public function __construct() {}

		public static function get_instance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}

		/**
		 * Add content to product
		 * @since 0.6.0
		 * @return void
		 */
		public function add_content( $product_id, $post_ids ) {

			$body = array(
				'content' => $post_ids
			);

			// Make API call only if no form errors up to this point
			$api = new Simple_Paywall_API();
			$api->method( 'POST' )
				->endpoint( '/products/' . $product_id . '/add-content' )
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
		 * Add new product
		 */
		public function create() {

			// Verify nonce or die
			Simple_Paywall_Validate::nonce( 'simple-paywall-add-product' );

			// Instantiate form errors
			$this->_form_errors = Simple_Paywall_Form_Error::get_instance();

			/** Get, sanitize, and set relevant $_POST values */

			/** @var array $body The array containing sanitized values to submit only validated values in API call */
			$body = array(
				'type' => $this->get_post_value_product_type( true ),
				'name' => $this->get_post_value_product_name( true ),
				'content' => $this->get_post_value_product_content(),
				'description' => $this->get_post_value_product_description()
			);

			// Filter out null values
			// This requires php >= 5.3
			$body = array_filter( $body, function( $value ) { return ! is_null( $value ); } );

			// Make API call only if no form errors up to this point
			if ( ! $this->_form_errors->has_errors() ) {
				$api = new Simple_Paywall_API();
				$api->method( 'POST' )
					->endpoint( '/products' )
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
					$this->_form_errors->add_error( '', 'There was some issue on our end that prevented us from updating the product. Please try again.' );
				}
			}

		}

		/**
		 * Get data for product
		 * @return array $this->_data The product data
		 */
		public function get() {
			$this->set_id();
			$api = new Simple_Paywall_API();
			$api->method( 'GET' )
				->endpoint( '/products/' . $this->_id )
				->call();
			$this->_data = $api->get_data();
			return $this->_data;
		}

		/**
		 * Get all products on account
		 * @since 0.6.0
		 * @return [type] [description]
		 */
		public function get_collection() {
			$api = new Simple_Paywall_API();
			$api->method( 'GET' )
				->endpoint( '/products' )
				->call();
			$this->set_data( $api->get_data() );
			return $this->_data;
		}

		public function get_data() {
			return $this->_data;
		}

		private function get_post_value_product_content() {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_product_content' ) ) ) {
				return null;
			}

			$valueArray = $this->get_post_value( 'simple_paywall_product_content' );

			if ( isset( $valueArray ) ) {
				// Cast string integers as int types
				foreach ( $valueArray as $key => $value ) {
					$valueArray[$key] = (int) sanitize_text_field( $value );
				}
				return $valueArray;
			}

			return null;

		}

		private function get_post_value_product_description() {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_product_description' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_product_description' ) );

			if ( isset( $value ) ) {
				// Check that value does not exceed 240 characters in length
				if ( ! Simple_Paywall_Validate::is_max_length( $value, 240 ) ) {
					$this->_form_errors->add_error( '', 'The field "Description" cannot exceed the maximum 240 characters allowed.' );
				}
				return $value;
			}

			return null;

		}

		private function get_post_value_product_name( $is_required = false ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_product_name' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_product_name' ) );

			if ( isset( $value ) ) {
				if ( $is_required ) {
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

		private function get_post_value_product_type( $is_required = false ) {

			// Check if value is null
			if ( is_null( $this->get_post_value( 'simple_paywall_product_type' ) ) ) {
				return null;
			}

			$value = sanitize_text_field( $this->get_post_value( 'simple_paywall_product_type' ) );

			if ( isset( $value ) ) {
				if ( $is_required ) {
					// Check that value is not an empty
					if ( empty( $value ) ) {
						$this->_form_errors->add_error( '', 'The field "Type" is required.' );
					}
				}
				return $value;
			}

			return null;

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
				->endpoint( '/products/remove-content' )
				->body( $body )
				->call();

			// Check if $api call experienced any issues
			if ( isset( $api ) ) {
				// ...
			}
		}

		public function update() {

			// Set id of resource we are updating
			$this->set_id();

			// Verify nonce or die
			Simple_Paywall_Validate::nonce( 'simple-paywall-update-product' );

			// Instantiate form errors
			$this->_form_errors = Simple_Paywall_Form_Error::get_instance();

			/** Get, sanitize, and set relevant $_POST values */

			/** @var array $body The array containing sanitized values to submit only validated values in API call */
			$body = array(
				'content' => $this->get_post_value_product_content(),
				'description' => $this->get_post_value_product_description(),
				'name' => $this->get_post_value_product_name()
				// 'type' => '' // Cannot up type after a product has been created
			);

			// Filter out null values
			// This requires php >= 5.3
			$body = array_filter( $body, function( $value ) { return ! is_null( $value ); } );

			// Make API call only if no form errors up to this point
			if ( ! $this->_form_errors->has_errors() ) {
				$api = new Simple_Paywall_API();
				$api->method( 'POST' )
					->endpoint( '/products/' . $this->_id )
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

		public function set_data( $data ) {
			$this->_data = $data;
		}

		public function set_id( $id = null ) {
			if ( is_null( $id ) ) {
				if ( isset( $_GET['product'] ) ) {
					$this->_id = sanitize_text_field( $_GET['product'] );
				}
			} else {
				$this->_id = $id;
			}
		}

		public function get_id() {
			return $this->_id;
		}

		private function get_post_value( $key ) {
			return isset( $_POST[$key] ) ? $_POST[$key] : null;
		}

	}
}
