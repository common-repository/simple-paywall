<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

if ( ! class_exists( 'Simple_Paywall_Products_View' ) ) {
	class Simple_Paywall_Products_View {

		private static $_instance = null;

		public function __construct() {}

		public static function get_instance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}

		/**
		 * ?page=simple-paywall-add-product
		 */
		public function add() { ?>
			<?php
			// Check if form has submitted
			if ( isset( $_POST['simple_paywall_add_product'] ) && $_POST['simple_paywall_add_product'] === 'true' ) {
				Simple_Paywall_Product::get_instance()->create();
			}
			?>
			<?php Simple_Paywall_View::get_instance()->get_header(); ?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<h1>Add New Product</h1>
					<p>Add a new product that your users or website visitors will be able to subscribe to.</p>
					<hr>
					<?php
					$form = new Simple_Paywall_Form_v2;
					$form->id( 'add-product' );

					// Product type
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'select' )
						->name( 'product_type' )
						->label( 'Product Type' )
						->options( array(
							array( 'service', 'Service' ),
							array( 'good', 'Good' )
						) )
						->description( 'There are two types of products: goods and services. Goods are intended for use with downloads or a single delivery of tangible goods, while services are for Plans (Subscriptions).' )
						->is_required();
					$form->add_element( $element );

					// Product name
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'text' )
						->name( 'product_name' )
						->label( 'Name' )
						->placeholder( 'e.g., All Access' )
						->description( 'Best practices: <br>Use a friendly and descriptive name for the product.<br>Avoid including pricing or duration information into the title for subscription-based products. That will come later.' )
						->is_required();
					$form->add_element( $element );

					// Product description
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'textarea' )
						->name( 'product_description' )
						->label( 'Description' )
						->placeholder( 'e.g., Access to all restricted content' )
						->description( 'Useful to describe what this product is offering.<br><i class="fas fa-info-circle"></i><em>This is for internal use only and is not displayed publicly.' );
					$form->add_element( $element );

					// Product name
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'text' )
						->name( 'product_price' )
						->label( 'Price' )
						->placeholder( 'e.g., $9.99' )
						->is_required();
					$form->add_element( $element );

					// Product currency
					$element = new Simple_Paywall_Form_Element;
					$element->type( 'select' )
						->name( 'product_currency' )
						->label( 'Currency' )
						->options( array(
							array( 'usd', 'USD ($)' )
						) )
						->description( 'The default currency can be adjusted in your <a href="https://app.simplepaywall.com/" target="_blank">Simple Paywall</a> account.' )
						->is_required();
					$form->add_element( $element );

					// Error
					$form->error_message( 'There was an issue adding a new product. Please see below for more details.' );

					// Success
					$form->success_message( 'Successfully added new product.' );

					// Submit
					$form->submit( 'Add New Product' );

					// Display
					$form->display();
					?>
				</div>
			</div>
			<?php
			/**
			 * Handle conditionals for form
			 */
			?>
			<script>
				jQuery.noConflict();
					( function( $ ) {
						// Handle conditionals
						$( function() {
							// Hide price and currency when product type is service
							var productType = $( '#simple-paywall-product-type-input' );
							var productPriceRow = $( '#simple-paywall-product-price-row' );
							var productCurrencyRow = $( '#simple-paywall-product-currency-row' );
							// Set default
							if ( productType.val() === 'service' ) {
								productPriceRow.hide();
								productCurrencyRow.hide();
							}
							// Event listener for product type
							$( productType ).change( function() {
								if ( productType.val() === 'service' ) {
									productPriceRow.hide();
									productCurrencyRow.hide();
								}
								if ( productType.val() === 'good' ) {
									productPriceRow.show();
									productCurrencyRow.show();
								}
							} );
						} );
				} )( jQuery );
			</script>
		<?php	}

		/**
		 * ?page=simple-paywall-product
		 * @return void
		 */
		public function product() { ?>
			<?php Simple_Paywall_View::get_instance()->get_header(); ?>
			<?php
			// Check if form has submitted
			if ( isset( $_POST['simple_paywall_update_product'] ) && $_POST['simple_paywall_update_product'] === 'true' ) {
				Simple_Paywall_Product::get_instance()->update();
			}
			$product = Simple_Paywall_Product::get_instance()->get();
			?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<h1>Product</h1>
					<?php if ( isset( $data ) ) { ?>
						<p><?php echo $data->name; ?></p>
					<?php } ?>
					<?php if ( Simple_Paywall_Product::get_instance()->get_id() !== null ) { ?>
						<p>ID <code><?php echo Simple_Paywall_Product::get_instance()->get_id(); ?></code></p>
					<?php } ?>
				</div>
			</div>
			<?php
			$tabs = new Simple_Paywall_Tabs( array(
				'Details'
			) );
			$tabs->display();
			/**
			 * Tabs
			 */
			switch ( $tabs->get_active_tab() ) {
				case '':
					$this->tab_details();
					break;
				case 'details':
					$this->tab_details();
					break;
				default:
					die( 'Invalid value for tabs url parameter.');
					break;
			}
			?>
		<?php	}

		/**
		 * ?page=simple-paywall-products
		 * @return void
		 */
		public function products() { ?>
			<?php Simple_Paywall_View::get_instance()->get_header(); ?>
			<div class="simple-paywall-container">
				<p><i class="fas fa-info-circle"></i>There are two types of products: goods and services. Goods are intended for use with downloads or a single delivery of tangible goods, while services are for Plans (Subscriptions).</p>
			</div>
			<div class="simple-paywall-container">
				<div class="wrap">
					<h1>Products <a href="?page=simple-paywall-add-product" class="add-new-h2" style="margin-bottom: 1em;">Add New</a></h2></h1>
					<form method="post">
						<?php
						$productListTable = new Simple_Paywall_Products_Table();
						$productListTable->views();
						$productListTable->prepare_items();
						$productListTable->search_box( 'Search', 'search' );
						$productListTable->display();
						?>
					</form>
				</div>
			</div>
			<?php
		}

		/**
		 * The details tab on ?page=simple-paywall-product
		 * @return void
		 */
		private function tab_details() { ?>
			<?php $product = Simple_Paywall_Product::get_instance()->get_data(); ?>
			<div class="simple-paywall-container">
				<div class="wrap">
					<?php
					$form = new Simple_Paywall_Form_v2;
					$form->id( 'update-product' );

					// Created on
					$created_on = new Simple_Paywall_Form_Element;
					$created_on->type( 'text' )
						->name( 'created_on' )
						->label( 'Created On' )
						->value( Simple_Paywall_Utility::get_wp_datetime( $product->created_on ) )
						->description( '*' . Simple_Paywall_Utility::get_wp_timezone() )
						->is_disabled();
					$form->add_element( $created_on );

					// Product type
					$created_on = new Simple_Paywall_Form_Element;
					$created_on->type( 'text' )
						->name( 'type' )
						->label( 'Product Type' )
						->value( ucfirst( $product->type ) )
						->description( 'Product type cannot be changed after its creation.' )
						->is_required()
						->is_disabled();
					$form->add_element( $created_on );

					// Name
					$name = new Simple_Paywall_Form_Element;
					$name->type( 'text' )
						->name( 'product_name' )
						->label( 'Name' )
						->value( $product->name )
						->is_required();
					$form->add_element( $name );

					// Description
					$description = new Simple_Paywall_Form_Element;
					$description->type( 'textarea' )
						->name( 'product_description' )
						->label( 'Description' )
						->placeholder( 'e.g., Access to all restricted content.' )
						->value( $product->description )
						->description( 'Useful to describe what this product is offering. <br>This is for internal use only and is not displayed publicly.</em>' );
					$form->add_element( $description );

					// Submit
					$form->submit( 'Update Product Details' );

					// Feedback
					$form->success_message( 'Successfully updated product.' );
					$form->error_message( 'There was an issue updating the product. Please see below for more details.' );

					// Display
					$form->display();

					?>
				</div>
			</div>
		<?php }

	}
}
