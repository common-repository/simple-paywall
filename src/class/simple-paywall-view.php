<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

if ( ! class_exists( 'Simple_Paywall_View' ) ) {
	class Simple_Paywall_View {

		private static $_instance = null;

		public function __construct() {}

		public static function get_instance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}

		public function header() { ?>
			<div class="simple-paywall-header">
				<div class="simple-paywall-logo">
					<img src="<?php echo esc_url( SIMPLE_PAYWALL_URL . '/public/images/simple-paywall-logo.png' ); ?>" alt="Simple Paywall Logo">
				</div>
			</div>
		<?php
		}

		public function get_header() { ?>
			<div class="simple-paywall-header">
				<div class="simple-paywall-logo">
					<img src="<?php echo esc_url( SIMPLE_PAYWALL_URL . '/public/images/simple-paywall-logo.png' ); ?>" alt="Simple Paywall Logo">
				</div>
			</div>
		<?php
		}

		/**
		 * Render and display "Visitors" page
		 */
		public function render_visitors_page() { ?>
			<?php $this->header() ?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<h1>Visitors</h1>
					<p><i class="fa fa-info-circle" aria-hidden="true"></i>These are website <code>visitors</code> that are consuming your content but are not actively signed in or have yet to register as a user.</p>
				</div>
			</div>
			<div class="simple-paywall-container">
				<div class="wrap">
					<?php $table = new Simple_Paywall_Visitors_Table(); ?>
					<form method="post">
						<?php
						$table->views();
						$table->prepare_items();
						// $table->search_box( 'Search', 'search' );
						$table->display();
						?>
					</form>
				</div>
			</div>
		<?php
		}

		/**
		 * Render content for "Settings" page
		 */
		public function render_settings_page() { ?>
			<?php $this->header(); ?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<h1>Settings</h1>
						<?php
						$tabs = new Simple_Paywall_Tabs( array(
							'API'
						) );
						$tabs->display();
						?>
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

		<?php
		}

	}
}
