<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

if ( ! class_exists( 'Simple_Paywall_Tabs' ) ) {
	class Simple_Paywall_Tabs {

		private static $_instance = null;

		private 	$_tabs,
					$_active_tab;

		public function __construct( $tabs ) {
			$this->_tabs = $this->replace_empty_spaces_with_dashes( $tabs );
			$this->_tabs = $tabs;
			$this->_active_tab = isset( $_GET[ 'tab' ] ) ? sanitize_text_field( $_GET[ 'tab' ] ) : strtolower( $this->_tabs[0] );
		}

		public static function getInstance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Get any additional parameters besides 'page' and 'tab' and append them to url
		 * @return string
		 */
		public function custom_parameters() {
			$params = $_GET;
			unset( $params['page'] );
			unset( $params['tab'] );
			$appending = '';
			foreach ( $params as $param => $value ) {
				$appending .= '&' . $param . '=' . sanitize_text_field( $value );
			}
			return $appending;
		}

		/**
		 * Format tab names for storing
		 * Replace " " with "-"
		 * Lowercase words
		 * @return string
		 */
		public function replace_empty_spaces_with_dashes( $tabs ) {
			foreach ( $tabs as $tab => $value ) {
				$tabs[$tab] = strtolower( $value );
				$tabs[$tab] = str_replace( ' ', '-', $value );
			}
			return $tabs;
		}

		/**
		 * Format tab names for display
		 * Replace "-" with " "
		 * Uppercase words
		 * @return string
		 */
		public function format( $tab ) {
			$tab = str_replace( '-', ' ', $tab );
			$tab = ucwords( $tab );
			return $tab;
		}

		public function display() { ?>
			<h2 class="nav-tab-wrapper">
			<?php
			$index = 0;
			foreach ( $this->_tabs as $tab ) {
				$slug = strtolower( $tab );
				$slug = str_replace( ' ', '-', $slug );
				$url = sprintf(
					'%sadmin.php?page=%s&tab=%s%s',
					admin_url(),
					sanitize_text_field( $_GET['page']),
					esc_html( $slug ),
					$this->custom_parameters()
				);
				$class = isset( $this->_active_tab ) ? ( ( $this->_active_tab === $slug ) ? 'nav-tab nav-tab-active' : 'nav-tab' ) : 'nav-tab nav-tab-active';
				?>
				<a class="<?php esc_attr_e( $class ); ?>" style="<?php echo esc_attr( ( $index === 0 ) ? 'margin-left: 20px': '' ); ?>" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( $tab ); ?></a>
				<?php $index++; ?>
			<?php } ?>
			</h2>
			<?php
		}

		public function get_active_tab() {
			return $this->_active_tab;
		}

	}
}
