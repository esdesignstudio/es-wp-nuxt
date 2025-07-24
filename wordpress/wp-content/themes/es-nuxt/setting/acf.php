<?php

// https://www.advancedcustomfields.com/resources/local-json/
add_filter('acf/settings/save_json', 'my_acf_json_save_point');
function my_acf_json_save_point( $path ) {
    
    // update path
    $path = get_stylesheet_directory() . '/acf-json';
    
    // return
    return $path;
    
}

// https://www.advancedcustomfields.com/resources/acf_add_options_page/
if( function_exists('acf_add_options_page') ) {
	
	acf_add_options_page(array(
		'page_title' 	=> '網站設定',
		'menu_title'	=> '網站設定',
		'menu_slug' 	=> 'theme-general-settings',
		'capability'	=> 'edit_posts',
		'redirect'		=> false,
        'position'      => '2.1',
	));
	
	// acf_add_options_sub_page(array(
	// 	'page_title' 	=> 'Theme Header Settings',
	// 	'menu_title'	=> 'Header',
	// 	'parent_slug'	=> 'theme-general-settings',
	// ));
	
	// acf_add_options_sub_page(array(
	// 	'page_title' 	=> 'Theme Footer Settings',
	// 	'menu_title'	=> 'Footer',
	// 	'parent_slug'	=> 'theme-general-settings',
	// ));
	
    // 添加保存後的動作
	add_action('acf/save_post', 'notify_frontend_on_options_update', 20);
}

// 新增函數來處理選項更新
function notify_frontend_on_options_update($post_id) {
	// 檢查是否為選項頁面
	if (strpos($post_id, 'options') !== false) {
		// 調用更新快取的函數，並傳遞 post_type
		if (function_exists('notify_frontend_on_update')) {
			notify_frontend_on_update($post_id, null, null);
		} else {
			error_log('notify_frontend_on_update 函數不存在');
		}
	}
}
