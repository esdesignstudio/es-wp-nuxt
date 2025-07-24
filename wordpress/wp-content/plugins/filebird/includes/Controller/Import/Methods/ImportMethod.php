<?php

namespace FileBird\Controller\Import\Methods;

use FileBird\Model\Folder as FolderModel;

defined( 'ABSPATH' ) || exit;

abstract class ImportMethod {
    const TMP_OPTION_FOLDER     = 'fb_tmp_folders_';
    const TMP_OPTION_ATTACHMENT = 'fb_tmp_attachments_';

    abstract public function get_counters( $data );

    abstract public function get_folders( $data);

    abstract public function get_attachments( $data);

    public function run_import_folders( $folders, $attachments, $parent = 0 ) {
        $folders_created = array();

		foreach ( $folders as $folder ) {
			$new_folder = FolderModel::newOrGet( $folder['name'], $parent );

            array_push( $folders_created, $folder['term_id'] );

			if ( isset( $attachments[ $folder['term_id'] ] )
			&& count( $attachments[ $folder['term_id'] ] ) > 0
			&& false !== $new_folder ) {
				FolderModel::setFoldersForPosts( $attachments[ $folder['term_id'] ], $new_folder['id'] );
			}

			if ( count( $folder['children'] ) > 0 ) {
				$new_child_folders = $this->run_import_folders( $folder['children'], $attachments, $new_folder['id'] );
				$folders_created   = array_merge( $folders_created, $new_child_folders );
			}
		}

		return $folders_created;
    }

    public function run( $data ) {
        $folders     = get_option( self::TMP_OPTION_FOLDER . $data->prefix );
		$attachments = get_option( self::TMP_OPTION_ATTACHMENT . $data->prefix );

		$folders_created = $this->run_import_folders( $folders, $attachments );

        delete_option( self::TMP_OPTION_FOLDER . $data->prefix );
        delete_option( self::TMP_OPTION_ATTACHMENT . $data->prefix );

        update_option( 'njt_fb_updated_from_' . $data->prefix, '1' );

		$mess = sprintf( __( 'Congratulations! We imported successfully %d folders into <strong>FileBird.</strong>', 'filebird' ), count( $folders_created ) );

		return new \WP_REST_Response( array( 'mess' => $mess ) );
    }
}
