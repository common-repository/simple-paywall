<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

/**
 * Advised to create local copy of WP_List_Table class.
 * It is "subject to change without warning in any future WordPress release" -- make own copy of WP_List_Table class!
 * Original WP_List_Table() is located in /wp-admin/includes/class-wp-list-table.php
 * @see https://codex.wordpress.org/Class_Reference/WP_List_Table
 * @see https://www.sitepoint.com/using-wp_list_table-to-create-wordpress-admin-tables/
 */
if ( ! class_exists( 'Simple_Paywall_WP_List_Table' ) ) {
	require_once( SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'wp-list-table.php' );
}

if ( ! class_exists( 'Simple_Paywall_Users_Table' ) ) {

	class Simple_Paywall_Users_Table extends Simple_Paywall_WP_List_Table {

		private
					/**
					 * @var $_count The total number of records in the table
					 */
					$_count,

					/**
					 * @var $_data The data to display in the table.
					 */
					$_data;

		/**
		 * Class constructor
		 */
		public function __construct() {
			parent::__construct( array(
				// Singular name of the listed records
				'singular' => __( 'User', 'simple-paywall' ),
				// Plural name of the listed records
				'plural'	=> __( 'Users', 'simple-paywall' ),
				'ajax' => false
			) );
		}

		/**
		 * Method for name column
		 *
		 * @param array $item an array of DB data
		 *
		 * @return string
		 */
		protected function column_name( $item ) {
			return sprintf(
				'<strong><a href="%s">%s</a></strong><br><code>%s</code>',
				esc_url( '?page=simple-paywall-user&user=' . $item->id . '&tab=profile&_wpnonce=' . wp_create_nonce( 'simple_paywall_get_user' ) ),
				esc_html( $item->first_name . ' ' .	$item->last_name ),
				esc_html( $item->id )
			);
		}

		/**
		 * Render the city column
		 * @param array $item
		 * @return string
		 */
		protected function column_city( $item ) {
			$output = sprintf( '—' );
			if ( isset( $item->city ) ) {
				$output = sprintf( $item->city );
			}
			return $output;
		}

		/**
		 * Render the country column
		 * @param array $item
		 * @return string
		 */
		protected function column_country( $item ) {
			$output = sprintf( '—' );
			if ( isset( $item->country ) ) {
				$output = sprintf(
					'%s',
					esc_html__( $item->country, 'simple-paywall' )
				);
			}
			return $output;
		}

		/**
		 * Render the bulk edit checkbox
		 * @param array $item
		 * @return string
		 */
		protected function column_email( $item ) {
			return sprintf(
				'<a href="%s">%s</a>',
				esc_url( 'mailto:' . $item->email ),
				esc_html( $item->email )
			);
		}

		/**
		 * Render the region column
		 * @param array $item
		 * @return string
		 */
		protected function column_region( $item ) {
			$output = sprintf( '—' );
			if ( isset( $item->region ) ) {
				$output = sprintf( $item->region );
			}
			return $output;
		}

		/**
		 * Method for status column
		 * @param array $item
		 * @return string
		 * @see https://flatuicolors.com/palette/defo
		 */
		protected function column_status( $item ) {
			$output = sprintf( '—' );
			if ( isset( $item->status ) ) {
				switch( $item->status ) {
					case 'active':
						$output = sprintf( '<div class="status status__active" title="Active"></div>' );
						break;
					case 'inactive':
						$output = sprintf( '<div class="status status__yellow" title="Inactive"></div>' );
						break;
					default:
						return 'n/a';
				}
			}
			return $output;
		}

		/**
		 * Render the postal code column
		 * @param array $item
		 * @return string
		 */
		protected function column_postal_code( $item ) {
			$output = sprintf( '—' );
			if ( isset( $item->postal_code ) ) {
				$output = sprintf(
					'%s',
					esc_html( $item->postal_code )
				);
			}
			return $output;
		}

		/**
		 * Associative array of columns
		 *
		 * @return array
		 */
		function get_columns() {
			$columns = [
				'status'=> __( 'Status', 'simple-paywall' ),
				'name'=> __( 'Name', 'simple-paywall' ),
				'email'	=> __( 'Email', 'simple-paywall' ),
				'city' => __( 'City', 'simple-paywall' ),
				'region' => __( 'State', 'simple-paywall' ),
				'country' => __( 'Country', 'simple-paywall' ),
				'postal_code' => __( 'Postal Code', 'simple-paywall' )
			];
			return $columns;
		}

		/**
		 * Retrieve items
		 *
		 * @param int $per_page
		 * @param int $page_number
		 *
		 * @return mixed
		 */
		public function get_items( $per_page = 10, $page_number = 1 ) {

			// Simple Paywall API Call
			$api = new Simple_Paywall_API();

			$query_params = array(
				'limit' => $per_page,
				'offset' => ( $page_number - 1 ) * $per_page
			);

			// Check if search query is set
			if ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) {
				$query_params['search'] = sanitize_text_field( $_REQUEST['s'] );
			}

			// Check if status=active or status=inactive is set
			if ( isset( $_GET['status'] ) ) {
				$query_params['status'] = Simple_Paywall_Validate::is_value_or_die( sanitize_text_field( $_GET['status'] ), array( 'active', 'inactive' ) );
			}

			$api->method( 'GET' )
				->endpoint( '/users' );

			if ( isset( $query_params ) ) {
				$api->query_string( $query_params );
			}

			$api->call();

			if ( $api->get_code() === 200 ) {
				$this->_data = $api->get_data();
				$this->_count = $api->get_meta()->total;
				return $this->_data;
			}

		}

		/**
		 * Add custom filters for table
		 * @see https://wordpress.stackexchange.com/questions/223552/how-to-create-custom-filter-options-in-wp-list-table
		 */
		protected function get_views() {
			return array(
				'all' => __( '<a class="' . esc_attr( ( ( ! isset( $_GET['status'] ) ) ? 'current' : '' ) ) . '" href="' . esc_url( '?page=simple-paywall-users' ) . '">All</a>', 'simple-paywall' ),
				'active' => __( '<a class="' . esc_attr( ( ( isset( $_GET['status'] ) && sanitize_text_field( $_GET['status'] ) === 'active' ) ? 'current' : '' ) ) . '" href="' . esc_url( '?page=simple-paywall-users&status=active' ) . '">Active</a>', 'simple-paywall' ),
				'inactive' => __( '<a class="' . esc_attr( ( ( isset( $_GET['status'] ) && sanitize_text_field( $_GET['status'] ) === 'inactive' ) ? 'current' : '' ) ) . '" href="' . esc_url( '?page=simple-paywall-users&status=inactive' ) . '">Inactive</a>', 'simple-paywall' )
			);
		}

		/**
		 * Columns to make sortable.
		 *
		 * @return array
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'city' => array( 'city', true ),
				'state' => array( 'state', true ),
				'country' => array( 'country', true ),
			);
			return $sortable_columns;
		}

		/**
		 * Add or override css styles for table
		 */
		function get_table_classes() {
			$css_classes = array(
				'wp-list-table',
				'widefat',
				'fixed',
				'striped'
			);
			return $css_classes;
		}

		/**
		 * Displays when no member data is available
		 */
		public function no_items() {
			_e( 'No users found. If you think you have received this message in error, please try refreshing the page.', 'simple-paywall' );
		}

		/**
		 * Handles data query and filter, sorting, and pagination – all in one!
		 */
		public function prepare_items() {

			$columns = $this->get_columns();

			$sortable_columns = array(
				'first_name' => 'First Name',
				'last_name' => 'Last Name',
				'email' => 'Email',
			);

			$current_page = $this->get_pagenum();

			$per_page = $this->get_items_per_page( 'simple_paywall_users_per_page', 10 );

			$this->items = $this->get_items( $per_page, $current_page );

			$hidden = array();

			$total_items = $this->_count;

			$this->_column_headers = array( $columns, $hidden, $sortable_columns );

			$this->process_bulk_action();

			$this->set_pagination_args( array(
				'total_items' => $total_items, // WE have to calculate the total number of items
				'per_page' => $per_page // WE have to determine how many items to show on a page
			) );

			/*
			$this->_column_headers = $this->get_column_info();
			*/

		}

		/**
		 * Returns the count of records in the database.
		 *
		 * @return null|string
		 */
		public function record_count() {
			return $this->_count;
		}

	}

}
