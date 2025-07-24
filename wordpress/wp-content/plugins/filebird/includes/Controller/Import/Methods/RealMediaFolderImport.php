<?php

namespace FileBird\Controller\Import\Methods;

defined( 'ABSPATH' ) || exit;

class RealMediaFolderImport extends ImportMethod {
    const TABLE              = 'realmedialibrary';
    const TABLE_RELATIONSHIP = 'realmedialibrary_posts';

    private $real_table;
    private $real_table_posts;

    public function __construct() {
        global $wpdb;

        $this->real_table       = $wpdb->prefix . self::TABLE;
        $this->real_table_posts = $wpdb->prefix . self::TABLE_RELATIONSHIP;
    }

    public function get_counters( $data ) {
		global $wpdb;

        $table_exist = $wpdb->get_var( $wpdb->prepare( 'show tables like %s', $this->real_table ) ) == $this->real_table;

        if ( $table_exist ) {
            return intval( $wpdb->get_var( 'SELECT COUNT(id) FROM ' . $this->real_table ) );
        }
        return 0;
	}

    public function get_folders( $data ) {
        $folders = $this->get_rml_folders();

        update_option( self::TMP_OPTION_FOLDER . $data->prefix, $folders, 'no' );

        return new \WP_REST_Response(
            array(
                'result' => true,
            )
        );
    }

    public function get_rml_folders( $parent_id = -1 ) {
        global $wpdb;

        $table_exist = $wpdb->get_var( $wpdb->prepare( 'show tables like %s', $this->real_table ) ) == $this->real_table;

		if ( ! $table_exist ) {
			return array();
		}

		$query = $wpdb->prepare( "SELECT id AS term_id, name FROM $this->real_table WHERE parent = %d", $parent_id );

		$folders = $wpdb->get_results( $query, ARRAY_A );

		foreach ( $folders as $key => $folder ) {
			$folders[ $key ]['children'] = $this->get_rml_folders( $folder['term_id'], $this->real_table, false );
		}

		return $folders;
    }

    public function get_attachments( $data ) {
        $folders = get_option( self::TMP_OPTION_FOLDER . $data->prefix, array() );

        $attachments = $this->get_rml_attachments( $folders );

        update_option( self::TMP_OPTION_ATTACHMENT . $data->prefix, $attachments, 'no' );

        return new \WP_REST_Response(
            array(
                'result' => true,
            )
        );
    }

    public function get_rml_attachments( $folders ) {
        global $wpdb;

        $table_exist = $wpdb->get_var( $wpdb->prepare( 'show tables like %s', $this->real_table_posts ) ) == $this->real_table_posts;

        if ( ! $table_exist ) {
			return array();
		}

		$attachments = array();

		$query = "SELECT attachment FROM $this->real_table_posts WHERE fid = %d";

		foreach ( $folders as $folder ) {
			$attachments[ $folder['term_id'] ] = $wpdb->get_col( $wpdb->prepare( $query, $folder['term_id'] ) );

			if ( count( $folder['children'] ) > 0 ) {
				$attachments = $attachments + $this->get_rml_attachments( $folder['children'], false );
			}
		}

		return $attachments;
    }
}

