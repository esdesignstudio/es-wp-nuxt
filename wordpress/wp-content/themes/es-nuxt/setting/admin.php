<?php 

// 修改後台底下的wordpress文字宣告
function custom_dashboard_footer ($text) {
    // 確保返回非空字串，避免與其他插件衝突
    return '網站設計單位 : <a href="https://e-s.tw" target="_blank">ES Design</a>，技術採用：開源程式<a href="https://wordpress.org/" target="_blank">Wordpress CMS</a>'; 
}
add_filter('admin_footer_text', 'custom_dashboard_footer', 20);

// 隱藏後台右下角wp版本號
function change_footer_version() {return 'Design is a relationship';}
add_filter( 'update_footer', 'change_footer_version', 9999);

//登入用的css
function eslogin_style() {
    wp_enqueue_style('admin-styles', get_template_directory_uri().'/asset/custom.css');
    wp_enqueue_style( 'custom-login', get_stylesheet_directory_uri() . '/asset/login.css' );
    wp_enqueue_script( 'custom-login', get_stylesheet_directory_uri() . '/asset/login.css' );
}
add_action( 'login_enqueue_scripts', 'eslogin_style' );

//後台使用的 css
function admin_style() {
    wp_enqueue_style('admin-styles', get_template_directory_uri().'/asset/custom.css');
    wp_enqueue_script('admin-scripts', get_template_directory_uri().'/asset/scripts.js');
}
add_action('admin_enqueue_scripts', 'admin_style');


//關閉 Gutenberg
add_filter( 'use_block_editor_for_post', 'es_disable_gutenberg', 10, 2 );
function es_disable_gutenberg( $can_edit, $post ) {
  if( $post->post_type == 'page' && get_page_template_slug( $post->ID ) == 'page-home.php' ) {
    return true;
  }

  return false;
}

// 移除所有留言
// https://gist.github.com/mattclements/eab5ef656b2f946c4bfb
add_action('admin_init', function () {
    // Redirect any user trying to access comments page
    global $pagenow;
    
    if ($pagenow === 'edit-comments.php') {
        wp_redirect(admin_url());
        exit;
    }

    // Remove comments metabox from dashboard
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');

    // Disable support for comments and trackbacks in post types
    foreach (get_post_types() as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
});

// 取消最大圖片寬高限制 2560px
add_filter( 'big_image_size_threshold', '__return_false' );

// Close comments on the front-end
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);

// Hide existing comments
add_filter('comments_array', '__return_empty_array', 10, 2);

// Remove comments page in menu
add_action('admin_menu', function () {
    remove_menu_page('edit-comments.php');
});

// Remove comments links from admin bar
add_action('init', function () {
    if (is_admin_bar_showing()) {
        remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
    }
});

// 停用所有自動更新
add_filter('automatic_updater_disabled', '__return_true');
add_filter('auto_update_core', '__return_false');
add_filter('auto_update_plugin', '__return_false');
// add_filter('auto_update_theme', '__return_false');
// add_filter('auto_update_translation', '__return_false');
add_filter('allow_minor_auto_core_updates', '__return_false');
add_filter('allow_major_auto_core_updates', '__return_false');

// 移除更新通知
// add_filter('pre_site_transient_update_core', '__return_null');
// add_filter('pre_site_transient_update_plugins', '__return_null');
// add_filter('pre_site_transient_update_themes', '__return_null');

// 針對非管理員隱藏特定後台選單
add_action('admin_menu', function() {
    if (!current_user_can('administrator')) {
        // 移除工具頁面
        remove_menu_page('tools.php');
        
        // 移除設定頁面
        remove_menu_page('options-general.php');
        
        // 移除 ACF 欄位群組頁面
        remove_menu_page('edit.php?post_type=acf-field-group');
        
        // 移除佈景主題頁面
        remove_menu_page('themes.php');
        
        // 移除外掛頁面
        remove_menu_page('plugins.php');
        
        // 移除 ACF 選項頁面
        remove_menu_page('acf-options');
        
        // 移除 Polylang 設定頁面
        remove_menu_page('mlang');
    }
});
