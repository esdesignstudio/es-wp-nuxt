<?php

namespace FileBird\Controller\Import;

use FileBird\Controller\Import\Methods\RealMediaFolderImport;
use FileBird\Controller\Import\Methods\TermFolderImport;
use FileBird\Controller\Import\Methods\WPMediaLibraryFolders;

defined( 'ABSPATH' ) || exit;

class ImportFactory {
	public static function getImportMethod( $prefix ) {
        switch ( $prefix ) {
            case 'happyfiles':
            case 'premio':
            case 'feml':
            case 'wpmf':
            case 'enhanced':
            case 'wf':
            case 'mla':
            case 'mediamatic':
                return new TermFolderImport();
            case 'wpmlf':
                return new WPMediaLibraryFolders();
            case 'realmedia':
                return new RealMediaFolderImport();
            default:
                throw new \Exception( 'Unknown Import Method!' );
        }
	}
}