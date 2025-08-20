<?php
/**
 * ES nuxt theme
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 * @package WordPress
 */

add_filter( 'acf/settings/rest_api_format', function () {
    return 'standard';
});

// Cros
add_filter( 'allowed_http_origins', 'add_allowed_origins' );
function add_allowed_origins( $origins ) {
    $origins[] = 'http://localhost:3000';
    return $origins;
};

// 引入
include_once "setting/acf.php"; 
include_once "setting/dashboard.php"; 
include_once "setting/admin.php"; 
include_once "setting/custom-login.php"; 
include_once "setting/custom-cache.php"; 
include_once "api/index.php"; 
