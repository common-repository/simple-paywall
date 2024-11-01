<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

if ( ! class_exists( 'Simple_Paywall_Config' ) ) {
	class Simple_Paywall_Config {

		private static 	$_instance = null;

		private $_version = '',
				/**
				 * States whether the plugin is activated with Simple Paywall.
				 * @var boolean
				 */
				$_activated = false,
				$_public_api_key,
				$_secret_api_key,
				$_access_token = null,
				$_refresh_token = null,
				$_debug_mode = false;

		public function __construct() {
			$this->_version = SIMPLE_PAYWALL_WORDPRESS_PLUGIN_VERSION;
			$this->add_hooks();
			$this->is_activated();
			// var_dump( $this->_activated ); die(); // Test
		}

		public static function getInstance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public static function get_instance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		private function add_hooks() {
			add_action( 'admin_init', array( $this, 'add_options' ) );
			// add_action( 'admin_notices', array( $this, 'your_admin_notices_action' ) );
		}

		private function is_activated() {
			if ( $this->has_access_token() ) {
				$this->_activated = true;
				return true;
			}
			$this->_activated = false;
			return false;
		}

		/**
		 * Checks if plugin is activated or not
		 * @return boolean
		 */
		public function get_activated() {
			return $this->_activated;
		}

		/**
		 * Add options
		 */
		public function add_options() {
			if ( ! get_option( 'simple_paywall_api_keys' ) ) {
				add_option( 'simple_paywall_api_keys', '', '', 'yes' );
			}
			if ( ! get_option( 'simple_paywall_oauth' ) ) {
				add_option( 'simple_paywall_oauth', '', '', 'yes' );
			}
			if ( ! get_option( 'simple_paywall_settings' ) ) {
				add_option( 'simple_paywall_settings', '', '', 'yes' );
				$default_enabled_post_types = array(
					'enabled_post_types' => array( 'post', 'page' )
				);
				update_option( 'simple_paywall_settings', json_encode( $default_enabled_post_types ) );
			}
		}

		public function api_keys_is_set() {

			if ( empty( get_option( 'simple_paywall_api_keys' ) ) ) {
				return false;
			}

			return true;

		}

		public function get_public_api_key() {
			$api_keys = json_decode( get_option( 'simple_paywall_api_keys' ) );
			$public_api_key = $api_keys->public_key;
			return $public_api_key;
		}

		public function get_private_api_key() {
			$api_keys = json_decode( get_option( 'simple_paywall_api_keys' ) );
			$private_api_key = $api_keys->private_key;
			return $private_api_key;
		}

		public function has_access_token() {
			$access_tokens = json_decode( get_option( 'simple_paywall_oauth' ) );
			return ( $access_tokens ) ? true : false;
		}

		public function get_access_token() {
			$access_tokens = json_decode( get_option( 'simple_paywall_oauth' ) );
			if ( $access_tokens ) {
				$access_token = $access_tokens->access_token;
			} else {
				$access_token = '';
			}
			return $access_token;
		}

		public function get_oauth_token() {
			$oauth_token = json_decode( get_option( 'simple_paywall_oauth' ) );
			return $oauth_token;
		}

		public function get_refresh_token() {
			$access_tokens = json_decode( get_option( 'simple_paywall_oauth' ) );
			if ( $access_tokens ) {
				$refresh_token = $access_tokens->refresh_token;
			} else {
				$refresh_token = '';
			}
			return $refresh_token;
		}

		public function is_debug_mode() {
			return $this->_debug_mode;
		}

	}
}
