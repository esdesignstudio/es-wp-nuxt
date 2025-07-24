<?php

use FileBird\I18n;

defined( 'ABSPATH' ) || exit;

if (!function_exists('filebird_gallery_block_assets')) {
    function filebird_gallery_block_assets() {
        wp_enqueue_style( 'filebird_gallery-fb-block-css' );
    
        wp_register_style( 'fbv-photoswipe', NJFB_PLUGIN_URL . 'assets/css/photoswipe/photoswipe.css', array(), NJFB_VERSION );
        wp_register_style( 'fbv-photoswipe-default-skin', NJFB_PLUGIN_URL . 'assets/css/photoswipe/default-skin.css', array(), NJFB_VERSION );
    
        wp_register_script( 'fbv-photoswipe', NJFB_PLUGIN_URL . 'assets/js/photoswipe/photoswipe.min.js', array(), NJFB_VERSION, true );
        wp_register_script( 'fbv-photoswipe-ui-default', NJFB_PLUGIN_URL . 'assets/js/photoswipe/photoswipe-ui-default.min.js', array(), NJFB_VERSION, true );
        wp_register_script( 'filebird-gallery', NJFB_PLUGIN_URL . 'assets/js/photoswipe/fbv-photoswipe.min.js', array(), NJFB_VERSION, true );
    
        register_block_type( __DIR__ . '/build' );
    }
}

if (!function_exists('filebird_gutenberg_get_images')) {
    function filebird_gutenberg_get_images() {
        register_rest_route(
            NJFB_REST_URL,
            'gutenberg-get-images',
            array(
                'methods'             => 'POST',
                'callback'            => 'filebird_gutenberg_render_callback',
                'permission_callback' => function(){
                    return current_user_can( 'upload_files' );
                }
            )
        );
    }
}

if (!function_exists('filebird_gutenberg_render_callback')) {
    function filebird_gutenberg_render_callback( $request ) {
        $attributes = $request->get_params();

        ob_start();
        include NJFB_PLUGIN_PATH . '/blocks/filebird-gallery/build/render.php';
        $html = ob_get_clean();
        wp_send_json(
            array(
                'html' => $html,
            )
        );
    }
}

add_action( 'init', 'filebird_gallery_block_assets', PHP_INT_MAX );
add_action( 'rest_api_init', 'filebird_gutenberg_get_images' );