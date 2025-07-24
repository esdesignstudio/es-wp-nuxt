<?php
require_once 'router/get_page_custom.php';
require_once 'router/get_global.php';
// sitemap
require_once 'router/get_sitemap.php';

/**
 * origin api
 * wp-json/wp/v2/[router]
 */
// !! 注意，後台「設定->永久連結」需要改成「http://localhost:9000/sample-post/」才可以生效

add_action('rest_api_init', function () {

    register_rest_route('api', '/get_global', array(
        'methods' => 'GET',
        'callback' => 'get_global'
    ));
    
    register_rest_route('api', '/get_page_custom', array(
        'methods' => 'GET',
        'callback' => 'get_page_custom'
    ));

    // sitemap
    register_rest_route('api', '/get_sitemap', array(
        'methods' => 'GET',
        'callback' => 'get_sitemap'
    ));
});