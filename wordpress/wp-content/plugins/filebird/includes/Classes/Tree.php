<?php
namespace FileBird\Classes;

defined( 'ABSPATH' ) || exit;

use FileBird\Model\Folder as FolderModel;
use FileBird\Model\UserSettingModel;

class Tree {
	private $order    = null;
	private $order_by = null;
	private $search   = null;
	private $userSettingModel;

	public function __construct( $orderby, $order, $search ) {
        $this->userSettingModel = UserSettingModel::getInstance();
		$orderSetting           = $this->userSettingModel->get( 'DEFAULT_SORT_FOLDERS' );
		$this->search           = $search;
		if ( 'reset' === $order ) {
			$this->userSettingModel->setSettings(
				array(
					'DEFAULT_SORT_FOLDERS' => null,
				)
			);
		} elseif ( $order && $orderby ) {
			$this->order    = $order;
			$this->order_by = $orderby;
			$this->userSettingModel->setSettings(
				array(
					'DEFAULT_SORT_FOLDERS' => array(
						'orderby' => $orderby,
						'order'   => $order,
					),
				)
			);
		} elseif ( is_array( $orderSetting ) ) {
			$this->order    = $orderSetting['order'];
			$this->order_by = $orderSetting['orderby'];
		}
	}

	public function get( $flat = false ) {
		$folders_from_db = FolderModel::allFolders( '*', null, $this->order_by, $this->order, $this->search );
		$folder_colors   = get_option( 'fbv_folder_colors', array() );
		$tree            = array();
		$folders_from_db = self::prepareTreeData( $folders_from_db, $folder_colors );
		$groups          = self::groupByParent( $folders_from_db );

		if ( ! empty( $this->search ) ) {
			return $folders_from_db;
		}

		if ( $flat === true ) {
			$tree = self::getFlatTreeByGroups( $groups, 0 );
		} else {
			$tree = self::getTreeByGroups( $groups, 0 );
		}

		return $tree;
	}

