<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

if ( ! class_exists( 'Simple_Paywall_Paywalls_View' ) ) {
	class Simple_Paywall_Paywalls_View {

		private static $_instance = null;

		public function __construct() {}

		public static function get_instance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}

		/**
		 * ?page=simple-paywall-add-paywall
		 * @return void
		 */
		public function add() {
			// Check if form has submitted
			if ( isset( $_POST['simple_paywall_add_paywall'] ) && $_POST['simple_paywall_add_paywall'] === 'true' ) {
				Simple_Paywall_Paywall::get_instance()->create();
			}
			?>
			<?php Simple_Paywall_View::get_instance()->get_header(); ?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<h1>Add New Paywall</h1>
					<p>Add a new paywall that will protect your premium content and encourage visitors to subscribe.</p>
					<?php
					$form = new Simple_Paywall_Form_v2;
					$form->id( 'add-paywall' );

					// Header
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'section_header' )
						->name('')
						->text( 'Paywall Details' );
					$form->add_section( $element );

					// Paywall name
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'text' )
						->name( 'paywall_name' )
						->label( 'Name' )
						->placeholder( 'e.g., 5 Free Articles per Month' )
						->description( 'Use a friendly and descriptive name.' )
						->is_required();
					$form->add_element( $element );

					// Paywall type
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'select' )
						->name( 'paywall_type' )
						->label( 'Paywall Type' )
						->options( array(
							array( 'soft', 'Metered (Soft)' ),
							array( 'hard', 'Hard' )
						) )
						->is_required();
					$form->add_element( $element );

					// User only
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'checkbox' )
						->name( 'paywall_is_user_only' )
						->label( 'Require visitors to create a free user account prior to accessing metered content' )
						->description( 'Applicable to metered (soft) paywalls only. Useful for cross-browser and cross-device tracking. <br />This requires the visitor to register as a User (for free) before they can view any "free" content.<br />Please review the Simple Paywall documentation for more details on the benefits and drawbacks of doing so.' );
					$form->add_element( $element );

					// Description
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'textarea' )
						->name( 'paywall_description' )
						->label( 'Description' )
						->placeholder( 'e.g., Prompt user with \'overlay\' offering when limit reached.' )
						->description( 'Useful to keep track of things.<br />This is for internal use only and is not displayed publicly.' );
					$form->add_element( $element );

					// Header
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'section_header' )
						->name( '' )
						->text( 'Limits' );
					$form->add_section( $element );

					// <i class="fas fa-info-circle"></i><em>Set the limits for the metered (soft) paywall.</em>

					// Limit count
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'text' )
						->name( 'paywall_limit_count' )
						->label( 'Limit Count' )
						->description( 'The number of unique items of content (post, page, media gallery, etc.) a visitor or user is allowed before activating this paywall and asking the user to subscribe or make a purchase.' )
						->width( '60px' )
						->value( '' )
						->is_required();
					$form->add_element( $element );

					// Limit cycle duration
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'duration' )
						->name( 'paywall_limit_cycle' )
						->label( 'Limit Cycle' )
						->description( 'The length of time the limit lasts for before the Limit Count resets.' )
						->is_required();
					$form->add_element( $element );

					// Limit is rolling
					// $element = new Simple_Paywall_Form_Element;
					// $element->type( 'checkbox' )
					// 	->name( 'paywall_is_rolling_limit' )
					// 	->label( 'Rolling Limit' )
					// 	->description( 'Reset the limit from the time the content item is consumed.<br>For example, if a user consumes content protected by a soft paywall that allows 5 posts per month, the rolling limit would allow the user to consume one article after a month (30 days) has passed from the time of consuming the first item of content. Alternatively, with no rolling basis set, a view count limit will reset at the beginning of the month.' )
					// 	->is_checked();
					// $form->add_element( $element );

					// Error
					$form->error_message( 'There was an issue adding a new plan. Please see below for more details.' );

					// Success
					$form->success_message( 'Successfully added new plan.' );

					// Submit
					$form->submit( 'Add New Paywall' );

					// Display
					$form->display();
					?>
				</div>
			</div>
			<?php
		}

		/**
		 * ?page=simple-paywall-paywall
		 */
		public function paywall() {
			// Check if form has submitted
			if ( isset( $_POST['simple_paywall_update_paywall'] ) && $_POST['simple_paywall_update_paywall'] === 'true' ) {
				Simple_Paywall_Paywall::get_instance()->update();
			}
			?>
			<?php Simple_Paywall_View::get_instance()->get_header(); ?>
			<?php Simple_Paywall_Paywall::get_instance()->get(); ?>
			<?php $paywall = Simple_Paywall_Paywall::get_instance()->get_data(); ?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<h1>Paywall</h1>
					<?php if ( isset( $paywall ) ) { ?>
						<?php if ( isset( $paywall->name ) ) { ?>
							<p><?php echo $paywall->name; ?></p>
						<?php } ?>
					<?php } ?>
					<?php if ( Simple_Paywall_Paywall::get_instance()->get_id() !== null ) { ?>
					<p>ID <code><?php echo Simple_Paywall_Paywall::get_instance()->get_id(); ?></code></p>
					<?php } ?>
				</div>
			</div>
			<?php
			$tabs_array = array(
				'Details',
				'Restrictions'
			);
			if ( $paywall->type === 'soft' ) {
				array_splice( $tabs_array, 1, 0, 'Limits' );
				array_splice( $tabs_array, -1, 0, 'Notices' );
			}
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
				case 'paywall-details':
					$this->tab_details();
					break;
				case 'limits':
					$this->tab_limits();
					break;
				case 'content':
					$this->tab_content();
					break;
				case 'notices':
					$this->tab_notices();
					break;
				case 'restrictions':
					$this->tab_restrictions();
					break;
				default:
					die( 'Invalid value for tabs url parameter.');
					break;
			}
		}

		/**
		 * ?page=simple-paywall-paywalls
		 */
		public function paywalls() { ?>
			<?php Simple_Paywall_View::get_instance()->get_header(); ?>
			<div class="simple-paywall-container">
				<p><i class="fas fa-info-circle"></i>This is where you limit and control access to your premium content. It's also your opportunity to configure your marketing to potential users. <br><i class="fas fa-info-circle"></i>Each content item (post, page, etc.) can only belong to one paywall. If content belonged to more than one paywall, how would we know which paywall to display if a visitor or user encounters one?</p>
			</div>
			<div class="simple-paywall-container">
				<div class="wrap">
					<h1>Paywalls <a href="?page=simple-paywall-add-paywall" class="add-new-h2" style="margin-bottom: 1em;">Add New</a></h2></h1>
					<form method="post">
						<?php
						$paywalls_obj = new Simple_Paywall_Paywalls_Table();
						$paywalls_obj->views();
						$paywalls_obj->prepare_items();
						$paywalls_obj->search_box( 'Search', 'search' );
						$paywalls_obj->display();
						?>
					</form>
				</div>
			</div>
		<?php	}

		/**
		 * Details tab ?page=simple-paywall-paywall
		 * @return void
		 */
		private function tab_details() {
			$paywall = Simple_Paywall_Paywall::get_instance()->get_data(); ?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<?php

					$form = new Simple_Paywall_Form_v2;
					$form->id( 'update-paywall' );

					// Created on
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'text' )
						->name( 'created_on' )
						->label( 'Created On' )
						->value( Simple_Paywall_Utility::get_wp_datetime( $paywall->created_on ) )
						->description( '*' . Simple_Paywall_Utility::get_wp_timezone() )
						->is_disabled();
					$form->add_element( $element );

					// Name
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'text' )
						->name( 'paywall_name' )
						->label( 'Name' )
						->value( $paywall->name )
						->description( 'Use a friendly and descriptive name.' )
						->is_required();
					$form->add_element( $element );

					// Type
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'select' )
						->name( 'paywall_type' )
						->label( 'Paywall Type' )
						->options( array(
							array( 'soft', 'Metered (Soft)' ),
							array( 'hard', 'Hard' )
						) )
						->value( $paywall->type )
						->description( 'This cannot be changed after the paywall is created.' )
						->is_required()
						->is_disabled();
					$form->add_element( $element );

					if ( $paywall->type === 'soft' ) {
						// User only
						$element = new Simple_Paywall_Form_Element;
						$element->type( 'checkbox' )
							->name( 'paywall_is_user_only' )
							->label( 'Require visitors to create a free user account prior to accessing metered content' )
							->description( 'Applicable to metered (soft) paywalls only. Useful for cross-browser and cross-device tracking. <br>This requires the visitor to register as a User (for free) before they can view any "free" content.<br>Please review the Simple Paywall documentation for more details on the benefits and drawbacks of doing so.' )
							->is_checked( $paywall->user_only );
						$form->add_element( $element );
					}

					// Description
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'textarea' )
						->name( 'paywall_description' )
						->label( 'Description' )
						->placeholder( 'e.g., Prompt user with \'overlay\' offering when limit reached.' )
						->value( $paywall->description )
						->description( 'Useful to keep track of things.<br>This is for internal use only and is not displayed publicly.' );
					$form->add_element( $element );

					// Submit button
					$form->submit( 'Update Paywall Details' );

					// Feedback
					$form->success_message( 'The paywall was successfully updated.' );
					$form->error_message( 'The paywall was not updated. Please see details below for more information.' );

					// Display the form
					$form->display();

					?>
				</div>
			</div>
		<?php }

		/**
		 * Limits tab ?page=simple-paywall-paywall
		 * @return void
		 */
		private function tab_limits() {
			$paywall = Simple_Paywall_Paywall::get_instance()->get_data(); ?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<?php

					// Create new form
					$form = new Simple_Paywall_Form_v2;
					$form->id( 'update-paywall' );

					// Limit count
					$limit_count = new Simple_Paywall_Form_Element;
					$limit_count->type( 'text' )
						->name( 'paywall_limit_count' )
						->label( 'Limit Count' )
						->value( $paywall->limit_count )
						->description( 'The number of unique items of content (post, page, media gallery, etc.) a visitor or user is allowed before activating this paywall and asking the user to subscribe or make a purchase.' )
						->width( '50px' )
						->is_required();
					$form->add_element( $limit_count );

					// Limit cycle
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'duration' )
						->name( 'paywall_limit_cycle' )
						->label( 'Limit Cycle' )
						->description( 'The length of time the limit lasts for before the Limit Count resets.' )
						->value( array( $paywall->limit_interval_count, $paywall->limit_interval ) )
						->is_required();
					$form->add_element( $element );

					// // Rolling limit
					// $rolling_limit = new Simple_Paywall_Form_Element;
					// $rolling_limit->type( 'checkbox' )
					// 	->name( 'paywall_limit_is_rolling' )
					// 	->label( 'Rolling Limit' )
					// 	->text( 'Limit Type' )
					// 	->is_checked( $paywall->limit_is_rolling )
					// 	->description( 'Reset the limit from the time the content item is consumed.<br>For example, if a user consumes content protected by a soft paywall that allows 5 posts per month, the rolling limit would allow the user to consume one article after a month (30 days) has passed from the time of consuming the first item of content. Alternatively, with no rolling basis set, a view count limit will reset at the beginning of the month.' );
					// $form->add_element( $rolling_limit );

					// Submit button
					$form->submit( 'Update Paywall Limit' );

					// Feedback
					$form->success_message( 'The paywall was successfully updated.' );
					$form->error_message( 'The paywall was not updated. Please see details below for more information.' );

					// Display the form
					$form->display();

					?>
				</div>
			</div>
		<?php }

		/**
		 * Notices tab ?page=simple-paywall-paywall
		 * @return void
		 */
		private function tab_notices() {

			/**
			 * Check if form has submitted
			 */
			if ( isset( $_POST['simple_paywall_update_paywall'] ) && $_POST['simple_paywall_update_paywall'] === 'true' ) {
				Simple_Paywall_Paywall::get_instance()->update();
			}

			?>
			<?php $paywall = Simple_Paywall_Paywall::get_instance()->get_data(); ?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<p><i class="fas fa-info-circle"></i><em>Notices display information to the visitor or end user regarding how many views or content items they have remaining for content protected by this paywall. <br> It's also an opportune moment to market to potential customers and/or subscribers.</em></p>
					<?php

					// Create new form
					$update_paywall_form = new Simple_Paywall_Form_v2;
					$update_paywall_form->id( 'update-paywall' );
					$notice_types = array(
						array( 'sticky_floating_footer_bar', 'Sticky Floating Footer Bar' ),
					);

					// Default notice type
					$notice_type = new Simple_Paywall_Form_Element;
					$notice_type->type( 'select' )
						->name( 'paywall_default_notice_type' )
						->label( 'Notice Type' )
						->options( $notice_types )
						->is_required();
					$update_paywall_form->add_element( $notice_type );

					// Notice styles section header
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'section_header' )
						->name( '' )
						->text( 'Styles' );
					$update_paywall_form->add_section( $element );

					// Notice style - background color
					$notice_styles = new Simple_Paywall_Form_Element;
					$notice_styles->type( 'text' )
						->name( 'paywall_notice_style_background_color' )
						->label( 'Background Color' )
						->value( isset( $paywall->notice->style->background_color ) ? $paywall->notice->style->background_color : '#f2f2f2' )
						->description( 'Only accepts HEX color values: e.g., Black: #000000' )
						->is_required();
					$update_paywall_form->add_element( $notice_styles );

					// Notice style - close button color
					$notice_styles = new Simple_Paywall_Form_Element;
					$notice_styles->type( 'text' )
						->name( 'paywall_notice_style_close_button_color' )
						->label( 'Close Button Color' )
						->value( isset( $paywall->notice->style->close_button_color ) ? $paywall->notice->style->close_button_color : '#000000' )
						->description( 'The color of the "close" notice button: <br />Only accepts HEX color values. e.g., White: #ffffff' )
						->is_required();
					$update_paywall_form->add_element( $notice_styles );

					// Notice style - max-width
					$notice_styles = new Simple_Paywall_Form_Element;
					$notice_styles->type( 'text' )
						->name( 'paywall_notice_style_max_width' )
						->label( 'Max Width' )
						->value( isset( $paywall->notice->style->max_width ) ? $paywall->notice->style->max_width : '' )
						->description( 'Set the maximum width of content container. Max width will be 100% of window if no value is set. <br />Examples of acceptable values include 1100px, 20em, or 25rem.' );
					$update_paywall_form->add_element( $notice_styles );

					// Content section header
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'section_header' )
						->name( '' )
						->text( 'Content' );
					$update_paywall_form->add_section( $element );

					// Default notices content
					$default_notice_content = new Simple_Paywall_Form_Element;
					$default_notice_content->type( 'wp_editor' )
						->name( 'paywall_default_notice_content' )
						->value( isset( $paywall->notice->html_content ) ? $paywall->notice->html_content : '' )
						->label( 'Default Notice Content' )
						->description( 'This notice displays by default and is most commonly used to tell the visitor or user know how many free views or content items remain for content protected under this paywall. Take advantage of Simple Paywall\'s merge tags to display dynamic information such as <code style="font-style: normal;">*|CONTENT_ITEMS_REMAINING|*</code>, which outputs the number of content items remaining for a visitor or user under this paywall.' )
						->is_required();
					$update_paywall_form->add_element( $default_notice_content );

					// // Default final notices content
					// $default_final_notice_content = new Simple_Paywall_Form_Element;
					// $default_final_notice_content->type( 'wp_editor' )
					// 	->name( 'paywall_default_final_notice_content' )
					// 	->value( isset( $paywall->default_final_notice_content ) ? $paywall->default_final_notice_content : '' )
					// 	->label( 'Final Notice Content' )
					// 	->description( 'If set, this content will display instead of the Default Notice Content above when there are no views remaining for content items protected by this paywall. This is the perfect opportunity to tailor your messaging for a visitor or user that has no free items remaining on this paywall.' );
					// $update_paywall_form->add_element( $default_final_notice_content );

					$update_paywall_form->submit( 'Update Paywall Notices' );
					$update_paywall_form->display();
					?>
				</div>
			</div>
		<?php }

		/**
		 * Restrictions tab ?page=simple-paywall-paywall
		 * @return void
		 */
		private function tab_restrictions() {
			/**
			 * Check if form has submitted
			 */
			if ( isset( $_POST['simple_paywall_update_paywall'] ) && $_POST['simple_paywall_update_paywall'] === 'true' ) {
				Simple_Paywall_Paywall::get_instance()->update();
			}
			$paywall = Simple_Paywall_Paywall::get_instance()->get();
			?>
			<div class="simple-paywall-container">
				<?php
				$header = new Simple_Paywall_Form_Element;
				$header->type( 'section_header' )
					->text( 'Restrictions' )
					->get();
				?>
				<?php
				$select_notice_type = new Simple_Paywall_Form_Element;
				$select_notice_type->type( 'label' )
					->name( 'paywall_default_notice_content' )
					->text( 'Default Notice Content' )
					->description( 'hello world - use placeholders such as <code>%articles_remaining%</code> to insert dynamic content.' )
					->is_required()
					->get();
				?>
				<div class="wrap">
					<p><i class="fas fa-info-circle"></i><em>This is where you set how to block or prevent an unauthorized visitor or user from accessing the content when the paywall has activated.</em></p>
					<?php
					$update_paywall_form = new Simple_Paywall_Form_v2;
					$update_paywall_form->id( 'update-paywall' );
					$restriction_types = array(
						array( 'overlay', 'Overlay' ),
					);
					$restriction_type = new Simple_Paywall_Form_Element;

					// Select restriction type
					$restriction_type->type( 'select' )
						->options( $restriction_types )
						->name( 'paywall_default_restriction_type' )
						->label( 'Restriction Type' )
						->is_required();
					$update_paywall_form->add_element( $restriction_type );

					// Visitor restriction content via WP Editor
					$visitor_restriction_content = new Simple_Paywall_Form_Element;
					$visitor_restriction_content->type( 'wp_editor' )
						->name( 'paywall_visitor_restriction_content' )
						->label( 'Content for Visitors' )
						->description( 'This content will be displayed to visitors when they are not authorized to view the content protected by this paywall.' )
						->value( isset( $paywall->default_visitor_restriction_content ) ? $paywall->default_visitor_restriction_content : '' )
						->is_required();
					$update_paywall_form->add_element( $visitor_restriction_content );

					// User restriction content via WP Editor
					$user_restriction_content = new Simple_Paywall_Form_Element;
					$user_restriction_content->type( 'wp_editor' )
						->name( 'paywall_user_restriction_content' )
						->label( 'Content for users' )
						->description( 'Restriction content for your users should differ from visitors since users have already registered for a free account and, perhaps, have already subscribed to a plan that does not include this content. Personalize your messaging to invite the user to subscribe to a plan that includes this content.' )
						->value( isset( $paywall->default_user_restriction_content ) ? $paywall->default_user_restriction_content : '' )
						->is_required();
					$update_paywall_form->add_element( $user_restriction_content );

					$update_paywall_form->submit( 'Update Paywall Restrictions' );
					$update_paywall_form->success_message( 'The paywall was successfully updated.' );
					$update_paywall_form->error_message( 'There were some issue(s) that prevented us from updating the paywall. Please see details below for more information.' );
					$update_paywall_form->display();
					?>
				</div>
			</div>
		<?php
		}

	}
}
