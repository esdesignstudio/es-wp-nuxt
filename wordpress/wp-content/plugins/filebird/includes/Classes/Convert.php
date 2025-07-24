<?php
namespace FileBird\Classes;

use FileBird\Controller\Convert as ConvertController;
use FileBird\Model\Folder as FolderModel;
use FileBird\Controller\Import\DataImport;

defined( 'ABSPATH' ) || exit;

class Convert {

	protected static $instance = null;
	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
			self::$instance->doHooks();
		}
		return self::$instance;
	}

	public function __construct() {
	}

	private function doHooks() {
		add_action( 'rest_api_init', array( $this, 'registerRestFields' ) );
	}

	public function registerRestFields() {
		//get old data
		register_rest_route(
			NJFB_REST_URL,
			'fb-get-old-data',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'ajaxGetOldData' ),
				'permission_callback' => array( $this, 'resPermissionsCheck' ),
			)
		);
		//insert old data
		register_rest_route(
			NJFB_REST_URL,
			'fb-insert-old-data',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'ajaxInsertOldData' ),
				'permission_callback' => array( $this, 'resPermissionsCheck' ),
			)
		);
		//wipe old data
		register_rest_route(
			NJFB_REST_URL,
			'fb-wipe-old-data',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'ajaxWipeOldData' ),
				'permission_callback' => array( $this, 'resPermissionsCheck' ),
			)
		);
		//wipe old data
		register_rest_route(
			NJFB_REST_URL,
			'fb-wipe-clear-all-data',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'ajaxClearAllData' ),
				'permission_callback' => array( $this, 'resPermissionsCheck' ),
			)
		);
		register_rest_route(
			NJFB_REST_URL,
			'fb-no-thanks',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'ajaxNoThanks' ),
				'permission_callback' => array( $this, 'resPermissionsCheck' ),
			)
		);
	}
	public function resPermissionsCheck() {
		return current_user_can( 'upload_files' );
	}

	public function ajaxGetOldData() {
		$folders       = ConvertController::getOldFolders();
		$folders_chunk = array_chunk( $folders, 20 );
		wp_send_json_success(
			array(
				'folders' => $folders_chunk,
			)
		);
	}
	public function ajaxInsertOldData( $request ) {
		$folders = isset( $request ) ? $request->get_params()['folders'] : '';
		if ( $folders != '' ) {
			ConvertController::insertToNewTable( $folders );
			update_option( 'fbv_old_data_updated_to_v4', '1' );
			wp_send_json_success( array( 'mess' => __( 'success', 'filebird' ) ) );
		} else {
			wp_send_json_error( array( 'mess' => __( 'validation failed', 'filebird' ) ) );
		}
	}

	public function ajaxWipeOldData() {
		global $wpdb;
		$queries = array(
			'DELETE FROM ' . $wpdb->prefix . 'termmeta WHERE `term_id` IN (SELECT `term_id` FROM ' . $wpdb->prefix . 'term_taxonomy WHERE `taxonomy` = %s)',
			'DELETE FROM ' . $wpdb->prefix . 'term_relationships WHERE `term_taxonomy_id` IN (SELECT `term_taxonomy_id` FROM ' . $wpdb->prefix . 'term_taxonomy WHERE `taxonomy` = %s)',
			'DELETE FROM ' . $wpdb->prefix . 'terms WHERE `term_id` IN (SELECT `term_id` FROM ' . $wpdb->prefix . 'term_taxonomy WHERE `taxonomy` = %s)',
			'DELETE FROM ' . $wpdb->prefix . 'term_taxonomy WHERE `taxonomy` = %s',
		);
		foreach ( $queries as $k => $query ) {
			// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( $wpdb->prepare( $query, 'nt_wmc_folder' ) );
		}
		wp_send_json_success(
			array(
				'mess' => __( 'Successfully wiped.', 'filebird' ),
			)
		);
	}
	public function ajaxClearAllData() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'fbv';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) ) ) == $table_name ) {
			FolderModel::deleteAll();

			foreach ( DataImport::get() as $data ) {
				update_option( "njt_fb_updated_from_{$data->prefix}", '0' );
			}

			wp_send_json_success(
				array(
					'mess' => __( 'Successfully cleared!', 'filebird' ),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'mess' => __( 'Please try again.', 'filebird' ),
				)
			);
		}

	}

	public function ajaxNoThanks( $request ) {
		$site = $request->get_param( 'site' );

		$site = isset( $site ) ? sanitize_text_field( $site ) : '';

		if ( $site === 'all' ) {
			foreach ( DataImport::get() as $data ) {
				update_option( "njt_fb_{$data->prefix}_no_thanks", '1' );
			}
		} else {
			update_option( "njt_fb_{$site}_no_thanks", '1' );
		}

		return new \WP_REST_Response(
			array(
				'mess' => __( 'Success', 'filebird' ),
			)
		);
	}
}