<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

if ( ! class_exists( 'Simple_Paywall_Settings_View' ) ) {
	class Simple_Paywall_Settings_View {

		private static $_instance = null;

		public function __construct() {}

		public static function get_instance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}

		/**
		 * Render content for "Settings" page
		 */
		public function page_settings() { ?>
			<?php Simple_Paywall_View::get_instance()->get_header(); ?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<h1>Settings</h1>
				</div>
			</div>
			<?php
			$tabs_array = array( 'API' );
			if ( Simple_Paywall_Config::get_instance()->get_activated() ) {
				$tabs_array = array_merge( $tabs_array, array(
					// 'General',
					// 'Legal'
				) );
			}
			$tabs = new Simple_Paywall_Tabs( $tabs_array );
			$tabs->display();
			// Set content for tab
			switch ( $tabs->get_active_tab() ) {
				case '':
					$this->tab_api();
					break;
				case 'api':
					$this->tab_api();
					break;
				case 'general':
					$this->tab_general();
					break;
				case 'legal':
					$this->tab_legal();
					break;
				default:
					$this->tab_api();
					break;
			}
			?>
		<?php
		}

		/**
		 * Content for the API tab
		 * @return void
		 */
		private function tab_api() { ?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<form id="api-keys">
						<h3>Simple Paywall API Keys</h3>
						<input type="hidden" name="simple_paywall_api_keys_updated" value="true" />
						<?php wp_nonce_field( 'simple_paywall_api_keys_update', 'simple_paywall_api_keys_update_wpnonce' ); ?>
						<?php
						if ( isset( $_POST['simple_paywall_api_keys_updated'] ) && sanitize_text_field( $_POST['simple_paywall_api_keys_updated'] ) === 'true' ) {
							$this->update_simple_paywall_api_keys();
						}
						?>
						<table class="form-table api-keys">
							<p>This plugin requires you have an account with <a href="https://simplepaywall.com/" target="_blank">Simple Paywall</a>.<br />You can <a href="https://simplepaywall.com/sign-up/" target="_blank">Sign Up</a> if you don't have one.</p>
							<tr valign="top">
								<th scope="row">Public Key*</th>
								<td>
									<input type="text" id="simple_paywall_public_api_key" name="simple_paywall_public_api_key" value="<?php echo esc_attr( ( Simple_Paywall_Config::getInstance()->api_keys_is_set() ) ? Simple_Paywall_Config::getInstance()->get_public_api_key() : '' ); ?>" <?php echo Simple_Paywall_Config::getInstance()->api_keys_is_set() ? 'disabled' : '' ; ?>/>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">Private Key*</th>
								<td><input type="text" id="simple_paywall_private_api_key" name="simple_paywall_private_api_key" value="<?php echo esc_attr( ( Simple_Paywall_Config::getInstance()->api_keys_is_set() ) ? Simple_Paywall_Config::getInstance()->get_private_api_key() : '' ); ?>" <?php echo Simple_Paywall_Config::getInstance()->api_keys_is_set() ? 'disabled' : '' ; ?>/>
								</td>
							</tr>
						</table>
							<div style="<?php echo esc_attr( ( Simple_Paywall_Config::getInstance()->api_keys_is_set() ) ? '' : 'display: none;' ); ?>" class="simple-paywall-active">
								<div id="simple-paywall-active-message" class="simple-paywall-success-message">
									<i>This plugin is activated with Simple Paywall. Simple Paywall is currently active.</i>
								</div>
								<p>*Deactivating this plugin will not delete or permanently remove any data.</p>
							</div>
							<div style="<?php echo esc_attr( ( Simple_Paywall_Config::getInstance()->api_keys_is_set() ) ? 'display: none;' : '' ); ?>" class="simple-paywall-inactive">
								<div id="simple-paywall-inactive-message" class="simple-paywall-error-message">
									<i>This plugin is not activated with Simple Paywall.</i>
									<p>Please input your API keys from your Simple Paywall account to get started.</p>
								</div>
								<p>Don't have them on hand? You can <a href="https://app.simplepaywall.com/" target="_blank">Sign In</a> to your Simple Paywall account and find them under <a href="https://app.simplepaywall.com/settings/">Settings</a>.</p>
								<p id="simple-paywall-key-in-use-message" style="display: none;">It seems this key is already in use. If this Simple Paywall for WordPress installation is intended as a development or staging site, be sure to add your projects URL to the list of domains authorized for read-only access in your Simple Paywall Account.</p>
							</div>
						<?php
						/**
						 * Form Submit Button
						 * @see https://developer.wordpress.org/reference/functions/submit_button/
						 */
						$button_text = Simple_Paywall_Config::getInstance()->api_keys_is_set() ? 'Deactivate' : 'Activate' ;
						$custom_attributes = array( 'id' => 'toggle-simple-paywall-activation' );
						submit_button( $button_text, 'large', '', true, $custom_attributes );
						?>
					</form>
					<div id="saveResult"></div>
				</div>
			</div>
			<?php	}

		private function tab_general() { ?>
			<?php
			// Check if form has submitted
			if ( isset( $_POST['simple_paywall_update_settings'] ) && $_POST['simple_paywall_update_settings'] === 'true' ) {
				// Enabled post types
				$enabled_post_types = array();
				foreach ( $_POST as $key => $value ) {
					if ( strpos( $key, 'simple_paywall_enabled_post_type_' ) !== false ) {
						if ( (int) sanitize_text_field( $_POST[$key] ) === 1 ) {
							$post_type = str_replace( 'simple_paywall_enabled_post_type_', '', $key );
							array_push( $enabled_post_types, $post_type );
						}
					}
				}
				// var_dump($enabled_post_types);die();
				Simple_Paywall_Setting_Local::get_instance()->update( 'enabled_post_types', $enabled_post_types );
			}
			?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<?php

					// New form
					$form = new Simple_Paywall_Form_v2;
					$form->id( 'update-settings' );

					// Header
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'section_header' )
						->name( '' )
						->text( 'Enabled Post Types' )
						->description( 'The post types that Simple Paywall is enabled for. Simple Paywall is enabled for both Posts and Pages by default.' );
					$form->add_section( $element );

					// Get enabled post types
					$enabled_post_types = Simple_Paywall_Setting_Local::get_instance()->get( 'enabled_post_types' );

					// Get and include checkboxes for all post types
					$post_types = get_post_types( array( 'public' => true ), 'objects' );
					foreach ( $post_types as $post_type ) {
						if ( $post_type->label !== 'Media' ) {
							$element = new Simple_Paywall_Form_Element;
							$is_checked = isset( $enabled_post_types ) ? in_array( $post_type->name, $enabled_post_types ) : false;
							$element->type( 'checkbox' )
								->name( 'enabled_post_type_' . $post_type->name )
								->label( ' Enable Simple Paywall for ' . $post_type->label . '' )
								->text( $post_type->label )
								->is_checked( $is_checked );
							$form->add_element( $element );
						}
					}

					// Error
					// $form->error_message( 'There was an issue updating the enabled post types. Please see below for more details.' );
					// Success
					// $form->success_message( 'Successfully updated enabled post types.' );
					// Submit
					$form->submit( 'Update Settings' );
					// Display
					$form->display();

					?>
				</div>
			</div>
		<?php }

		private function tab_legal() { ?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<?php

					// New form
					$form = new Simple_Paywall_Form_v2;
					$form->id( 'update-legal' );

					// Header
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'section_header' )
						->name( '' )
						->text( 'Your Terms & Privacy Policy*' )
						->description( 'Please provide the name for your Terms and a link to the web page containing your Terms and Privacy Policy below. These will show on user Checkout and Sign Up process.' );
					$form->add_section( $element );

					// Terms Name
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'text' )
						->name( 'name_of_terms' )
						->text( 'Name of Terms' )
						->label( 'Name of Terms' )
						->placeholder( 'Terms of Service' )
						->is_required()
						->description( 'What do you call your Terms? <br />e.g., "Terms and Conditions", "Terms of Service", "Terms of Use"' );
					$form->add_element( $element );

					// Terms URL
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'text' )
						->name( 'terms_url' )
						->label( 'URL to Terms' )
						->placeholder( 'https://domain.com/terms-of-service/' )
						->description( 'Provide the full URL to where your users can see your Terms. <br />e.g., "https://yourdomain.com/terms-of-service/"' )
						->is_required();
					$form->add_element( $element );

					// Privacy Policy URL
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'text' )
						->name( 'privacy_policy_url' )
						->label( 'URL to Privacy Policy' )
						->placeholder( 'https://domain.com/privacy-policy/' )
						->description( 'Provide the full URL to where your users can see your Terms. <br />e.g., "https://domain.com/terms/privacy-policy/"' )
						->is_required();
					$form->add_element( $element );

					// Error
					$form->error_message( 'There was an issue updating the settings.' );
					// Success
					$form->success_message( 'Successfully updated settings.' );
					// Submit
					$form->submit( 'Update Settings' );
					// Display
					$form->display();
					?>
				</div>
				<div class="wrap">
					<p><strong>*Note:</strong><br>Simple Paywall also requires that all users agree to its <a href="https://simplepaywall.com/terms-of-use/" target="_blank">Terms of Use</a> and <a href="https://simplepaywall.com/privacy-policy/">Privacy Policy</a>. For users that have been imported into Simple Paywall, they will be prompted with a "Terms of Use" and "Privacy Policy" update notice and are required to select "Agree" prior to using services.</p>
				</div>
			</div>
		<?php }

	}
}
