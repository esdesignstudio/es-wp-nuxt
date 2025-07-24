<?php
namespace FileBird\Classes;

use FileBird\Admin\Settings;
use FileBird\Utils\Singleton;
use FileBird\Model\Folder as FolderModel;
use FileBird\Utils\Vite;
use FileBird\Classes\Helpers as Helpers;
use FileBird\Controller\Import\ImportController;
use FileBird\I18n as I18n;
use FileBird\Model\UserSettingModel;
use FileBird\Model\SettingModel;
use FileBird\Controller\Convert as ConvertController;
use FileBird\Classes\FolderStateManager;

defined( 'ABSPATH' ) || exit;

class Core {
	private $userSettingModel = null;
	private $settingModel     = null;

    use Singleton;

    public function __construct() {
		$this->userSettingModel = UserSettingModel::getInstance();
		$this->settingModel     = SettingModel::getInstance();
		$this->loadModules();
		$this->checkUpdateDatabase();
	}

	private function doHooks() {
        add_filter( 'media_library_infinite_scrolling', '__return_true' );
		add_filter( 'ajax_query_attachments_args', array( $this, 'ajaxQueryAttachmentsArgs' ), 20 );
		add_filter( 'mla_media_modal_query_final_terms', array( $this, 'ajaxQueryAttachmentsArgs' ), 20 );
		add_filter( 'restrict_manage_posts', array( $this, 'restrictManagePosts' ) );
		add_filter( 'posts_clauses', array( $this, 'postsClauses' ), 10, 2 );
		add_filter( 'attachment_fields_to_save', array( $this, 'attachment_fields_to_save' ), 10, 2 );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAdminScripts' ), PHP_INT_MAX );
		add_action( 'add_attachment', array( $this, 'addAttachment' ) );
		add_action( 'delete_attachment', array( $this, 'deleteAttachment' ) );
		add_action( 'pre-upload-ui', array( $this, 'actionPluploadUi' ) );
		add_action( 'wp_ajax_fbv_first_folder_notice', array( $this, 'ajax_first_folder_notice' ) );
		add_action( 'admin_notices', array( $this, 'adminNotices' ) );
		add_action( 'attachment_fields_to_edit', array( $this, 'attachment_fields_to_edit' ), 10, 2 );
		add_filter( 'wp_edited_image_metadata', array( $this, 'edited_image_metadata' ), 10, 3 );

		add_filter( 'users_have_additional_content', array( $this, 'users_have_additional_content' ), 10, 2 );
		add_action( 'deleted_user', array( $this, 'deleted_user' ), 10, 3 );
		// Fix WordPress VIP doesn't load CSS file correctly
		add_filter( 'css_do_concat', array( $this, 'wordpress_vip_css_concat_filter' ), 10, 2 );
		// -----
	}

    public function checkUpdateDatabase() {
		if ( is_admin() ) {
			$is_converted = get_option( 'fbv_old_data_updated_to_v4', '0' );
			if ( $is_converted !== '1' ) {
				if ( ConvertController::countOldFolders() > 0 && ! isset( $_GET['autorun'] ) ) {
					add_filter( 'fbv_update_database_notice', '__return_true' );
				}
			}
		}
	}

	public function wordpress_vip_css_concat_filter( $do_concat, $handle ) {
		if ( 'filebird-ui' === $handle ) {
			return false;
		}
		return $do_concat;
	}

    private function loadModules() {
		new Attachment\AttachmentSize();
		new Modules\ModuleExclude();
		new Modules\ModuleUser();
		new Modules\ModuleCompatibility();
		new Modules\ModuleSvg();
	}

    public function adminNotices() {
		global $pagenow;

		$optionFirstFolder = get_option( 'fbv_first_folder_notice' );
		if ( FolderModel::countFolder() === 0 && $pagenow !== 'upload.php' &&
		( $optionFirstFolder === false || time() >= intval( $optionFirstFolder ) ) ) {
			include NJFB_PLUGIN_PATH . '/views/notices/html-notice-first-folder.php';
		}

		if ( $pagenow !== 'upload.php' && apply_filters( 'fbv_update_database_notice', false ) ) {
			include NJFB_PLUGIN_PATH . '/views/notices/html-notice-update-database.php';
		}
	}

    public function ajax_first_folder_notice() {
		check_ajax_referer( 'fbv_nonce', 'nonce', true );
		update_option( 'fbv_first_folder_notice', time() + 30 * 60 * 60 * 24 ); //After 3 months show
		wp_send_json_success();
	}

