<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

if ( ! class_exists( 'Simple_Paywall_Plugin_Activator' ) ) {

	/**
	 * Executed during plugin activation
	 */
	class Simple_Paywall_Plugin_Activator {

		function __construct() {}

		public function activate() {
			add_option( 'wp_paywall_activation_redirect' , true );
			add_action( 'admin_init', array( $this, 'activation_redirect') );
			// $this->simple_paywall_database_setup();
		}

		public static function deactivate() {}

		/**
		 * Set up custom database tables
		 * @see https://codex.wordpress.org/Creating_Tables_with_Plugins
		 * @see https://premium.wpmudev.org/blog/creating-database-tables-for-plugins/
		 */
		public function simple_paywall_database_setup() {

			global $wpdb;

			$charset_collate = $wpdb->get_charset_collate();
			$table_name = $wpdb->prefix . 'wp_paywall_subscribers';

			$sql = "CREATE TABLE $table_name (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					added_on datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
					first_name tinytext NOT NULL,
					last_name tinytext NOT NULL,
					email text NOT NULL,
					PRIMARY KEY  (id)
					) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

		}

	}

}
