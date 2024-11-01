<?php

/**
 * Copyright (c) 2018 Simple Paywall LLC. All Rights Reserved.
 * Released under the GPLv2 or later license
 * https://opensource.org/licenses/gpl-license.php
 */

class Simple_Paywall_Invalid_Value_Exception extends Simple_Paywall_Exception {

	public function __construct( $message, $code = 0, Exception $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}

	// custom string representation of object
	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}

}
