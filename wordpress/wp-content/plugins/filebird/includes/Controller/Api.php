<?php
namespace FileBird\Controller;

use FileBird\Model\Folder as FolderModel;
use FileBird\Classes\Helpers as Helpers;
use FileBird\Classes\Tree;

defined( 'ABSPATH' ) || exit;

class Api {
	public function restApi( $request ) {
		$act = $request->get_param( 'act' );
		$act = isset( $act ) ? sanitize_text_field( $act ) : '';
		if ( $act == 'generate-key' ) {
			$key = $this->generateRandomString( 40 );
			update_option( 'fbv_rest_api_key', $key );
			wp_send_json_success( array( 'key' => $key ) );
		}
		wp_send_json_error(
			array(
				'mess' => __( 'Invalid action', 'filebird' ),
			)
		);
	}

	private function addFolderCounter( $folders, $counter ) {
		if ( is_array( $folders ) ) {
			foreach ( $folders as $k => $v ) {
				$folders[ $k ]['data-count'] = isset( $counter[ $v['id'] ] ) ? $counter[ $v['id'] ] : 0;
				if ( isset( $v['children'] ) ) {
					$folders[ $k ]['children'] = $this->addFolderCounter( $v['children'], $counter );
				}
			}
		}
		return $folders;
	}

	public function publicRestApiGetFolders( $request ) {
		$lang = sanitize_key( $request->get_param( 'language' ) );
		$lang = ! empty( $lang ) ? $lang : null;

		$counter           = FolderModel::countAttachments( $lang )['display'];
		$result['folders'] = $this->addFolderCounter( Tree::getFolders(), $counter );

		wp_send_json_success( $result );
	}

	public function publicRestApiGetFolderDetail( $request ) {
		$folder_id = $request->get_param( 'folder_id' );
		wp_send_json_success(
			array(
				'folder' => FolderModel::findById( $folder_id, 'id, name, parent' ),
			)
		);
	}
	public function publicRestApiGetAttachmentIds( $request ) {
		$folder_id = $request->get_param( 'folder_id' );

		if ( $folder_id != '' ) {
			wp_send_json_success(
				array(
					'attachment_ids' => Helpers::getAttachmentIdsByFolderId( $folder_id ),
				)
			);
		}
		wp_send_json_error(
			array(
				'mess' => __( 'folder_id is missing.', 'filebird' ),
			)
		);
	}
	public function publicRestApiGetAttachmentCount( $request ) {
		$folder_id = $request->get_param( 'folder_id' );
		$icl_lang  = $request->get_param( 'icl_lang' );
		if ( $folder_id !== '' ) {
			$count = 0;
			if ( $folder_id > 0 ) {
				$count = Helpers::getAttachmentCountByFolderId( $folder_id );
			} elseif ( $folder_id == -1 ) {
				$count = is_null( $icl_lang ) ? Tree::getCount( -1 ) : Tree::getCount( -1, $icl_lang );
			} else {
				//Uncategorized
				$total            = is_null( $icl_lang ) ? Tree::getCount( -1 ) : Tree::getCount( -1, $icl_lang );
				$folders          = is_null( $icl_lang ) ? Tree::getAllFoldersAndCount() : Tree::getAllFoldersAndCount( $icl_lang );
				$count_of_folders = 0;
				foreach ( $folders as $k => $v ) {
					$count_of_folders += $v;
				}
				$count = $total - $count_of_folders;
			}
			wp_send_json_success(
				array(
					'count' => $count,
				)
			);
		}
		wp_send_json_error(
			array(
				'mess' => __( 'folder_id is missing.', 'filebird' ),
			)
		);
	}
	public function publicRestApiNewFolder( $request ) {
		$parent_id = (int) ( $request->get_param( 'parent_id' ) );
		$name      = sanitize_text_field( $request->get_param( 'name' ) );

		if ( $name != '' ) {
			$folder = FolderModel::newOrGet( $name, $parent_id );
			wp_send_json_success( array( 'id' => $folder['id'] ) );
		}
		wp_send_json_error(
			array(
				'mess' => __( 'Required fields are missing.', 'filebird' ),
			)
		);
	}
	public function publicRestApiSetAttachment( $request ) {
		$ids    = $request->get_param( 'ids' );
		$folder = $request->get_param( 'folder' );

		$ids    = ! empty( $ids ) ? Helpers::sanitize_array( $ids ) : '';
		$folder = sanitize_text_field( $folder );

		if ( \is_numeric( $ids ) ) {
			$ids = array( $ids );
		}

		if ( $ids != '' && is_numeric( $folder ) ) {
			FolderModel::setFoldersForPosts( $ids, $folder );
			wp_send_json_success();
		}
		wp_send_json_error(
			array(
				'mess' => __( 'Validation failed', 'filebird' ),
			)
		);
	}

	private function generateRandomString( $length = 10 ) {
		$characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen( $characters );
		$randomString     = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$randomString .= $characters[ wp_rand( 0, $charactersLength - 1 ) ];
		}
		return $randomString;
	}
}