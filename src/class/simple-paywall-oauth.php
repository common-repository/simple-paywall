<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

/**
 * This is a wrapper for using the WordPress wp_remote_get() function
 */
if ( ! class_exists( 'Simple_Paywall_OAuth' ) ) {
	class Simple_Paywall_OAuth {

		// Get new OAuth token from Simple Paywall's API
		public static function get( $public_api_key, $private_api_key ) {}

		// Refresh OAuth token using refresh_token provided by Simple Paywall's API
		public static function renew( $access_token, $refresh_token ) {

			/**
			 * @see https://developer.wordpress.org/reference/functions/wp_remote_get/
			 */
			$response = wp_remote_post( SIMPLE_PAYWALL_API . '/oauth/wordpress-plugin/refresh' , array(
				'body' => json_encode( array(
					'access_token' => $access_token,
					'refresh_token' => $refresh_token
				) )
			) );

			// Convert JSON string to object
			$response = json_decode( $response['body'] );

			/**
			 * Check for a null response object, indicating some error on the API side.
			 * Null is usually a result of the response not being capable of being processed
			 * as an argument in the json_decode() method.
			 */
			// if ( $api->get_response() === null ) {
			// 	die('we are here');
			// 	/** @todo Handle error */
			// }

			if ( $response->status->code === 201 ) {

				// Store new oauth token data
				$token = array(
					'access_token' => $response->data->access_token,
					'refresh_token' => $response->data->refresh_token,
					'expires_on' => $response->data->expires_on,
					'token_type' => $response->data->token_type,
					'scope' => $response->data->scope
				);
				update_option( 'simple_paywall_oauth', json_encode( $token ) );

			} else {
				/** @todo Handle error */
			}

		}

	}
}
