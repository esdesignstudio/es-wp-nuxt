<?php

namespace FileBird\Controller;

use FileBird\Model\Folder as FolderModel;
use FileBird\Model\UserSettingModel;
use FileBird\Classes\Tree;

defined( 'ABSPATH' ) || exit;
class FolderController extends Controller {
	public function getFolders( \WP_REST_Request $request ) {
		$order   = sanitize_key( $request->get_param( 'order' ) );
		$orderby = sanitize_key( $request->get_param( 'orderby' ) );
		$lang    = sanitize_key( $request->get_param( 'language' ) );
		$search  = sanitize_text_field( $request->get_param( 'search' ) );

		$tree = ( new Tree( $orderby, $order, $search ) )->get();

		return rest_ensure_response(
			array(
				'tree'                => $tree,
				'allAttachmentsCount' => Tree::getCount( -1, $lang ),
				'attachmentsCount'    => FolderModel::countAttachments( $lang ),
			)
		);
	}

	public function setFolderCounter( \WP_REST_Request $request ) {
		$type = sanitize_key( $request->get_param( 'type' ) );
		$lang = sanitize_key( $request->get_param( 'language' ) );

		// add_filter(
        //     'fbv_counter_type',
        //     function() use ( $type ) {
		// 		return $type;
		// 	}
        // );

		UserSettingModel::getInstance()->setFolderCounterType( $type );

		return rest_ensure_response( FolderModel::countAttachments( $lang ) );
	}

	public function updateFolderColor( \WP_REST_Request $request ) {
		$id    = sanitize_key( $request->get_param( 'folderId' ) );
		$color = sanitize_hex_color( $request->get_param( 'color' ) );

		$option        = get_option( 'fbv_folder_colors', array() );
		$option[ $id ] = $color;

		update_option( 'fbv_folder_colors', $option );

		return rest_ensure_response( true );
	}

	public function createFolder( \WP_REST_Request $request ) {
		$name   = $request->get_param( 'title' );
		$parent = $request->get_param( 'parent' );
		$name   = isset( $name ) ? sanitize_text_field( wp_unslash( $name ) ) : '';
		$parent = isset( $parent ) ? sanitize_text_field( $parent ) : '';
		if ( $name != '' && $parent != '' ) {
			$insert = FolderModel::newOrGet( $name, $parent, false );
			if ( $insert !== false ) {
				return rest_ensure_response( $insert );
			} else {
				return new \WP_Error( 'folder_name_exist', __( 'A folder with this name already exists. Please choose another one.', 'filebird' ) );
			}
		} else {
			return new \WP_Error( 'validation_failed', __( 'Validation failed', 'filebird' ) );
		}
	}

	public function updateFolder( \WP_REST_Request $request ) {
		$id     = $request->get_param( 'id' );
		$parent = $request->get_param( 'parent' );
		$name   = $request->get_param( 'title' );

		$id     = isset( $id ) ? sanitize_text_field( $id ) : '';
		$parent = isset( $parent ) ? intval( sanitize_text_field( $parent ) ) : '';
		$name   = isset( $name ) ? sanitize_text_field( wp_unslash( $name ) ) : '';

		$current_user_id = get_current_user_id();
		$folder_per_user = get_option( 'njt_fbv_folder_per_user', '0' ) === '1';

		if ( is_numeric( $id ) && is_numeric( $parent ) && $name != '' && FolderModel::verifyAuthor( $id, $current_user_id, $folder_per_user ) ) {
			$update = FolderModel::updateFolderName( $name, $parent, $id );
			if ( true === $update ) {
				return rest_ensure_response( $update );
			} else {
				return new \WP_Error( 'folder_name_exist', __( 'A folder with this name already exists. Please choose another one.', 'filebird' ) );
			}
		}
		return new \WP_Error( 'validation_failed', __( 'Validation failed', 'filebird' ) );
	}

