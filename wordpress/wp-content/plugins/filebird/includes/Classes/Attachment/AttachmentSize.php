<?php
namespace FileBird\Classes\Attachment;

defined( 'ABSPATH' ) || exit;

use FileBird\Utils\Singleton;
use FileBird\Classes\Helpers;

class AttachmentSize {
    use Singleton;

    const META_KEY = 'fb_filesize';

    public function __construct() {
        add_filter( 'manage_media_columns', array( $this, 'manage_media_columns' ) );
		add_action( 'manage_media_custom_column', array( $this, 'manage_media_custom_column' ), 10, 2 );
		add_filter( 'manage_upload_sortable_columns', array( $this, 'manage_upload_sortable_columns' ) );
        add_action( 'added_post_meta', array( $this, 'added_post_meta' ), 10, 4 );
    }

    public function manage_media_columns( $posts_columns ) {
		$posts_columns[ self::META_KEY ] = __( 'File Size', 'filebird' );
		return $posts_columns;
	}

	public function manage_upload_sortable_columns( $columns ) {
		$columns[ self::META_KEY ] = self::META_KEY;
		return $columns;
	}

	public function manage_media_custom_column( $column_name, $post_id ) {
		if ( self::META_KEY === $column_name ) {
			echo esc_html( size_format( Helpers::get_bytes( $post_id ), 2 ) );
		}
		return false;
	}

    public function added_post_meta( $meta_id, $post_id, $meta_key, $meta_value ) {
        if ( '_wp_attachment_metadata' === $meta_key ) {
			$bytes = Helpers::get_bytes( $post_id );
			if ( $bytes ) {
				update_post_meta( $post_id, self::META_KEY, $bytes );
			}
		}
    }

	public function apiCallback( \WP_REST_Request $request ) {
		$page = intval( $request->get_param( 'page' ) );
		if ( $page < 1 ) {
			$page = 1;
		}

		$result = array();

		$args = array(
			'post_type'      => 'attachment',
			'posts_per_page' => 50,
			'post_status'    => 'inherit',
			'fields'         => 'ids',
			'paged'          => $page,
		);

		$query = new \WP_Query( $args );
		$ids   = $query->posts;

		wp_reset_postdata();

		if ( is_array( $ids ) && count( $ids ) > 0 ) {
			foreach ( $ids as $id ) {
				$bytes = Helpers::get_bytes( $id );
				if ( $bytes ) {
					update_post_meta( $id, self::META_KEY, $bytes );
				}
			}
			$result['success'] = true;
			$result['next']    = '1';
		} else {
			$result['success'] = true;
			$result['next']    = '0';
		}
		return new \WP_REST_Response( $result );
	}
}