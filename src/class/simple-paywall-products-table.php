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

if ( ! class_exists( 'Simple_Paywall_Products_Table' ) ) {

	class Simple_Paywall_Products_Table extends Simple_Paywall_WP_List_Table {

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
			parent::__construct(
				array(
				// Singular name of the listed records
				'singular' => __( 'Product', 'simple-paywall' ),
				// Plural name of the listed records
				'plural'	=> __( 'Products', 'simple-paywall' ),
				'ajax' => false
				)
			);
		}

		/**
		 * Add custom filters for table
		 * @see https://wordpress.stackexchange.com/questions/223552/how-to-create-custom-filter-options-in-wp-list-table
		 */
		protected function get_views() {
			$views = array(
				'all' => __( '<a class="' . esc_attr( ( ( ! isset( $_GET['status'] ) ) ? 'current' : '' ) ) . '" href="' . esc_url( '?page=simple-paywall-products' ) . ' ">All</a>', 'simple-paywall' ),
				'active' => __( '<a class="' . esc_attr( ( ( isset( $_GET['status'] ) && $_GET['status'] === 'active' ) ? 'current' : '' ) ) . '" href="' . esc_url ( '?page=simple-paywall-products&status=active' ) . '">Active</a>', 'simple-paywall' ),
				'inactive' => __( '<a class="' . esc_attr( ( ( isset( $_GET['status'] ) && $_GET['status'] === 'inactive' ) ? 'current' : '' ) ) . '" href="' . esc_url( '?page=simple-paywall-products&status=inactive' ) . '">Inactive</a>', 'simple-paywall' ),
			);
			return $views;
		}

		/**
		 * Retrieve products data
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

			// Check if search=query is set
			if ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) {
				$query_params['search'] = sanitize_text_field( $_REQUEST['s'] );
			}

			// Check if status=active is set
			if ( isset( $_GET['status'] ) ) {
				$query_params['status'] = Simple_Paywall_Validate::is_value_or_die( sanitize_text_field( $_GET['status'] ), array( 'active', 'inactive' ) );
			}

			$api->method( 'GET' )
				->endpoint( '/products' );

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
		 * @return null|string
		 */
		public function record_count() {
			return $this->_count;
		}

		/**
		 * Method for name column
		 * @param array $item an array of DB data
		 * @return string
		 */
		protected function column_name( $item ) {
			$output = sprintf(
				'<strong><a href="%s">%s</a></strong><br><code>%s</code>',
				esc_url( '?page=simple-paywall-product&product=' . $item->id . '&tab=details' ),
				esc_html__( $item->name, 'simple-paywall' ),
				esc_html( $item->id )
			);
			return $output;
		}

		/**
		 * Method for type column
		 * @param array $item an array of DB data
		 * @return string
		 */
		protected function column_type( $item ) {
			$output = '';
			if ( $item->type ) {
				$output = sprintf(
					'%s',
					esc_html__( ucfirst( $item->type ), 'simple-paywall' )
				);
			}
			return $output;
		}

		protected function column_description( $item ) {
			$output = sprintf( '—' );
			if ( isset( $item->description ) ) {
				$output = sprintf( '%s', esc_html__( $item->description, 'simple-paywall' ) );
			}
			return $output;
		}

		protected function column_price( $item ) {
			$output = 'See Plans';
			if ( $item->type === 'good' ) {
				if ( isset( $item->price ) ) {
					$output = $item->price;
				} else {
				}
			}
			if ( $item->type === 'service' ) {
				$output = sprintf(
					'<strong><a href="%s">%s</a></strong>',
					esc_url( '?page=simple-paywall-plans' . '&s=' . $item->id ),
					esc_html__( 'See Plans', 'simple-paywall' )
				);
			}
			return $output;
		}

		/**
		 * Associative array of columns
		 * @return array
		 */
		function get_columns() {
			$columns = [
				// 'status' => __()
				'name' => __( 'Product', 'simple-paywall' ),
				'type' => __( 'Type', 'simple-paywall' ),
				'description' => __( 'Description', 'simple-paywall' ),
				'price' => __( 'Price', 'simple-paywall' ),
				'status' => __( '', 'simple-paywall' ),
			];
			return $columns;
		}

		/**
		 * Columns to make sortable.
		 * @return array
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'name' => array( 'name', true ),
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
			$per_page = $this->get_items_per_page( 'simple_paywall_products_per_page', 10 );

			$this->items = $this->get_items( $per_page, $current_page );

			$hidden = array();
			$total_items  = $this->_count;

			$this->_column_headers = array( $columns, $hidden, $sortable_columns );

			$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $per_page
			) );

		}

		/**
		 * Displays when no member data is available
		 */
		public function no_items() {
			_e( 'We could not find any products to display here.', 'simple-paywall' );
		}

	}

}
