<?php
namespace FileBird\Rest;

defined( 'ABSPATH' ) || exit;

use FileBird\Classes\Attachment\AttachmentSize;
use FileBird\Controller\FolderController;

class FolderApi {
	private $controller;

	public function register_rest_routes() {
		$this->controller = new FolderController();

		register_rest_route(
			NJFB_REST_URL,
			'get-folders',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this->controller, 'getFolders' ),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		);
		register_rest_route(
			NJFB_REST_URL,
			'gutenberg-get-folders',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this->controller, 'gutenbergGetFolder' ),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		);

		register_rest_route(
			NJFB_REST_URL,
			'new-folder',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this->controller, 'createFolder' ),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		);
		register_rest_route(
			NJFB_REST_URL,
			'update-folder',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this->controller, 'updateFolder' ),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		);
		register_rest_route(
			NJFB_REST_URL,
			'update-folder-color',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this->controller, 'updateFolderColor' ),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		);
		register_rest_route(
			NJFB_REST_URL,
			'update-folder-ord',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this->controller, 'updateFolderOrder' ),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		);
		register_rest_route(
			NJFB_REST_URL,
			'delete-folder',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this->controller, 'deleteFolder' ),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		);
		register_rest_route(
			NJFB_REST_URL,
			'assign-folder',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this->controller, 'assignFolder' ),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		);
		register_rest_route(
			NJFB_REST_URL,
			'set-folder-counter',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this->controller, 'setFolderCounter' ),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		);
		register_rest_route(
			NJFB_REST_URL,
			'generate-attachment-size',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( AttachmentSize::getInstance(), 'apiCallback' ),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		);
	}

	public function permission_callback() {
		return current_user_can( 'upload_files' );
	}
}