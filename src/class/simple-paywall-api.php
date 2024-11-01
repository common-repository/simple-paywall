<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

/**
 * This is a wrapper for using the WordPress wp_remote_get() function
 */
class Simple_Paywall_API {

	private

		/**
		 * @var array $_body The body that will be submitted in api call
		 */
		$_body,

		/**
		 * @var int $_code The response code we received.
		 * Integer
		 */
		$_code,

		/**
		 * array
		 */
		$_cookies = null,

		/**
		 * Sets whether or not the API call returned an error.
		 * bool
		 */
		$_error = false,

		/**
		 * array
		 */
		$_headers,

		/**
		 * @var string $_message The message returned with the status in API call
		 */
		$_message,

		/**
		 * @var string $_method The method we are using in the API call
		 */
		$_method,

		/**
		 * Data of response body, if exists
		 */
		$_data = null,

		/**
		 * Data of response body, if exists
		 */
		$_meta = null,

		/**
		 * string
		 */
		$_api_base_url,

		/**
		 * @var string $_query_string The query params we are appending to the endpoint
		 */
		$_query_string,

		/**
		 * @var string $_response_str The response in its raw form
		 */
		$_response_str,

		/**
		 * @var object $_response The response as an object
		 */
		$_response,

		/**
		 * @var string $_wordpress_plugin_version The version of the Simple Paywall Plugin being used
		 */
		$_wordpress_plugin_version;

	public function __construct() {
		// Set base url for Simple Paywall API
		$this->_api_base_url = SIMPLE_PAYWALL_API;
		$this->set_access_token();
		// Include user session if it is set
		if ( isset( $_COOKIE['simple_paywall_user_session'] ) ) {
			$this->_cookies['simple_paywall_user_session'] = $_COOKIE['simple_paywall_user_session'];
		}
		// Set version of Simple Paywall plugin in use
		$this->_wordpress_plugin_version = SIMPLE_PAYWALL_WORDPRESS_PLUGIN_VERSION;
		$this->_headers['X-Simple-Paywall-WordPress-Plugin-Version'] = $this->_wordpress_plugin_version;
	}

	public function body( $array ) {
		$this->_body = $array;
		return $this;
	}

	/**
	 * Used to handle API calls to Simple Paywall's API.
	 * @uses wp_remote_get()
	 * @param array $args An array containing custom headers, cookies, and/or body to submit along with request.
	 * @return none Response can be accessed via other vars such as $this->_data.
	 */
	public function call_legacy( $endpoint, $args = array(), $return_object = true ) {

		if ( ! isset( $args['method'] ) ) {
			$args['method'] = 'GET';
		}

		// $endpoint = $this->prepare_endpoint( $endpoint );

		if ( isset( $args['body'] ) ) {
			$args['body'] = json_encode( $args['body'] );
			$args['method'] = 'POST';
		}

		// Set $args['headers'] if is it not set
		if ( ! isset( $args['headers'] ) ) {
			$args['headers'] = array();
		}

		// Add $this->_headers to $args['headers']
		if ( isset( $this->_headers ) ) {
			$args['headers'] = array_merge( $this->_headers, $args['headers'] );
		}

		// Set $args['cookies'] if is it not set
		if ( ! isset( $args['cookies'] ) && isset( $this->_cookies ) ) {
			$args['cookies'] = array();
		}

		// Add $this->_cookies to $args['cookies']
		if ( isset( $this->_cookies ) ) {
			$args['cookies'] = array_merge( $this->_cookies, $args['cookies'] );
		}

		// Add URL query params if they are set
		if ( isset( $args['query_params'] ) ) {
			$query_string = '?';
			$query_params_count = count( $args['query_params'] );
			$query_params_index = 1;
			foreach ( $args['query_params'] as $key => $value ) {
				$query_string .= $key . '=' . $value;
				if ( $query_params_index < $query_params_count ) {
					$query_string .= '&';
				}
				$query_params_index++;
			}
		}

		/**
		 * @see https://developer.wordpress.org/reference/functions/wp_remote_get/
		 */
		$api_call = wp_remote_get( $this->_api_base_url . $endpoint . ( isset( $query_string ) ? $query_string : '' ), $args );

		// Convert JSON string to object
		$this->_response = json_decode( $api_call['body'] );

		/**
		 * @todo Update this to handle certain response codes gracefully.
		 */

		/**
		 * Send request again with refresh token in event access token is expired
		 */

		/**
		 * @todo Update this to use new /oauth/refresh endpoint
		 * Consider checking expiration of token locally and determining beforehand
		 * if a new token is required.
		 */

		// Detect if oauth token has expired
		// ...

		// if ( $this->_response->status->message === 'Access token is expired' ) {
		// 	// Add refresh token to headers and submit request again
		// 	$args['headers'] = array_merge( array( "X-Simple-Paywall-Refresh-Token" => Simple_Paywall_Config::getInstance()->get_refresh_token() ), $args['headers'] );
		// 	$api_call = wp_remote_get( $this->_api_base_url . $endpoint, $args );
		// 	// Convert JSON string to object
		// 	$this->_response = json_decode( $api_call['body'] );
		// }

		// Set data if any is returned
		if ( isset( $this->_response->data ) ) {
			$this->_data = $this->_response->data;
		}

		// Set metadata if any is returned
		if ( isset( $this->_response->meta ) ) {
			$this->_meta = $this->_response->meta;
		}

		// Set status code of response
		if ( isset( $this->_response->status->code ) ) {
			$this->_code = $this->_response->status->code;
		}

		// Set error of response if there is one
		if ( isset( $this->_response->status->error ) ) {
			$this->_error = $this->_response->status->error;
		}

	}

