<?php
namespace FileBird\Classes\Modules;

class ModuleExclude {
	public function __construct() {
		add_filter( 'fbv_get_count_where_query', array( $this, 'exclude_get_count_where_query' ), 10, 1 );
	}

	public function exclude_get_count_where_query( $where ) {
		global $wpdb;
		if ( function_exists( '_is_elementor_installed' ) ) {
			$where[] = " posts.ID NOT IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_elementor_is_screenshot') ";
		}

		if ( function_exists( 'picu_exclude_collection_images_from_library' ) ) {
			$where[] = " posts.post_parent NOT IN (SELECT {$wpdb->posts}.ID FROM {$wpdb->posts} WHERE {$wpdb->posts}.post_type = 'picu_collection') ";
		}

		if ( function_exists( 'uncode_get_gallery_attachment_ids' ) ) {
			$media_attachments_ids = implode( ',', uncode_get_gallery_attachment_ids() );
			$where[]               = "posts.ID NOT IN ($media_attachments_ids)";
		}

		// Compatible https://wordpress.org/plugins/pdf-image-generator/
		if ( class_exists( 'PIGEN' ) ) {
			$opt = get_option( 'pigen_options' );
			if ( isset( $opt['hidethumb'] ) && $opt['hidethumb'] !== '' ) {
				$where[] = " posts.ID NOT IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_thumbnail_id') ";
			}
		}

		// Compatible https://wordpress.org/plugins/w3-total-cache/
		if ( defined( 'W3TC_VERSION' ) ) {
			$where[] = " posts.ID NOT IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'w3tc_imageservice_file') ";
		}

		return $where;
	}
}