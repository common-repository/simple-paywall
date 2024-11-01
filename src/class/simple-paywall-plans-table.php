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

if ( ! class_exists( 'Simple_Paywall_Plans_Table' ) ) {

	class Simple_Paywall_Plans_Table extends Simple_Paywall_WP_List_Table {

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
				'singular' => __( 'Plan', 'simple-paywall' ),
				// Plural name of the listed records
				'plural'	=> __( 'Plans', 'simple-paywall' ),
				'ajax' => false
			) );
		}

		/**
		 * Method for status column
		 * @param array $item
		 * @return string
		 * @see https://flatuicolors.com/palette/defo
		 */
		protected function column_status( $item ) {
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

		protected function column_duration( $item ) {

			$output = '';

			switch ( $item->interval ) {
				case 'hour':
					$output = sprintf(
						'%s',
						esc_html__( $item->interval_count . ' Hour' . ( $item->interval_count > 1 ? 's' : '' ), 'simple-paywall' )
					);
					break;
				case 'day':
					$output = sprintf(
						'%s',
						esc_html__( $item->interval_count . ' Day' . ( $item->interval_count > 1 ? 's' : '' ), 'simple-paywall' )
					);
					break;
				case 'week':
					$output = sprintf(
						'%s',
						esc_html__( $item->interval_count . ' Week' . ( $item->interval_count > 1 ? 's' : '' ), 'simple-paywall' )
					);
					break;
				case 'month':
					$output = sprintf(
						'%s',
						esc_html__( $item->interval_count . ' Month' . ( $item->interval_count > 1 ? 's' : '' ), 'simple-paywall' )
					);
					break;
				case 'year':
					$output = sprintf(
						'%s',
						esc_html__( $item->interval_count . ' Year' . ( $item->interval_count > 1 ? 's' : '' ), 'simple-paywall' )
					);
					break;
			}
			return $output;

		}

		/**
		 * Method for name column
		 *
		 * @param array $item an array of DB data
		 *
		 * @return string
		 */
		protected function column_name( $item ) {
			$output = sprintf(
				'<strong><a href="%s">%s</a></strong><br><code>%s</code>',
				esc_url( '?page=simple-paywall-plan&plan=' . $item->id . '&tab=details' ),
				esc_html__( $item->name, 'simple-paywall' ),
				esc_html( $item->id )
			);
			return $output;
		}

		/**
		 * Method for name column
		 * @param array $item an array of DB data
		 * @return string
		 */
		protected function column_product( $item ) {
			$output = sprintf(
				'<strong><a href="%s">%s</a></strong><br><code>%s</code>',
				esc_url( '?page=simple-paywall-product&product=' . $item->product_id . '&tab=details' ),
				esc_html__( $item->product_name, 'simple-paywall' ),
				esc_html( $item->product_id )
			);
			return $output;
		}

		/**
		 * Method for price column
		 * @param array $item
		 * @return string
		 */
		protected function column_price( $item ) {

			$output = '';

			if ( $item->currency == 'usd' ) {

				$main = substr( $item->price, 0, -2 );
				$fractional = substr( $item->price, -2 );

				// Set main to zero if not set
				if ( ! $main ) {
					$main = 0;
				}

				// Add trailing zero to fractional based on string length
				if ( strlen( $fractional ) === 1 ) {
					$fractional = '0' . $fractional;
				}

				$output = sprintf(
					esc_html( '$' . $main . '.' . $fractional . ' ' . strtoupper( $item->currency ) )
				);

			}

			/**
			 * @todo Expand to include other currencies
			 */

			return $output;

		}

		protected function column_recurring( $item ) {
			$output = '—';
			if ( $item->is_recurring == 1 ) {
				$output = sprintf(
					'<p style="%s"><i style="" class="%s"></i></p>',
					esc_attr( 'text-align: left;' ),
					esc_attr( 'fas fa-check' )
				);
			}
			return $output;
		}

		protected function column_subscribers( $item ) {
			$output = '—';
			if ( isset( $item->active_subscriptions ) && $item->active_subscriptions > 0 ) {
				$output = sprintf(
					'<strong><a href="%s">%s</a></strong>',
					esc_url( '?page=simple-paywall-subscriptions' . '&s=' . $item->id ),
					esc_html( $item->active_subscriptions )
				);
			}
			return $output;
		}


		/**
		 * Associative array of columns
		 * @return array
		 */
		public function get_columns() {
			$columns = array(
				'status' => 'Status',
				'name' => 'Plan',
				'product' => 'Product',
				'price' => 'Price',
				'duration' => 'Duration',
				'recurring'	=> 'Recurring',
				'subscribers' => 'Active Subscribers'
			);
			return $columns;
		}

		public function get_data() {
			return $this->_data;
		}

		/**
		 * Columns to make sortable.
		 *
		 * @return array
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'name' => array( 'name', true ),
				'product' => array( 'product', true ),
				'price' => array( 'price', true ),
			);
			return $sortable_columns;
		}

		/**
		 * Retrieve plans data
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

			// If products filter is set
			if ( isset( $_REQUEST['product'] ) && ! empty( $_REQUEST['product'] ) ) {
				$query_params['product'] = sanitize_text_field( $_REQUEST['product'] );
			}

			// Check if search query is set
			if ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) {
				$query_params['search'] = sanitize_text_field( $_REQUEST['s'] );
			}

			// Check if status=active is set
			if ( isset( $_GET['status'] ) ) {
				switch ( sanitize_text_field( $_GET['status'] ) ) {
					case 'active':
						$query_params['status'] = 'active';
						break;
					case 'inactive':
						$query_params['status'] = 'inactive';
						break;
					default:
						throw new Simple_Paywall_Invalid_Value_Exception( 'The url parameter "status" can only accept the following values: "active", "inactive".' );
				}
			}

			// If orderby set
			if ( isset( $_GET['orderby'] ) ) {
				$query_params['orderby'] = Simple_Paywall_Validate::is_value_or_die( sanitize_text_field( $_GET['orderby'] ), array( 'name', 'price', 'product' ));
 				$query_params['order'] = Simple_Paywall_Validate::is_value_or_die( sanitize_text_field( $_GET['order'] ), array( 'asc', 'desc' ) );
			}

			/**
			 * @todo Do something in the event items are not retrieved.
			 * Better yet, handle this in the API class.
			 */

			$api = new Simple_Paywall_API();
			$api->method( 'GET' )
				->endpoint( '/plans' );

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
		 * Add or override css styles for table
		 */
		protected function get_table_classes() {
			$css_classes = array(
				'wp-list-table',
				'widefat',
				'fixed',
				'striped'
			);
			return $css_classes;
		}

		/**
		 * Add custom filters for table
		 * @see https://wordpress.stackexchange.com/questions/223552/how-to-create-custom-filter-options-in-wp-list-table
		 */
		protected function get_views() {
			$views = array(
				"all" => __( '<a class="' . ( ( ! isset( $_GET['status'] ) ) ? 'current' : '' ) . '" href="?page=simple-paywall-plans">All</a>', 'simple-paywall' ),
				"active" => __( "<a class='" . ( ( isset( $_GET['status'] ) && sanitize_text_field( $_GET['status'] ) === 'active' ) ? 'current' : '' ) . "' href='?page=simple-paywall-plans&status=active'>Active</a>", 'simple-paywall' ),
				"inactive" => __( "<a class='" . ( ( isset( $_GET['status'] ) && sanitize_text_field( $_GET['status'] ) === 'inactive' ) ? 'current' : '' ) . "' href='?page=simple-paywall-plans&status=inactive'>Inactive</a>", 'simple-paywall' ),
			);
			return $views;
		}

		/**
		 * Displays when no data is available
		 */
		public function no_items() {
			switch ( true ) {
				// Search
				case ( isset( $_REQUEST['s'] ) ):
					$output = sprintf(
						'No plans found for the search term: %s',
						sanitize_text_field( $_REQUEST['s'] )
					);
					esc_html__e( $output, 'simple-paywall' );
					break;
				// Default
				default:
					esc_html__e( 'No plans found. Please try refreshing your page if you were expecting plans and think you received this message in error.', 'simple-paywall' );
					break;
			}
		}

		/**
		 * Handles data query and filter, sorting, and pagination – all in one!
		 */
		public function prepare_items() {
			$columns = $this->get_columns();
			$sortable_columns = $this->get_sortable_columns();
			$current_page = $this->get_pagenum();
			$per_page = $this->get_items_per_page( 'simple_paywall_plans_per_page', 10 );
			try {
				$this->items = $this->get_items( $per_page, $current_page );
			} catch( Simple_Paywall_Invalid_Value_Exception $e ) {
				$e->fatal_error();
			} catch( Exception $e ) {
				$e->fatal_error();
			}
			$hidden = array();
			$total_items  = $this->_count;
			$this->_column_headers = array( $columns, $hidden, $sortable_columns );
			// $this->process_bulk_action();
			$this->set_pagination_args( array(
				'total_items' => $total_items, // WE have to calculate the total number of items
				'per_page' => $per_page // WE have to determine how many items to show on a page
			) );
		}

		/**
		 * Returns the count of records in the database.
		 * @return null|string
		 */
		public function record_count() {
			return $this->_count;
		}

	}

}