    public function enqueueAdminScripts( $screenId ) {
		$fbv_data = apply_filters(
			'fbv_data',
			array(
				'nonce'                  => wp_create_nonce( 'fbv_nonce' ),
				'rest_nonce'             => wp_create_nonce( 'wp_rest' ),
				'is_upload_screen'       => 'upload.php' === $screenId ? '1' : '0',
				'i18n'                   => I18n::getTranslation(),
				'media_mode'             => get_user_option( 'media_library_mode', get_current_user_id() ),
				'json_url'               => apply_filters( 'filebird_json_url', get_rest_url( null, NJFB_REST_URL ) ),
				'media_url'              => admin_url( 'upload.php' ),
				'asset_url'              => NJFB_PLUGIN_URL . 'assets/',
				'data_import'            => ImportController::get_notice_import( $screenId ),
				'data_import_url'        => esc_url(
					add_query_arg(
						array(
							'page' => Settings::SETTING_PAGE_SLUG . '#/import-export',
						),
						admin_url( 'admin.php' )
					)
				),
				'is_new_user'            => get_option( 'fbv_is_new_user', false ),
				'update_database_notice' => apply_filters( 'fbv_update_database_notice', false ),
				'is_rtl'                 => is_rtl(),
				'tree_mode'              => 'attachment',
				'folder_search_api'      => apply_filters( 'fbv_folder_api_search', $this->settingModel->get( 'IS_SEARCH_USING_API' ) ),
			)
		);

		$script_handle = Vite::enqueue_vite( 'main.tsx' );

		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		wp_enqueue_script( 'wp-dom-ready' );
		wp_enqueue_style( 'filebird-ui', NJFB_PLUGIN_URL . 'assets/dist/style.css', array(), NJFB_VERSION );

		if ( wp_is_mobile() ) {
			wp_enqueue_script( 'jquery-touch-punch-fixed', NJFB_PLUGIN_URL . 'assets/js/jquery.ui.touch-punch.js', array( 'jquery-ui-widget', 'jquery-ui-mouse' ), NJFB_VERSION, false );
		}

		wp_enqueue_script( 'filebird-e', NJFB_PLUGIN_URL . 'assets/js/e.js', array(), NJFB_VERSION );

		wp_localize_script( 'filebird-e', 'fbv_data', $fbv_data );
		wp_localize_script( $script_handle, 'fbv_data', $fbv_data );
		wp_localize_script( 'wp-dom-ready', 'fbv_data', $fbv_data );
	}

    public function restrictManagePosts() {
		$screen = get_current_screen();
		if ( $screen->id == 'upload' ) {
			$fbv     = ( ( isset( $_GET['fbv'] ) ) ? (int) sanitize_text_field( $_GET['fbv'] ) : FolderModel::ALL_CATEGORIES );
			$folders = FolderModel::allFolders();

			$all       = new \stdClass();
			$all->id   = -1;
			$all->name = __( 'All Folders', 'filebird' );

			$uncategorized       = new \stdClass();
			$uncategorized->id   = 0;
			$uncategorized->name = __( 'Uncategorized', 'filebird' );

			array_unshift( $folders, $all, $uncategorized );
			echo '<select name="fbv" id="filter-by-fbv" class="fbv-filter attachment-filters fbv">';
			foreach ( $folders as $k => $folder ) {
				echo sprintf( '<option value="%1$d" %3$s>%2$s</option>', esc_html( $folder->id ), esc_html( $folder->name ), selected( intval( $folder->id ), $fbv, false ) );
			}
			echo '</select>';
		}
	}

    public function postsClauses( $clauses, $query ) {
		global $wpdb;

		if ( $query->get( 'post_type' ) !== 'attachment' ) {
			return $clauses;
		}

		$fb_folder = ( new FolderStateManager( $query, $this->userSettingModel ) )->getFbFolder();

		if ( ! \is_null( $fb_folder ) ) {
			if ( FolderModel::ALL_CATEGORIES === $fb_folder ) {
				return $clauses;
			} elseif ( FolderModel::UN_CATEGORIZED === $fb_folder ) {
				$clauses = FolderModel::getRelationsWithFolderUser( $clauses );
			} else {
				$clauses['join']  .= $wpdb->prepare( " LEFT JOIN {$wpdb->prefix}fbv_attachment_folder AS fbva ON fbva.attachment_id = {$wpdb->posts}.ID AND fbva.folder_id = %d ", $fb_folder );
				$clauses['where'] .= ' AND fbva.folder_id IS NOT NULL';
			}
		}

		return $clauses;
	}

