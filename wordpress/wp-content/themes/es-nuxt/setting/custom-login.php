<?php 

// =====================================
// 完全替換 wp-login 路徑 - 主題整合版
// =====================================
// 
// 功能：
// 1. 完全替換 wp-login.php 為自訂路徑
// 2. 所有 wp- 路徑未登入時直接導向 404
//
// 使用方法：
// 修改下方的 'wp-es-login' 為您想要的登入網址名稱
//
$es_custom_login_slug = 'wp-es-login';

// =====================================
// 早期攔截機制 - 在主題載入時立即執行
// =====================================

// 立即檢查並攔截請求（主題載入時執行）
// 只攔截 GET 請求到禁止路徑，允許 POST 登入請求
if (!defined('DOING_AJAX') && !defined('DOING_CRON') && !defined('WP_CLI')) {
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    $parsed_url = parse_url($request_uri);
    $path = ltrim($parsed_url['path'] ?? '', '/');
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    
    // 只攔截 GET 請求的 wp-login 和 wp-login.php 路徑
    // 允許 POST 請求（登入提交）和登出操作通過
    if (($path === 'wp-login' || $path === 'wp-login.php') && $method === 'GET') {
        // 檢查是否為登出操作或登出後頁面
        $query_string = $parsed_url['query'] ?? '';
        if (strpos($query_string, 'action=logout') !== false || 
            strpos($query_string, 'loggedout=true') !== false) {
            // 允許登出相關操作通過
            return;
        }
        
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $redirect_url = $protocol . '://' . $host . '/404';
        
        header('HTTP/1.1 302 Found');
        header('Location: ' . $redirect_url);
        exit;
    }
}

// =====================================
// WordPress Hook 機制
// =====================================

// 在 WordPress 初始化之前攔截請求
function es_early_intercept() {
    global $es_custom_login_slug;
    
    // 只在前端執行
    if (is_admin() || defined('DOING_AJAX') || defined('DOING_CRON')) {
        return;
    }
    
    $request_uri = $_SERVER['REQUEST_URI'];
    $parsed_url = parse_url($request_uri);
    $path = ltrim($parsed_url['path'], '/');
    
    // 檢查是否訪問自訂登入路徑
    if ($path === $es_custom_login_slug || strpos($path, $es_custom_login_slug . '/') === 0) {
        // 早期攔截，在 WordPress 完全載入前處理
        add_action('wp_loaded', 'es_intercept_login_requests', 1);
    }
}
add_action('init', 'es_early_intercept', 1);

// 攔截所有請求，檢查是否為自訂登入路徑
function es_intercept_login_requests() {
    global $es_custom_login_slug;
    
    $request_uri = $_SERVER['REQUEST_URI'];
    $parsed_url = parse_url($request_uri);
    $path = $parsed_url['path'];
    
    // 移除開頭的斜線來比較
    $clean_path = ltrim($path, '/');
    
    // 檢查是否訪問自訂登入路徑
    if ($clean_path === $es_custom_login_slug || strpos($clean_path, $es_custom_login_slug . '/') === 0) {
        // 設置 WordPress 環境
        if (!defined('WP_USE_THEMES')) {
            define('WP_USE_THEMES', false);
        }
        
        // 設置查詢參數
        $query_string = $parsed_url['query'] ?? '';
        if ($query_string) {
            parse_str($query_string, $query_params);
            foreach ($query_params as $key => $value) {
                $_GET[$key] = $value;
                $_REQUEST[$key] = $value;
            }
        }
        
        // 設置 WordPress 的全局變數，讓它認為這是 wp-login.php
        $GLOBALS['pagenow'] = 'wp-login.php';
        $_SERVER['SCRIPT_NAME'] = '/wp-login.php';
        $_SERVER['PHP_SELF'] = '/wp-login.php';
        
        // 設置正確的請求 URI，讓 WordPress 知道這是從自訂路徑來的
        $_SERVER['REQUEST_URI'] = '/wp-login.php' . (isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '');
        
        // 初始化 wp-login.php 需要的變數
        $user_login = '';
        $error = '';
        $errors = new WP_Error();
        $redirect_to = '';
        $requested_redirect_to = '';
        
        // 從查詢參數中設置變數
        if (isset($_REQUEST['user_login'])) {
            $user_login = $_REQUEST['user_login'];
        }
        if (isset($_REQUEST['redirect_to'])) {
            $redirect_to = $_REQUEST['redirect_to'];
            $requested_redirect_to = $_REQUEST['redirect_to'];
        }
        if (isset($_REQUEST['error'])) {
            $error = $_REQUEST['error'];
        }
        
        // 設置全域變數
        $GLOBALS['user_login'] = $user_login;
        $GLOBALS['error'] = $error;
        $GLOBALS['errors'] = $errors;
        $GLOBALS['redirect_to'] = $redirect_to;
        $GLOBALS['requested_redirect_to'] = $requested_redirect_to;
        
        // 載入 wp-login.php 的內容
        ob_start();
        include ABSPATH . 'wp-login.php';
        $content = ob_get_clean();
        
        // 輸出內容並結束
        echo $content;
        exit;
    }
}

