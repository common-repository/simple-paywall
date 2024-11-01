<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

if ( ! class_exists( 'Simple_Paywall_Subscription' ) ) {
	class Simple_Paywall_Subscription {

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

		/** Class methods go below here in alphabetical order */

		/**
		 * Create a new subscription
		 * @return void
		 */
		public function create() {}

		/**
		 * Return data for subscription
		 * @return array The subscription data
		 */
		public function get() {
			$this->set_id();
			$api = new Simple_Paywall_API();
			$api->method( 'GET' )
				->endpoint( '/subscriptions/' . $this->_id )
				->call();
			$this->set_data( $api->get_data() );
			return $api->get_data();
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

		/**
		 * Modify views on simple-paywall_page_simple_paywall_subscribers
		 * @see https://wordpress.stackexchange.com/questions/149143/hide-the-post-count-behind-post-views-remove-all-published-and-trashed-in-cus
		 * @see https://stackoverflow.com/questions/15295853/how-to-filter-views-edit-in-user-list-table
		 * @todo Does not appear to be working
		 */
		public function modify_views_simple_paywall_page_simple_paywall_subscribers( $views ) {
			foreach ( $views as $index => $view ) {
				$views[ $index ] = preg_replace( '/ <span class="count">\([0-9]+\)<\/span>/', '', $view );
			}
			return $views;
		}

		/**
		 * Update subscription details
		 * @return void
		 */
		public function update() {}

		public function set_data( $data ) {
			$this->_data = $data;
		}

		/**
		 * Set id of the subscription from query url param
		 * @param [type] $id [description]
		 */
		public function set_id( $id = null ) {
			if ( is_null( $id ) ) {
				if ( isset( $_GET['subscription'] ) ) {
					$this->_id = sanitize_text_field( $_GET['subscription'] );
				}
			} else {
				$this->_id = $id;
			}
		}

	}
}
