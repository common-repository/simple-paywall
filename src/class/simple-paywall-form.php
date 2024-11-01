<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

if ( ! class_exists( 'Simple_Paywall_Form' ) ) {
	class Simple_Paywall_Form {
		private
				/**
				 * The string containing the form output
				 */
				$_form,
				$_method = 'post',
				$_id,
				$_debug_mode = false;

		public function __construct( $id ) {
			$this->_id = $id;
			$this->_form = '<form class="simple-paywall" id="simple-paywall-' . $this->_id . '" method=' . $this->_method . '>';
		}

		/**
		 * Add a specified group of fields to form
		 * @param  [type] $fields [description]
		 * @return [type]         [description]
		 */
		public function fields( $fields ) {

			// Start table
			$this->_form .= '<table class="form-table">';
			$this->_form .= '<tbody>';

			foreach ( $fields as $field ) {

				/**
				 * Add special exceptions for certain types
				 */
				if ( $field['type'] === 'hidden' ) {
					$this->_form .= $this->hidden_input( $field['name'], $field['value'] );
					continue; // Skip the rest of the current loop iteration and continue execution at the condition evaluation and then the beginning of the next iteration
				}

				if ( $field['type'] === 'multiselect' ) {
					$this->_form .= $this->multiselect_input( $field );
					continue;
				}

				if ( $field['type'] === 'selecttwo' ) {
					$this->_form .= $this->select_two_input( $field );
					continue;
				}

				/**
				 * The "basic" types
				 */
				// Start table row
				$this->_form .= '<tr id="simple-paywall-' . $field['name'] . '">';
				$this->_form .= '<th scope="row">';

				if ( $field['type'] !== 'checkbox' ) {
					$this->_form .= $this->label( $field['label'], $field['name'], ( isset( $field['required'] ) ? $field['required'] : null ) );
				}

				// Start table column
				$this->_form .= '<td>';

				// Cases organized alphabetically
				switch ( $field['type'] ) {
					// If checkbox field
					case 'checkbox':
						$this->_form .= $this->checkbox_input( $field['name'], isset( $field['value'] ) ? $field['value'] : 0, isset( $field['disabled'] ) ? $field['disabled'] : false );
						break;
					// If code, a special type
					case 'code':
						$this->_form .= $this->code_element( $field['value'] );
						break;
					// If email field
					case 'email':
						$this->_form .=	$this->email_input(
							$field['name'],
							$field['label'],
							isset( $field['value'] ) ? $field['value'] : $this->autofill( $field['name'] ),
							isset( $field['placeholder'] ) ? $field['placeholder'] : null,
							isset( $field['disabled'] ) ? $field['disabled'] : null
						);
						break;
					// If select field
					case 'select':
						$args = [
							'name' => $field['name'],
							'label' => $field['label'],
							'options' => $field['options'],
							'default_value' => isset( $field['default_value'] ) ? $field['default_value'] : null,
							'required' => $field['required'],
							'description' => isset( $field['description'] ) ? $field['description'] : null,
							'disabled' => isset( $field['disabled'] ) ? $field['disabled'] : false
						];
						$this->_form .= $this->select_input( $args );
						break;
					// If text field
					case 'text':
						$args = [
							'name' => $field['name'],
							'label' => $field['label'],
							'value' => isset( $field['value'] ) ? $field['value'] : $this->autofill( $field['name'] ),
							'placeholder' => isset( $field['placeholder'] ) ? $field['placeholder'] : null,
							'disabled' => isset( $field['disabled'] ) ? $field['disabled'] : null,
							'width' => isset( $field['width'] ) ? $field['width'] : null
						];
						$this->_form .=	$this->text_input( $args );
						break;
					// If textarea field
					case 'textarea':
						$this->_form .=	$this->textarea_input(
							$field['name'],
							isset( $field['value'] ) ? $field['value'] : $this->autofill( $field['name'] ),
							isset( $field['placeholder'] ) ? $field['placeholder'] : null,
							isset( $field['disabled'] ) ? $field['disabled'] : null
						);
						break;
				}

				if ( $field['type'] === 'checkbox' ) {
					$this->_form .= $this->label( $field['label'], $field['name'], isset( $field['required'] ) ? $field['required'] : null );
				}

				if ( isset( $field['description'] ) ) {
					$this->_form .= '<p class="description">' . $field['description'] . '</p>';
				}

				/**
				 * @todo
				 * Where is the </th>?
				 */

				// End column
				$this->_form .= '</td>';
				// End table row
				$this->_form .= '</tr>';

			}

			$this->_form .= '</tbody>';
			// End table
			$this->_form .= '</table>';

		}

		public function section_title( $title, $description = null ) {
			$this->_form .= '<h2>' . $title . '</h2>';
			if ( isset( $description ) ) {
				$this->_form .= '<p>' . $description . '</p>';
			}
		}

		public function section_description( $description ) {
			$this->_form .= '<p>' . $description . '</p>';
		}

		public function label( $label, $name, $required = false ) {
			return '<label for="simple-paywall-' . $name . '-input">' . $label . ( $required ? '*' : '' ) . '</label>';
		}

		public function hidden_input( $name, $value ) {
			return '<input type="hidden" name="simple-paywall-' . $name . '" value="true" />';
		}

		private function text_input( $args ) {
			if ( isset( $args['width'] ) ) {
				if ( $args['width'] === 'small' ) {
					$width = 'style="width: 3.5em"';
				}
			}
			return '<input type="text" id="simple-paywall-' . $args['name'] . '-input" class="regular-text" name="simple-paywall-' . $args['name'] . '" value="' . $args['value'] .'" autocomplete="off" placeholder="' . ( $args['placeholder'] ? $args['placeholder'] : '' ) .'" ' . ( isset( $width ) ? $width . ' ' : '' ) . ( $args['disabled'] ? 'disabled' : '' ) . '/>';
		}

		private function email_input( $name, $label, $value, $placeholder = null, $disabled = null ) {
			return '<input type="email" id="simple-paywall-' . $name . '" class="regular-text" name="simple-paywall-' . $name . '" value="' . $value .'" placeholder="' . ( $placeholder ? $placeholder : '' ) .'" ' . ( $disabled ? 'disabled ' : '' ) . '/>';
		}

		private function checkbox_input( $name, $value = 0, $disabled = false ) {
			// Set value if not checked
			$checkbox_element = '<input name="simple-paywall-' . $name . '" id="simple-paywall-' . $name . '-hidden" type="hidden" value="0">';
			// Set value if checked
			$checkbox_element .= '<input type="checkbox" name="simple-paywall-' . $name . '" id="simple-paywall-' . $name . '-input" class="regular-text" value="1"' . ( $value == 1 ? ' checked' : '' ) . ( $disabled ? ' disabled' : '' ) . ' />';
			return $checkbox_element;
		}

		private function textarea_input( $name, $value, $placeholder = null, $disabled = null ) {
			return '<textarea id="simple-paywall-' . $name . '-input" name="simple-paywall-' . $name . '" cols="30" rows="5" placeholder="' . $placeholder . '">'. $value .'</textarea>';
		}

		private function code_element( $value ) {
			return '<div style="margin-top: .5em;"><code>' . $value . '</code></div>';
		}

		/**
		 * Builds the select input
		 * @param array $args Array containing necessary params (see below)
		 * $args = [
		 * 		'name' => string The value for the name attribute for this input
		 * 		'label' => string The label for this input
		 * 		'options' => [
		 * 			[
		 * 				$value, string The value of this option
		 * 				$text string The text to display for this option
		 * 			],
		 * 			[
		 * 				$value, string The value of this option
		 * 				$text string The text to display for this option
		 * 			]
		 * 		],
		 * 		'default_selected' => string The option value that is selected by default
		 * ];
		 * @return string The html that is added to $this->_form
		 */
		private function select_input( $args ) {
			$selectInput = '<select id="simple-paywall-' . $args['name'] . '" name="simple-paywall-' . $args['name'] . '" autocomplete="off"' . ( isset( $args['disabled'] ) ? ( $args['disabled'] ? ' disabled' : '' ) : '' ) . '>';
			foreach ( $args['options'] as $option ) {
				$selectInput .= '<option ' . ( ( isset( $args['default_value'] ) && $args['default_value'] === $option[0] ) ? 'selected ' : '' ) . 'value="' . $option[0] . '">' . $option[1] . '</option>';
			}
			$selectInput .= '</select>';
			return $selectInput;
		}

		private function select_two_input( $args ) {

			// Start table row
			$select_two_input = '<tr id="simple-paywall-' . $args['field_one']['name'] . '">';
			$select_two_input .= '<th scope="row">';
			$select_two_input .= $this->label( $args['field_one']['label'], $args['required'] );;
			$select_two_input .= '</th>';

			// Start table column
			$select_two_input .= '<td>';

			$select_two_input .= '<div class="select-two">';
			$select_two_input .= $this->select_input( $args['field_one'] );
			$select_two_input .= $this->select_input( $args['field_two'] );
			$select_two_input .= isset( $args['description'] ) ? '<p class="description">' . $args['description'] .'</p>' : '';
			$select_two_input .= '</div>';

			// End column
			$select_two_input .= '</td>';
			// End table row
			$select_two_input .= '</tr>';

			return $select_two_input;

		}

		private function multiselect_input( $args ) {

			// var_dump( $args ); die(); // Test

			$multiselectInput = '<select id="simple-paywall-' . $args['name'] . '" multiple="multiple" name="simple-paywall-' . $args['name'] . '[]" data-placeholder="Select Content" value="" autocomplete="off" style="width: 25em;">';

			if ( isset( $args['options'] ) ) {
				foreach ( $args['options'] as $key => $value ) {

					/**
					 * Check if options is a single list of a multidimensional list
					 */

					// This indicates it is a single list since an array reflects a single type or entity
					if ( is_array( $key ) ) {
						/**
						 * @todo
						 * Build out for single lists
						 */
					}

					/**
					 * This indicates it is a multidimensional array since a string reflects a "type" or "category"
					 * that posts will be found in
					 */
					if ( is_string( $key ) ) {
						$multiselectInput .= '<optgroup label=' . $key . '>';
						foreach ( $value as $item ) {
							$multiselectInput .= '<option value="' . $item['id'] . '"' . ( isset( $args['values'] ) ? ( in_array( $item['id'], $args['values'] ) ? 'selected' : '' ) : '' ) . '>' . $item['title'] . '</option>';
						}
						$multiselectInput .= '</optgroup>';
					}

				}
			}

			// Keep for reference... just for now...

			// foreach ( $args['options'] as $option ) {
			// 	$selectInput .= '<option ' . ( ( isset( $args['default_value'] ) && $args['default_value'] === $option[0] ) ? 'selected ' : '' ) . 'value="' . $option[0] . '">' . $option[1] . '</option>';
			// }

			$multiselectInput .= '</select>';
			return $multiselectInput;

		}

		/**
		 * Form Submit Button
		 * @see https://developer.wordpress.org/reference/functions/submit_button/
		 */
		public function submit_button( $id, $class, $value ) {
			$this->_form .= '<p class="submit">';
			$this->_form .= '<input id="simple-paywall-' . $id . '" class="' . $class . '" value="' . $value . '" type="submit">';
			$this->_form .= '</p>';
			// if ( ! Simple_Paywall_Config::getInstance()->api_keys_is_set() ) :
			// 	$custom_attributes[ 'disabled' ] = 'disabled';
			// endif;
			// submit_button( $button_text, 'primary large', '', true, $custom_attributes );
		}

		/**
		 * Inserts horizontal rule
		 */
		public function horizontal_rule() {
			$this->_form .= '<hr class="simple-paywall" style="margin: 2em 0;">';
		}

		/**
		 * Append the final tags and output the $form variable
		 * @return string $form
		 */
		// public function return() {
		// 	$this->_form .= '</form>';
		// 	return $this->_form;
		// }

		/**
		 * Append the final tags and output the $form variable
		 * @return string $form
		 */
		public function display() {
			$this->_form .= '</form>';
			echo $this->_form;
		}

		/**
		 * Autofill forms by field name to automate testing and debugging
		 * @return $value The test value to put into the field
		 */
		private function autofill( $name ) {
			if ( Simple_Paywall_Config::getInstance()->is_debug_mode() && $this->_debug_mode ) {
				switch ( $name ) {
					case 'First Name':
						$value = 'John';
						break;
					case 'Last Name':
						$value = 'Doe';
						break;
					case 'Email':
						$value = 'john.doe@simplepaywall.com';
						break;
					case 'Birthday':
						$value = '01/01/1980';
						break;
					case 'Street Address':
						$value = '101 Paywall Ave';
						break;
					case 'City':
						$value = 'Smallville';
						break;
					case 'State/Province/Region':
						$value = 'Indiana';
						break;
					case 'ZIP/Postal Code':
						$value = '10001';
						break;
					case 'Country':
						$value = 'United States';
						break;
					case 'paywall-name':
						$value = 'Sample Metered Paywall';
						break;
					case 'paywall-limit-count':
						$value = '99';
						break;
					case 'paywall-type':
						$value = 'hard';
						break;
					case 'paywall-description':
						$value = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Incidunt quo debitis deserunt error deleniti fugiat qui, veritatis, dolores iste aliquam voluptas.';
						break;
					default:
						$value = '';
				}
				return $value;
			}
		}

	}
}