// 最早期攔截所有 wp-login 相關路徑
function es_very_early_block() {
    $request_uri = $_SERVER['REQUEST_URI'];
    $parsed_url = parse_url($request_uri);
    $path = ltrim($parsed_url['path'], '/');
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    
    // 只攔截 GET 請求的 wp-login 或 wp-login.php
    // 允許 POST 請求（登入提交）和登出操作通過
    if (($path === 'wp-login' || $path === 'wp-login.php') && $method === 'GET') {
        // 檢查是否為登出操作或登出後頁面
        $query_string = $parsed_url['query'] ?? '';
        if (strpos($query_string, 'action=logout') !== false || 
            strpos($query_string, 'loggedout=true') !== false) {
            // 允許登出相關操作通過
            return;
        }
        
        // 直接重定向到 404，完全不讓 WordPress 處理
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $redirect_url = $protocol . '://' . $host . '/404';
        
        header('Location: ' . $redirect_url, true, 302);
        exit;
    }
}

// 在最早的時間點執行攔截
add_action('muplugins_loaded', 'es_very_early_block', 1);
add_action('plugins_loaded', 'es_very_early_block', 1);

// 阻止直接訪問 wp-login.php 和 wp-login
function es_block_direct_wp_login() {
    global $pagenow;
    
    if (!is_admin() && !defined('DOING_AJAX') && !defined('DOING_CRON')) {
        $request_uri = $_SERVER['REQUEST_URI'];
        $parsed_url = parse_url($request_uri);
        $path = ltrim($parsed_url['path'], '/');
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // 只攔截 GET 請求，允許 POST 請求（登入提交）和登出操作
        if (($pagenow === 'wp-login.php' || $path === 'wp-login' || $path === 'wp-login.php') && $method === 'GET') {
            // 檢查是否為登出操作或登出後頁面
            if ((isset($_GET['action']) && $_GET['action'] === 'logout') || 
                (isset($_GET['loggedout']) && $_GET['loggedout'] === 'true')) {
                // 允許登出相關操作通過
                return;
            }
            
            // 重定向到 404
            wp_redirect(home_url('/404'));
            exit;
        }
    }
}
add_action('template_redirect', 'es_block_direct_wp_login');

// 阻止所有 wp- 路徑未登入時的自動重定向
function es_block_wp_paths_redirect() {
    global $pagenow;
    
    // 阻止後台訪問（只針對未登入用戶）
    if (is_admin() && !is_user_logged_in() && $pagenow !== 'admin-ajax.php') {
        wp_die('403 - 訪問被拒絕', '訪問被拒絕', array('response' => 403));
    }
    
    // 如果用戶已登入，允許所有 wp- 路徑
    if (is_user_logged_in()) {
        return;
    }
    
    // 阻止其他 wp- 路徑的訪問（只針對未登入用戶）
    if (!is_admin()) {
        $request_uri = $_SERVER['REQUEST_URI'];
        $parsed_url = parse_url($request_uri);
        $path = ltrim($parsed_url['path'], '/');
        
        // 檢查是否為 wp- 開頭的路徑
        if (strpos($path, 'wp-') === 0) {
            global $es_custom_login_slug;
            
            // 允許自訂登入路徑
            if ($path === $es_custom_login_slug || strpos($path, $es_custom_login_slug . '/') === 0) {
                return;
            }
            
            // 允許靜態資源
            if (strpos($path, 'wp-content/') === 0 || 
                strpos($path, 'wp-includes/') === 0 || 
                strpos($path, 'wp-json/') === 0) {
                return;
            }
            
            // 其他 wp- 路徑重定向到 404（只針對未登入用戶）
            wp_redirect(home_url('/404'));
            exit;
        }
    }
}
add_action('admin_init', 'es_block_wp_paths_redirect');
add_action('template_redirect', 'es_block_wp_paths_redirect');

// 修改 WordPress 內部的登入 URL 引用
function es_replace_login_url($login_url, $redirect, $force_reauth) {
    global $es_custom_login_slug;
    
    $custom_url = home_url('/' . $es_custom_login_slug);
    
    if (!empty($redirect)) {
        $custom_url .= '?redirect_to=' . urlencode($redirect);
    }
    
    if ($force_reauth) {
        $separator = (strpos($custom_url, '?') !== false) ? '&' : '?';
        $custom_url .= $separator . 'reauth=1';
    }
    
    return $custom_url;
}
add_filter('login_url', 'es_replace_login_url', 10, 3);

// 修改註冊頁面 URL
function es_replace_register_url($register_url) {
    global $es_custom_login_slug;
    return home_url('/' . $es_custom_login_slug . '?action=register');
}
add_filter('register_url', 'es_replace_register_url');

// 修改忘記密碼 URL
function es_replace_lostpassword_url($lostpassword_url) {
    global $es_custom_login_slug;
    return home_url('/' . $es_custom_login_slug . '?action=lostpassword');
}
add_filter('lostpassword_url', 'es_replace_lostpassword_url');

