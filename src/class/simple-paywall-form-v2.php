<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

if ( ! class_exists( 'Simple_Paywall_Form_v2' ) ) {
	class Simple_Paywall_Form_v2 {
		private
				/**
				 * The string containing the form output
				 */
				$_form_elements,

				/**
				 * @var string $_error_message The generic error message to use in wp notice
				 */
				$_error_message,

				$_id,

				$_debug_mode = false,

				/**
				 * @var string $_submit The submit element for the form
				 */
				$_submit,

				/**
				 * @var string $_success_message The generic success message to use in wp notice
				 */
				$_success_message;

		public function __construct() {}

		/**
		 * Set the form id. All ids are prefixed with "simple-paywall-". Form ids uses dashes and not underscores.
		 * @return $this
		 */
		public function id( $id ) {
			$this->_id = 'simple-paywall-' . $id;
			return $this;
		}

		/**
		 * Add element to the form
		 * @param Simple_Paywall_Form_Element $element An element to add to the form
		 */
		public function add_element( Simple_Paywall_Form_Element $element ) {
			$this->_form_elements .= '<tr id="simple-paywall-' . $this->underscores_to_dashes( $element->get_name() ) . '-row">';
			$this->_form_elements .= '<th scope="row">';
			// Insert label here if not checkbox type
			if (
				$element->has_label()
				&& $element->get_type() !== 'checkbox'
			) {
				$this->_form_elements .= '<label for="simple-paywall-' . $element->get_name() . '-input" >' . $element->get_label() . ( $element->get_is_required() ? '<span style="color: red">*</span>' : '' ) . '</label>';
			}
			// If checkbox type
			if ( $element->get_type() === 'checkbox' ) {
				$this->_form_elements .= $element->get_text();
			}
			// If section header type
			if ( $element->get_type() === 'section_header' ) {
				$this->_form_elements .= $element->get();
			}
			$this->_form_elements .= '</th>';
			$this->_form_elements .= '<td>';
			if (
				isset( $this->_form_elements )
				&& $element->get_type() !== 'section_header'
			) {
				$this->_form_elements .= $element->get();
			}
			$this->_form_elements .= '</td>';
			$this->_form_elements .= '<tr>';
			return $this;
		}

		public function add_section( Simple_Paywall_Form_Element $header ) {
			$this->_form_elements .= '</tbody>';
			$this->_form_elements .= '</table>';
			$this->_form_elements .= $header->get();
			$this->_form_elements .= '<table class="form-table">';
			$this->_form_elements .= '<tbody>';
		}

		private function dashes_to_underscores( string $string ) {
			$string = str_replace( '-', '_', $string );
			return $string;
		}

		public function display() {
			echo '<form class="simple-paywall"' . ( isset( $this->_id ) ? ' id="' . $this->_id . '"' : '' ) . ' method="post">';
			echo '<p>Fields marked with a <span style="color: red">*</span> are required.</p>';
			echo '<table class="form-table">';
			echo '<tbody>';
			echo $this->_form_elements;
			echo '</tbody>';
			echo '</table>';
			echo $this->wp_nonce();
			$this->errors();
			echo $this->_submit;
			echo '</form>';
			$this->success();
		}

		public function submit( string $text ) {
			$this->_submit = '';
			// Hidden form element to detect submission
			$hidden = new Simple_Paywall_Form_Element;
			$this->_submit .= $hidden->type( 'hidden' )->name( 'update_paywall' )->get();
			$this->_submit .= '<input type="hidden" name="' . $this->dashes_to_underscores( $this->_id ) . '" value="true" />';
			// Submit button
			$header = new Simple_Paywall_Form_Element;
			$this->_submit .= $header->type( 'submit_button' )->name( 'update_paywall' )->text( $text )->get();

		}

		private function wp_nonce() {
			// Start output buffer
			ob_start();
			wp_nonce_field( $this->_id, 'simple_paywall_wp_nonce' );
			// Store the printed data in $editor variable
			$wp_nonce = ob_get_clean();
			return $wp_nonce;
		}

		public function error_message( $message ) {
			$this->_error_message = $message;
			return $this;
		}

		public function success_message( $message ) {
			$this->_success_message = $message;
			return $this;
		}

		/**
		 * Show error messages if errors are present
		 * @return void
		 */
		public function errors() {
			if ( Simple_Paywall_Form_Error::get_instance()->has_errors() ) {
				if ( ! isset( $this->_error_message ) ) {
					$this->_error_message = 'The form did not submit due to some issue(s). Please see below for more details.';
				}
				Simple_Paywall_Form_Error::get_instance()->show_wp_error( $this->_error_message );
				Simple_Paywall_Form_Error::get_instance()->show_error();
			}
		}

		/**
		 * Show success message if no errors are present
		 * @return void
		 */
		public function success() {
			// If form has submitted and there are no errors
			if (
				isset( $_POST[ $this->dashes_to_underscores( $this->_id ) ] ) &&
				$_POST[ $this->dashes_to_underscores( $this->_id ) ] === 'true' &&
				! Simple_Paywall_Form_Error::get_instance()->has_errors()
			) {
				if ( ! isset( $this->_success_message ) ) {
					$this->_success_message = 'The form successfully submitted';
				} ?>
				<div class="notice notice-success is-dismissible" >
					<p><?php _e( $this->_success_message, 'simple-paywall' ); ?></p>
				</div>
			<?php }
		}

		private function underscores_to_dashes( string $string ) {
			$string = str_replace( '_', '-', $string );
			return $string;
		}

	}
}
