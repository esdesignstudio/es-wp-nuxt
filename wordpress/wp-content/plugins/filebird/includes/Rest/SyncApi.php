<?php
namespace FileBird\Rest;

defined( 'ABSPATH' ) || exit;

use FileBird\Controller\SyncController;

class SyncApi {
	private $controller;

	public function register_rest_routes() {
		$this->controller = new SyncController();

        register_rest_route(
			NJFB_REST_URL,
			'export-csv',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this->controller, 'exportCSV' ),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		);

		register_rest_route(
			NJFB_REST_URL,
			'import-csv',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this->controller, 'importCSV' ),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		);

		register_rest_route(
			NJFB_REST_URL,
			'import-csv-detail',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this->controller, 'getImportCSVDetail' ),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		);
	}

    public function permission_callback() {
		return current_user_can( 'upload_files' );
	}

}