// 修改登出確認頁面中的登入連結
function es_replace_logout_url($logout_url) {
    global $es_custom_login_slug;
    // 將 wp-login.php 替換為自訂登入路徑
    return str_replace('wp-login.php', $es_custom_login_slug, $logout_url);
}
add_filter('logout_url', 'es_replace_logout_url');

// 移除 WordPress 預設的登入重定向邏輯
remove_action('template_redirect', 'wp_redirect_admin_locations', 1000);

// 覆蓋 WordPress 核心的認證重定向函數
if (!function_exists('wp_redirect_admin_locations')) {
    function wp_redirect_admin_locations() {
        // 完全停用這個函數，不讓它重定向到登入頁面
        return;
    }
}

// 覆蓋 must_log_in 函數
function es_override_must_log_in() {
    wp_redirect(home_url('/404'));
    exit();
}

// 在最早期執行，覆蓋核心函數
function es_override_core_functions() {
    // 如果檢測到未登入的後台訪問，直接阻止
    if (is_admin() && !is_user_logged_in()) {
        // 覆蓋 wp_die 在認證失敗時的行為
        add_filter('wp_die_handler', function($function) {
            return function($message, $title, $args) {
                wp_redirect(home_url('/404'));
                exit();
            };
        });
    }
}
add_action('muplugins_loaded', 'es_override_core_functions', 1);

// 移除所有可能觸發登入重定向的 WordPress 內建行為
function es_remove_login_redirects() {
    // 移除認證失敗時的重定向
    remove_action('wp_authenticate', 'wp_authenticate_username_password', 20);
    remove_action('wp_authenticate', 'wp_authenticate_email_password', 20);
    
    // 移除各種登入檢查
    remove_action('admin_init', 'wp_admin_headers');
    remove_action('init', 'wp_widgets_init', 1);
    
    // 阻止 auth_redirect 函數的執行
    if (!function_exists('auth_redirect')) {
        function auth_redirect($scheme = '') {
            if (!is_user_logged_in()) {
                wp_redirect(home_url('/404'));
                exit();
            }
        }
    }
}
add_action('init', 'es_remove_login_redirects', 1);

// 更強力的後台訪問阻止
function es_force_admin_block() {
    if (is_admin() && !is_user_logged_in() && !defined('DOING_AJAX') && !defined('DOING_CRON')) {
        // 直接重定向到 404，不給 WordPress 任何機會處理
        wp_redirect(home_url('/404'));
        exit();
    }
}
// 在最早期執行，比其他所有 admin_init 都早
add_action('admin_init', 'es_force_admin_block', 1);

// 覆蓋 wp_redirect 在特定情況下的行為
function es_intercept_wp_redirect($location, $status) {
    // 檢查是否為登出操作
    if (isset($_GET['action']) && $_GET['action'] === 'logout') {
        // 允許登出重定向，不攔截
        return $location;
    }
    
    // 檢查是否為登出後的重定向
    if (strpos($_SERVER['REQUEST_URI'], 'action=logout') !== false) {
        // 允許登出流程完成
        return $location;
    }
    
    // 只對未登入用戶攔截重定向到登入頁面
    if (!is_user_logged_in()) {
        // 檢查是否要重定向到登入頁面（包含自訂登入頁面）
        if (strpos($location, 'wp-login.php') !== false || 
            strpos($location, '/wp-login') !== false ||
            strpos($location, 'wp-es-login') !== false) {
            // 改為重定向到 404
            return home_url('/404');
        }
    }
    return $location;
}
add_filter('wp_redirect', 'es_intercept_wp_redirect', 10, 2);

// 處理登入成功後的重定向
function es_login_redirect_handler($redirect_to, $request, $user) {
    // 如果沒有指定重定向位置，重定向到後台
    if (empty($redirect_to) || $redirect_to === 'wp-admin/' || $redirect_to === admin_url()) {
        return admin_url();
    }
    
    // 如果重定向到自訂登入頁面，改為重定向到後台
    global $es_custom_login_slug;
    if (strpos($redirect_to, $es_custom_login_slug) !== false) {
        return admin_url();
    }
    
    return $redirect_to;
}
add_filter('login_redirect', 'es_login_redirect_handler', 10, 3);

// 處理登出後的重定向
function es_logout_redirect_handler($redirect_to, $requested_redirect_to, $user) {
    // 登出後重定向到首頁，確保 cookies 已被清除
    return home_url();
}
add_filter('logout_redirect', 'es_logout_redirect_handler', 10, 3);

// 攔截登出後的 wp-login.php 重定向
function es_handle_logout_redirect() {
    // 檢查是否是登出後的頁面
    if (isset($_GET['loggedout']) && $_GET['loggedout'] === 'true') {
        // 如果是登出後的頁面，重定向到首頁
        wp_redirect(home_url());
        exit;
    }
}
add_action('wp_loaded', 'es_handle_logout_redirect', 1);