    public function addAttachment( $post_id ) {
		$fbv = ( ( isset( $_REQUEST['fbv'] ) ) ? sanitize_text_field( $_REQUEST['fbv'] ) : '' );
		if ( $fbv != '' ) {
			if ( is_numeric( $fbv ) ) {
				$parent = $fbv;
			} else {
				$fbv    = explode( '/', ltrim( rtrim( $fbv, '/' ), '/' ) );
				$parent = (int) $fbv[0];
				if ( $parent < 0 ) {
					$parent = 0; //important
				}
				if ( apply_filters( 'fbv_auto_create_folders', true ) ) {
					unset( $fbv[0] );
					foreach ( $fbv as $k => $v ) {
						$folder = FolderModel::newOrGet( $v, $parent );
						$parent = $folder['id'];
					}
				}
			}

			$ids = array_map( 'intval', apply_filters( 'fbv_ids_assigned_to_folder', array( $post_id ) ) );

			$user_has_own_folder = get_option( 'njt_fbv_folder_per_user', '0' ) === '1';
			$current_user_id     = get_current_user_id();
			if( FolderModel::verifyAuthor( $parent, $current_user_id, $user_has_own_folder ) ){
				FolderModel::setFoldersForPosts( $ids, $parent );
			}
		}
	}

	public function deleteAttachment( $post_id ) {
		FolderModel::deleteFoldersOfPost( $post_id );
	}

	public function ajaxQueryAttachmentsArgs( $query ) {
		// phpcs::ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST['query']['fbv'] ) ) {
			$fbv          = Helpers::sanitize_intval_array( $_REQUEST['query']['fbv'] );
			$query['fbv'] = ( new FolderStateManager( $query, $this->userSettingModel ) )->getState( $fbv );
		}
		return $query;
	}

    public function attachment_fields_to_edit( $form_fields, $post ) {
		$screen = null;
		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();

			if ( ! is_null( $screen ) && 'attachment' === $screen->id ) {
				return $form_fields;
			}
		}

		$fbv_folder  = FolderModel::getFolderFromPostId( $post->ID );
		$fbv_folder  = count( $fbv_folder ) > 0 ? $fbv_folder[0] : (object) array(
			'folder_id' => 0,
			'name'      => __( 'Uncategorized', 'filebird' ),
		);
		$folder_name = esc_attr( $fbv_folder->name );
		$folder_id   = (int) $fbv_folder->folder_id;
		$post_id     = (int) $post->ID;

		$form_fields['fbv'] = array(
			'html'  => "<div class='fbv-attachment-edit-wrapper' data-folder-id='{$folder_id}' data-attachment-id='{$post_id}'><input readonly type='text' value='{$folder_name}'/></div>",
			'label' => esc_html__( 'FileBird folder:', 'filebird' ),
			'helps' => esc_html__( 'Click on the folder name to move this file to another folder', 'filebird' ),
			'input' => 'html',
		);

		return $form_fields;
	}

	public function edited_image_metadata( $new_image_meta, $new_attachment_id, $attachment_id ) {
		$folder = FolderModel::getFolderFromPostId( $attachment_id );
		if ( is_array( $folder ) && count( $folder ) > 0 ) {
			if ( (int) $folder[0]->folder_id > 0 ) {
				FolderModel::setFoldersForPosts( $new_attachment_id, (int) $folder[0]->folder_id );
			}
		}
		return $new_image_meta;
	}

	public function attachment_fields_to_save( $post, $attachment ) {
		if ( isset( $attachment['fbv'] ) ) {
			FolderModel::setFoldersForPosts( $post['ID'], $attachment['fbv'] );
		}
		return $post;
	}

	public function actionPluploadUi() {
		?>
			<div class="fbv-upload-inline">
				<label for="fbv"><?php esc_html_e( 'Choose folder: ', 'filebird' ); ?></label>
				<span id="fbv-folder-selector" class="fbv-folder-selector" name="fbv"></span>
			</div>
		<?php
	}

	public function getFlatTree( $data = array(), $parent = 0, $default = null, $level = 0 ) {
		$tree = is_null( $default ) ? array() : $default;
		foreach ( $data as $k => $v ) {
			if ( $v->parent == $parent ) {
				$node     = array(
					'title' => str_repeat( '-', $level ) . $v->name,
					'value' => $v->id,
				);
				$tree[]   = $node;
				$children = $this->getFlatTree( $data, $v->id, null, $level + 1 );
				foreach ( $children as $k2 => $child ) {
					$tree[] = $child;
				}
			}
		}
		return $tree;
	}

	public function deleted_user( $id, $reassign, $user ) {
		if ( $reassign === null ) {
			FolderModel::deleteByAuthor( $id );
		} else {
			FolderModel::updateAuthor( $id, (int) $reassign );
		}
	}

	public function users_have_additional_content( $users_have_content, $userids ) {
		global $wpdb;
		if ( $userids && ! $users_have_content ) {
			if ( $wpdb->get_var( "SELECT id FROM {$wpdb->prefix}fbv WHERE created_by IN( " . implode( ',', $userids ) . ' ) LIMIT 1' ) ) {
				$users_have_content = true;
			}
		}
		return $users_have_content;
	}
}