	/**
	 * Use to make calls to Simple Paywall's API
	 * @see https://developer.wordpress.org/reference/functions/wp_remote_request/
	 */
	public function call() {

		/** @todo Check that required class properties are set or throw Exception */

		// Method
		$args = [
			'method' => $this->_method
		];

		// Headers
		if ( isset( $this->_headers ) ) {
			$args['headers'] = $this->_headers;
		}

		// Cookies
		if ( isset( $this->_cookies ) ) {
			$args['cookies'] = $this->_cookies;
		}

		// Body
		if ( isset( $this->_body ) ) {
			$args['body'] = json_encode( $this->_body );
		}

		// Sanitize $this->_query_string if set
		// $this->_query_string = isset( $this->_query_string ) ? htmlspecialchars( $this->_query_string, ENT_QUOTES, 'UTF-8' ) : '';

		$api = wp_remote_request( $this->_api_base_url . $this->_endpoint . ( isset( $this->_query_string ) ? $this->_query_string : '' ), $args );

		$this->set_response( $api );

		return $this;

	}

	/**
	 * Set the endpoint to make API call to
	 * @param string $endpoint
	 * @return $this
	 */
	public function endpoint( $endpoint ) {
		$this->_endpoint = $endpoint;
		return $this;
	}

	/**
	 * @return int $this->_code
	 */
	public function get_code() {
		return $this->_code;
	}

	/**
	 * @return object $this->_data
	 */
	public function get_data() {
		return $this->_data;
	}

	/**
	 * Return boolean for whether or not error exists
	 */
	public function get_error() {
		return $this->_error;
	}

	public function get_response_str() {
		return $this->_response_str;
	}

	public function get_message() {
		return $this->_message;
	}

	public function get_meta() {
		return $this->_meta;
	}

	/**
	 * @return object $this->_response
	 */
	public function get_response() {
		return $this->_response;
	}

	public function headers( $headers ) {
		// Check that $headers is an array
		if ( ! is_array( $headers ) ) {
			throw new InvalidArgumentException( '$query_params must be an array' );
		}
		if ( isset( $this->_headers ) ) {
			array_push( $this->_headers, $headers );
		} else {
			$this->_headers = $headers;
		}
		return $this;
	}

	public function method( $method ) {
		$this->_method = $method;
		return $this;
	}

	public function query_string( $query_params ) {

		// Check type of $query_params
		if ( ! is_array( $query_params ) ) {
			throw new InvalidArgumentException( '$query_params must be an array' );
		}

		$query_string = '?';
		$query_params_count = count( $query_params );
		$query_params_index = 1;

		foreach ( $query_params as $key => $value ) {
			$query_string .= $key . '=' . $value;
			if ( $query_params_index < $query_params_count ) {
				$query_string .= '&';
			}
			$query_params_index++;
		}

		$this->_query_string = $query_string;

	}

	/**
	 * Check and set access token
	 * Get Simple Paywall's OAuth token and see if it is still valid (not expired) and automatically attempt to refresh if it is.
	 */
	private function set_access_token() {

		// Check if Simple Paywall OAuth token is still valid
		$oauth_token = Simple_Paywall_Config::getInstance()->get_oauth_token();

		$expires_on = new DateTime( $oauth_token->expires_on );
		$now = new DateTime( 'now' );

		// Check if oauth token has expired
		if ( $now > $expires_on ) {
			// Get a new token and set it
			Simple_Paywall_OAuth::renew( $oauth_token->access_token, $oauth_token->refresh_token );
		}

		// Set valid access token
		if ( Simple_Paywall_Config::getInstance()->get_access_token() ) {
			$this->_headers = array(
				"Authorization" => 'Bearer ' . Simple_Paywall_Config::getInstance()->get_access_token()
			);
		}

	}

	private function set_response( $response ) {
		$this->_response_str = $response['body'];
		// Convert JSON string to object
		$this->_response = json_decode( $response['body'] );
		$this->_error = $this->_response->status->error;
		$this->_code = $this->_response->status->code;
		$this->_message = $this->_response->status->message;
		$this->_status = $this->_response->status;
		if ( isset( $this->_response->data ) ) {
			$this->_data = $this->_response->data;
		}
		if ( isset( $this->_response->meta ) ) {
			$this->_meta = $this->_response->meta;
		}

	}

}
