<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

if ( ! class_exists( 'Simple_Paywall' ) ) {
	class Simple_Paywall {

		static $instance;

		protected $_version;

		public $subscribers_obj;

		/**
		 * The slugs for simple paywall pages
		 * @var array
		 */
		public $simple_paywall_pages = array(
			'simple-paywall-add-user',
			'simple-paywall-user',
			'simple-paywall-users',
			'simple-paywall-paywall',
			'simple-paywall-paywalls',
			'simple-paywall-add-paywall',
			'simple-paywall-plans',
			'simple-paywall-plan',
			'simple-paywall-add-plan',
			'simple-paywall-products',
			'simple-paywall-product',
			'simple-paywall-add-product',
			'simple-paywall-subscription',
			'simple-paywall-subscriptions',
			'simple-paywall-add-subscription',
			'simple-paywall-edit-subscription',
			'simple-paywall-settings',
			'simple-paywall-visitor',
			'simple-paywall-visitors',
			'simple-paywall-support'
		);

		public function __construct() {
			$this->_version = SIMPLE_PAYWALL_WORDPRESS_PLUGIN_VERSION;
		}

		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function init() {
			$this->load_dependencies();
			$this->load_config();
			$this->add_hooks();
			$this->load_ajax();
			$this->add_actions();
		}

		/**
		 * Load dependencies
		 */
		private function load_dependencies() {
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'ajax' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'api' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'exception' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'exceptions-invalid-value' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'visitors-table' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'plugin-activator' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'config' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'user' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'users-table' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'form-element' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'form-error' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'form' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'form-v2' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'oauth' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'paywall' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'paywalls-view' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'paywalls-table' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'plan' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'plans-table' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'plans-view' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'product' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'products-table' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'products-view' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'sanitize' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'setting-local' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'settings-view' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'subscription' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'subscriptions-table' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'subscriptions-view' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'tabs' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'utility' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'users-view' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'validate' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'view' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'visitor-activity-table' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'visitors-view' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'wp-list-table' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'wp-session-tokens' . '.php';
			require_once SIMPLE_PAYWALL_PATH . 'src/class/' . SIMPLE_PAYWALL_SLUG . '-' . 'wp-api' . '.php';
		}

		private function add_hooks() {

			// Actions
			add_action( 'admin_menu', array( $this, 'add_simple_paywall_page' ) );
			add_action( 'admin_menu', array( $this, 'hide_simple_paywall_page'), 999 );

			// Filters
			// add_filter( 'plugin_action_links_' . SIMPLE_PAYWALL_BASENAME, array( $this, 'add_get_started_link' ) );
			add_filter( 'plugin_action_links_' . SIMPLE_PAYWALL_BASENAME, array( $this, 'add_settings_link' ) );
			add_filter( 'set-screen-option', array( $this, 'simple_paywall_set_screen_options' ), 10, 3 );

			// Conditional add filter
			if ( isset( $_GET['page'] ) && ( in_array( $_GET['page'], $this->simple_paywall_pages ) ) ) {
				// Update footer admin text
				add_filter( 'admin_footer_text', array( $this, 'simple_paywall_footer_admin' ) );
			}

		}

		public function add_actions() {
			add_action( 'admin_enqueue_scripts', array( $this, 'simple_paywall_admin_styles_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'simple_paywall_styles_scripts' ) );
			add_action( 'wp_head', array( $this, 'noscript_test' ) );
			add_action( 'admin_menu', array( $this, 'register_pages' ) );
			add_action( 'admin_head', array( $this, 'custom_admin_header' ) );
			add_action( 'the_content', array( $this, 'simple_paywall_content_div' ) );
			add_action( 'init', array( $this, 'register_shortcodes' ) );
			add_action( 'wp_footer', array( $this, 'add_simple_paywall_settings' ) );
			// Only run the following actions if the plugin has been activated with Simple Paywall
			if ( Simple_Paywall_Config::get_instance()->get_activated() ) {
				// Enable Simple Paywall for only enabled post types
				$enabled_post_types = Simple_Paywall_Setting_Local::get_instance()->get( 'enabled_post_types' );
				foreach ( $enabled_post_types as $enabled_post_type ) {
					add_filter( 'bulk_actions-edit-' . $enabled_post_type, array( $this, 'register_simple_paywall_bulk_actions' ) );
					add_filter( 'handle_bulk_actions-edit-' . $enabled_post_type, array( $this, 'simple_paywall_handle_bulk_actions' ), 10, 3 );
					add_action( 'manage_' . $enabled_post_type . ( ( $enabled_post_type === 'post' || $enabled_post_type === 'page' ) ? 's' : '' ) . '_custom_column' , array( $this, 'simple_paywall_table_columns' ), 10, 2 );
					add_filter( 'manage_' . $enabled_post_type . ( ( $enabled_post_type === 'post' || $enabled_post_type === 'page' ) ? 's' : '' ) . '_columns' , array( $this, 'add_simple_paywall_table_columns' ) );
				}
				// add_action( 'add_meta_boxes', array( $this, 'my_custom_meta_box' ) );
				$wp_rest_api = new Simple_Paywall_WP_API;
			}
		}

		// public function my_custom_meta_box() {
		//
		// 	$args = array();
		//
		// 	add_meta_box(
		// 		'my_metabox_id',
		// 		__( 'Simple Paywall', 'my_textdomain' ), // Title
		// 		array( $this, 'simple_paywall_custom_meta_box_content' ), // Callback function that renders the content of the meta box
		// 		'post', // Admin page (or post type) to show the meta box on
		// 		'side', // Context where the box is shown on the page
		// 		'high', // Priority within that context
		// 		$args // Arguments to pass the callback function, if any
		// 	);
		//
		// }
		//
		// public function simple_paywall_custom_meta_box_content() {
		// 	echo "Hello, World!";
		// }

		public function simple_paywall_table_columns( $column, $post_id ) {
			$paywalls = Simple_Paywall_Paywall::get_instance()->get_data();
			$products = Simple_Paywall_Product::get_instance()->get_data();
			switch ( $column ) {
				case 'paywall':
					$paywallContent = array();
					foreach ( $paywalls as $paywall ) {
						// Return if paywall has no content
						if ( ! isset( $paywall->content ) ) {
							echo '—';
							return;
						}
						$paywallContent = array_flip( $paywall->content );
						if ( isset( $paywallContent[$post_id] ) ) {
							$output = sprintf(
								'<a href="%s">%s</a><br /><code>%s</code>',
								esc_url( '?page=simple-paywall-paywall&paywall=' . $paywall->id . '&tab=details' ),
								esc_html( $paywall->name ),
								esc_html( $paywall->id )
							);
							echo $output;
							return;
						}
					}
					// Return empty if nothing found
					echo '—';
					break;
				case 'product':
					$productContent = array();
					$count = 0;
					foreach ( $products as $product ) {
						// Return if product has no content
						if ( ! isset( $product->content ) ) {
							echo '—';
							return;
						}
						$productContent = array_flip( $product->content );
						if ( isset( $productContent[$post_id] ) ) {
							echo ( $count > 0 ) ? '<br />' : '';
							echo sprintf(
								'<a href="%s">%s</a><br /><code>%s</code>',
								esc_url( '?page=simple-paywall-product&product=' . $product->id . '&tab=details' ),
								esc_html( $product->name ),
								esc_html( $product->id )
							);
							$count++;
						}
					}
					// Return empty if nothing found
					echo ( $count === 0 ) ? '—' : '';
					break;
			}
		}

		public function add_simple_paywall_table_columns( $columns ) {
			return array_merge( $columns, array(
				'paywall' => __( 'Paywall<br /> <span style="font-size: 12px;">(Simple Paywall)</span>', 'simple-paywall' ),
				'product' => __( 'Product(s)<br /> <span style="font-size: 12px;">(Simple Paywall)</span>', 'simple-paywall' ),
			) );
		}

		/**
		 * Define and add the bulk actions to the select dropdown list
		 * @param array $bulk_actions The list of bulk actions
		 * @return array $bulk_actions The bulk actions
		 */
		public function register_simple_paywall_bulk_actions( $bulk_actions ) {

			// Paywall actions
			$paywalls = Simple_Paywall_Paywall::get_instance()->get_collection();
			if ( isset( $paywalls ) && ! empty( $paywalls ) ) {
				foreach ( $paywalls as $paywall ) {
					$bulk_actions['simple_paywall_bulk_action_add_to_paywall_' . $paywall->id] = __( 'Simple Paywall - Add to Paywall - ' . $paywall->name . ' (' . $paywall->id . ')', 'simple-paywall');
				}
			}
			$bulk_actions['simple_paywall_bulk_action_remove_from_paywall'] = __( 'Simple Paywall - Remove from Paywall', 'simple-paywall');

			// Product actions
			$products = Simple_Paywall_Product::get_instance()->get_collection();
			if ( isset( $products ) && ! empty( $products ) ) {
				foreach ( $products as $product ) {
					$bulk_actions['simple_paywall_bulk_action_add_to_product_' . $product->id] = __( 'Simple Paywall - Add to Product - ' . $product->name . ' (' . $product->id . ')', 'simple-paywall');
				}
			}
			$bulk_actions['simple_paywall_bulk_action_remove_from_products'] = __( 'Simple Paywall - Remove from Product(s)', 'simple-paywall');
			return $bulk_actions;

		}

		/**
		 * Handle bulk actions for Simple Paywall
		 * @param [type] $redirect
		 * @param string $doaction The action being performed, set by $bulk_actions array in register_simple_paywall_bulk_actions()
		 * @param [type] $object_ids [description]
		 * @return
		 */
		public function simple_paywall_handle_bulk_actions( $redirect_to, $action, $post_ids ) {

			// Add to paywall
			if ( strpos( $action, 'simple_paywall_bulk_action_add_to_paywall_wall_' ) !== false ) {
				$paywall_id = str_replace( 'simple_paywall_bulk_action_add_to_paywall_', '', $action );
				Simple_Paywall_Paywall::get_instance()->add_content( $paywall_id, $post_ids );
			}

			// Remove from paywall
			if ( $action === 'simple_paywall_bulk_action_remove_from_paywall' ) {
				Simple_Paywall_Paywall::get_instance()->remove_content( $post_ids );
			}

			// Add to product
			if ( strpos( $action, 'simple_paywall_bulk_action_add_to_product_prod_' ) !== false ) {
				$product_id = str_replace( 'simple_paywall_bulk_action_add_to_product_', '', $action );
				Simple_Paywall_Product::get_instance()->add_content( $product_id, $post_ids );
			}

			// Remove content from all products
			if ( $action === 'simple_paywall_bulk_action_remove_from_products' ) {
				Simple_Paywall_Product::get_instance()->remove_content( $post_ids );
			}

			// Set admin notice
			$redirect_to = add_query_arg( 'simple_paywall_bulk_action_add_to_paywall_results', count( $post_ids ), $redirect_to );

			return $redirect_to;

		}

		public function simple_paywall_bulk_action_add_to_paywall_notice() {
			// if ( isset( $_REQUEST['simple_paywall_bulk_action_add_to_paywall_results'] ) ) {
			// $paywall_id = sanitize_text_field( $_REQUEST['simple_paywall_bulk_action_add_to_paywall_results'] );
			//
			// echo '<div id="message" class="' . ( $updated_products_count > 0 ? 'updated' : 'error' ) . '">';
			//
			// if ( $updated_products_count > 0 ) {
			// echo '<p>' . __( 'Updated ' . $updated_products_count . '!' , 'simple-paywall' ) . '</p>';
			// } else {
			// echo '<p>' . __( 'Not updated!', 'simple-paywall' ) . '</p>';
			// }
			//
			// echo '</div>';
			// }
		}

		public function register_shortcodes() {}

		/**
		 * This wraps the_content() in a div.simple-paywall-content and creates a hook
		 * for the restriction to take place.
		 * @param string $content The content of the page or post
		 * @return string $content The content of the page or post wrapped in div.simple-paywall-content
		 */
		public function simple_paywall_content_div( $content ) {
			return '<div class="simple-paywall-content">' . $content . '</div>';
		}

		public function add_simple_paywall_settings() {
			$site_url = get_site_url();
			$site_url = str_replace( 'http://', '', $site_url );
			$site_url = str_replace( 'https://', '', $site_url );
			?>
			<script type="text/javascript">
				window.simplePaywallSettings = {"site":"<?php echo esc_url( $site_url ); ?>"};
			</script>
		<?php }

		public function custom_admin_header() {

			$page = ( isset( $_GET['page'] ) ) ? sanitize_text_field( $_GET['page'] ) : false;

			// Style columns for "Products" page
			if ( $page === 'simple-paywall-products' ) {
				$output_css = '<style type="text/css">';
				$output_css .= '.wp-list-table th.column-name { width: 250px; padding-left: 0; }';
				$output_css .= '.wp-list-table th.column-name a { padding-left: 1.5em; }';
				$output_css .= '.wp-list-table td.column-name { width: auto; padding-left: 1.5em; }';
				$output_css .= '.wp-list-table th.column-type { width: 50px; }';
				$output_css .= '</style>';
				echo $output_css;
				return;
			}

			// Style columns for "Plans" page
			if ( $page === 'simple-paywall-plans' ) {
				$output_css = '<style type="text/css">';
				$output_css .= '.wp-list-table th.column-name { width: 250px; padding-left: 0; }';
				$output_css .= '.wp-list-table th.column-name a { padding-left: 1.5em; }';
				$output_css .= '.wp-list-table td.column-name { width: auto; padding-left: 1.5em; }';
				$output_css .= '</style>';
				echo $output_css;
				return;
			}

			// Style columns for "Users" page
			if ( $page === 'simple-paywall-users' ) {
				$output_css = '<style type="text/css">';
				$output_css .= '.wp-list-table th.column-name { width: 220px; padding-left: 1.5em; }';
				$output_css .= '.wp-list-table th.column-name a { padding-left: 1.5em; }';
				$output_css .= '.wp-list-table td.column-name { width: auto; padding-left: 1.5em; }';
				$output_css .= '</style>';
				echo $output_css;
				return;
			}

			// Style columns for "Paywalls" page
			if ( $page === 'simple-paywall-paywalls' ) {
				$output_css = '<style type="text/css">';
				$output_css .= '.wp-list-table th.column-name { width: 220px; padding-left: 0; }';
				$output_css .= '.wp-list-table th.column-name a { padding-left: 1.5em; }';
				$output_css .= '.wp-list-table td.column-name { width: auto; padding-left: 1.5em; }';
				$output_css .= '</style>';
				echo $output_css;
				return;
			}

			return;

		}

		/**
		 * Register pages in the order you want them to display
		 * @return null
		 */
		public function register_pages() {
			if ( Simple_Paywall_Config::get_instance()->get_activated() ) {
				// Register the submenu pages in the order that wish for them to be displayed
				$this->register_submenu_page( 'Simple_Paywall_Visitors_View', 'render_visitors_page', 'visitors', 'simple-paywall', true );
				$this->register_submenu_page( 'Simple_Paywall_Users_View', 'render_users_page', 'users', 'simple-paywall', true );
				$this->register_submenu_page( 'Simple_Paywall_Users_View', 'edit', 'user', null );
				$this->register_submenu_page( 'Simple_Paywall_Users_View', 'render_add_user_page', 'add user', null );
				$this->register_submenu_page( 'Simple_Paywall_Products_View', 'products', 'products', 'simple-paywall', true );
				$this->register_submenu_page( 'Simple_Paywall_Products_View', 'product', 'product', null );
				$this->register_submenu_page( 'Simple_Paywall_Products_View', 'add', 'add product', null );
				$this->register_submenu_page( 'Simple_Paywall_Plans_View', 'plans', 'plans', 'simple-paywall', true );
				$this->register_submenu_page( 'Simple_Paywall_Plans_View', 'plan', 'plan', null );
				$this->register_submenu_page( 'Simple_Paywall_Plans_View', 'add', 'add plan', null );
				$this->register_submenu_page( 'Simple_Paywall_Paywalls_View', 'paywall', 'paywall', null );
				$this->register_submenu_page( 'Simple_Paywall_Paywalls_View', 'paywalls', 'paywalls', 'simple-paywall', true );
				$this->register_submenu_page( 'Simple_Paywall_Paywalls_View', 'add', 'add paywall', null );
				$this->register_submenu_page( 'Simple_Paywall_Subscriptions_View', 'render_subscriptions_page', 'subscriptions', 'simple-paywall', true );
				$this->register_submenu_page( 'Simple_Paywall_Subscriptions_View', 'add_new', 'add subscription', null );
				$this->register_submenu_page( 'Simple_Paywall_Subscriptions_View', 'subscription', 'subscription', null );
				$this->register_submenu_page( 'Simple_Paywall_Visitors_View', 'render_visitor_page', 'visitor', null, true );
			}
			$this->register_submenu_page( 'Simple_Paywall_Settings_View', 'page_settings', 'settings' );
		}

		private function register_submenu_page( $callback_class, $callback_function, $slug, $parent_slug = 'simple-paywall', $has_screen_options = false ) {

			/** @todo Add a way to prepend &#160; &#8212; for "child pages" in the menu */

			// Format title
			$title = ucwords( $slug );

			// Format slug
			$slug = strtolower( $slug ); // Lowercase
			$slug = str_replace( ' ', '-', $slug ); // Replace spaces with dashes

			/** @see https://developer.wordpress.org/reference/functions/add_submenu_page/ */

			// Get instance of callback class
			$class = $callback_class::get_instance();

			if ( $has_screen_options ) {
				$hook = add_submenu_page(
					$parent_slug, // $parent_slug
					$title . ' - Simple Paywall', // $page_title
					$title, // $menu_title
					'administrator', // $capability
					'simple-paywall-' . $slug, // $menu_slug
					array( $class, $callback_function )	// $function
				);
				add_action( "load-$hook", array( $this, $slug . '_screen_options' ) );
			} else {
				$hook = add_submenu_page(
					$parent_slug, // $parent_slug
					$title . ' - Simple Paywall', // $page_title
					$title, // $menu_title
					'administrator', // $capability
					'simple-paywall-' . $slug, // $menu_slug
					array( $class, $callback_function )	// $function
				);
			}

		}

		/**
		 * Set screen options for pages
		 * @see https://chrismarslender.com/2012/01/26/wordpress-screen-options-tutorial/
		 */
		public function simple_paywall_set_screen_options( $status, $option, $value ) {

			// Limit max number allowed for pagination option
			$pagination_options = array(
				'simple_paywall_paywalls_per_page',
				'simple_paywall_plans_per_page',
				'simple_paywall_products_per_page',
				'simple_paywall_users_per_page',
				'simple_paywall_subscriptions_per_page',
				'simple_paywall_visitors_per_page',
				'simple_paywall_visitor_activity_log_entries_per_page'
			);

			if ( in_array( $option, $pagination_options ) ) {
				if ( $value > 100 ) {
					$value = 100;
				}
			}

			// Save options
			if ( 'simple_paywall_paywalls_per_page' == $option ) return $value;
			if ( 'simple_paywall_plans_per_page' == $option ) return $value;
			if ( 'simple_paywall_products_per_page' == $option ) return $value;
			if ( 'simple_paywall_users_per_page' == $option ) return $value;
			if ( 'simple_paywall_subscriptions_per_page' == $option ) return $value;
			if ( 'simple_paywall_visitors_per_page' == $option ) return $value;
			if ( 'simple_paywall_visitor_activity_log_entries_per_page' == $option ) return $value;

			 return $status;

		}

		public function paywalls_screen_options() {

			/**
			 * Pagination
			 */
			$columnsOption = 'per_page';
			$columnsOptionArgs = array(
				'label' => 'Number of items per page:',
				'default' => 10,
				'option' => 'simple_paywall_paywalls_per_page'
			);

			add_screen_option( $columnsOption, $columnsOptionArgs );

			/**
			 * Toggle Columns
			 */
			$paywallListTable = new Simple_Paywall_Paywalls_Table();

		}

		public function plans_screen_options() {

			// Pagination
			$columnsOption = 'per_page';
			$columnsOptionArgs = array(
				'label' => 'Number of plans per page:',
				'default' => 10,
				'option' => 'simple_paywall_plans_per_page'
			);

			add_screen_option( $columnsOption, $columnsOptionArgs );

			// Toggle Columns
			$planListTable = new Simple_Paywall_Plans_Table();

		}

		public function products_screen_options() {

			// Pagination
			$columnsOption = 'per_page';
			$columnsOptionArgs = array(
				'label' => 'Number of items per page:',
				'default' => 10,
				'option' => 'simple_paywall_products_per_page'
			);

			add_screen_option( $columnsOption, $columnsOptionArgs );

			// Toggle Columns
			$productListTable = new Simple_Paywall_Products_Table();

		}

		public function users_screen_options() {

			// Pagination
			$columns_option = 'per_page';
			$columns_option_args = array(
				'label' => 'Number of users per page:',
				'default' => 10,
				'option' => 'simple_paywall_users_per_page'
			);

			add_screen_option( $columns_option, $columns_option_args );

			// Toggle Columns
			$userListTable = new Simple_Paywall_Users_Table();

		}

		public function subscriptions_screen_options() {

			// Pagination
			$columns_option = 'per_page';
			$columns_option_args = array(
				'label' => 'Number of subscriptions per page:',
				'default' => 10,
				'option' => 'simple_paywall_subscriptions_per_page'
			);

			add_screen_option( $columns_option, $columns_option_args );

			// Toggle Columns
			$subscriptionListTable = new Simple_Paywall_Subscriptions_Table();

		}

		public function visitor_screen_options() {

			// Pagination
			$columns_option = 'per_page';
			$columns_option_args = array(
				'label' => 'Number of activity log entries per page:',
				'default' => 10,
				'option' => 'simple_paywall_visitor_activity_log_entries_per_page'
			);

			add_screen_option( $columns_option, $columns_option_args );

			// Toggle Columns
			$visitorListTable = new Simple_Paywall_Visitor_Activity_Table();

		}

		public function visitors_screen_options() {

			// Pagination
			$columns_option = 'per_page';
			$columns_option_args = array(
				'label' => 'Number of visitors per page:',
				'default' => 10,
				'option' => 'simple_paywall_visitors_per_page'
			);

			add_screen_option( $columns_option, $columns_option_args );

			// Toggle Columns
			$visitorListTable = new Simple_Paywall_Visitors_Table();

		}

		private function load_ajax() {
			$ajax = new Simple_Paywall_AJAX();
		}

		private function load_config() {
			Simple_Paywall_Config::get_instance();
		}

		public function noscript_test() { ?>
			<noscript>
				<div style="position: fixed; top: 0px; left: 0px; z-index: 3000; height: 100%; width: 100%; background-color: #FFFFFF">
					<p style="margin-left: 10px">For full functionality of this site it is necessary to enable JavaScript.
 Here are the <a href="https://www.enable-javascript.com/" target="_blank">
 instructions how to enable JavaScript in your web browser</a>.</p>
				</div>
				<style>body { display: none; }</style>
			</noscript>
		<?php }

		/**
		 * Load scripts for admin dashboard
		 * @see https://developer.wordpress.org/reference/functions/wp_enqueue_script/
		 * @return void
		 */
		public function simple_paywall_admin_styles_scripts() {

			// Simple Paywall Plugin's global scripts and styles
			wp_enqueue_style( 'simple-paywall.min.css', SIMPLE_PAYWALL_URL . '/public/css/simple-paywall' . ( SIMPLE_PAYWALL_ENV === 'production' ? '.' . SIMPLE_PAYWALL_WORDPRESS_PLUGIN_VERSION : '' ) . '.min.css', null, SIMPLE_PAYWALL_WORDPRESS_PLUGIN_VERSION );

			if ( isset( $_GET['page'] ) && ( in_array( $_GET['page'], $this->simple_paywall_pages ) ) ) {

				/**
				 * jQuery
				 * v1.12.4 is used as of WordPress 4.6
				 */
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'jquery-form' );

				/**
				 * multiselect.js v0.9.12
				 * jQuery plugin for user-friendlier drop-in replacement for the standard select
				 * with multiple attribute activated
				 * @see https://github.com/lou/multi-select
				 * @see http://loudev.com/
				 */
				wp_enqueue_script( 'multi-select.min.js', SIMPLE_PAYWALL_URL . '/public/js/vendor/' . 'jquery.multi-select.min.js', array( 'jquery' ), null, true );
				wp_enqueue_style( 'multi-select.min.css', SIMPLE_PAYWALL_URL . '/public/css/vendor/' . 'multi-select.min.css' );

				/**
				 * quicksearch.js v2.4.0
				 * jQuery plugin for filtering large data sets with user input
				 * @see https://github.com/riklomas/quicksearch
				 */
				wp_enqueue_script( 'quicksearch.min.js', SIMPLE_PAYWALL_URL . '/public/js/vendor/' . 'jquery.quicksearch.min.js', array( 'jquery' ), null, true );

				/**
				 * chosen.js v1.8.3
				 * jQuery plugin that makes long, unwieldy select boxes much more user-friendly
				 * @see https://harvesthq.github.io/chosen/
				 */
				wp_enqueue_style( 'chosen.min.css', SIMPLE_PAYWALL_URL . '/public/css/vendor/' . 'chosen.min.css' );
				wp_enqueue_script( 'chosen.jquery.min.js', SIMPLE_PAYWALL_URL . '/public/js/vendor/' . 'chosen.jquery.min.js', array( 'jquery' ), null, true );

				/**
				 * Font Awesome v5.0.8
				 * The web's most popular icon set and toolkit
				 * @see https://fontawesome.com/
				 */
				wp_enqueue_script( 'font-awesome.js', 'https://use.fontawesome.com/releases/v5.0.8/js/all.js' );

				// Simple Paywall Plugin scripts and styles for plugin's admin pages
				wp_enqueue_style( 'simple-paywall.min.css', SIMPLE_PAYWALL_URL . '/public/css/simple-paywall' . ( SIMPLE_PAYWALL_ENV === 'production' ? '.' . SIMPLE_PAYWALL_WORDPRESS_PLUGIN_VERSION : '' ) . '.min.css', null, SIMPLE_PAYWALL_WORDPRESS_PLUGIN_VERSION );
				wp_enqueue_style( 'simple-paywall-admin.min.css', SIMPLE_PAYWALL_URL . '/public/css/simple-paywall-admin' . ( SIMPLE_PAYWALL_ENV === 'production' ? '.' . SIMPLE_PAYWALL_WORDPRESS_PLUGIN_VERSION : '' ) . '.min.css', null, SIMPLE_PAYWALL_WORDPRESS_PLUGIN_VERSION );
				wp_register_script( 'simple-paywall-admin.min.js', SIMPLE_PAYWALL_URL . '/public/js/simple-paywall-admin' . ( SIMPLE_PAYWALL_ENV === 'production' ? '.' . SIMPLE_PAYWALL_WORDPRESS_PLUGIN_VERSION : '' ) . '.min.js', array( 'jquery' ), SIMPLE_PAYWALL_WORDPRESS_PLUGIN_VERSION, true );

				/**
				 * Enable ajax
				 * @see https://codex.wordpress.org/Function_Reference/wp_localize_script
				 */
				wp_localize_script( 'simple-paywall-admin.min.js', 'simple_paywall_ajax', array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'wp_nonce' => wp_create_nonce( 'simple_paywall_wp_nonce' )
				) );
				wp_enqueue_script( 'simple-paywall-admin.min.js' );

				// Special adjustments
				// Remove WP update notices for Simple Paywall pages
				remove_action( 'admin_notices', 'update_nag', 3 );

			}

		}

		public function simple_paywall_styles_scripts() {

			// Only call if plugin is thought to be activated with Simple Paywall
			if ( Simple_Paywall_Config::get_instance()->api_keys_is_set() ) {

				// Simple Paywall Plugin's global scripts and styles
				wp_enqueue_style( 'simple-paywall.min.css', SIMPLE_PAYWALL_URL . '/public/css/simple-paywall' . ( SIMPLE_PAYWALL_ENV === 'production' ? '.' . SIMPLE_PAYWALL_WORDPRESS_PLUGIN_VERSION : '' ) . '.min.css', null, SIMPLE_PAYWALL_WORDPRESS_PLUGIN_VERSION );

				/**
				 * Simple Paywall
				 * Plugin scripts and styles for front end
				 */
				wp_register_script( 'simple-paywall.min.js', SIMPLE_PAYWALL_URL . '/public/js/simple-paywall' . ( SIMPLE_PAYWALL_ENV === 'production' ? '.' . SIMPLE_PAYWALL_WORDPRESS_PLUGIN_VERSION : '' ) . '.min.js', array( 'jquery' ), null, true );

				$jsObject = array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'simple_paywall_nonce' => wp_create_nonce( 'simple_paywall_nonce' ),
					'post_id' => get_the_id(),
					'is_post' => is_singular() ? 1 : 0,
				);

				/**
				 * Enable AJAX
				 * Plugin scripts for front end
				 */
				wp_localize_script( 'simple-paywall.min.js', 'simple_paywall_ajax', $jsObject );
				wp_enqueue_script( 'simple-paywall.min.js' );

			}

		}

		public function simple_paywall_footer_admin () {
			echo 'Please rate <b>Simple Paywall</b> <a style="color: #FFB900; text-decoration: none;" href="https://wordpress.org/plugins/simple-paywall/" target="_blank">★★★★★</a> on WordPress to help us spread the word. Thank you from the Simple Paywall team!';
		}

		/**
		 * Add Get Started link to plugin on plugins page
		 */
		public function add_get_started_link( $links ) {
			$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=simple_paywall_settings_get_started' ) ) . '">Get Started!</a>';
			array_unshift( $links, $settings_link );
			return $links;
		}

		/**
		 * Add settings link to plugin on plugins page
		 */
		public function add_settings_link( $links ) {
			$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=simple-paywall-settings&tab=api' ) ) . '">Settings</a>';
			array_unshift( $links, $settings_link );
			return $links;
		}

		/**
		 * Hide parent plugin page from menu
		 * @todo May not be necessary to hide it -- check if you can use null for $page_title value instead
		 */
		public function hide_simple_paywall_page() {
			remove_submenu_page( 'simple-paywall', 'simple-paywall' );
		}

		/**
		 * Add parent plugin page
		 * @see https://developer.wordpress.org/reference/functions/add_menu_page/
		 */
		public function add_simple_paywall_page() {
			add_menu_page(
				'Simple Paywall', // $page_title
				'Simple Paywall', // $menu_title
				'administrator', // $capability
				'simple-paywall', // $menu_slug
				'simple_paywall', // $function
				'', // $icon_url
				99 // $position
			);
		}

	}
}
