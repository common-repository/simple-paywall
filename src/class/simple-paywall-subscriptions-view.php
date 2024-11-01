<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

if ( ! class_exists( 'Simple_Paywall_Subscriptions_View' ) ) {
	class Simple_Paywall_Subscriptions_View {

		private static $_instance = null;

		public function __construct() {}

		public static function get_instance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}

		/**
		 * Render and display Subscriptions page
		 */
		public function render_subscriptions_page() { ?>
			<?php Simple_Paywall_View::get_instance()->header(); ?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<h1>Subscriptions<?php /* <a href="?page=simple-paywall-add-subscription" class="add-new-h2" style="margin-bottom: 1em;">Add New</a> */ ?></h1>
				</div>
			</div>
			<div class="simple-paywall-container">
				<div class="wrap">
					<?php $table = new Simple_Paywall_Subscriptions_Table(); ?>
					<form method="post">
						<?php
						$table->views();
						$table->prepare_items();
						$table->search_box( 'Search', 'search' );
						$table->display();
						?>
					</form>
					<script>
					( function( $ ) {
						// if ( $( 'th#duration' ).hasClass( 'hidden' ) ) {
						// 	$( 'input#duration-hide' ).prop( 'checked', false );
						// }
						var hiddenCols = $('.wp-list-table th.manage-column.hidden');
						$.each( hiddenCols, function ( index, value ) {
							// console.log(this.id);
							$( 'input#' + this.id + '-hide' ).prop( 'checked', false );
						} );
					} )( jQuery );
					</script>
					<p>*All dates and times are listed in <?php echo Simple_Paywall_Utility::get_wp_timezone(); ?></p>
				</div>
			</div>
		<?php
		}

		/**
		 * Render and display "Add New" page
		 */
		public function add_new() {
			// Check if form has submitted
			if ( isset( $_POST['simple_paywall_add_subscription'] ) && $_POST['simple_paywall_add_subscription'] === 'true' ) {
				Simple_Paywall_Subscription::get_instance()->create();
			}
			?>
			<?php Simple_Paywall_View::get_instance()->header(); ?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<h1>Add New Subscription</h1>
					<p>Manually subscribe a user to a plan. If a plan requires payment, the selected user is required to have a default payment method set up in order this process.</p>
				</div>
			</div>
			<div class="simple-paywall-container">
				<div class="wrap">
					<?php
					$form = new Simple_Paywall_Form_v2;
					$form->id( 'add-subscription' );

					// Section header
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'section_header' )
					->text( 'Subscription Details' );
					$form->add_section( $element );

					// Get users
					$api = new Simple_Paywall_API();
					$api->method( 'GET' )
						->endpoint( '/users' )
						->call();
					$users = $api->get_data();
					$user_options = array();
					foreach ( $users as $user ) {
						$option = array( $user->id, $user->first_name . ' ' . $user->last_name . ' ' . '(' . $user->email . ')' . ' ' . '(' . $user->id . ')' );
						array_push( $user_options, $option );
					}

					// User
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'select' )
						->options( $user_options )
						->name( 'subscription_user' )
						->label( 'User' )
						->description( 'Select the user you want to create a subscription for.' )
						->is_required();
					$form->add_element( $element );

					// Get plans
					$api = new Simple_Paywall_API();
					$api->method( 'GET' )->endpoint( '/plans' )->call();
					$plans = $api->get_data();
					$plan_options = array();
					foreach ( $plans as $plan ) {
						$option = array( $plan->id, $plan->name . ' ' . '(' . $plan->id . ')' );
						array_push( $plan_options, $option );
					}

					// Plan
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'select' )
						->options( $plan_options )
						->label( 'Plan' )
						->name( 'subscription_plan' )
						->description( 'Select the plan you want to create a subscription for.' )
						->is_required();
					$form->add_element( $element );

					// Start date
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'date' )
						->name( 'start_date' )
						->label( 'Start Date' )
						->description( 'Leave blank to start the subscription immediately.' );
					$form->add_element( $element );

					// Section header
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'section_header' )
						->text( 'Payment' );
					$form->add_section( $element );

					$element = new Simple_Paywall_Form_Element;
					$element->type( 'checkbox' )
						->name( 'subscription_charge' )
						->label( 'Charge user\'s default payment method. (Only applicable if the plan selected requires payment)' )
						->is_checked( true )
						->is_disabled( true )
						->description( 'The user must have a default payment method set if the selected plan requires payment. <br> If the selected user does not have a default
	payment method and a plan requires payment, a new subscription will <u>not</u> be created. Payment methods can only be managed by the user.' );
					$form->add_element( $element );

					// Error
					$form->error_message( 'There was an issue in adding a new subscription. Please see below for more details.' );

					// Success
					$form->success_message( 'Successfully created a new subscription.' );

					// Submit
					$form->submit( 'Create Subscription' );

					// Display
					$form->display();

					?>
				</div>
			</div>
			<?php
		}

		/**
		 * ?page=simple-paywall-subscription
		 * @return void
		 */
		public function subscription() { ?>
			<?php
			// Check if form has submitted
			if ( isset( $_POST['simple_paywall_update_paywall'] ) && $_POST['simple_paywall_update_paywall'] === 'true' ) {
				Simple_Paywall_Paywall::get_instance()->update();
			}
			Simple_Paywall_Subscription::get_instance()->get();
			$subscription = Simple_Paywall_Subscription::get_instance()->get_data();
			Simple_Paywall_View::get_instance()->header();
			?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<h1>Subscription</h1>
					<?php if ( Simple_Paywall_Subscription::get_instance()->get_id() !== null ) { ?>
						<p>ID: <code><?php echo esc_html( Simple_Paywall_Subscription::get_instance()->get_id() ); ?></code></p>
					<?php } ?>
					<?php if ( ! is_null( $subscription->user->email ) ) { ?>
						<p>User: <a href="<?php echo esc_url( '/?page=simple-paywall-user&user=' . $subscription->user->id . '&tab=profile' ); ?>"><?php echo esc_html( $subscription->user->first_name . ' ' . $subscription->user->last_name . ' (' . $subscription->user->email . ')' ); ?></a></p>
					<?php } ?>
				</div>
			</div>
			<?php
			$tabs_array = array(
				'Details'
			);
			$tabs = new Simple_Paywall_Tabs( $tabs_array );
			$tabs->display();
			// Set content for tab
			switch ( $tabs->get_active_tab() ) {
				case '':
					$this->tab_details();
					break;
				case 'details':
					$this->tab_details();
					break;
				default:
					die( 'Invalid value for tabs url parameter.');
					break;
			}
			?>
			<?php $subscription = Simple_Paywall_Subscription::get_instance()->get(); ?>
		<?php
		}

		/**
		 * The subscription details tab at
		 * Limits tab ?page=simple-paywall-subscription
		 * @return void
		 */
		public function tab_details() { ?>
			<?php $subscription = Simple_Paywall_Subscription::get_instance()->get_data(); ?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<?php

					// Create new form
					$form = new Simple_Paywall_Form_v2;
					$form->id( 'update-subscription' );

					// Created on
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'text' )
						->name( 'subscription_created_on' )
						->label( 'Created On' )
						->value( $subscription->created_on . ' UTC' )
						->is_disabled();
					$form->add_element( $element );

					// Starts on
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'text' )
						->name( 'subscription_started_on' )
						->label( 'Starts On' )
						->value( Simple_Paywall_Utility::get_wp_datetime( $subscription->started_on ) )
						->is_disabled();
					$form->add_element( $element );

					// Ends on
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'text' )
						->name( 'subscription_ended_on' )
						->label( 'Ends On' )
						->value( Simple_Paywall_Utility::get_wp_datetime( $subscription->ended_on ) )
						->is_disabled();
					$form->add_element( $element );

					// Duration
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'text' )
						->name( 'subscription_plan_duration' )
						->label( 'Duration' )
						->value( $subscription->plan->interval_count . ' ' . ucfirst( $subscription->plan->interval ) . ( $subscription->plan->interval_count > 1 ? 's' : '' ) )
						->is_disabled();
					$form->add_element( $element );

					// Format price for price field
					if ( isset( $subscription->plan->price ) ) {
						if ( strlen( $subscription->plan->price ) === 2 ) {
							$subscription->plan->price = '0.' . $subscription->plan->price;
						}
						/** @todo For decimal currencies only. Currency must be taken into account. */
						if ( strlen( $subscription->plan->price ) > 2 ) {
							$subscription->plan->price = substr_replace( $subscription->plan->price, '.', -2, 0 );
						}
						if ( $subscription->plan->currency === 'usd' ) {
							$subscription->plan->price = '$' . $subscription->plan->price;
						}
					}

					// Price
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'text' )
						->name( 'subscription_plan_price' )
						->label( 'Price' )
						->value( $subscription->plan->price . ' ' . strtoupper( $subscription->plan->currency ) )
						->is_disabled();
					$form->add_element( $element );

					// Automatically renew
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'checkbox' )
						->text( 'Automatic Renewal' )
						->name( 'subscription_is_auto_renew' )
						->label( 'Automatically renew the subscription when it ends?' )
						->description( 'Any charges created for new subscriptions set to automatically renew will be made at least 24 hours in advance for any plan with a duration greater than 1 day.' )
						->value( $subscription->auto_renew )
						->is_disabled();
					$form->add_element( $element );

					// Renewal plan
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'text' )
						->name( 'auto_renew' )
						->label( 'Renewal Plan' )
						->value( $subscription->plan->id )
						->description( 'The plan we are renewing to in the autorenewal.' )
						->is_disabled();
					$form->add_element( $element );

					// Display the form
					$form->display();

					?>
				</div>
			</div>
		<?php
		}

	}
}
