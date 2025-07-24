<?php

namespace FileBird\Utils;

trait Singleton {
	protected static $instance = null;

	final public static function getInstance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
			self::$instance->doHooks();
		}
		return static::$instance;
	}

	public function __construct() {

	}

	private function doHooks() {

	}
}
