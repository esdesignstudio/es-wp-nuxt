<?php
namespace FileBird\Classes\Modules;

defined( 'ABSPATH' ) || exit;

class ModuleUser {
	private $is_enabled;
	private $current_user_id;

	public function __construct() {
		$this->is_enabled      = $this->isEnabled();
		$this->current_user_id = get_current_user_id();

		if ( $this->is_enabled ) {
			add_filter( 'fbv_folder_created_by', array( $this, 'fbv_folder_created_by' ) );
		}
	}

	public function fbv_folder_created_by() {
		return $this->current_user_id;
	}

	private function isEnabled() {
		return get_option( 'njt_fbv_folder_per_user', '0' ) === '1';
	}
}