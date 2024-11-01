<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

if ( ! class_exists( 'Simple_Paywall_Form_Element' ) ) {
	class Simple_Paywall_Form_Element {
		private

			$_debug_mode = false,

			/**
			 * @var string $_default_value The default value for an element (if applicable)
			 */
			$_default_value,

			/**
			 * @var string $_field The string containing the element
			 */
			$_element,

			/**
			 * @var string $_id The id attribute of the field we are building
			 */
			$_id,

			/**
			 * @var boolean $_is_checked Sets whether a checkbox is checked by default (if applicable)
			 */
			$_is_checked = false,

			/**
			 * @var boolean $_is_disabled Sets whether or not the field is disabled
			 */
			$_is_disabled = false,

			/**
			 * @var boolean $_is_required Sets whether or not the field is a required field
			 */
			$_is_required = false,

			/**
			 * @var string $_label
			 */
			$_label,

			/**
			 * @var array $_options The options available in a select type form element
			 */
			$_options,

			/**
			 * @var string $_placeholder Set a placeholder value for an element (if applicable)
			 */
			$_placeholder,

			/**
			 * @var string $_style Apply a particular style that is predefined.
			 */
			$_style,

			/**
			 * @var $_text The text that is found inside the opening and closing tags of an element
			 */
			$_text,

			/**
			 * @var $_type The type of field we are building
			 */
			$_type,

			/**
			 * @var $_value The starting or default value of an element
			 */
			$_value,

			/**
			 * @var $_width The width of a field
			 */
			$_width;

		public function __construct() {}

		private function date() {
			$output = null;
			// Month
			$output .= $this->input_select_month();
			// Day
			$output .= $this->input_text_day();
			// Comma
			$output .= ', ';
			// Year
			$output .= $this->input_text_year();
			return $output;
		}

		public function default_value( $default_value_string ) {
			$this->_default_value = $default_value_string;
			return $this;
		}

		/**
		 * The description to show along with a field
		 * @param string $description The description you wish to display beneath the field.
		 * @return self
		 */
		public function description( $description ) {
			if ( is_string( $description ) ) {
				if ( isset( $this->_description ) ) {
					$this->_description .= '<p class="description"><i class="fas fa-info-circle"></i><em>' . $description . '</em></p>';
				} else {
					$this->_description = '<p class="description"><i class="fas fa-info-circle"></i><em>' . $description . '</em></p>';
				}
			}
			if ( is_array( $description ) ) {
				foreach ( $description as $descript ) {
					$this->_description .= '<br><i class="fas fa-info-circle"></i><em>' . $description . '</em>';
				}
			}
			return $this;
		}

		private function duration_select() {

			$options = '';

			// Set default values, if values are set
			if ( isset( $this->_value ) ) {
				if ( ! is_array( $this->_value ) ) {
					throw new \InvalidArgumentException( 'value() must be an array for this form element.' );
				}
				$default_interval_count = $this->_value[0];
				$default_interval = $this->_value[1];
			}

			for ( $x = 1; $x <= 10; $x++ ) {
				$options .= '<option ';
				$options .= ( isset( $default_interval_count ) ? $default_interval_count == $x ? 'selected="' . esc_attr( 'selected' ) . '" ' : '' : '' );
				$options .= 'value="' . $x . '" ';
				$options .= '>';
				$options .= esc_html( $x );
				$options .= '</option>';
			}

			// Add infinity option - value of infinity in this case is 0
			$options .= '<option value="' . 0 . '">' . '&#x221e;' . '</option>';

			// Interval count
			$element = '<select ';
			$element .= 'id="' . esc_attr( 'simple-paywall-' . $this->underscores_to_dashes( $this->_name ) . '-interval-count') . '" ';
			$element .= 'name="' . esc_attr( 'simple_paywall_' . $this->_name . '_interval_count' ) . '" ';
			$element .= 'style="width: 50px;" ';
			$element .= 'autocomplete="off" ';
			$element .= '>';
			$element .= $options;
			$element .= '</select>';

			$interval_values = array(
				'hour' => 'Hour(s)',
				'day' => 'Day(s)',
				'week' => 'Week(s)',
				'month' => 'Month(s)',
				'year' => 'Year(s)'
				// 'infinity' => '&#x221e;'
			);

			$interval_options = '';
			foreach ( $interval_values as $key => $value ) {
				$interval_options .= '<option ';
				$interval_options .= ( isset( $default_interval ) ? $default_interval == $key ? 'selected="selected" ' : '' : ( $key === 'month' ? 'selected="selected" ' : '' ) ) ;
				$interval_options .= 'value="' . $key . '"';
				$interval_options .= '>';
				$interval_options .= __( $value, 'simple-paywall' );
				$interval_options .= '</option>';
			}

			// Interval
			$element .= '<select ';
			$element .= 'id="' . esc_attr( 'simple-paywall-' . $this->underscores_to_dashes(  $this->_name ) . '-interval ') . '" ';
			$element .= 'name="' . esc_attr( 'simple_paywall_' . $this->_name . '_interval') . '" ';
			$element .= 'style="width: 150px;" ';
			$element .= 'autocomplete="off" ';
			$element .= '>';
			$element .= __( $interval_options, 'simple-paywall' );
			$element .= '</select>';

			return $element;

		}

		/**
		 * Get and echo the field
		 * @return $this->_field
		 */
		public function get() {
			// Check that required properties are set
			if ( ! isset( $this->_type ) ) {
				throw new Exception( 'The field\'s type was not set.' );
			}

			switch ( $this->_type ) {
				case 'checkbox':
					$this->_element = $this->input_checkbox() . $this->get_description();
					break;
				case 'date':
					$this->_element = $this->date() . $this->get_description();
					break;
				case 'duration':
					$this->_element = $this->duration_select() . $this->get_description();
					break;
				case 'email':
					$this->_element = $this->input_text() . $this->get_description();
					break;
				case 'hidden':
					$this->_element = $this->hidden();
					break;
				case 'message':
					$this->_element = $this->message();
					break;
				case 'multiselect':
					$this->_element = $this->get_description() . '<br>' . $this->input_multiselect();
					break;
				case 'section_header':
					$this->_element = $this->section_header() . $this->get_description();
					break;
				case 'select':
					$this->_element = $this->input_select();
					break;
				case 'submit_button':
					$this->_element = $this->submit_button() . $this->get_description();
					break;
				case 'text':
					$this->_element = $this->input_text() . $this->get_description();
					break;
				case 'textarea':
					$this->_element = $this->input_textarea() . $this->get_description();
					break;
				case 'wp_editor':
					$this->_element = $this->get_description() . '<br>' . $this->input_wp_editor();
					break;
			}

			return $this->_element;
		}

		public function get_description() {
			if ( isset( $this->_description ) ) {
				return $this->_description;
			}
			return '';
		}

		public function get_is_required() {
			return $this->_is_required;
		}

		public function get_label() {
			return $this->_label;
		}

		public function get_name() {
			return $this->_name;
		}

		public function get_text() {
			return $this->_text;
		}

		public function get_type() {
			return $this->_type;
		}

		public function has_label() {
			if ( is_null( $this->_label ) ) {
				return false;
			}
			return true;
		}

		private function hidden() {
			$element = '<input type="hidden"';
			$element .= 'name="' . esc_attr( 'simple_paywall_' . $this->_name ) . '" ';
			$value = isset( $this->_value ) ? $this->_value : 'true';
			$element .= 'value="' . esc_attr( $value ) . '" ';
			$element .= '/>';
			return $element;
		}

		private function input_checkbox() {

			/**
			 * Must set a value via a hidden input in case the checkbox is not checked.
			 * Unchecked checkbox inputs are not sent to POST.
			 */

			// Hidden
			$element = '<input type="hidden"';
			$element .= 'id="' . esc_attr( 'simple-paywall-' . $this->underscores_to_dashes( $this->_name ) . '-hidden' ) . '" ';
			$element .= 'name="' . esc_attr( 'simple_paywall_' . $this->_name ) . '" ';
			$element .= 'value="0" ';
			$element .= '>';

			// Checkbox
			$element .= '<input type="checkbox" ';
			$element .= 'id="' . esc_attr( 'simple-paywall-' . $this->underscores_to_dashes( $this->_name ) . '-input' ) . '" ';
			$element .= 'name="' . esc_attr( 'simple_paywall_' . $this->_name ) . '" ';
			$element .= 'class="regular-text"';
			$element .= 'value="1"';
			$element .= ( isset( $this->_is_checked ) && $this->_is_checked === true ) ? 'checked ' : '';
			$element .= isset( $this->_disabled ) ? 'disabled ' : '';
			$element .= 'autocomplete="off" ';
			$element .= '>';

			// Label
			$element .= '<label ';
			$element .= 'for="' . esc_attr( 'simple-paywall-' . $this->underscores_to_dashes( $this->_name ) . '-input' ) . '"';
			$element .= '>';
			$element .= isset( $this->_label ) ? esc_html( $this->_label ) . ' ' : '';
			$element .= '</label>';

			return $element;

		}

		private function input_duration_picker() {}

		private function input_email() {
			$element = '<input type="email" ';
			$element .= 'id="' . esc_attr( 'simple-paywall-' . $this->underscores_to_dashes( $this->_name ) . '-input' ) . '" ';
			$element .= 'name="' . esc_attr( 'simple_paywall_' . $this->_name ) . '" ';
			$element .= 'value="' . esc_attr( $this->_value ) . '" ';
			$element .= 'placeholder="' . esc_attr( $this->_placeholder ) . '" ';
			$element .= 'autocomplete="off" ';
			$element .= isset( $this->_width ) ? 'style="' . esc_attr( 'width:' . $this->_width ) . '" ' : '';
			$element .= isset( $this->_disabled ) ? 'disabled ' : '';
			$element .= '/>';
			return $element;
		}

		private function input_multiselect() {

			$element = '<select ';
			$element .= 'id="' . esc_attr( 'simple-paywall-' . $this->underscores_to_dashes( $this->_name ) ) . '" ';
			$element .= 'multiple="multiple" ';
			$element .= 'name="' . esc_attr( 'simple_paywall_' . $this->_name . '[]' ) . '" ';
			$element .= 'data-placeholder="Select Content" ';
			$element .= 'value="" ';
			$element .= 'autocomplete="off" ';
			$element .= 'style="width: 25em;" ';
			$element .= '>';

			if ( isset( $this->_options ) ) {
				foreach ( $this->_options as $key => $value ) {
					/**
					 * Check if options is a single list or a multidimensional list
					 */
					// This indicates it is a single list since an array reflects a single type or entity
					if ( is_array( $key ) ) {
						/** @todo Build out for single lists */
					}
					/**
					 * This indicates it is a multidimensional array since a string reflects a "type" or "category"
					 * that posts will be found in
					 */
					if ( is_string( $key ) ) {
						$element .= '<optgroup label=' . esc_attr( $key ) . '>';
						foreach ( $value as $item ) {
							$element .= '<option value="' . esc_attr( $item['id'] ) . '"' . ( isset( $this->_value ) ? ( in_array( $item['id'], $this->_value ) ? 'selected' : '' ) : '' ) . '>' . esc_html( $item['title'] ) . '</option>';
						}
						$element .= '</optgroup>';
					}

				}
			}

			$element .= '</select>';

			// Include a hidden element to detect empty multiselect submissions
			$element .= '<input type="hidden"';
			$element .= 'id="' . esc_attr( 'simple-paywall-' . $this->underscores_to_dashes( $this->_name ) . '-hidden' ) . '" ';
			$element .= 'name="' . esc_attr( 'simple_paywall_' . $this->_name ) . '_submitted" ';
			$element .= 'value="1" ';
			$element .= '>';

			return $element;

		}

		private function input_select() {

			$element = '<select ';
			$element .= 'id="' . esc_attr( 'simple-paywall-' . $this->underscores_to_dashes( $this->_name ) . '-input') . '" ';
			$element .= 'name="' . esc_attr( 'simple_paywall_' . $this->_name ) . '" ';
			$element .= 'autocomplete="off" ';
			$element .= isset( $this->_is_disabled ) ? ( $this->_is_disabled ? 'disabled ' : '' ) : '';
			$element .= '>';

			foreach ( $this->_options as $option ) {
				$element .= '<option ';
				$element .= ( isset( $this->_value ) && $this->_value === $option[0] ) ? 'selected ' : '';
				$element .= 'value="' . esc_attr( $option[0] ) . '" ';
				$element .= '>';
				$element .= esc_html( $option[1] );
				$element .= '</option>';
			}

			$element .= '</select>';
			$element .= $this->get_description();

			return $element;

		}

		private function input_select_month() {

			$value = isset( $this->_value ) ? substr( $this->_value, 5, 2 ) : null;

			// Generate twelve months
			$month_options = '<option selected value="' . esc_attr( 0 ) . '">' . 'Month' . '</option>';
			for ( $i = 1; $i <= 12; $i++ ) {
				$month_num = str_pad( $i, 2, 0, STR_PAD_LEFT );
				$month_name = date( 'F', mktime( 0, 0, 0, $i + 1, 0, 0 ) );
				$month_options .= '<option ';
				$month_options .= ( ( isset( $value ) && $value == $month_num ) ? 'selected ' : '' );
				$month_options .= 'value="' . esc_attr( $month_num ) . '"';
				$month_options .= '>';
				$month_options .= __( $month_name );
				$month_options .= '</option>';
			}

			$element = '<select ';
			$element .= 'style="width: 120px" ';
			$element .= 'id="' . esc_attr( 'simple-paywall-' . $this->_name . '-month-input' ) . '" ';
			$element .= 'name="' . esc_attr( 'simple_paywall_' . $this->_name . '_month' ) . '" ';
			$element .= 'autocomplete="off" ';
			$element .= ( isset( $this->_is_disabled ) ? ( $this->_is_disabled ? 'disabled ' : '' ) : '' );
			$element .= '>';
			$element .= $month_options;
			$element .= '</select>';

			return $element;

		}

		private function input_text() {

			$element = '<input type="text" ';
			$element .= 'id="' . esc_attr( 'simple-paywall-' . $this->underscores_to_dashes( $this->_name ) . '-input' ) . '" ';
			$element .= 'class="regular-text" ';
			$element .= 'autocomplete="off" ';
			// $element .= 'placeholder="' . isset( $this->_placeholder ) ? esc_attr( $this->_placeholder . '" ' ) : '" ';
			$element .= 'placeholder="' . ( isset( $this->_placeholder ) ? esc_attr( $this->_placeholder ) : '' ) . '" ';
			$element .= 'name="' . esc_attr( 'simple_paywall_' . $this->_name ) . '" ';
			$element .= 'value="' . esc_attr( $this->_value ) . '" ';
			$element .= isset( $this->_is_disabled ) ? ( $this->_is_disabled ? ' disabled' : '' ) : '';
			$element .= isset( $this->_width ) ? 'style="' . esc_attr( 'width:' . $this->_width ) . '" ' : '';
			$element .= '/>';
			return $element;
		}

		private function input_textarea() {
			$element = '<textarea ';
			$element .= 'id="' . esc_attr( 'simple-paywall-' . $this->underscores_to_dashes( $this->_name ) . '-input' ) . '" ';
			$element .= 'name="' . esc_attr( 'simple_paywall_' . $this->_name ) . '" ';
			$element .= 'cols="30" ';
			$element .= 'rows="5" ';
			$element .= 'placeholder="' . esc_attr( $this->_placeholder ) . '" ';
			$element .= 'autocomplete="off" ';
			$element .= '>';
			$element .= isset( $this->_value ) ? esc_textarea( $this->_value ) : '';
			$element .= '</textarea>';
			return $element;
		}

		private function input_text_day() {

			$day = isset( $this->_value ) ? substr( $this->_value, 8 ) : '';

			$element = '<input ';
			$element .= 'style="width: 50px;" ';
			$element .= 'maxlength="2" ';
			$element .= 'type="text" ';
			$element .= 'value="' . esc_attr( $day ) . '" ';
			$element .= 'id="' . esc_attr( 'simple-paywall-' . $this->_name . '-day-input' ) . '" ';
			$element .= 'name="' . esc_attr( 'simple_paywall_' . $this->_name . '_day' ) . '" ';
			$element .= 'autocomplete="off" ';
			$element .= 'placeholder="DD" ';
			$element .= '>';
			return $element;
		}

		private function input_text_year() {

			$year = isset( $this->_value ) ? substr( $this->_value, 0, 4 ) : '';

			$element = '<input ';
			$element .= 'style="width: 100px;" ';
			$element .= 'maxlength="4" ';
			$element .= 'type="text" ';
			$element .= 'value="' . esc_attr( $year ) . '" ';
			$element .= 'id="' . esc_attr( 'simple-paywall-' . $this->_name . '-year-input' ) . '" ';
			$element .= 'class="regular-text" ';
			$element .= 'name="' . esc_attr( 'simple_paywall_' . $this->_name . '_year' ) . '" ';
			$element .= 'autocomplete="off" ';
			$element .= 'placeholder="YYYY" ';
			$element .= '>';

			return $element;

		}

		private function input_wp_editor() {
			$wp_editor_settings = array(
				'media_buttons' => true,
				'teeny' => false,
				'wpautop' => false
			);
			$wp_editor_id = 'simple_paywall_' . $this->_name;
			// Start output buffer
			ob_start();
			wp_editor( $this->_value, $wp_editor_id, $wp_editor_settings );
			// Store the printed data in $editor variable
			$wp_editor = ob_get_clean();
			return $wp_editor;
		}

		public function is_checked( bool $boolean = true ) {
			$this->_is_checked = $boolean;
			return $this;
		}

		public function is_disabled( bool $boolean = true ) {
			$this->_is_disabled = $boolean;
			return $this;
		}

		public function is_required( bool $boolean = true ) {
			$this->_is_required = $boolean;
			return $this;
		}

		public function label( $text ) {
			$this->_label = $text;
			return $this;
		}

		public function section_header() {
			return '<h2>' . esc_html( $this->_text ) . '</h2>';
		}

		public function text( $text_string ) {
			$this->_text = $text_string;
			return $this;
		}

		public function type( $type_string ) {
			$this->_type = $type_string;
			return $this;
		}

		public function name( $name_string ) {
			// Force convention of using underscores for names and not dashes
			if ( strpos( $name_string, '-' ) !== false ) {
				throw new InvalidArgumentException( '$name_string should only use underscores in its naming.' );
			}
			$this->_name = $name_string;
			return $this;
		}

		public function options( $options_array ) {
			if ( ! is_array( $options_array ) ) {
				throw new InvalidArgumentException();
			}
			$this->_options = $options_array;
			return $this;
		}

		public function placeholder( $placeholder_string ) {
			$this->_placeholder = $placeholder_string;
			return $this;
		}

		private function submit_button() {
			if ( isset( $this->_style ) ) {
				switch ( $this->_style ) {
					case '':
						break;
					default:
						break;
				}
			} else {
				$class = 'button button-primary';
			}
			$element = '<p class="submit">';
			$element .= '<input ';
			$element .= 'type="submit" ';
			$element .= 'id="' . esc_attr( 'simple-paywall-' . $this->_name ) . '" ';
			$element .= 'class="' . esc_attr( 'button button-primary' ) . '" ';
			$element .= 'value="' . esc_attr( $this->_text ) . '" ';
			$element .= $this->_is_disabled ? 'disabled ' : '';
			$element .= '/>';
			$element .= '</p>';
			return $element;
		}

		private function message() {
			if ( ! isset( $this->_type ) ) {
				throw new Exception( '$this->_text is not set' );
			}
			return '<p>' . esc_html( $this->_text ) . '</p>';
		}

		private function underscores_to_dashes( string $string ) {
			$string = str_replace( '_', '-', $string );
			return $string;
		}

		public function value( $value ) {
			$this->_value = $value;
			return $this;
		}

		/**
		 * Set the width of the field
		 * @param string $width Small, medium, large
		 * @return void
		 */
		public function width( string $width ) {
			$this->_width = $width;
			return $this;
		}

	}
}
