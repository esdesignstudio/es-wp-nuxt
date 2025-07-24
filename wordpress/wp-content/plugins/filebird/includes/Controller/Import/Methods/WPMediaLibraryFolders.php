<?php

namespace FileBird\Controller\Import\Methods;

defined( 'ABSPATH' ) || exit;

class WPMediaLibraryFolders extends ImportMethod {
    const TABLE = 'mgmlp_folders';

	private $wpmlf_table;

    public function __construct() {
        global $wpdb;

        $this->wpmlf_table = $wpdb->prefix . self::TABLE;
    }

	public function get_counters( $data ) {
		global $wpdb;
		return intval( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type = %s", 'mgmlp_media_folder' ) ) );
	}


    public function get_folders( $data ) {
        $folders = $this->get_wpmlf_folders();

        update_option( self::TMP_OPTION_FOLDER . $data->prefix, $folders, 'no' );

        return new \WP_REST_Response(
            array(
                'result' => true,
            )
        );
    }

    public function get_wpmlf_folders( $parent = 0 ) {
        global $wpdb;

        $table_exist = $wpdb->get_var( $wpdb->prepare( 'show tables like %s', $this->wpmlf_table ) ) == $this->wpmlf_table;

        if ( ! $table_exist ) {
			return array();
		}

		$query = $wpdb->prepare(
			"SELECT ID as term_id, post_title as name
			from {$wpdb->prefix}posts
			LEFT JOIN {$this->wpmlf_table} ON( {$wpdb->prefix}posts.ID = {$this->wpmlf_table}.post_id )
			where post_type = %s and {$this->wpmlf_table}.folder_id = %d
			order by folder_id",
			'mgmlp_media_folder',
			$parent
		);

		$folders = $wpdb->get_results( $query, ARRAY_A );

		foreach ( $folders as $key => $folder ) {
			$folders[ $key ]['children'] = $this->get_wpmlf_folders( $folder['term_id'] );
		}

		return $folders;
	}

    public function get_attachments( $data ) {
        $folders = get_option( self::TMP_OPTION_FOLDER . $data->prefix, array() );

        $attachments = $this->get_wpmlf_attachments( $folders );

        update_option( self::TMP_OPTION_ATTACHMENT . $data->prefix, $attachments, 'no' );

        return new \WP_REST_Response(
            array(
                'result' => true,
            )
        );
    }

    public function get_wpmlf_attachments( $folders ) {
        global $wpdb;

		$query = "SELECT {$wpdb->prefix}posts.ID FROM {$wpdb->prefix}posts
		LEFT JOIN {$wpdb->prefix}postmeta as pm ON pm.post_id = {$wpdb->prefix}posts.ID
		LEFT JOIN {$wpdb->prefix}mgmlp_folders ON( {$wpdb->prefix}posts.ID = {$wpdb->prefix}mgmlp_folders.post_id )
		WHERE post_type   = 'attachment'
		and pm.meta_key = '_wp_attached_file'
		and folder_id     = %d";

		foreach ( $folders as $folder ) {
			$attachments[ $folder['term_id'] ] = $wpdb->get_col( $wpdb->prepare( $query, $folder['term_id'] ) );
			if ( count( $folder['children'] ) > 0 ) {
				$attachments = $attachments + $this->get_wpmlf_attachments( $folder['children'] );
			}
		}

		return $attachments;
    }
}