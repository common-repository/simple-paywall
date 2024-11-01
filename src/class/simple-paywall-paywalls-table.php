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

if ( ! class_exists( 'Simple_Paywall_Paywalls_Table' ) ) {

	class Simple_Paywall_Paywalls_Table extends Simple_Paywall_WP_List_Table {

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
				'singular' => __( 'Paywall', 'simple-paywall' ),
				// Plural name of the listed records
				'plural'	=> __( 'Paywalls', 'simple-paywall' ),
				'ajax' => false
			) );
		}

		/**
		 * Add custom filters for table
		 * @see https://wordpress.stackexchange.com/questions/223552/how-to-create-custom-filter-options-in-wp-list-table
		 */
		protected function get_views() {
			$views = array(
				'all' => __( '<a class="' . esc_attr( ( ( ! isset( $_GET['status'] ) ) ? 'current' : '' ) ) . '" href="' . esc_url( '?page=simple-paywall-paywalls' ) . '">All</a>', 'simple-paywall' ),
				'active' => __( '<a class="' . esc_attr( ( ( isset( $_GET['status'] ) && sanitize_text_field( $_GET['status'] ) === 'active' ) ? 'current' : '' ) ) . '" href="' . esc_url( '?page=simple-paywall-paywalls&status=active' ) . '">Active</a>', 'simple-paywall' ),
				'inactive' => __( '<a class="' . esc_attr( ( ( isset( $_GET['status'] ) && sanitize_text_field( $_GET['status'] ) === 'inactive' ) ? 'current' : '' ) ) . '" href="' . esc_url( '?page=simple-paywall-paywalls&status=inactive' ) . '">Inactive</a>', 'simple-paywall' )
			);
			return $views;
		}

		/**
		 * Retrieve paywalls from Simple Paywall API
		 *
		 * @param int $per_page
		 * @param int $page_number
		 *
		 * @return mixed
		 */
		public function get_items( $per_page = 10, $page_number = 1 ) {

			// Set limit and offset by default values set in screen options
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

			// Check if orderby is set
			if ( isset( $_GET['orderby'] ) ) {
				$query_params['orderby'] = sanitize_text_field( $_GET['orderby'] );
				$query_params['order'] = Simple_Paywall_Validate::is_value_or_die( sanitize_text_field( $_GET['order'] ), array( 'asc', 'desc' ) );
			}

			$api = new Simple_Paywall_API();
			$api->method( 'GET' )
				->endpoint( '/paywalls' );

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
		 * Method for name column
		 *
		 * @param array $item an array of DB data
		 *
		 * @return string
		 */
		function column_name( $item ) {
			return sprintf(
				'<strong><a href="%s">%s</a></strong><br><code>%s</code>',
				esc_url( '?page=simple-paywall-paywall&paywall=' . $item->id . '&tab=details' ),
				esc_html__( $item->name, 'simple-paywall' ),
				esc_html( $item->id )
			);
		}

		/**
		 * Method for type column
		 * @param array $item an array of API data
		 * @return string
		 */
		function column_type( $item ) {
			if ( $item->type === 'soft' ) {
				$item->type	= 'metered';
			}
			return sprintf(
				esc_html__( ucfirst( $item->type ), 'simple-paywall' )
			);
		}

		/**
		 * Method for limit column
		 * @param array $item an array of DB data
		 * @return string
		 */
		function column_limit( $item ) {
			if ( ! isset( $item->limit_count ) ) {
				$item->limit_count = 'n/a';
			}
			$output = sprintf(
				esc_html( $item->limit_count )
			);
			return $output;
		}

		/**
		 * Method for limit duration column
		 * @param array $item an array of DB data
		 * @return string
		 */
		function column_limit_cycle( $item ) {
			$output = '—';
			if ( $item->type === 'soft' || $item->type === 'metered' ) {
				switch ( $item->limit_interval ) {
					case 'hour':
						$output = sprintf(
							'%s',
							esc_html__( $item->limit_interval_count . ' Hour' . ( $item->limit_interval_count > 1 ? 's' : '' ), 'simple-paywall' )
						);
						break;
					case 'day':
						$output = sprintf(
							'%s',
							esc_html__( $item->limit_interval_count . ' Day' . ( $item->limit_interval_count > 1 ? 's' : '' ), 'simple-paywall' )
						);
						break;
					case 'week':
						$output = sprintf(
							'%s',
							esc_html__( $item->limit_interval_count . ' Week' . ( $item->limit_interval_count > 1 ? 's' : '' ), 'simple-paywall' )
						);
						break;
					case 'month':
						$output = sprintf(
							'%s',
							esc_html__( $item->limit_interval_count . ' Month' . ( $item->limit_interval_count > 1 ? 's' : '' ), 'simple-paywall' )
						);
						break;
					case 'year':
						$output = sprintf(
							'%s',
							esc_html__( $item->limit_interval_count . ' Year' . ( $item->limit_interval_count > 1 ? 's' : '' ), 'simple-paywall' )
						);
						break;
				}
			}
			return $output;
		}

		/**
		 * Method for limit duration column
		 * @param array $item an array of DB data
		 * @return string
		 */
		function column_limit_is_rolling( $item ) {
			$output = '—';
			if ( $item->limit_is_rolling ) {
				$output = sprintf(
					'<p style="%s"><i class="%s"></i></p>',
					esc_attr( 'text-align: left;' ),
					esc_attr( 'fas fa-check' )
				);
			}
			return $output;
		}

		/**
		 * Method for limit duration column
		 * @param array $item an array of DB data
		 * @return string
		 */
		function column_user_only( $item ) {
			$output = '—';
			if ( $item->user_only ) {
				$output = sprintf(
					'<p style="%s"><i style="" class="%s"></i></p>',
					esc_attr('text-align: left;'),
					esc_attr('fas fa-check')
				);
			}
			return $output;
		}

		/**
		 * Method for status column
		 * @param array $item
		 * @return string
		 */
		function column_status( $item ) {
			if ( isset( $item->status ) ) {
				switch( $item->status ) {
					case 'active':
						return '<div class="status status__active" title="Active"></div>';
						break;
					case 'inactive':
						return '<div class="status status__inactive" title="Inactive"></div>';
					default:
						return 'n/a';
				}
			}
		}

		/**
		 *  Associative array of columns
		 *
		 * @return array
		 */
		function get_columns() {
			$columns = array(
				'status' => __( 'Status', 'simple-paywall' ),
				'name' => __( 'Name', 'simple-paywall' ),
				'type' => __( 'Type', 'simple-paywall' ),
				'limit' => __( 'Limit', 'simple-paywall' ),
				'limit_cycle' => __( 'Limit Cycle', 'simple-paywall' ),
				'limit_is_rolling' => __( 'Rolling Limit <i title="A rolling limit starts count from the time the first content item is consumed." class="fas fa-info-circle"></i>', 'simple-paywall' ),
				'user_only' => __( 'User Account Required', 'simple-paywall' )
			);
			return $columns;
		}

		/**
		 * Columns to make sortable.
		 * @return array
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'name' => array( 'name', true ),
				'type' => array( 'type', true ),
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
		 * Handles data query and filter, sorting, and pagination – all in one!
		 */
		public function prepare_items() {

			$columns = $this->get_columns();

			$sortable_columns = $this->get_sortable_columns();

			$current_page = $this->get_pagenum();

			$per_page = $this->get_items_per_page( 'simple_paywall_paywalls_per_page', 10 );

			$this->items = $this->get_items( $per_page, $current_page );

			$hidden = array();
			$total_items  = $this->_count;

			$this->_column_headers = array( $columns, $hidden, $sortable_columns );

			$this->process_bulk_action();

			$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page' => $per_page
			) );

		}

		/**
		 * Displays when no member data is available
		 */
		public function no_items() {
			_e( 'There are no paywalls to display here. Add a new one, or please try refreshing the pageif you think there should be something here.', 'simple-paywall' );
		}

	}

}