	public function updateFolderOrder( \WP_REST_Request $request ) {
		global $wpdb;

		$data = array(
			'dropPosition' => $request->get_param( 'dropPosition' ),
			'dropNodeId'   => $request->get_param( 'dropNodeId' ),
			'dragNodeId'   => $request->get_param( 'dragNodeId' ),
			'toParentId'   => $request->get_param( 'toParentId' ),
		);

		$lang = sanitize_key( $request->get_param( 'language' ) );

		foreach ( $data as $param ) {
			if ( strlen( $param ) === 0 || ! is_numeric( $param ) ) {
				return new \WP_Error( 'invalid_params', __( 'Invalid params', 'filebird' ), array( 'status' => 400 ) );
			}
		}

		$old_node = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, parent, ord FROM {$wpdb->prefix}fbv WHERE `id` = %d AND `created_by` = %d",
				$data['dropNodeId'],
				apply_filters( 'fbv_folder_created_by', 0 )
			),
			ARRAY_A
		);

		// Drop folder inside another folder
		if ( 0 === $data['dropPosition'] ) {
			$children = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT id, ord FROM {$wpdb->prefix}fbv WHERE (parent = %d and created_by = %d and id != %d) ORDER BY ord+0 ASC",
					$data['dropNodeId'],
					apply_filters( 'fbv_folder_created_by', 0 ),
					$data['dragNodeId']
				)
			);

			array_unshift(
                $children,
                (object) array(
					'id'  => $data['dragNodeId'],
					'ord' => 0,
				)
                );

			foreach ( $children as $key => $child ) {
				$wpdb->update(
					"{$wpdb->prefix}fbv",
					array(
						'ord'    => $key + 1,
						'parent' => $data['dropNodeId'],
					),
					array(
						'id' => $child->id,
					),
					array( '%d', '%d' ),
					array( '%d' )
				);
			}
		}

		if ( 1 === $data['dropPosition'] ) {
			$children = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT id, ord FROM {$wpdb->prefix}fbv WHERE (parent = %d and created_by = %d and id != %d) ORDER BY ord+0 ASC",
					$data['toParentId'],
					apply_filters( 'fbv_folder_created_by', 0 ),
					$data['dragNodeId']
				)
			);

			$ord = 0;

			foreach ( $children as $child ) {
				$wpdb->update(
					"{$wpdb->prefix}fbv",
					array(
						'ord'    => $ord++,
						'parent' => $data['toParentId'],
					),
					array(
						'id' => $child->id,
					),
					array( '%d', '%d' ),
					array( '%d' )
				);
				if ( intval( $child->id ) === $data['dropNodeId'] ) {
					$wpdb->update(
						"{$wpdb->prefix}fbv",
						array(
							'ord'    => $ord++,
							'parent' => $data['toParentId'],
						),
						array(
							'id' => $data['dragNodeId'],
						),
						array( '%d', '%d' ),
						array( '%d' )
					);
				}
			}
		}

		if ( -1 === $data['dropPosition'] ) {
			$children = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT id, ord FROM {$wpdb->prefix}fbv WHERE (parent = %d and created_by = %d and id != %d) ORDER BY ord+0 ASC",
					$data['toParentId'],
					apply_filters( 'fbv_folder_created_by', 0 ),
					$data['dragNodeId']
				)
			);

			array_unshift(
                $children,
                (object) array(
					'id'  => $data['dragNodeId'],
					'ord' => 0,
				)
                );

			foreach ( $children as $key => $child ) {
				$wpdb->update(
					"{$wpdb->prefix}fbv",
					array(
						'ord'    => $key + 1,
						'parent' => $data['toParentId'],
					),
					array(
						'id' => $child->id,
					),
					array( '%d', '%d' ),
					array( '%d' )
				);
			}
		}

		if ( ! is_null( $old_node ) && ( $old_node['parent'] != $data['toParentId'] ) ) {
			do_action( 'fbv_folder_parent_updated', $data['dragNodeId'], $data['toParentId'] );
		}

		if ( UserSettingModel::getInstance()->get( 'FOLDER_COUNTER_TYPE' ) === 'counter_file_in_folder_and_sub' ) {
			return rest_ensure_response( FolderModel::countAttachments( $lang ) );
		}

		return rest_ensure_response( true );
	}

	public function deleteFolder( \WP_REST_Request $request ) {
		$ids  = $request->get_param( 'ids' );
		$lang = sanitize_key( $request->get_param( 'language' ) );

		if ( empty( $ids ) ) {
			return new \WP_Error( 'delete_failed', __( 'Can\'t delete folder, please try again later', 'filebird' ) );

		}

		$ids = array_map( 'intval', $ids );

		$current_user_id = get_current_user_id();
		$folder_per_user = get_option( 'njt_fbv_folder_per_user', '0' ) === '1';
		foreach ( $ids as $k => $id ) {
			if ( ! FolderModel::verifyAuthor( $id, $current_user_id, $folder_per_user ) ) {
				unset( $ids[ $k ] );
			}
		}
		$folderCounter = FolderModel::delete( $ids, $lang );

		return rest_ensure_response( $folderCounter );
	}

	public function assignFolder( \WP_REST_Request $request ) {
		$folderId = $request->get_param( 'folderId' );
		$ids      = $request->get_param( 'ids' );
		$lang     = sanitize_key( $request->get_param( 'language' ) );

		if ( empty( $folderId ) && ! is_numeric( $folderId ) && empty( $ids ) ) {
			return new \WP_Error( 'validation_failed', __( 'Validation failed', 'filebird' ) );
		}

		$ids = array_map( 'intval', apply_filters( 'fbv_ids_assigned_to_folder', $ids ) );

		$current_user_id = get_current_user_id();
		$user_has_own_folder = get_option( 'njt_fbv_folder_per_user', '0' ) === '1';
		if( ! FolderModel::verifyAuthor( $folderId, $current_user_id, $user_has_own_folder ) ) {
			return new \WP_Error( 'validation_failed', __( 'Permisson Denied.', 'filebird' ) );
		}
		$folderCounter = FolderModel::assignFolder( $folderId, $ids, $lang );

		return rest_ensure_response( $folderCounter );
	}

	public function gutenbergGetFolder() {
		$_folders = Tree::getFolders( null, null, true );
		$folders  = array(
			array(
				'value'    => 0,
				'label'    => __( 'Please choose folder', 'filebird' ),
				'disabled' => true,
			),
		);
		foreach ( $_folders as $k => $v ) {
			$folders[] = array(
				'value' => $v['id'],
				'label' => $v['text'],
			);
		}

		wp_send_json_success( $folders );
	}
}