	public static function getCount( $folder_id, $lang = null ) {
		global $wpdb;

		$select = "SELECT COUNT(*) FROM {$wpdb->posts} as posts WHERE ";
		$where  = array( "post_type = 'attachment'" );

		// With $folder_id == -1. We get all
		$where[] = "(posts.post_status = 'inherit' OR posts.post_status = 'private')";

		// with specific folder
		if ( $folder_id > 0 && ! apply_filters( 'fbv_speedup_get_count_query', false ) ) {
			$post__in = $wpdb->get_col( "SELECT `attachment_id` FROM {$wpdb->prefix}fbv_attachment_folder WHERE `folder_id` = " . (int) $folder_id );
			if ( count( $post__in ) == 0 ) {
				$post__in = array( 0 );
			}
			$where[] = '(ID IN (' . implode( ', ', $post__in ) . '))';
		} elseif ( $folder_id == 0 ) {
			return 0;//return 0 if this is uncategorized folder
		}

		$where = apply_filters( 'fbv_get_count_where_query', $where );
		$query = apply_filters( 'fbv_get_count_query', $select . implode( ' AND ', $where ), $folder_id, $lang );
		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $query );
	}

	public static function getAllFoldersAndCount( $lang = null ) {
		global $wpdb;
		$check_author = apply_filters( 'fbv_will_check_author', true );
		if( $check_author ) {
			$query = $wpdb->prepare(
				"SELECT fbva.folder_id, count(fbva.attachment_id) as count FROM {$wpdb->prefix}fbv_attachment_folder as fbva 
				INNER JOIN {$wpdb->prefix}fbv as fbv ON fbv.id = fbva.folder_id 
				INNER JOIN {$wpdb->posts} as posts ON fbva.attachment_id = posts.ID  
				WHERE (posts.post_status = 'inherit' OR posts.post_status = 'private') 
				AND (posts.post_type = 'attachment') 
				AND fbv.created_by = %d 
				GROUP BY fbva.folder_id",
				apply_filters( 'fbv_folder_created_by', '0' )
			);
		} else {
			$query = "SELECT fbva.folder_id, count(fbva.attachment_id) as count FROM {$wpdb->prefix}fbv_attachment_folder as fbva 
				INNER JOIN {$wpdb->prefix}fbv as fbv ON fbv.id = fbva.folder_id 
				INNER JOIN {$wpdb->posts} as posts ON fbva.attachment_id = posts.ID  
				WHERE (posts.post_status = 'inherit' OR posts.post_status = 'private') 
				AND (posts.post_type = 'attachment') 
				GROUP BY fbva.folder_id";
		}
		
		$query = apply_filters( 'fbv_all_folders_and_count', $query, $lang );

		$results = $wpdb->get_results( $query );
		$return  = array();
		if ( is_array( $results ) ) {
			foreach ( $results as $k => $v ) {
				$return[ $v->folder_id ] = $v->count;
			}
		}
		return $return;
	}

	public static function getFolders( $order_by = null, $order = null, $flat = false ) {
		$settings             = UserSettingModel::getInstance()->get( 'THEME' );
		$folders_from_db      = FolderModel::allFolders( '*', null, $order_by, $order );
		$folder_colors        = get_option( 'fbv_folder_colors', array() );
		$folder_default_color = $settings['colors'];
		$tree                 = array();

		$folders_from_db = self::prepareTreeData( $folders_from_db, $folder_colors, $folder_default_color );
		$groups          = self::groupByParent( $folders_from_db );
		if ( $flat === true ) {
			$tree = self::getFlatTreeByGroups( $groups, 0 );
		} else {
			$tree = self::getTreeByGroups( $groups, 0 );
		}
		return $tree;
	}

	public static function getFolder( $folder_id, $order_by = null, $order = null ) {
		$tree = self::getFolders( $order_by, $order );
		return Helpers::findFolder( $folder_id, $tree );
	}

	private static function groupByParent( $data ) {
		$group = array();
		if ( is_array( $data ) ) {
			foreach ( $data as $v ) {
				if ( ! isset( $group[ $v['parent'] ] ) ) {
					$group[ $v['parent'] ] = array();
				}
				$group[ $v['parent'] ][] = $v;
			}
		}
		return $group;
	}

	private static function getTreeByGroups( $groups, $parent = 0 ) {
		$tree = array();
		if ( isset( $groups[ $parent ] ) && is_array( $groups[ $parent ] ) ) {
			foreach ( $groups[ $parent ] as $node ) {
				$node['children'] = isset( $groups[ $node['id'] ] ) ? self::getTreeByGroups( $groups, $node['id'] ) : array();
				$tree[]           = $node;
			}
		}

		return $tree;
	}

	private static function getFlatTreeByGroups( $groups, $parent = 0, $level = 0 ) {
		$tree = array();
		if ( isset( $groups[ $parent ] ) && is_array( $groups[ $parent ] ) ) {
			foreach ( $groups[ $parent ] as $node ) {
				$node['text'] = str_repeat( '-', $level ) . $node['text'];
				$tree[]       = $node;
				if ( isset( $groups[ $node['id'] ] ) ) {
					$tree = array_merge( $tree, self::getFlatTreeByGroups( $groups, $node['id'], $level + 1 ) );
				}
			}
		}

		return $tree;
	}

	private static function prepareTreeData( $data, $folder_colors = array() ) {
		if ( ! is_array( $data ) ) {
			return array();
		}
		foreach ( $data as $k => $v ) {
			$data[ $k ] = array(
				'key'        => (int) $v->id,
                'id'         => (int) $v->id,
				'children'   => array(),
                'text'       => $v->name,
				'title'      => $v->name,
				'color'      => ( isset( $folder_colors[ $v->id ] ) ? sanitize_hex_color( $folder_colors[ $v->id ] ) : '' ),
				'data-id'    => (int) $v->id,
				'parent'     => (int) $v->parent,
				'data-count' => 0,
				'ord'        => $v->ord,
            );
		}
		return $data;
	}
}
