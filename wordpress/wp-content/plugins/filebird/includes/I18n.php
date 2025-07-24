<?php
namespace FileBird;

defined( 'ABSPATH' ) || exit;
/**
 * I18n Logic
 */
class I18n {
	public function __construct() {
		add_action( 'init', array( $this, 'loadPluginTextdomain' ) );
	}

	public function loadPluginTextdomain() {
		if ( function_exists( 'determine_locale' ) ) {
			$locale = determine_locale();
		} else {
			$locale = is_admin() ? get_user_locale() : get_locale();
		}
		unload_textdomain( 'filebird' );
		load_textdomain( 'filebird', WP_LANG_DIR . '/plugins/filebird-' . $locale . '.mo' );
		load_plugin_textdomain( 'filebird', false, NJFB_PLUGIN_PATH . '/i18n/languages/' );
	}

	public static function getTranslation() {
		$translation = array(
			'noMedia'                           => __( 'No media files found.', 'filebird' ),
			'new_folder'                        => __( 'New Folder', 'filebird' ),
			'delete'                            => __( 'Delete', 'filebird' ),
			'folders'                           => __( 'Folders', 'filebird' ),
			'ok'                                => __( 'Ok', 'filebird' ),
			'cancel'                            => __( 'Cancel', 'filebird' ),
			'close'                             => __( 'Close', 'filebird' ),
			'cut'                               => __( 'Cut', 'filebird' ),
			'paste'                             => __( 'Paste', 'filebird' ),
			'download'                          => __( 'Download', 'filebird' ),
			'download_pro_version'              => __( 'Download (Pro version)', 'filebird' ),
			'loading'                           => __( 'Loading...', 'filebird' ),
			'generate_download'                 => __( 'Generating download link...', 'filebird' ),
			'move_done'                         => __( 'Successfully moved', 'filebird' ),
			'move_error'                        => __( 'Unsuccessfully moved', 'filebird' ),
			'update_done'                       => __( 'Successfully updated!', 'filebird' ),
			'update_error'                      => __( 'Unsuccessfully updated!', 'filebird' ),
			'delete_done'                       => __( 'Successfully deleted!', 'filebird' ),
			'delete_error'                      => __( "Can't delete!", 'filebird' ),
			'change_color'                      => __( 'Change Color', 'filebird' ),
			'save'                              => __( 'Save', 'filebird' ),
			'save_changes'                      => __( 'Save Changes', 'filebird' ),
			'folder'                            => __( 'Folder', 'filebird' ),
			'folder_name_placeholder'           => __( 'Folder name...', 'filebird' ),
			'folders'                           => __( 'Folders', 'filebird' ),
			'enter_folder_name_placeholder'     => __( 'Enter folder name...', 'filebird' ),
			'are_you_sure_delete'               => __( 'Are you sure you want to delete', 'filebird' ),
			'are_you_sure'                      => __( 'Are you sure?', 'filebird' ),
			'all_files_will_move'               => __( 'Those files will be moved to <strong>Uncategorized</strong> folder.', 'filebird' ),
			'editing_warning'                   => __( 'You are editing another folder! Please complete the task first!', 'filebird' ),
			'sort_folders'                      => __( 'Sort Folders', 'filebird' ),
			'delete_folder'                     => __( 'Delete Folder', 'filebird' ),
			'sort_files'                        => __( 'Sort Files', 'filebird' ),
			'bulk_select'                       => __( 'Bulk Select', 'filebird' ),
			'all_files'                         => __( 'All Files', 'filebird' ),
			'uncategorized'                     => __( 'Uncategorized', 'filebird' ),
			'previous_folder_selected'          => __( 'Previous folder selected', 'filebird' ),
			'most_recent_folder' 				=> __( 'Most recent folder', 'filebird' ),
			'rename'                            => __( 'Rename', 'filebird' ),
			'are_you_sure_delete_this_folder'   => __( 'Are you sure you want to delete this folder? Those files will be moved to <strong>Uncategorized</strong> folder.', 'filebird' ),
			'sort_ascending'                    => __( 'Sort Ascending', 'filebird' ),
			'sort_descending'                   => __( 'Sort Descending', 'filebird' ),
			'reset'                             => __( 'Reset', 'filebird' ),
			'by_name'                           => __( 'By Name', 'filebird' ),
			'name_ascending'                    => __( 'Name Ascending', 'filebird' ),
			'name_descending'                   => __( 'Name Descending', 'filebird' ),
			'by_date'                           => __( 'By Date', 'filebird' ),
			'date_ascending'                    => __( 'Date Ascending', 'filebird' ),
			'date_descending'                   => __( 'Date Descending', 'filebird' ),
			'by_modified'                       => __( 'By Modified', 'filebird' ),
			'modified_ascending'                => __( 'Modified Ascending', 'filebird' ),
			'modified_descending'               => __( 'Modified Descending', 'filebird' ),
			'by_author'                         => __( 'By Author', 'filebird' ),
			'by_file_name'                      => __( 'By File Name', 'filebird' ),
			'author_ascending'                  => __( 'Author Ascending', 'filebird' ),
			'author_descending'                 => __( 'Author Descending', 'filebird' ),
			'by_title'                          => __( 'By Title', 'filebird' ),
			'title_ascending'                   => __( 'Title Ascending', 'filebird' ),
			'title_descending'                  => __( 'Title Descending', 'filebird' ),
			'by_size'                           => __( 'By Size', 'filebird' ),
			'size_ascending'                    => __( 'Size Ascending', 'filebird' ),
			'size_descending'                   => __( 'Size Descending', 'filebird' ),
			'skip_and_deactivate'               => __( 'Skip & Deactivate', 'filebird' ),
			'deactivate'                        => __( 'Deactivate', 'filebird' ),
			'thank_you_so_much'                 => __( 'Thank you so much!', 'filebird' ),
			'feedback'                          => array(
				'no_features'            => __( 'It doesn\'t have the features I\'m looking for.', 'filebird' ),
				'not_working'            => __( 'Not work with my theme or other plugins.', 'filebird' ),
				'found_better_plugin'    => __( 'Found another plugin that works better.', 'filebird' ),
				'not_know_using'         => __( 'Don\'t know how to use it.', 'filebird' ),
				'temporary_deactivation' => __( 'This is just temporary, I will use it again.', 'filebird' ),
				'other'                  => __( 'Other', 'filebird' ),
			),
			'which_features'                    => __( 'Which features please?', 'filebird' ),
			'found_better_plugin_placeholder'   => __( 'Please tell us which one', 'filebird' ),
			'not_know_using_document'           => __( 'Please read FileBird documentation <a target="_blank" href="https://ninjateam.gitbook.io/filebird/">here</a> or <a target="_blank" href="https://ninjateam.org/support/">chat with us</a> if you need help', 'filebird' ),
			'not_working_support'               => __( 'Please <a target="_blank" href="https://ninjateam.org/support/">ask for support here</a>, we will fix it for you.', 'filebird' ),
			'other_placeholder'                 => __( 'Please share your thoughts...', 'filebird' ),
			'quick_feedback'                    => __( 'Want a better FileBird?', 'filebird' ),
			'deactivate_sadly'                  => __( 'Sorry to see you walk away, please share why you want to deactivate FileBird?', 'filebird' ),
			'folder_limit_reached'              => __( 'Folder Limit Reached', 'filebird' ),
			'limit_folder'                      => __(
				'<p>FileBird Lite version supports up to 10 folders.<br>Please upgrade to have unlimited folders and other premium features!</p>
        <ul class="fbv-in_feature">
          <li>Unlimited Folders</li>
          <li>Sort Files / Folders</li>
          <li>Compatible with Premium Page Builders <span id="fbv-pagebuilder" class="njn-i"><svg viewBox="0 0 192 512"><path fill="currentColor" d="M20 424.229h20V279.771H20c-11.046 0-20-8.954-20-20V212c0-11.046 8.954-20 20-20h112c11.046 0 20 8.954 20 20v212.229h20c11.046 0 20 8.954 20 20V492c0 11.046-8.954 20-20 20H20c-11.046 0-20-8.954-20-20v-47.771c0-11.046 8.954-20 20-20zM96 0C56.235 0 24 32.235 24 72s32.235 72 72 72 72-32.235 72-72S135.764 0 96 0z"></path></svg></span></li>
          <li>Get Fast Updates</li>
          <li>Premium Technical Support</li>
          <li>One-time Payment</li>
          <li>30-day Refund Guarantee</li>
        </ul>',
				'filebird'
			),
			'pagebuilder_support'               => __( 'Including Divi, Fusion, Thrive Architect, WPBakery...', 'filebird' ),
			'upgrade_to_pro'                    => __( 'Upgrade to FileBird Pro', 'filebird' ),
			'success'                           => __( 'Success.', 'filebird' ),
			'filebird_db_updated'               => __( 'Congratulations. Successfully imported!', 'filebird' ),
			'go_to_media'                       => __( 'Go To Media', 'filebird' ),
			'update_noti_title'                 => __( 'FileBird 4 Update Required', 'filebird' ),
			'update_noti_desc'                  => __( 'You\'re using the new FileBird 4. Please import database to view your folders correctly.', 'filebird' ),
			'update_noti_btn'                   => __( 'Import now', 'filebird' ),
			'update'                            => __( 'Update', 'filebird' ),
			'import_failed'                     => __( 'Import failed. Please try again or <a href="https://ninjateam.org/support" target="_blank">contact our support</a>.', 'filebird' ),
			'purchase_code_missing'             => __( 'Please enter your Purchase Code.', 'filebird' ),
			'envato_token_missing'              => __( 'Please enter your Personal Access Token or get one.', 'filebird' ),
			'envato_invalid_license'            => __( 'Can not active your License, please try again.', 'filebird' ),
			'settings'                          => __( 'Settings', 'filebird' ),
			'fb_settings'                       => __( 'FileBird Settings', 'filebird' ),
			'select_default_startup_folder'     => __( 'Select a default startup folder:', 'filebird' ),
			'auto_sort_files_by'                => __( 'Auto sort files by:', 'filebird' ),
			'default'                           => __( 'Default', 'filebird' ),
			'set_setting_success'               => __( 'Settings saved', 'filebird' ),
			'set_setting_fail'                  => __( 'Failed to save settings. Please try again!', 'filebird' ),
			'unlock_new_features_title'         => __( 'Unlock new features', 'filebird' ),
			'unlock_new_features_desc'          => __( 'To use FileBird folders with your current page builder/plugin, please upgrade to PRO version.', 'filebird' ),
			'do_more_with_filebird_title'       => __( 'Do more with FileBird PRO', 'filebird' ),
			'do_more_with_filebird_desc'        => __( 'You\'re using a third party plugin, which is supported in FileBird PRO. Please upgrade to browse files faster and get more done.', 'filebird' ),
			'go_pro'                            => __( 'Go Pro', 'filebird' ),
			'view_details'                      => __( 'View details.', 'filebird' ),
			'turn_off_for_7_days'               => __( 'Turn off for 7 days', 'filebird' ),
			'collapse'                          => __( 'Collapse', 'filebird' ),
			'expand'                            => __( 'Expand', 'filebird' ),
			'uploaded'                          => __( 'Uploaded', 'filebird' ),
			'lessThanAMin'                      => __( 'Less than a min', 'filebird' ),
			'totalSize'                         => __( 'Total size', 'filebird' ),
			'move'                              => __( 'Move', 'filebird' ),
			'item'                              => __( 'item', 'filebird' ),
			'items'                             => __( 'items', 'filebird' ),
			'import_folder_to_filebird'         => __( 'Import folders to FileBird', 'filebird' ),
			'go_to_import'                      => __( 'Go to import', 'filebird' ),
			'no_thanks'                         => __( 'No, thanks', 'filebird' ),
			'import_some_folders'               => __( 'You have some folders created by other media plugins. Would you like to import them?', 'filebird' ),
			'default_tree_view'                 => __( 'Default Tree View', 'filebird' ),
			'flat_tree_view'                    => __( 'Flat Tree View', 'filebird' ),
			'processing'                        => __( 'Processing...', 'filebird' ),
			'generating'                        => __( 'Generating...', 'filebird' ),
			'generated'                         => __( 'Generated!', 'filebird' ),
			'imported'                          => __( 'Imported!', 'filebird' ),
			'please_try_again'                  => __( 'Please try again.', 'filebird' ),
			'successfully_exported'             => __( 'Successfully exported!', 'filebird' ),
			'successfully_imported'             => __( 'Successfully imported!', 'filebird' ),
			'active_to_use_feature'             => __( 'Please activate FileBird license to use this feature.', 'filebird' ),
			'all_folders'                       => __( 'All folders', 'filebird' ),
			'common_folders'                    => __( 'Common folders', 'filebird' ),
			'all_folders_description'           => __( 'This option imports the common folder tree and user-based folder trees.', 'filebird' ),
			'common_folders_description'        => __( 'This option imports only the common folder tree.', 'filebird' ),
			'user_folders_description'          => __( 'This option imports the folder tree created by', 'filebird' ),
			'add_your_first_folder'             => __( 'Add your first folder', 'filebird' ),
			'add_your_first_folder_description' => __( 'You don\'t have any folder. Add folder to easily manage your', 'filebird' ),
			'add_folder'                        => __( 'Add Folder', 'filebird' ),
			'all_pages'                         => __( 'All Pages', 'filebird' ),
			'all_posts'                         => __( 'All Posts', 'filebird' ),
			'all_items'                         => __( 'All Items', 'filebird' ),
			'files'                             => __( 'files', 'filebird' ),
			'posts'                             => __( 'posts', 'filebird' ),
			'pages'                             => __( 'pages', 'filebird' ),
			'ascending'                         => __( 'Ascending', 'filebird' ),
			'descending'                        => __( 'Descending', 'filebird' ),
			'reset_folders_arrangement'         => __( 'Reset folders arrangement', 'filebird' ),
			'reset_files_arrangement'           => __( 'Reset files arrangement', 'filebird' ),
			'login_with_envato'                 => __( 'Login with Envato', 'filebird' ),
			'update_old_folder_desc'            => __(
                'By running this action, all folders created in version 3.9 & earlier installs will
			be imported.',
                'filebird'
                ),
			'import_from_old_version'           => __( 'Import from old version', 'filebird' ),
			'rest_api_key'                      => __( 'REST API key', 'filebird' ),
			'generate'                          => __( 'Generate', 'filebird' ),
			'attachment_size'                   => __( 'Attachment Size', 'filebird' ),
			'generate_api_desc'                 => __(
                'Please see FileBird API for developers
			<a
			  target="_blank"
			  href="https://ninjateam.gitbook.io/filebird/integrations/developer-zone/apis"
			  rel="noreferrer"
			>here</a>.',
                'filebird'
                ),
			'generate_attachment_size_desc'     => __( 'Generate attachment size used in "Sort by size" function.', 'filebird' ),
			'clear_all_data'                    => __( 'Clear All Data', 'filebird' ),
			'clear'                             => __( 'Clear', 'filebird' ),
			'clear_all_data_desc'               => __(
                 'This action will delete all FileBird data, FileBird settings and bring you back to
			WordPress default media library.',
                'filebird'
                ),
			'import_folders_desc'               => __(
                "Import categories/folders from other plugins. We import virtual folders, your website will
			be safe, don't worry ;)",
                'filebird'
                ),
			'contact_support'                   => __( 'Contact Support', 'filebird' ),
			'import'                            => __( 'Import', 'filebird' ),
			'export_csv'                        => __( 'Export CSV', 'filebird' ),
			'export_csv_now'                    => __( 'Export Now', 'filebird' ),
			'export_csv_desc'                   => __( 'The current folder structure will be exported.', 'filebird' ),
			'import_csv'                        => __( 'Import CSV', 'filebird' ),
			'import_csv_desc'                   => __(
                    'Choose FileBird CSV file to import.
                <br />
                (Please check to make sure that there is no duplicated name. The current folder
                structure is preserved.)
                <a
                  href="https://ninjateam.gitbook.io/filebird/settings/import-and-export-folder-structure"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  Learn more
                </a>
                <br />',
                    'filebird'
                    ),
			'download_file'                     => __( 'Download File', 'filebird' ),
			'choose_user_folder'                => __( 'Choose user folder:', 'filebird' ),
			'which_post_types_do_you_want'      => __( 'Which post types do you want to use with FileBird?', 'filebird' ),
			'by'                                => __( 'by', 'filebird' ),
			'display_folder_id'                 => __( 'Display folder ID', 'filebird' ),
			'hide_folder_id'                    => __( 'Hide folder ID', 'filebird' ),
			'active_your_license'               => __( 'Activate Your License!', 'filebird' ),
			'explore_filebird'                  => __( 'Explore the best of FileBird', 'filebird' ),
			'feature_1'                         => __( 'Download entire folders in ZIP', 'filebird' ),
			'feature_2'                         => __( 'Advanced third-party compatibility', 'filebird' ),
			'feature_3'                         => __( 'Sort files and folders', 'filebird' ),
			'feature_4'                         => __( 'Folder themes (Windows 11 and Dropbox)', 'filebird' ),
			'feature_5'                         => __( 'File count, breadcrumb, folder custom colors, and more', 'filebird' ),
			'feature_6'                         => __( 'One-time payment', 'filebird' ),
			'feature_7'                         => __( 'VIP live chat support', 'filebird' ),
			'congratulations'                   => __( 'Congratulations!', 'filebird' ),
			'activated_license'                 => __( 'Your FileBird Pro was activated!', 'filebird' ),
			'buy_another_license'               => __( 'Buy another license.', 'filebird' ),
			'your_license_is'                   => __( 'Your license is:', 'filebird' ),
			'license_note'                      => __( 'If you are on a development site and you want to activate this license later on the production domain, please deactivate it from here first.', 'filebird' ),
			'learn_more'                        => __( 'Learn more', 'filebird' ),
			'deactivated_license'               => __( 'Deactivate license.', 'filebird' ),
			'note'                              => __( 'Note', 'filebird' ),
			'starting_new_site'                 => __( 'Starting a new site?', 'filebird' ),
			'confirm'                           => __( 'Confirm', 'filebird' ),
			'sync_wpml'                         => __( 'Sync WPML', 'filebird' ),
			'sync'                              => __( 'Sync', 'filebird' ),
			'sync_wpml_desc'                    => __( 'Assign WPML existing translated media to FileBird folders.', 'filebird' ),
			'count'                             => __( 'Count', 'filebird' ),
			'count_in_folder'                   => __( 'Count files in each folder', 'filebird' ),
			'count_nested'                      => __( 'Count files in both parent folder and subfolders', 'filebird' ),
			'general'                           => __( 'General', 'filebird' ),
			'each_user_has_own_folder'          => __( 'Each user has his own folders', 'filebird' ),
			'show_breadcrumb'                   => __( 'Show breadcrumb', 'filebird' ),
			'folder_counter'                    => __( 'Folder counter', 'filebird' ),
			'theme'                             => __( 'Theme', 'filebird' ),
			'theme_alert'                       => sprintf( __( 'The theme switcher is for previewing purposes only. Please activate your <a href="%s" target="_blank" rel="noopener noreferrer">FileBird license</a> to apply it accordingly.', 'filebird' ), 'https://ninjateam.gitbook.io/filebird/features/interface/folder-tree-themes' ),
			'max_input_length'                  => __( 'Please ensure your folder name is fewer than 200 characters.', 'filebird' ),
			'svg_upload_desc'                   => __( 'Allow built-in SVG upload & sanitization', 'filebird' ),
			'open_filebird'                     => __( 'Open FileBird', 'filebird' ),
			'activation'                        => __( 'Activation', 'filebird' ),
			'tools'                             => __( 'Tools', 'filebird' ),
			'import_export'                     => __( 'Import/Export', 'filebird' ),
			'select_theme'                      => __( 'Select theme', 'filebird' ),
			'by'                                => __( 'By', 'filebird' ),
			'lifetime_license'                  => __( 'Lifetime license', 'filebird' ),
			'pro'                               => __( 'PRO', 'filebird' ),
			'no_folders_export'                 => __( 'There are no folders to export.', 'filebird' ),
			'searching_folder_api'              => __( 'Switch from searching using JavaScript to using an API', 'filebird' ),
			'folders_for_media_library'         => __( 'Folders for media library', 'filebird' ),
			'folders_for_post_types'            => __( 'Folders for post types', 'filebird' ),
		);
		return $translation;
	}
}