<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

if ( ! class_exists( 'Simple_Paywall_Plans_View' ) ) {
	class Simple_Paywall_Plans_View {

		private static $_instance = null;

		public function __construct() {}

		public static function get_instance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}

		/**
		 * ?page=simple-paywall-add-plan
		 */
		public function add() {
			// Check if form has submitted
			if ( isset( $_POST['simple_paywall_add_plan'] ) && $_POST['simple_paywall_add_plan'] === 'true' ) {
				Simple_Paywall_Plan::get_instance()->create();
			}
			?>
			<?php Simple_Paywall_View::get_instance()->get_header(); ?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<h1>Add New Plan</h1>
					<p><i class="fas fa-info-circle"></i>Plans provide users with access to a particular product for a specified length of time. Plans allow variations in duration, price, and restrictions in access to a product.</p>
					<?php
					$form = new Simple_Paywall_Form_v2;
					$form->id( 'add-plan' );

					// Get $options for product
					$api = new Simple_Paywall_API();
					$api->method( 'GET' )->endpoint( '/products?type=service' )->call();
					$products = $api->get_data();

					// Sort products by name
					arsort( $products );
					$options = array();
					foreach ( $products as $product ) {
						$option = array( $product->id, $product->name . ' ' . '(ID: ' . $product->id . ')' );
						array_push( $options, $option );
					}
					array_multisort( $options, SORT_DESC, $products );

					// Product
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'select' )
						->name( 'plan_product' )
						->label( 'Product' )
						->options( $options )
						->description( 'Select the product that this plan is attached to.<br>A product must be a "service" and be active in order to be listed here.' )
						->is_required();
					$form->add_element( $element );

					// Plan name
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'text' )
						->name( 'plan_name' )
						->label( 'Name' )
						->placeholder( 'e.g., Monthly Access' )
						->description( 'Best practices: <br>Use a friendly and descriptive name for the product.<br>' )
						->is_required();
					$form->add_element( $element );

					// Receipt name
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'text' )
						->name( 'plan_receipt_name' )
						->label( 'Receipt Name' )
						// ->value( $plan->receipt_name )
						->description( 'How should this plan appear on the receipt emailed to subscriber?<br>Leave blank to use the name of the plan set above.' );
					$form->add_element( $element );

					// Description
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'textarea' )
						->name( 'plan_description' )
						->label( 'Description' )
						->placeholder( '' )
						->description( 'Useful to describe what this product is offering. <br>This is for internal use only and is not displayed publicly.</em>' );
					$form->add_element( $element );

					// Price
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'text' )
						->name( 'plan_price' )
						->label( 'Price' )
						->placeholder( 'e.g., $9.99' )
						->description( 'The price cannot be changed or adjusted after a plan has been created.<br><!--<a href="javascript:void();" target="_blank">Why can\'t I change the price instead of creating a new plan?</a>-->' )
						->is_required();
					$form->add_element( $element );

					// Product currency
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'select' )
						->name( 'plan_currency' )
						->label( 'Currency' )
						->options( array(
							array( 'usd', 'USD ($)' )
						) )
						->description( 'The default currency can be adjusted in your <a href="https://app.simplepaywall.com/" target="_blank">Simple Paywall</a> account.' )
						->is_required();
					$form->add_element( $element );

					// Duration
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'duration' )
						->name( 'plan_duration' )
						->label( 'Duration' )
						->description( 'Set the length of time that access to the product under this plan is granted for. <br>Select "&#x221e;" (infinity) if you would like for the plan to go on indefinitely or until it is manually cancelled.' )
						->is_required();
					$form->add_element( $element );

					// Recurring
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'checkbox' )
						->name( 'plan_is_recurring' )
						->text( 'Auto Renew' )
						->label( 'Recurring' )
						->is_checked( true )
						->description( 'The plan automatically renews at the end of its duration.' );
					$form->add_element( $element );

					// // Limit Recurring Cycles
					// $element = new Simple_Paywall_Form_Element;
					// $element->type( 'checkbox' )
					// 	->name( 'plan_is_recurring' )
					// 	->text( 'Auto Renew' )
					// 	->label( 'Recurring' )
					// 	->is_checked( true )
					// 	->description( 'The plan automatically renews at the end of it\'set duration.' );
					// $form->add_element( $element );

					// OLD - keeping around for reference

					// Limit recurring cycles
					// array(
					// 	'type' => 'checkbox',
					// 	'label' => 'Limit the Number of Recurring Cycles',
					// 	'name' => 'plan-limit-recurring',
					// 	'value' => 0,
					// 	'description' => '<i class="fas fa-info-circle"></i><em>Limit the number of times that a plan will renew for.</em>'
					// ),
					// // Recurring cycles
					// array(
					// 	'type' => 'text',
					// 	'label' => '',
					// 	'name' => 'plan-recurring-cycles',
					// 	'description' => '<i class="fas fa-info-circle"></i><em>Set the number of cycles this plan will recur (renew) for.</em><br><br><b>Example</b><br>If the duration is set to 1 month, and you set this 11 cycles, the total lifetime of a plan is for 12 months (1 month + 11 cycles).'
					// ),

					// Error
					$form->error_message( 'There was an issue adding a new plan. Please see below for more details.' );

					// Success
					$form->success_message( 'Successfully added new plan.' );

					// Submit
					$form->submit( 'Add New Plan' );

					// Display
					$form->display();

					?>
				</div>
			</div>
		<?php }

		/**
		 * Details tab for ?page=simple-paywall-plan
		 * @return void
		 */
		private function details_tab() { ?>
			<?php $plan = Simple_Paywall_Plan::get_instance()->get_data(); ?>
			<div class="simple-paywall-container">
				<div class="wrap">
				<?php
				$form = new Simple_Paywall_Form_v2;
				$form->id( 'update-plan' );

				// Created on
				$element = new Simple_Paywall_Form_Element;
				$element->type( 'text' )
					->name( 'created_on' )
					->label( 'Created On' )
					->value( Simple_Paywall_Utility::get_wp_datetime( $plan->created_on ) )
					->description( '*' . Simple_Paywall_Utility::get_wp_timezone() )
					->is_disabled();
				$form->add_element( $element );

				// Name
				$element = new Simple_Paywall_Form_Element;
				$element->type( 'text' )
					->name( 'plan_name' )
					->label( 'Name' )
					->value( $plan->name )
					->is_required();
				$form->add_element( $element );

				// Receipt name
				$element = new Simple_Paywall_Form_Element;
				$element->type( 'text' )
					->name( 'plan_receipt_name' )
					->label( 'Receipt Name' )
					->description( 'How should this plan appear on the receipt emailed to the subscriber?<br>Leave blank to use the name of the plan set above.' );
				$form->add_element( $element );

				// Description
				$element = new Simple_Paywall_Form_Element;
				$element->type( 'textarea' )
					->name( 'plan_description' )
					->label( 'Description' )
					->placeholder( '' )
					->value( $plan->description )
					->description( 'Useful to describe what this product is offering. <br>This is for internal use only and is not displayed publicly.</em>' );
				$form->add_element( $element );

				if ( isset( $plan->price ) ) {
					if ( strlen( $plan->price ) === 2 ) {
						$plan->price = '0.' . $plan->price;
					}
					/** @todo For decimal currencies only. Currency must be taken into account. */
					if ( strlen( $plan->price ) > 2 ) {
						$plan->price = substr_replace( $plan->price, '.', -2, 0 );
					}
					if ( $plan->currency === 'usd' ) {
						$plan->price = '$' . $plan->price;
					}
				}

				// Price
				$element = new Simple_Paywall_Form_Element;
				$element->type( 'text' )
					->name( 'plan_price' )
					->label( 'Price' )
					->placeholder( '' )
					->value( $plan->price )
					->description( 'The price cannot be changed or adjusted after a plan has been created.<br>Please create a new plan if you wish to change the price. <br><!--<a href="javascript:void();" target="_blank">Why can\'t I change the price instead of creating a new plan?</a>-->' )
					->is_disabled();
				$form->add_element( $element );

				// Currency
				$element = new Simple_Paywall_Form_Element;
				$element->type( 'text' )
					->name( 'plan_currency' )
					->label( 'Currency' )
					->value( strtoupper( $plan->currency ) )
					->description( 'The currency cannot be changed after a plan has been created.<br>Please create a new plan if you wish to offer access to a product in a different currency. <br><!--<a href="javascript:void();" target="_blank">Why can\'t I change the price instead of creating a new plan?</a>-->' )
					->is_disabled();
				$form->add_element( $element );

				// Recurring
				$element = new Simple_Paywall_Form_Element;
				$element->type( 'checkbox' )
					->name( 'plan_is_recurring' )
					->text( 'Renewals' )
					->label( 'Recurring' )
					->is_checked( $plan->is_recurring === 1 ? true : false )
					->is_disabled()
					->description( 'The plan automatically renews at the end of it\'set duration.' );
				$form->add_element( $element );

				//'<code>'.$plan->product->id.'</code> <a href="?page=simple-paywall-product&product=' . $plan->product->id . '&tab=product-details">View Product</a> <br><i class="fas fa-info-circle"></i><em>This cannot be changed once a plan has been created.

				// Error
				$form->error_message( 'There was an issue updating the plan. Please see below for more details.' );

				// Success
				$form->success_message( 'Successfully updated plan.' );

				// Submit
				$form->submit( 'Update Plan Details' );

				// Display
				$form->display();
				?>
				</div>
			</div>
		<?php }

		/**
		 * ?page=simple-paywall-plan
		 */
		public function plan() { ?>
			<?php Simple_Paywall_View::get_instance()->get_header(); ?>
			<div class="simple-paywall-container">
				<p><i class="fas fa-info-circle"></i>Every plan is attached to a product. Products can have more than one plan, reflecting variations in price and duration—–such as monthly and annual pricing at different rates.</p>
			</div>
			<?php
			// Check if form has submitted
			if ( isset( $_POST['simple_paywall_update_plan'] ) && $_POST['simple_paywall_update_plan'] === 'true' ) {
				Simple_Paywall_Plan::get_instance()->update();
			}
			Simple_Paywall_Plan::get_instance()->get();
			$plan = Simple_Paywall_Plan::get_instance()->get_data();
			?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<h1>Plan</h1>
					<?php if ( isset( $plan ) ) { ?>
						<p><?php echo $plan->name; ?></p>
					<?php } ?>
					<?php if ( Simple_Paywall_Plan::get_instance()->get_id() !== null ) { ?>
					<p>ID: <code><?php echo Simple_Paywall_Plan::get_instance()->get_id(); ?></code></p>
					<p>Checkout URL: <br><code><?php echo get_site_url() . '/?simple_paywall=check_out&plan=' . Simple_Paywall_Plan::get_instance()->get_id(); ?></code></p>
					<?php } ?>
				</div>
			</div>
			<?php
			$tabs = new Simple_Paywall_Tabs( array(
				'Details'
			) );
			$tabs->display(); ?>
			<?php
			/**
			 * Tabs
			 */
			switch ( $tabs->get_active_tab() ) {
				case '':
					$this->details_tab();
					break;
				case 'details':
					$this->details_tab();
					break;
				default:
					die( 'Invalid value for tabs url parameter.');
					break;
			}

		}

		public function plans() { ?>
			<?php Simple_Paywall_View::get_instance()->get_header(); ?>
			<div class="simple-paywall-container">
				<p><i class="fas fa-info-circle"></i>Every plan is attached to a product. Products can have more than one plan, reflecting variations in price and duration—–such as monthly and annual pricing at different rates.</p>
			</div>
			<div class="simple-paywall-container">
				<div class="wrap">
					<h1>Plans <a href="?page=simple-paywall-add-plan" class="add-new-h2" style="margin-bottom: 1em;">Add New</a></h2></h1>
					<form method="post">
						<?php
						$table = new Simple_Paywall_Plans_Table();
						$table->views();
						$table->prepare_items();
						$table->search_box( 'Search', 'search' );
						$table->display();
						?>
					</form>
				</div>
			</div>
		<?php }

	}
}
