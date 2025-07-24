<?php
namespace FileBird;

defined( 'ABSPATH' ) || exit;

use FileBird\Classes\Review;
use FileBird\Classes\Schedule as FilebirdSchedule;
use FileBird\Install;
use FileBird\Model\Folder as FolderModel;
use FileBird\Utils\Singleton;

/**
 * Plugin activate/deactivate logic
 */
class Plugin {
	use Singleton;

	public static $hasBackup = false;

	public function __construct() {
        self::prepareRun();
	}

	public static function prepareRun() {
		$current_version = get_option( 'fbv_version' );
		if ( version_compare( NJFB_VERSION, $current_version, '>' ) ) {
			if ( ! self::$hasBackup ) {
				self::runBackup();
			}
			self::activate();
			update_option( 'fbv_version', NJFB_VERSION );
			Review::update_time_display();
		}
	}

	public static function runBackup() {
		$folders = FolderModel::exportAll();
		update_option( 'filebird_backup_' . date( 'Y_m_d_H_i_s' ), $folders, false );
		self::$hasBackup = true;
	}

	/** Plugin activated hook */
	public static function activate() {
		$first_time_active = get_option( 'fbv_first_time_active' );
		if ( $first_time_active === false ) {
			update_option( 'fbv_is_new_user', 1 );
			update_option( 'fbv_first_time_active', 1 );
		}
		Install::create_tables();
		FilebirdSchedule::registerSchedule();
	}

	/** Plugin deactivate hook */
	public static function deactivate() {
		FilebirdSchedule::clearSchedule();
	}
}