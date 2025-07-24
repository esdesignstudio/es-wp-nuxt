<?php
namespace FileBird\Model;

use FileBird\Utils\Singleton;
use FileBird\Model\Folder as FolderModel;

defined( 'ABSPATH' ) || exit;

class UserSettingModel {
  	use Singleton;

	const DEFAULT_FOLDER       = '_njt_fbv_default_folder';
	const DEFAULT_SORT_FILES   = '_njt_fbv_default_sort_files';
	const DEFAULT_SORT_FOLDERS = 'fbv_folder_sort';
	const FOLDER_COUNTER_TYPE  = 'fbv_counter_type';
	const FOLDER_STARTUP       = 'fbv_folder_startup';
	const THEME                = 'fbv_theme';
	const SHOW_BREAD_CRUMB     = 'fbv_show_breadcrumb';

	private $userId = '';

	private $settings = array();
	private $config   = array();

	public function __construct() {
		$this->initialize();

		$this->userId   = get_current_user_id();
		$this->settings = $this->loadSettings();

		add_filter( 'fbv_data', array( $this, 'addUserSettingsData' ), 10, 1 );
	}

	public function initialize() {
		$this->config = array(
			'DEFAULT_FOLDER'       => array(
				'get' => 'getDefaultFolder',
				'set' => 'setDefaultFolder',
			),
			'DEFAULT_SORT_FILES'   => array(
				'get' => 'getSortFiles',
				'set' => 'setSortFiles',
			),
			'DEFAULT_SORT_FOLDERS' => array(
				'get' => 'getSortFolders',
				'set' => 'setSortFolders',
			),
			'FOLDER_STARTUP'       => array(
				'get' => 'getFolderStartup',
				'set' => 'setFolderStartup',
			),
			'FOLDER_COUNTER_TYPE'  => array(
				'get' => 'getFolderCounterType',
				'set' => 'setFolderCounterType',
			),
			'THEME'                => array(
				'get' => 'getCurrentTheme',
				'set' => 'setCurrentTheme',
			),
			'SHOW_BREAD_CRUMB'     => array(
				'get' => 'getBreadCrumb',
				'set' => 'setBreadCrumb',
			),
		);
	}

	public function addUserSettingsData( $data ) {
		$data['user_settings'] = $this->settings;
		return $data;
	}

	public function loadSettings() {
		foreach ( $this->config as $key => $value ) {
			$this->settings[ $key ] = $this->{$value['get']}();
		}

		return $this->settings;
	}

	public function get( $key ) {
		if ( in_array( $key, array_keys( $this->config ) ) ) {
			return $this->settings[ $key ];
		}
	}

	public function setSettings( $params ) {
		foreach ( $params as $key => $value ) {
			if ( isset( $this->config[ $key ] ) ) {
				$this->{$this->config[ $key ]['set']}( $value );
			}
		}
	}

	public function getSortFiles() {
		$meta = get_user_meta( $this->userId, self::DEFAULT_SORT_FILES, true );

		if ( ! empty( $meta ) ) {
			$parse = explode( '-', $meta );

			return array(
				'orderby' => $parse[0],
				'order'   => $parse[1] ?? '',
			);
		}

		return null;
	}

	public function getFolderStartup() {
		$defaultFolder = $this->settings['DEFAULT_FOLDER'];
		$startupFolder = get_user_meta( $this->userId, self::FOLDER_STARTUP, true );
		$startupFolder = is_numeric( $startupFolder ) ? intval( $startupFolder ) : FolderModel::ALL_CATEGORIES;

		if ( $defaultFolder == FolderModel::PREVIOUS_FOLDER ) {
			if ( $startupFolder > 0 ) {
				if ( FolderModel::isFolderExist( $startupFolder ) ) {
					return intval( $startupFolder );
				} else {
					$this->settings['FOLDER_STARTUP'] = FolderModel::ALL_CATEGORIES;
					$this->setFolderStartup( FolderModel::ALL_CATEGORIES );
					return FolderModel::ALL_CATEGORIES;
				}
			} else {
				if ( in_array( $startupFolder, array( FolderModel::ALL_CATEGORIES, FolderModel::UN_CATEGORIZED ), true ) ) {
					return $startupFolder;
				}
			}
		}

		return FolderModel::ALL_CATEGORIES;
	}

	public function setFolderStartup( $value ) {
		update_user_meta( $this->userId, self::FOLDER_STARTUP, $value );
	}

	public function getSortFolders() {
		return get_user_meta( $this->userId, self::DEFAULT_SORT_FOLDERS, true );
	}

	public function setSortFolders( $value ) {
		update_user_meta( $this->userId, self::DEFAULT_SORT_FOLDERS, $value );
	}

    public function getCurrentTheme() {
		$theme = get_user_meta( get_current_user_id(), self::THEME, true ) ?: 'default';

		return array(
			'name'   => $theme,
			'colors' => ThemeModel::COLORS,
		);
	}

    public function getDefaultFolder() {
		$folder_id = get_user_meta( $this->userId, self::DEFAULT_FOLDER, true );
		$folder_id = intval( $folder_id );

		if ( $folder_id > 0 ) {
			if ( is_null( FolderModel::findById( $folder_id ) ) ) {
				$folder_id = FolderModel::ALL_CATEGORIES;
			}
		}
		return $folder_id;
	}

    public function getFolderCounterType() {
        $type = get_user_meta( $this->userId, self::FOLDER_COUNTER_TYPE, true );
		return empty( $type ) ? 'counter_file_in_folder' : $type;
    }

	public function getBreadCrumb() {
		return get_user_meta( $this->userId, self::SHOW_BREAD_CRUMB, true );
	}

    public function setDefaultFolder( $value ) {
		$value = (int) $value;
		update_user_meta( $this->userId, self::DEFAULT_FOLDER, $value );
	}

    public function setSortFiles( $value ) {
		$value = $value ? "{$value['orderby']}-{$value['order']}" : '';

		update_user_meta( $this->userId, self::DEFAULT_SORT_FILES, $value );
	}

    public function setCurrentTheme( $theme ) {
		// Fixed old FileBird version below 6
		update_user_meta( $this->userId, self::THEME, $theme['name'] === 'default' ? '' : $theme['name'] );
    }

    public function setFolderCounterType( $type ) {
        update_user_meta( $this->userId, self::FOLDER_COUNTER_TYPE, $type );
		$this->settings['FOLDER_COUNTER_TYPE'] = $type;
    }

	public function setBreadCrumb( $value ) {
		update_user_meta( $this->userId, self::SHOW_BREAD_CRUMB, $value );
	}
}