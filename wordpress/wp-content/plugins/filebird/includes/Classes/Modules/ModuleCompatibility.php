<?php
namespace FileBird\Classes\Modules;

defined( 'ABSPATH' ) || exit;

class ModuleCompatibility {
	public function __construct() {
		// MailPoet plugin support
        add_filter( 'mailpoet_conflict_resolver_whitelist_script', array( $this, 'mailpoet_conflict_resolver_whitelist_script' ), 10, 1 );
		add_filter( 'mailpoet_conflict_resolver_whitelist_style', array( $this, 'mailpoet_conflict_resolver_whitelist_style' ), 10, 1 );
	}

	public function mailpoet_conflict_resolver_whitelist_script( $scripts ) {
		$scripts[] = 'filebird';
		$scripts[] = 'filebird-pro';
		return $scripts;
	}

	public function mailpoet_conflict_resolver_whitelist_style( $styles ) {
		$styles[] = 'filebird';
		$styles[] = 'filebird-pro';
		return $styles;
	}
}