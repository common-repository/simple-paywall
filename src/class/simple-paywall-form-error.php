<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

if ( ! class_exists( 'Simple_Paywall_Form_Error' ) ) {
	class Simple_Paywall_Form_Error {

		private static $_instance = null;

		private
				/**
				 * @var boolean $_is_errors Whether there are form errors present or not
				 */
				$_is_errors = false,

				/**
				 * @var array $_errors The form errors
				 */
				$_errors = array();

		public function __construct() {}

		public static function get_instance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public function add_error( $elementName, $message ) {
			array_push( $this->_errors, $message );
			$this->_is_errors = true;
		}

		public function has_errors() {
			return $this->_is_errors;
		}

		public function get_errors() {
			return $this->_errors;
		}

		public function show_error() { ?>
			<div class="simple-paywall-error-message">
				<strong>The resource could not be updated because:</strong>
				<ul style="list-style: disc; margin-left: 2em;">
					<?php foreach ( Simple_Paywall_Form_Error::get_instance()->get_errors() as $error ) {
						echo '<li>' . esc_html( $error ) . '</li>';
					} ?>
				</ul>
			</div>
		<?php }

		public function show_success( $message ) { ?>
			<div class="notice notice-success is-dismissible" >
				<p><?php _e( $message, 'simple-paywall' ); ?></p>
			</div>
		<?php }

		public function show_wp_error( $message ) { ?>
			<div class="notice notice-error is-dismissible" >
				<p><?php _e( $message, 'simple-paywall' ); ?></p>
			</div>
		<?php }

	}
}
