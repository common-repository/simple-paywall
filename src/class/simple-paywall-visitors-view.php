<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

if ( ! class_exists( 'Simple_Paywall_Visitors_View' ) ) {
	class Simple_Paywall_Visitors_View {

		private static $_instance = null;

		public function __construct() {}

		public static function get_instance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}

		/**
		 * Render and display "Visitor" page
		 */
		public function render_visitor_page() { ?>
			<?php Simple_Paywall_View::get_instance()->get_header(); ?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<h1>Visitor</h1>
					<code><?php echo $_GET['visitor'] ?></code>
					<!-- <p><i class="fa fa-info-circle" aria-hidden="true"></i>These are website <code>visitors</code> that are consuming your content but are not actively signed in or have yet to register as a user.</p> -->
				</div>
			</div>
			<?php
			$tabs = new Simple_Paywall_Tabs( [ 'Details', 'Activity' ] );
			$tabs->display(); ?>
			<?php if ( $tabs->get_active_tab() === 'activity' || $tabs->get_active_tab() === '' ) { ?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<?php $table = new Simple_Paywall_Visitor_Activity_Table(); ?>
					<form method="post">
						<?php
						$table->views();
						$table->prepare_items();
						$table->display();
						?>
					</form>
				</div>
			</div>
		<?php } ?>
		<?php }

		/**
		 * Render and display "Visitors" page
		 */
		public function render_visitors_page() { ?>
			<?php Simple_Paywall_View::get_instance()->get_header(); ?>
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
						$table->display();
						?>
					</form>
				</div>
			</div>
		<?php }

	}
}
