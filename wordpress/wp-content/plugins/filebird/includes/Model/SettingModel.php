<?php
namespace FileBird\Model;

use FileBird\Utils\Singleton;
use FileBird\Admin\Settings;

defined( 'ABSPATH' ) || exit;

class SettingModel {
  	use Singleton;
	private $settings = array();
	private $config   = array();

	public function __construct() {
		$this->initialize();
		$this->settings = $this->loadSettings();

		add_filter( 'fbv_data', array( $this, 'addUserSettingsData' ), 10, 1 );
	}

	public function initialize() {
		$this->config = array(
			'USER_MODE'           => array(
				'get' => 'getUserMode',
				'set' => 'setUserMode',
			),
			'SVG_SUPPORT'         => array(
				'get' => 'getSvgSupport',
				'set' => 'setSvgSupport',
			),
			'IS_SEARCH_USING_API' => array(
				'get' => 'getFolderSearchMethod',
				'set' => 'setFolderSearchMethod',
			),
		);
	}

	public function addUserSettingsData( $data ) {
		$data['user_settings'] = array_merge( $data['user_settings'], $this->settings );

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

	public function getUserMode() {
		return get_option( 'njt_fbv_folder_per_user', '0' ) === '1';
	}

	public function setUserMode( $value ) {
		update_option( 'njt_fbv_folder_per_user', $value );
	}

	public function getSvgSupport() {
		return get_option( 'njt_fbv_allow_svg_upload', '0' ) === '1';
	}

	public function setSvgSupport( $value ) {
		update_option( 'njt_fbv_allow_svg_upload', $value );
	}

	public function getFolderSearchMethod() {
		return get_option( 'njt_fbv_is_search_using_api', '0' ) === '1';
	}

	public function setFolderSearchMethod( $value ) {
		update_option( 'njt_fbv_is_search_using_api', $value );
	}
}