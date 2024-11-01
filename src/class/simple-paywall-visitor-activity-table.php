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

if ( ! class_exists( 'Simple_Paywall_Visitor_Activity_Table' ) ) {

	class Simple_Paywall_Visitor_Activity_Table extends Simple_Paywall_WP_List_Table {

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
				'singular' => __(	'Visitor', 'simple-paywall' ),
				// Plural name of the listed records
				'plural'	=> __( 'Visitors', 'simple-paywall' ),
				'ajax' => false
			) );
		}

		/**
		 * Method for status column
		 * @param array $item
		 * @return string
		 * @see https://flatuicolors.com/palette/defo
		 */
		protected function column_access( $item ) {
			$output = sprintf( '—' );
			if ( isset( $item->access_granted ) ) {
				switch( $item->access_granted ) {
					case 0:
						$output = sprintf( '<div class="status status__red" title="Denied"></div>' );
						break;
					case 1:
						$output = sprintf( '<div class="status status__green" title="Allowed"></div>' );
						break;
					default:
						/** @todo Throw error */
				}
			}
			return $output;
		}

		protected function column_activity( $item ) {
			$string_limit = 120;
			$post_title = get_the_title( $item->post_id );
			$post_title = ( ( strlen( $post_title ) > $string_limit ) ? substr( $post_title , 0, $string_limit ) . '...' : $post_title );
			return sprintf(
				'Visited: <a href="%s" target="_blank">%s</a>',
				esc_url( get_permalink( $item->post_id ) ),
				esc_html( $post_title )
			);
		}

		/**
		 * Method for date column
		 * @param array $item an array of DB data
		 * @return string
		 */
		protected function column_requested_on( $item ) {
			return esc_html( Simple_Paywall_Utility::get_wp_datetime( $item->requested ) );
		}

		/**
		 * Method for name column
		 *
		 * @param array $item an array of DB data
		 *
		 * @return string
		 */
		protected function column_id( $item ) {
			return sprintf(
				'<code><a href="%s">%s</a></code>',
				esc_url( '?page=simple-paywall-visitor&visitor=' . $item->id . '&tab=profile&_wpnonce=' . wp_create_nonce( 'simple_paywall_get_visitor' ) ),
				esc_html( $item->id )
			);
		}

		/**
		 * Method for name column
		 *
		 * @param array $item an array of DB data
		 *
		 * @return string
		 */
		protected function column_user_agent( $item ) {
			$output = esc_attr( $item->user_agent );
			return $output;
		}

		protected function column_last_seen( $item ) {
			$output = sprintf( '—' );
			if ( isset( $item->activity->requested ) ) {
				$output = Simple_Paywall_Utility::get_wp_datetime( $item->activity->requested );
			}
			return $output;
		}

		/**
		 * Associative array of columns
		 *
		 * @return array
		 */
		public function get_columns() {
			return array(
				'access' => __( 'Access', 'simple-paywall' ),
				'requested_on' => __( 'Requested On', 'simple-paywall' ),
				'activity' => __( 'Activity', 'simple-paywall' ),
				// 'user_agent' => __( 'Device Information', 'simple-paywall' ),
				'ipv4' => __( 'IP Address', 'simple-paywall' )
			);
		}

		/**
		 * Retrieve items you want to display in table
		 *
		 * @param int $per_page
		 * @param int $page_number
		 *
		 * @return mixed
		 */
		public function get_items( $per_page = 10, $page_number = 1 ) {

			$query_params = array(
				'limit' => $per_page,
				'offset' => ( $page_number - 1 ) * $per_page
			);

			// Check if search query is set
			if ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) {
				$query_params['search'] = sanitize_text_field( $_REQUEST['s'] );
			}

			// Simple Paywall API Call
			$api = new Simple_Paywall_API();
			$api->method( 'GET' )
				->endpoint( '/visitors/' . $_GET['visitor'] . '/activity' );

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
		 * Columns to make sortable.
		 *
		 * @return array
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'last_seen' => array( 'last_seen', true ),
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
			_e( 'No visitors yet!', 'simple-paywall' );
		}

		/**
		 * Handles data query and filter, sorting, and pagination – all in one!
		 */
		public function prepare_items() {
			// Get columns
			$columns = $this->get_columns();
			$current_page = $this->get_pagenum();
			$per_page = $this->get_items_per_page( 'simple_paywall_visitor_activity_log_entries_per_page', 10 );
			// Get content
			$this->items = $this->get_items( $per_page, $current_page );
			// Hidden columns
			$hidden = array();
			// Total item count
			$total_items  = $this->_count;
			// Column headers
			$this->_column_headers = array( $columns, $hidden, $this->get_sortable_columns() );
			// Pagination
			$this->set_pagination_args( array(
				'total_items' => $total_items, // WE have to calculate the total number of items
				'per_page'    => $per_page // WE have to determine how many items to show on a page
			) );
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
