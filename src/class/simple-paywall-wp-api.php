<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

/**
 * Registers custom rest API endpoints for Simple Paywall plugin
 */
class Simple_Paywall_WP_API {

	private static $_instance = null;

	private	$_version;

	public function __construct() {
		$this->_version = 1;
		$this->add_actions();
	}

	public static function get_instance() {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

	public function add_actions() {
		add_action( 'rest_api_init', array( $this, 'register_simple_paywall_local_api_hooks' ) );
	}

	/**
	 * Registers WP API hooks for Simple Paywall
	 * This feature requires at least WordPress version >= 4.7.0
	 * @since 0.6.0
	 * @return void
	 */
	public function register_simple_paywall_local_api_hooks() {

		/**
		 * GET /wp-json/simple-paywall/v1/hello-world
		 * @since 0.6.0
		 */
		register_rest_route(
			'simple-paywall',
			'/v1/hello-world/',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'hello_world' ),
			)
		);

		/**
		 * POST /wp-json/simple-paywall/v1/auth/user
		 * @since 0.6.0
		 */
		register_rest_route(
			'simple-paywall',
			'/v1/auth/user',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'auth_wp_user' ),
			)
		);

	}

	/** STOP! All functions after this point should listed alphabetically */

	/**
	 * Checks if credentials provided are valid.
	 * We are not actually signing the WP user in here.
	 * @since 0.6.0
	 * @param WP_REST_Request $request The API request received
	 * @return void
	 */
	public function auth_wp_user( WP_REST_Request $request ) {

		$request_body_json = $request->get_body();
		$request_body = json_decode( $request_body_json );

		// Get username
		if ( ! isset( $request_body->username ) ) {
			$response = array(
				'status' => array(
					'code' => 400,
					'error' => true,
					'message' => 'Missing username.'
				)
			);
			wp_send_json( $response, $response['status']['code'] );
		}
		$username = $request_body->username;

		// Get password
		if ( ! isset( $request_body->password ) ) {
			$response = array(
				'status' => array(
					'code' => 400,
					'error' => true,
					'message' => 'Missing password.'
				)
			);
		}
		$password = $request_body->password;

		// Check if user credentials provided are valid
		$user = wp_authenticate( $username, $password );

		// If user credentials are invalid
		if ( is_wp_error( $user ) ) {
			$response = array(
				'status' => array(
					'code' => 401,
					'error' => true,
					'message' => 'Username or password provided does not match.'
				)
			);
			wp_send_json( $response, $response['status']['code'] );
		}

		// If user credentials are valid
		$response = array(
			'status' => array(
				'code' => 200,
				'error' => false,
				'message' => 'OK'
			)
		);
		wp_send_json( $response, $response['status']['code'] );

	}

	public function hello_world() {
		$response = array(
			'status' => array(
				'code' => 200,
				'error' => false,
				'message' => 'Hello, World!'
			)
		);
		wp_send_json( $response, $response['status']['code'] );
	}

}
