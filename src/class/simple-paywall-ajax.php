<?php

/**
* Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
* Released under the GPLv2 or later license
* https://opensource.org/licenses/gpl-license.php
 */

/**
 * The file that handles most ajax calls for the Simple Paywall plugin
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link https://simplepaywall.com/
 * @since 0.9.0
 *
 * @package Simple_Paywall
 * @subpackage Plugin_Name/includes
 * @author Simple Paywall <support@simplepaywall.com>
 */

if ( ! class_exists( 'Simple_Paywall_AJAX' ) ) {
	class Simple_Paywall_AJAX {

		public function __construct() {
			$this->add_hooks();
		}

		private function add_hooks() {
			add_action( 'wp_ajax_nopriv_simple_paywall_auth_view', array( $this, 'simple_paywall_auth_view' ) );
			add_action( 'wp_ajax_simple_paywall_auth_view', array( $this, 'simple_paywall_auth_view' ) );
			add_action( 'wp_ajax_nopriv_simple_paywall_hello_world', array( $this, 'simple_paywall_hello_world' ) );
			add_action( 'wp_ajax_activate_simple_paywall', array( $this, 'activate_simple_paywall' ) );
			add_action( 'wp_ajax_deactivate_simple_paywall', array( $this, 'deactivate_simple_paywall' ) );
			add_action( 'wp_ajax_nopriv_simple_paywall_sign_out', array( $this, 'sign_out' ) );
			add_action( 'wp_ajax_nopriv_simple_paywall_sign_in', array( $this, 'sign_in' ) );
		}

		public function simple_paywall_hello_world() {
			wp_send_json( 'Hello, World!', 200 );
		}

		public function activate_simple_paywall() {

			// Check nonce
			check_ajax_referer( 'simple_paywall_wp_nonce', 'activate_simple_paywall_wp_nonce' );

			/** Sanitize */

			$simple_paywall_public_api_key = sanitize_text_field( $_POST['simple_paywall_public_api_key'] );
			$simple_paywall_private_api_key = sanitize_text_field( $_POST['simple_paywall_private_api_key'] );

			/** Validate */

			// Check for only alphanumeric characters
			if ( ! Simple_Paywall_Validate::is_alpha_numeric( $simple_paywall_public_api_key ) ) {
				wp_send_json( '', 400 );
				die();
			}

			// Check for only alphanumeric characters
			if ( ! Simple_Paywall_Validate::is_alpha_numeric( $simple_paywall_private_api_key ) ) {
				wp_send_json( '', 400 );
				die();
			}

			// Check that length is 30 characters
			if ( ! Simple_Paywall_Validate::is_length( $simple_paywall_public_api_key, 30 ) ) {
				wp_send_json( '', 400 );
				die();
			}

			// Check that length is 30 characters
			if ( ! Simple_Paywall_Validate::is_length( $simple_paywall_private_api_key, 30 ) ) {
				wp_send_json( '', 400 );
				die();
			}

			// Call Simple Paywall API
			$api = new Simple_Paywall_API();
			$api->method( 'POST' )
				->endpoint( '/oauth/wordpress-plugin' )
				->body( array(
					'public_key' => $simple_paywall_public_api_key,
					'private_key' => $simple_paywall_private_api_key
				) )
				->call();

			/**
			 * Check for a null response object, indicating some error on the API side.
			 * Null is usually a result of the response not being capable of being processed
			 * as an argument in the json_decode() method.
			 */
			if ( $api->get_response() === null ) {
				wp_send_json( 'Something has gone wrong. We received a null response from the Simple Paywall API.', 500 );
				wp_die();
			}

			if ( $api->get_code() === 201 ) {

				// Store API keys
				$api_keys = array(
					'public_key' => $simple_paywall_public_api_key,
					'private_key' => $simple_paywall_private_api_key
				);
				update_option( 'simple_paywall_api_keys', json_encode( $api_keys ) );

				/** Validate keys received from API */

				// Check that access_token contains only alphanumeric characters
				if ( ! Simple_Paywall_Validate::is_alpha_numeric( $api->get_response()->data->access_token ) ) {
					wp_send_json( '', 400 );
					die();
				}

				// Check that access_token length is 256 characters in length
				if ( ! Simple_Paywall_Validate::is_length( $api->get_response()->data->access_token, 256 ) ) {
					wp_send_json( '', 400 );
					die();
				}

				// Check that refresh_token is 256 characters in length
				if ( ! Simple_Paywall_Validate::is_length( $api->get_response()->data->refresh_token, 256 ) ) {
					wp_send_json( '', 400 );
					die();
				}

				// Check that refresh_token contains only alphanumeric characters
				if ( ! Simple_Paywall_Validate::is_alpha_numeric( $api->get_response()->data->refresh_token ) ) {
					wp_send_json( '', 400 );
					die();
				}

				// Store oauth token
				$token = array(
					'access_token' => $api->get_response()->data->access_token,
					'refresh_token' => $api->get_response()->data->refresh_token,
					'expires_on' => $api->get_response()->data->expires_on,
					'token_type' => $api->get_response()->data->token_type,
					'scope' => $api->get_response()->data->scope
				);
				update_option( 'simple_paywall_oauth', json_encode( $token ) );

			} else {
				// Handle error
				wp_send_json( $api->response() );
				die();
			}

			$data = array(
				'message' => 'Successfully activated Simple Paywall plugin.'
			);

			// Success
			wp_send_json( $data, 200 );
			die();

		}

		public function deactivate_simple_paywall() {

			// Check nonce
			check_ajax_referer( 'simple_paywall_wp_nonce', 'deactivate_simple_paywall_wp_nonce' );

			// Set "simple_paywall_api_keys" option to empty string
			update_option( 'simple_paywall_api_keys', '' );

			// Set "simple_paywall_oauth" option to empty string
			update_option( 'simple_paywall_oauth', '' );

			$data = array(
				'message' => 'Successfully deactivated Simple Paywall license.'
			);

			wp_send_json( $data, 200 );

		}

		public function get_ip_address() {
			// Check ip from share internet
			if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) )	{
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )	{
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
			}
			return $ip;
		}

		/**
		 * Return the IP version
		 * @param string $ip The ip address we are determining the version of
		 * @return int
		 */
		public function get_ip_version( $ip ) {
			// ipv4
			if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
				return 4;
			}
			// ipv6
			if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
				return 6;
			}
		}

		/**
		 * Check whether visitor or user is authorized to view the current content
		 * @return void
		 */
		public function simple_paywall_auth_view() {

			check_ajax_referer( 'simple_paywall_nonce', 'wp_nonce' );

			$url = wp_get_referer();
			$post_id = url_to_postid( $url );
			$login_page_id = 19;

			if ( $post_id !== $login_page_id ) {

				// Get and sanitize data
				$device_fingerprint = sanitize_text_field( $_POST['device_fingerprint'] );
				$device_fingerprint_source_json = sanitize_text_field( $_POST['device_fingerprint_source'] );
				$post_id = sanitize_text_field( $_POST['post_id'] );

				/** @todo Consider alternative approach to below */

				// Clean up mess
				$device_fingerprint_source_json = str_replace( '\"', '"', $device_fingerprint_source_json );

				// Rearrange data in $device_fingerprint_source_json array
				$device_fingerprint_source = array();
				$device_fingerprint_source = json_decode( $device_fingerprint_source_json );

				foreach ( $device_fingerprint_source as $source ) {
					$device_fingerprint_source[$source->key] = $source->value;
				}

				/**
				 * get_the_id()
				 * @see https://developer.wordpress.org/reference/functions/get_the_id/
				 */
				$post = get_post( $post_id );

				/**
				 * @todo Consider splitting up calls for /auth/user vs /auth/viewer
				 */

				$body = array(
					'device_fingerprint' => $device_fingerprint,
					'device_fingerprint_source' => $device_fingerprint_source,
					'post_id' => $post->ID,
					'post_type' => $post->post_type,
					'post_title' => $post->post_name,
					'post_name' => $post->title,
					'post_guid' => $post->guid,
					'post_permalink' => get_post_permalink( $post->ID ),
					'post_url' => get_permalink( $post->ID )
				);

				// Set ip and append to body
				$ip_address = $this->get_ip_address();

				// Set ipv4
				if ( $this->get_ip_version( $ip_address ) === 4 ) {
					$body['ipv4'] = $ip_address;
				}

				// Set ipv6
				if ( $this->get_ip_version( $ip_address ) === 6 ) {
					$body['ipv6'] = $ip_address;
				}

				$api = new Simple_Paywall_API();
				$api->method( 'POST' )
					->endpoint( '/auth/view' )
					->body( $body )
					->call();

				/**
				 * Check for a null response object, indicating some error on the API side.
				 * Null is usually a result of the response not being capable of being processed
				 * as an argument in the json_decode() method.
				 */
				if ( $api->get_response() === null ) {
					if ( SIMPLE_PAYWALL_ENV === 'development' ) {
						// Dev mode only. This exposes the raw response from the API.
						wp_send_json( $api->get_response_str(), 500 );
					} else {
						wp_send_json( 'Something has gone wrong. We received a null response from the Simple Paywall API.', 500 );
					}
					wp_die();
				}

				// Return something useful
				wp_send_json( $api->get_response(), $api->get_code() );
				wp_die();

			}

		}

		/**
		 * Sign user into WordPress account from Simple Paywall
		 * @return void
		 */
		public function sign_in() {

			$creds = array(
				'user_login' => $_POST['username'],
				'user_password' => $_POST['password'],
				'remember' => true
			);

			// $user = wp_signon( $creds, false );
			//
			// // check_ajax_referer( 'simple_paywall_nonce', 'wp_nonce' );
			//
			// // $cookies = $_POST['cookies'];
			// // // var_dump($cookies);die();
			// // foreach ( $cookies as $cookie ) {
			// // 	setcookie( $cookie['cookie_name'], urlencode( $cookie['cookie_value'] ), $cookie['cookie_expires'], $cookie['cookie_path'], is_ssl(), false, true );
			// // }
			//
			// wp_send_json( 'sign_in() called', 200 );
			// wp_die();

			// $user = wp_signon( $creds, false );
			$user = wp_authenticate( $creds['user_login'], $creds['user_password'] );

			if ( is_wp_error( $user ) ) {
				wp_send_json( $user->get_error_message() );
			}

			if ( is_ssl() ) {
				$auth_cookie_name = SECURE_AUTH_COOKIE;
				$scheme = 'secure_auth';
			} else {
				$auth_cookie_name = AUTH_COOKIE;
				$scheme = 'auth';
			}

			$expiration = $expire = time() + apply_filters( 'auth_cookie_expiration', 1209600, $user->ID, true );
			// $expiration = time() + apply_filters('auth_cookie_expiration', $seconds, $user->ID, true);

			$wp_sessions = WP_Session_Tokens::get_instance( $user->ID );
			// $wp_sessions = Simple_Paywall_WP_Session_Tokens::get_instance( $user->ID );
			$token = $wp_sessions->create( $expiration );

			$auth_cookie = wp_generate_auth_cookie( $user->ID, $expiration, $scheme, $token );
			$logged_in_cookie = wp_generate_auth_cookie( $user->ID, $expiration, 'logged_in', $token );

			do_action( 'set_auth_cookie', $auth_cookie, $expire, $expiration, $user->ID, $scheme, $token );
			do_action( 'set_logged_in_cookie', $logged_in_cookie, $expire, $expiration, $user->ID, 'logged_in', $token );

			setcookie( $auth_cookie_name, $auth_cookie, $expire, PLUGINS_COOKIE_PATH, COOKIE_DOMAIN, $secure, true );
			setcookie( $auth_cookie_name, $auth_cookie, $expire, ADMINE_COOKIE_PATH, COOKIE_DOMAIN, $secure, true );
			setcookie( LOGGED_IN_COOKIE, $logged_in_cookie, $expire, COOKIEPATH, COOKIE_DOMAIN, false, true );

			wp_send_json( 'sign_in() called', 200 );

		}

		public function sign_out() {

			// Check nonce
			check_ajax_referer( 'simple_paywall_nonce', 'wp_nonce' );

			$api = new Simple_Paywall_API();
			$api->call_legacy( '/auth/user/logout' );
			wp_send_json( $api->response(), 200 );
			wp_die();

		}

	}
}
