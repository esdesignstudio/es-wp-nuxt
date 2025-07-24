<?php

namespace FileBird\Controller\Import\Methods;

defined( 'ABSPATH' ) || exit;

class TermFolderImport extends ImportMethod {
	public function get_counters( $data ) {
		global $wpdb;
		return intval( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(term_taxonomy_id) FROM $wpdb->term_taxonomy WHERE taxonomy = %s", $data->taxonomy ) ) );
	}

    public function get_folders( $data ) {
        $folders = $this->get_term_folders( $data->taxonomy );

        update_option( self::TMP_OPTION_FOLDER . $data->prefix, $folders, 'no' );

        return new \WP_REST_Response(
            array(
                'result' => true,
            )
        );
    }

    public function get_term_folders( $taxonomy = '', $parent = 0 ) {
        global $wpdb;

        $query = $wpdb->prepare(
			"SELECT term_taxonomy.term_id, terms.name, term_taxonomy.term_taxonomy_id FROM $wpdb->term_taxonomy as `term_taxonomy`
			JOIN $wpdb->terms as `terms`
			ON term_taxonomy.term_taxonomy_id = terms.term_id
			WHERE taxonomy = %s and parent = %d",
			$taxonomy,
			$parent
		);

		$folders = $wpdb->get_results( $query, ARRAY_A );

		foreach ( $folders as $key => $folder ) {
			$folders[ $key ]['children'] = $this->get_term_folders( $taxonomy, $folder['term_id'] );
		}

        return $folders;
    }

    public function get_attachments( $data ) {
        $folders = get_option( self::TMP_OPTION_FOLDER . $data->prefix, array() );

        $attachments = $this->get_term_attachments( $data->taxonomy, $folders );

        update_option( self::TMP_OPTION_ATTACHMENT . $data->prefix, $attachments, 'no' );

        return new \WP_REST_Response(
            array(
                'result' => true,
            )
        );
    }

    public function get_term_attachments( $taxonomy, $folders ) {
        global $wpdb;

        $attachments = array();

		$query = "SELECT term_relationships.object_id
			FROM $wpdb->term_relationships as `term_relationships`
			JOIN $wpdb->term_taxonomy as `term_taxonomy`
			ON term_relationships.term_taxonomy_id = term_taxonomy.term_taxonomy_id
			WHERE taxonomy = %s and term_id = %d";

		foreach ( $folders as $folder ) {
			$attachments[ $folder['term_id'] ] = $wpdb->get_col( $wpdb->prepare( $query, $taxonomy, $folder['term_id'] ) );

			if ( count( $folder['children'] ) > 0 ) {
				$attachments = $attachments + $this->get_term_attachments( $taxonomy, $folder['children'] );
			}
		}

		return $attachments;
    }
}

