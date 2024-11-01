<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

if ( ! class_exists( 'Simple_Paywall_Setting_Local' ) ) {
	class Simple_Paywall_Setting_Local {

		private static $_instance = null;

		private
					$_id,
					$_data;

		public function __construct() {
			$this->get_all();
		}

		public static function get_instance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}

		/**
		 * Get data for specific setting
		 * @since 0.6.0
		 * @return void
		 */
		public function get( $setting_name ) {
			if ( isset( $this->_data->$setting_name ) ) {
				return $this->_data->$setting_name;
			}
		}

		/**
		 * Get all Simple Paywall settings from WordPress options
		 * @since 0.6.0
		 * @return void
		 */
		public function get_all() {
			if ( ! empty( get_option( 'simple_paywall_settings' ) ) ) {
				$this->_data = json_decode( get_option( 'simple_paywall_settings' ) );
			}
			return $this->_data;
		}

		public function get_data() {
			return $this->_data;
		}

		public function set_data( $data ) {
			$this->_data = $data;
		}

		/**
		 * Update specific Simple Paywall setting
		 * @since 0.6.0
		 * @return void
		 */
		public function update( $setting_name, $setting_value ) {
			$this->_data->$setting_name = $setting_value;
			// var_dump($this->_data);die();
			update_option( 'simple_paywall_settings', json_encode( $this->_data ) );
		}

	}
}
