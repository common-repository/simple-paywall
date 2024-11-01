<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

if ( ! class_exists( 'Simple_Paywall_Users_View' ) ) {
	class Simple_Paywall_Users_View {

		private static $_instance = null;

		public function __construct() {}

		public static function get_instance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		private function account_history_tab() { ?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<p><?php _e( 'There is no user account history data to show here at the moment. Please check back later.' ); ?></p>
				</div>
			</div>
		<?php }

		private function activity_tab() { ?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<p><?php _e( 'There is no user activity data to show here at the moment. Please check back later.' ); ?></p>
				</div>
			</div>
		<?php }

		/**
		 * Render content for "Add User" page
		 * @return null
		 */
		public function render_add_user_page() { ?>
			<?php Simple_Paywall_View::get_instance()->get_header(); ?>
			<?php
			// Check if form has submitted
			if ( isset( $_POST['simple_paywall_add_user'] ) && sanitize_text_field( $_POST['simple_paywall_add_user'] ) === 'true' ) {
				Simple_Paywall_User::get_instance()->create();
			}
			?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<h1>Add New User</h1>
					<p>Manually add a new user. <br>You can add the user to a product or plan after they have been created.</p>
					<!-- <p>A user will still need to complete the checkout process on their own if payment is required for the product/plan they are added to before they are able to access it.</p> -->
					<!-- <p><i class="fas fa-info-circle"></i>Have more than a few to add? Try using our <a href="javascript:void(0);">bulk import</a> tool.</p> -->
					<?php
					/**
					 * Using css #createuser in order to use WordPress 'Add User' form styles
					 */
					?>
				</div>
			</div>
			<hr class="simple-paywall">
			<?php
			/**
			 * Testing the new Form class
			 */
			?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<?php
					$add_user_form = new Simple_Paywall_Form_v2;
					$add_user_form->id( 'add-user' );

					// Section header
					$address_section_header = new Simple_Paywall_Form_Element;
					$address_section_header->type( 'section_header' )
						->text( 'Personal Information' );
					$add_user_form->add_section( $address_section_header );

					// First name
					$first_name = new Simple_Paywall_Form_Element;
					$first_name->type( 'text' )
						->label( 'First Name' )
						->name( 'first_name' )
						->width( '200px' )
						->is_required();
					$add_user_form->add_element( $first_name );

					// Last name
					$last_name = new Simple_Paywall_Form_Element;
					$last_name->type( 'text' )
						->label( 'Last Name' )
						->name( 'last_name' )
						->width( '200px' )
						->is_required();
					$add_user_form->add_element( $last_name );

					// Email
					$email = new Simple_Paywall_Form_Element;
					$email->type( 'email' )
						->label( 'Email' )
						->name( 'email' )
						->is_required();
					$add_user_form->add_element( $email );

					// Birthday
					$birthday = new Simple_Paywall_Form_Element;
					$birthday->type( 'date' )
						->label( 'Birthday' )
						->name( 'birthday' )
						->description( 'User\'s must be at least 13 years of age in order to comply with <a href="https://en.wikipedia.org/wiki/Children%27s_Online_Privacy_Protection_Act" target="_blank">Children\'s Online Privacy Protection Act (COPPA)</a>.' );
					$add_user_form->add_element( $birthday );

					/**
					 * @todo Get this working
					 */

					// Do not have birthday
					// $no_birthday = new Simple_Paywall_Form_Element;
					// $no_birthday->type( 'checkbox' )
					// 	->label( 'I do not have this user\'s birthday. Require this user to provide when they verify their account by email.' )
					// 	->name( 'no_birthday' )
					// 	->description( 'We must verify this user is at least 13 years of age in order to comply with Children\'s Online Privacy Protection Act. <br><i class="fas fa-info-circle" style="margin-right: .5em;"></i><i>Age restrictions can be adjusted in your <a href="https://app.simplepaywall.com/" target="_blank">Simple Paywall</a> account.</i>' );
					// $add_user_form->add_element( $no_birthday );

					/**
					 * Address section
					 */

					// Section header
					$address_section_header = new Simple_Paywall_Form_Element;
					$address_section_header->type( 'section_header' )
						->text( 'Address Information' )
						->description( 'Required for users that receive anything tangible as part of their subscription(s) or product purchases.' );
					$add_user_form->add_section( $address_section_header );

					// Street address - line 1
					$street_address = new Simple_Paywall_Form_Element;
					$street_address->type( 'text' )
						->label( 'Street Address' )
						->name( 'street_address_line_one' )
						->placeholder( 'Line 1' );
					$add_user_form->add_element( $street_address );

					// Street address - line 2
					$street_address_2 = new Simple_Paywall_Form_Element;
					$street_address_2->type( 'text' )
						->name( 'street_address_line_two' )
						->placeholder( 'Line 2' );
					$add_user_form->add_element( $street_address_2 );

					// City
					$city = new Simple_Paywall_Form_Element;
					$city->type( 'text' )
						->name( 'city' )
						->label( 'City' );
					$add_user_form->add_element( $city );

					// State/Province/Region
					$region = new Simple_Paywall_Form_Element;
					$region->type( 'text' )
						->name( 'region' )
						->label( 'State/Province/Region' );
					$add_user_form->add_element( $region );

					// ZIP/Postal Code
					$postal_code = new Simple_Paywall_Form_Element;
					$postal_code->type( 'text' )
						->name( 'postal_code' )
						->label( 'ZIP/Postal Code' );
					$add_user_form->add_element( $postal_code );

					// Country
					$country = new Simple_Paywall_Form_Element;
					$country->type( 'text' )
						->name( 'country' )
						->label( 'Country' );
					$add_user_form->add_element( $country );

					/**
					 * Notifications section
					 */

					// Section header
					$notification_section_header = new Simple_Paywall_Form_Element;
					$notification_section_header->type( 'section_header' )
						->text( 'Notifications' )
						->description( 'Set how you want to notify the user in adding them.' );
					$add_user_form->add_section( $notification_section_header );

					// Notify user of new account by email (recommended)
					$notify_user = new Simple_Paywall_Form_Element;
					$notify_user->type( 'checkbox' )
						->text( 'New Account Email' )
						->label( 'Send email to new user notifying them of new account' )
						->name( 'notify_user' )
						->is_checked()
						->is_disabled();
					$add_user_form->add_element( $notify_user );

					// Require user to verify their account by email
					$verify_account = new Simple_Paywall_Form_Element;
					$verify_account->type( 'checkbox' )
						->text( 'Verify Account' )
						->label( 'Require user to verify account (via email) to activate user account' )
						->name( 'verify_account' );
					$add_user_form->add_element( $verify_account );

					$add_user_form->error_message( 'A new user was not added. Please see details below for more information.' );
					$add_user_form->success_message( 'A new user was successfully added' );

					$add_user_form->submit( 'Add New User' );
					$add_user_form->display();
					?>
				</div>
			</div>
			<script>
				jQuery.noConflict();
					( function( $ ) {
						// Handle conditionals
						$( function() {
							$( '#simple-paywall-user-age-verification' ).click( function() {
								$( '#simple-paywall-user-require-age-verification' ).removeAttr( 'checked' );
								$( '#simple-paywall-user-require-age-verification' ).attr( 'value', '0' );
								$( '#simple-paywall-user-require-account-verification' ).removeAttr( 'disabled' );
								$( 'simple-paywall-message-required-for-age-verification' ).css( 'display', 'none' );
							} );
							$( '#simple-paywall-user-require-age-verification' ).click( function() {
								$( '#simple-paywall-user-age-verification' ).removeAttr( 'checked' );
								$( '#simple-paywall-user-age-verification' ).attr( 'value', '0' );
								$( '#simple-paywall-user-require-account-verification' ).attr( 'disabled', '' );
								$( 'simple-paywall-message-required-for-age-verification' ).css( 'display', 'inline' );
							} );
						} );

						// Handle multiple select w/ Chosen
						$( function() {
							// $('#simple-paywall-user-subscriptions').chosen({disable_search_threshold: 10});
							$('#simple-paywall-user-subscriptions').chosen(
								{
									no_results_text: "Didn't find any subscriptions by that name:"
								}
							);
						} );

				} )( jQuery );
			</script>
		<?php }

		/**
		 * Render and display content for simple-paywall-user page
		 * @return null
		 */
		public function edit() {

			$user_id = $_GET['user'];
			Simple_Paywall_User::get_instance()->set_id( $user_id );

			/**
			 * @todo Check if any form was submitted
			 * See if you can work out a "one size fits all" approach
			 */

			// Update user
			if ( isset( $_POST['simple_paywall_update_user'] ) && sanitize_text_field( $_POST['simple_paywall_update_user'] ) === 'true' ) {
				Simple_Paywall_User::get_instance()->update();
			}

			// Delete user
			if ( isset( $_POST['simple-paywall-delete_user'] ) && sanitize_text_field( $_POST['simple-paywall-delete_user'] ) === 'true' ) {
				Simple_Paywall_User::get_instance()->delete( $_GET['user'] );
			}
			$user = Simple_Paywall_User::get_instance()->get();

			Simple_Paywall_View::get_instance()->header(); ?>

			<div class="simple-paywall-container">
				<div class="wrap">
					<h1>User</h1>
					<p><?php echo esc_html__( $user->first_name . ' ' .  $user->last_name ); ?> <code><a href="<?php echo esc_url( 'mailto:' . $user->email ); ?>"><?php esc_html_e( $user->email ); ?></a></code></p>
					<p>ID <code><?php echo isset( $user->id ) ? $user->id : ''; ?></code></p>
				</div>
			</div>
			<?php
			$tabs = new Simple_Paywall_Tabs( array(
				'Profile',
				'Activity',
				'Manage Account',
				'Account History'
			) );
			$tabs->display();
			// Set content for tab
			switch ( $tabs->get_active_tab() ) {
				case 'account':
					$this->profile_tab();
					break;
				case 'account-history':
					$this->account_history_tab();
					break;
				case 'activity':
					$this->activity_tab();
					break;
				case 'manage-account':
					$this->manage_account_tab();
					break;
				case 'profile':
					$this->profile_tab();
					break;
				default:
					die( 'Invalid value for tabs url parameter.');
					break;
			}
		}

		private function manage_account_tab() { ?>
			<?php $user = Simple_Paywall_User::get_instance()->get_data(); ?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<?php
					// Update email
					$update_email = new Simple_Paywall_Form_v2;
					$update_email->id( 'update-user' );

					// Section header
					$header = new Simple_Paywall_Form_Element;
					$header->type( 'section_header' )
						->text( 'Change Email Address' )
						->description( 'Any email address change is required to be verified by email before the change can take effect.' );
					$update_email->add_section( $header );

					// Current email
					$email = new Simple_Paywall_Form_Element;
					$email->type( 'email' )
						->name( 'email' )
						->label( 'Current Email' )
						->value( $user->email )
						->is_disabled();
					$update_email->add_element( $email );

					// New email
					$newEmail = new Simple_Paywall_Form_Element;
					$newEmail->type( 'email' )
						->name( 'new_email' )
						->label( 'New Email' )
						->is_required();
					$update_email->add_element( $newEmail );

					$update_email->submit( 'Update Email Address' );
					$update_email->display();
					?>
				</div>
			</div>
			<hr>
			<?php
			/*
			<div class="simple-paywall-container">
				<div class="wrap">
					<?php
					// Disable/enable user account
					$delete_user = new Simple_Paywall_Form_v2;
					$delete_user->id( 'enable-disable-user' );
					$header = new Simple_Paywall_Form_Element;
					$header->type( 'section_header' )
						->text( 'Enable/Disable User Account' )
						->description( 'Are you sure you want to do this? <b><u>This is permanent and irreversible.</u> It cannot be undone!</b>' );
					$delete_user->add_section( $header );
					$delete_user->submit( 'Disable User Account' );
					$delete_user->display();
					?>
				</div>
			</div>
			*/
			?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<?php
					// Delete user
					$delete_user = new Simple_Paywall_Form_v2;
					$delete_user->id( 'delete-user' );
					$header = new Simple_Paywall_Form_Element;
					$header->type( 'section_header' )
						->text( 'Delete User Account' )
						->description( 'STOP! Are you sure you want to do this? <b><u>This is permanent and irreversible.</u> It cannot be undone!</b>' );
					$delete_user->add_section( $header );
					$delete_user->submit( 'Delete User Account' );
					$delete_user->display();
					?>
				</div>
			</div>
			<hr>
		<?php }

		private function profile_tab() {
			$user = Simple_Paywall_User::get_instance()->get_data(); ?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<?php
					// Update user form
					$update_user = new Simple_Paywall_Form_v2;
					$update_user->id( 'update-user' );

					/**
					 * Personal information section
					 */
					$personal_information = new Simple_Paywall_Form_Element;
					$personal_information->type( 'section_header' )
						->text( 'Personal Information' );
					$update_user->add_section( $personal_information );

					// First name
					$first_name = new Simple_Paywall_Form_Element;
					$first_name->type( 'text' )
						->name( 'first_name' )
						->label( 'First Name' )
						->value( $user->first_name )
						->is_required();
					$update_user->add_element( $first_name );

					// Last name
					$last_name = new Simple_Paywall_Form_Element;
					$last_name->type( 'text' )
						->name( 'last_name' )
						->label( 'Last Name' )
						->value( $user->last_name )
						->is_required();
					$update_user->add_element( $last_name );

					// Birthday
					$birthday = new Simple_Paywall_Form_Element;
					$birthday->type( 'date' )
						->name( 'birthday' )
						->label( 'Birthday' )
						->value( $user->birthday );
					$update_user->add_element( $birthday );

					// Registered on
					$registered_on = new Simple_Paywall_Form_Element;
					$registered_on->type( 'text' )
						->name( 'registered_on' )
						->label( 'Registered On' )
						->value( Simple_Paywall_Utility::get_wp_datetime( $user->created_on ) )
						->description( '*' . Simple_Paywall_Utility::get_wp_timezone() )
						->is_disabled();
					$update_user->add_element( $registered_on );

					/**
					 * Address section
					 */

					// Section header
					$address_section_header = new Simple_Paywall_Form_Element;
					$address_section_header->type( 'section_header' )
						->text( 'Address Information' )
						->description( 'Required for users that receive anything tangible as part of their subscription(s) or product purchases.' );
					$update_user->add_section( $address_section_header );

					// Street address - line 1
					$street_address = new Simple_Paywall_Form_Element;
					$street_address->type( 'text' )
						->label( 'Street Address' )
						->name( 'street_address_line_one' )
						->placeholder( 'Line 1' )
						->value( $user->street_address ? $user->street_address : '' );
					$update_user->add_element( $street_address );

					// Street address - line 2
					$street_address_2 = new Simple_Paywall_Form_Element;
					$street_address_2->type( 'text' )
						->name( 'street_address_line_two' )
						->placeholder( 'Line 2' )
						->value( $user->street_address_2 ? $user->street_address_2 : '' );
					$update_user->add_element( $street_address_2 );

					// City
					$city = new Simple_Paywall_Form_Element;
					$city->type( 'text' )
						->name( 'city' )
						->label( 'City' )
						->value( $user->city ? $user->city : '' );
					$update_user->add_element( $city );

					// State/Province/Region
					$region = new Simple_Paywall_Form_Element;
					$region->type( 'text' )
						->name( 'region' )
						->label( 'State/Province/Region' )
						->value( $user->region ? $user->region : '' );
					$update_user->add_element( $region );

					// ZIP/Postal Code
					$postal_code = new Simple_Paywall_Form_Element;
					$postal_code->type( 'text' )
						->name( 'postal_code' )
						->label( 'ZIP/Postal Code' )
						->value( $user->postal_code ? $user->postal_code : '' );
					$update_user->add_element( $postal_code );

					// Country
					$country = new Simple_Paywall_Form_Element;
					$country->type( 'text' )
						->name( 'country' )
						->label( 'Country' )
						->value( $user->country ? $user->country : '' );
					$update_user->add_element( $country );

					// Submit
					$update_user->submit( 'Update User Profile' );

					// Feedback
					$update_user->success_message( 'Successfully updated the user\'s profile.' );
					$update_user->error_message( 'There was an issue updating the user. Please see below for more details.' );

					// Display
					$update_user->display();
					?>
				</div>
			</div>
		<?php }

		/**
		 * Render and display content for simple-paywall-users page
		 * @return null
		 */
		public function render_users_page() {
			Simple_Paywall_View::get_instance()->get_header(); ?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<h1>Users <a href="?page=simple-paywall-add-user" class="add-new-h2">Add New</a></h2></h1>
					<form method="post">
						<?php
						$userListTable = new Simple_Paywall_Users_Table();
						$userListTable->views();
						$userListTable->prepare_items();
						$userListTable->search_box( 'Search', 'search' );
						$userListTable->display();
						?>
					</form>
				</div>
			</div>
		<?php }

	}
}
