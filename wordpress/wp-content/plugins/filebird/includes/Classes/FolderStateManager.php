<?php
namespace FileBird\Classes;

defined( 'ABSPATH' ) || exit;

use FileBird\Model\Folder as FolderModel;

class FolderStateManager {
    private $fb_folder    = null;
    private $query        = null;
    private $settingModel = null;

    public function __construct( $query, $settingModel ) {
        $this->query        = $query;
        $this->settingModel = $settingModel;
    }

    public function getFbFolder() {
        $paramUrl        = $this->getParamFromUrl();
        $this->fb_folder = $this->getParamFromQuery();

        if ( Helpers::isListMode() ) {
            $this->fb_folder = $this->settingModel->get( 'DEFAULT_FOLDER' );

            if ( $this->fb_folder === FolderModel::PREVIOUS_FOLDER ) {
                $this->fb_folder = $this->settingModel->get( 'FOLDER_STARTUP' );
            }

            if ( ! \is_null( $paramUrl ) ) {
                $this->fb_folder = $paramUrl;
                $this->settingModel->setFolderStartup( $paramUrl );
            }
        }

        return $this->fb_folder;
    }

    private function getParamFromUrl() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
         return isset( $_GET['fbv'] ) ? intval( $_GET['fbv'] ) : null;
    }

    private function getParamFromQuery() {
        $folder = null;

        if ( '' !== $this->query->get( 'fbv' ) ) {
            $folder = intval( $this->query->get( 'fbv' ) );
        }

        return $folder;
    }

    // TODO: getFolderStartup is not good performance
    public function getState( $folderId ) {
        $this->settingModel->setFolderStartup( $folderId );

        if ( $this->settingModel->get( 'DEFAULT_FOLDER' ) == FolderModel::PREVIOUS_FOLDER ) {
            return $this->settingModel->getFolderStartup();
        }

        return $folderId;
    }
}