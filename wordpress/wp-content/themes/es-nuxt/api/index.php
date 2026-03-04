<?php
require_once 'router/get_page_custom.php';
require_once 'router/get_global.php';
require_once 'router/get_collection_works.php';
// sitemap
require_once 'router/get_sitemap.php';

/**
 * origin api
 * wp-json/wp/v2/[router]
 */
// !! 注意，後台「設定->永久連結」需要改成「http://localhost:9000/sample-post/」才可以生效

// 啟用 CORS 支援
add_action('rest_api_init', function () {
    remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
    add_filter('rest_pre_serve_request', function ($value) {
        $allowed_origins = array(
            'http://localhost:3000',
            'http://localhost:4001',
            'http://localhost:9000'
        );
        
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        header('Access-Control-Allow-Origin: ' . $origin);
    
        return $value;
    });
});

add_action('rest_api_init', function () {

    register_rest_route('api', '/get_global', array(
        'methods' => 'GET',
        'callback' => 'get_global'
    ));
    
    register_rest_route('api', '/get_page_custom', array(
        'methods' => 'GET',
        'callback' => 'get_page_custom'
    ));


    register_rest_route('api', '/get_collection_works', array(
        'methods' => 'GET',
        'callback' => 'get_collection_works'
    ));
    // sitemap
    register_rest_route('api', '/get_sitemap', array(
        'methods' => 'GET',
        'callback' => 'get_sitemap'
    ));
});