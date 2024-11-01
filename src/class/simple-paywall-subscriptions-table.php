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

if ( ! class_exists( 'Simple_Paywall_Subscriptions_Table' ) ) {

	class Simple_Paywall_Subscriptions_Table extends Simple_Paywall_WP_List_Table {

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
				'singular' => __(	'Subscription', 'simple-paywall'	),
				// Plural name of the listed records
				'plural'	=> __( 'Subscriptions', 'simple-paywall' ),
				'ajax' => false
			) );
		}

		public function column_end_date( $item ) {
			return sprintf(
				'<p>%s</p>',
				esc_html( Simple_Paywall_Utility::get_wp_datetime( $item->ended_on ) )
			);
		}

		public function column_plan( $item ) {
			return sprintf(
				'<p><a href="%s">%s</a><br><code>%s</code></p>',
				esc_url( '?page=simple-paywall-plan&plan=' . $item->plan->id . '&tab=details' ),
				esc_html( $item->plan->name ),
				esc_html( $item->plan->id )
			);
		}

		/**
		 * Method for status column
		 * @param array $item
		 * @return string
		 * @see https://flatuicolors.com/palette/defo
		 */
		public function column_status( $item ) {
			if ( isset( $item->status ) ) {
				switch( $item->status ) {
					case 'active':
						return '<div class="status status__active" title="Active"></div>';
						break;
					case 'pending':
						return '<div class="status status__pending" title="Pending"></div>';
						break;
					case 'completed':
						return '<div class="status status__complete" title="Completed"></div>';
						break;
					default:
						return 'n/a';
				}
			}
		}

		public function column_subscription( $item ) {
			return sprintf(
				'<code><a href="%s">%s</a></code>',
				esc_url( '?page=simple-paywall-subscription&subscription=' . $item->id . '&_wpnonce=' . wp_create_nonce( 'simple_paywall_get_subscription' ) ),
				$item->id
			);
		}

		public function column_start_date( $item ) {
			return sprintf(
				'<p>%s</p>',
				esc_html( Simple_Paywall_Utility::get_wp_datetime( $item->started_on ) )
			);
		}

		public function column_user( $item ) {
			return sprintf(
				'<p><a href="%s">%s</a><br><code>%s</code></p>',
				esc_url( '?page=simple-paywall-user&user=' . $item->user->id . '&tab=account' ),
				esc_html( $item->user->first_name . ' ' . $item->user->last_name ),
				esc_html( $item->user->id )
			);
		}

		/**
		 * Add custom filters for table
		 * @see https://wordpress.stackexchange.com/questions/223552/how-to-create-custom-filter-options-in-wp-list-table
		 */
		protected function get_views() {
			return array(
				'all' => __( '<a class="' . esc_attr( ( ( ! isset( $_GET['status'] ) ) ? 'current' : '' ) ) . '" href="' . esc_url( '?page=simple-paywall-subscriptions' ) . '">All</a>', 'simple-paywall' ),
				'active' => __( '<a class="' . esc_attr( ( ( isset( $_GET['status'] ) && sanitize_text_field( $_GET['status'] ) === 'active' ) ? 'current' : '' ) ) . '" href="' . esc_url( '?page=simple-paywall-subscriptions&status=active' ) . '">Active</a>', 'simple-paywall' ),
				'pending' => __( '<a class="' . esc_attr( ( ( isset( $_GET['status'] ) && sanitize_text_field( $_GET['status'] ) === 'pending' ) ? 'current' : '' ) ) . '" href="' . esc_url( '?page=simple-paywall-subscriptions&status=pending' ) . '">Pending</a>', 'simple-paywall' ),
				'completed' => __( '<a class="' . esc_attr( ( ( isset( $_GET['status'] ) && sanitize_text_field( $_GET['status'] ) === 'completed' ) ? 'current' : '' ) ) . '" href="' . esc_url( '?page=simple-paywall-subscriptions&status=completed' ) . '">Completed</a>', 'simple-paywall' )
			);
		}

		/**
		 * Retrieve account’s subscribers data from the database
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

			// If orderby is set
			if ( isset( $_GET['orderby'] ) ) {
				$query_params['orderby'] = Simple_Paywall_Validate::is_value_or_die( sanitize_text_field( $_GET['orderby'] ), array( 'start_date', 'end_date' ) );
				$query_params['order'] = Simple_Paywall_Validate::is_value_or_die( sanitize_text_field( $_GET['order'] ), array( 'asc', 'desc' ) );
			}

			$api->method( 'GET' )
				->endpoint( '/subscriptions' );

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
		 * Returns the count of records in the database.
		 *
		 * @return null|string
		 */
		public function record_count() {
			return $this->_count;
		}

		/**
		 *  Associative array of columns
		 *
		 * @return array
		 */
		public function get_columns() {
			return array(
				'status' => __( 'Status', 'simple-paywall' ),
				'subscription' => __( 'Subscription', 'simple-paywall' ),
				'plan' => __( 'Plan', 'simple-paywall' ),
				'user' => __( 'User', 'simple-paywall' ),
				'start_date' => __( 'Start Date', 'simple-paywall' ),
				'end_date' => __( 'End Date', 'simple-paywall', 'simple-paywall' ),
				'duration' => __( 'Duration', 'simple-paywall', 'simple-paywall' )
			);
		}

		/**
		 * Columns to make sortable.
		 *
		 * @return array
		 */
		public function get_sortable_columns() {
			return array(
				'start_date' => array( 'start_date', true ),
				'end_date' => array( 'end_date', true )
			);
		}

		/**
		 * Provides an associative array containing the bulk action
		 *
		 * @return array
		 */
		public function get_bulk_actions() {
			// $actions = array(
			// 	'bulk-delete' => 'Delete'
			// );
			// return $actions;
		}

		/**
		 * Add or override css styles for table
		 */
		public function get_table_classes() {
			return array(
				'wp-list-table',
				'widefat',
				'fixed',
				'striped'
			);
		}

		public function get_hidden_columns() {

			$hidden_columns = get_user_meta( get_current_user_id(), 'managesimple-paywall_page_simple-paywall-subscriptionscolumnshidden' );

			// Set columns to hide by default, if no options stored in wp_usermeta
			if ( empty( $hidden_columns ) ) {

				// Set hidden columns by default and serialize
				$hidden_columns = array(
					'duration',
					'start_date',
					'end_date'
				);

				// var_dump( serialize( $hidden_columns ) ); die();

				update_user_meta( get_current_user_id(), 'managesimple-paywall_page_simple-paywall-subscriptionscolumnshidden', $hidden_columns );

				return $hidden_columns;

			}

			return $hidden_columns[0];

		}

		/**
		 * Handles data query and filter, sorting, and pagination – all in one!
		 */
		public function prepare_items() {

			$columns = $this->get_columns();
			$sortable_columns = $this->get_sortable_columns();
			$current_page = $this->get_pagenum();
			$per_page = $this->get_items_per_page( 'simple_paywall_subscriptions_per_page', 10 );

			$this->items = $this->get_items( $per_page, $current_page );
			$hidden = $this->get_hidden_columns();

			// $total_items  = self::record_count();
			$total_items  = $this->_count;
			$this->_column_headers = array( $columns, $hidden, $sortable_columns );
			// $this->_column_headers = $this->get_column_info();
			// var_dump( $this->_column_headers ); die();
			// $this->process_bulk_action();
			$this->set_pagination_args( array(
				'total_items' => $total_items, // WE have to calculate the total number of items
				'per_page'    => $per_page // WE have to determine how many items to show on a page
			) );

		}

		/**
		 * @todo Fix this!
		 */
		public function process_bulk_action() {

			// Detect when a bulk action is being triggered
			/*
			if ( 'delete' === $this->current_action() ) {
				// In our file that handles the request, verify the nonce.
				$nonce = esc_attr( $_REQUEST['_wpnonce'] );
				if ( ! wp_verify_nonce( $nonce, 'simple_paywall_delete_customer' ) ) {
					die( 'Go get a life script kiddies' );
				}
				else {
					self::delete_customer( absint( $_GET['customer'] ) );
					wp_redirect( esc_url( add_query_arg() ) );
					exit;
				}
			}

			// If the delete bulk action is triggered

			if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' ) || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' ) ) {
				$delete_ids = esc_sql( $_POST['bulk-delete'] );

				// loop over the array of record IDs and delete them
				foreach ( $delete_ids as $id ) {
					self::delete_customer( $id );
				}

				wp_redirect( esc_url( add_query_arg() ) );
				exit;
			}
			*/
		}

		/**
		 * Style the table
		 */
		function admin_header() {
			echo '<style type="text/css">';
			echo '.wp-list-table .column-checkbox { width: 5%; }';
			echo '.wp-list-table .column-name { width: 40%; }';
			echo '.wp-list-table .column-email { width: 35%; }';
			echo '.wp-list-table .column-status { width: 20%; }';
			echo '</style>';
		}

		/**
		 * Displays when no member data is available
		 */
		public function no_items() {
			_e( 'No subscriptions found. <br>Either you don\'t have any subscriptions yet or there was some problem in loading them. <br><br>Please try refreshing the page if there should be subscriptions here.', 'simple-paywall' );
		}

	}

}
