<?php
// ========================================
// 手動清除全部快取
// ========================================

// 在 Admin Bar 加入清除快取按鈕
function add_clear_cache_button($wp_admin_bar) {
    // 只有登入的使用者才能看到
    if (!is_user_logged_in()) {
        return;
    }
    
    $args = array(
        'id'    => 'clear-nuxt-cache',
        'title' => '<span class="ab-icon dashicons dashicons-update" style="margin-top: 2px;"></span> 清除全部快取',
        'href'  => '#',
        'meta'  => array(
            'class' => 'clear-nuxt-cache-button',
            'title' => '清除 Nuxt 全部快取'
        )
    );
    
    $wp_admin_bar->add_node($args);
}
add_action('admin_bar_menu', 'add_clear_cache_button', 999);

// 加入 JavaScript 處理點擊事件
function add_clear_cache_script() {
    // 只有登入的使用者才載入
    if (!is_user_logged_in() || !is_admin_bar_showing()) {
        return;
    }
    ?>
    <script type="text/javascript">
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            const clearCacheBtn = document.querySelector('#wp-admin-bar-clear-nuxt-cache a');
            
            if (!clearCacheBtn) return;
            
            clearCacheBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // 確認對話框
                if (!confirm('確定要清除全部快取嗎？')) {
                    return;
                }
                
                // 顯示載入狀態
                const originalText = clearCacheBtn.innerHTML;
                clearCacheBtn.innerHTML = '<span class="ab-icon dashicons dashicons-update" style="margin-top: 2px; animation: rotation 1s linear infinite;"></span> 清除中...';
                clearCacheBtn.style.pointerEvents = 'none';
                clearCacheBtn.style.opacity = '0.6';
                
                // 呼叫 WordPress AJAX（透過 PHP 轉發到 Nuxt）
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'clear_nuxt_cache',
                        nonce: '<?php echo wp_create_nonce('clear_cache_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            // 成功
                            clearCacheBtn.innerHTML = '<span class="ab-icon dashicons dashicons-yes" style="margin-top: 2px; color: #46b450;"></span> 清除成功！';
                            
                            // 顯示詳細訊息
                            const data = response.data;
                            if (data.details && data.details.length > 0) {
                                let message = '成功清除：\n';
                                data.details.forEach(function(detail) {
                                    message += '- ' + detail.type + ': ' + detail.count + ' 筆\n';
                                });
                                message += '\n總共清除 ' + data.totalCount + ' 筆快取';
                                alert(message);
                            } else {
                                alert(data.message || '快取清除成功！');
                            }
                        } else {
                            // 失敗
                            clearCacheBtn.innerHTML = '<span class="ab-icon dashicons dashicons-no" style="margin-top: 2px; color: #dc3232;"></span> 清除失敗';
                            alert('清除快取失敗：' + (response.data.message || '未知錯誤'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('清除快取錯誤:', error);
                        clearCacheBtn.innerHTML = '<span class="ab-icon dashicons dashicons-no" style="margin-top: 2px; color: #dc3232;"></span> 連線失敗';
                        alert('無法連線到伺服器：' + error);
                    }
                });
            });
        });
    })();
    </script>
    
    <style type="text/css">
        #wp-admin-bar-clear-nuxt-cache:hover a,
        #wp-admin-bar-clear-nuxt-cache:hover .ab-icon:before {
            color: #fff !important;
        }
    </style>
    <?php
}
add_action('wp_footer', 'add_clear_cache_script');
add_action('admin_footer', 'add_clear_cache_script');

// AJAX 處理（透過 WordPress 轉發請求到 Nuxt）
function handle_clear_cache_ajax() {
    // 檢查 nonce
    check_ajax_referer('clear_cache_nonce', 'nonce');
    
    // 檢查權限
    if (!(current_user_can('administrator') || current_user_can('editor'))) {
        wp_send_json_error(array(
            'message' => '您沒有權限執行此操作。'
        ));
        return;
    }
    
    // 取得 Nuxt API URL
    $API_URL = getenv('NUXT_API_URL') ?: 'http://nuxt-app:3000';
    $CLEAR_CACHE_API = $API_URL . '/api/cache/clear-all';
    
    // 呼叫 Nuxt API（使用 wp_remote_post）
    $response = wp_remote_post($CLEAR_CACHE_API, array(
        'method' => 'POST',
        'timeout' => 30,
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array())  // 空的 body
    ));
    
    // 檢查是否有錯誤
    if (is_wp_error($response)) {
        wp_send_json_error(array(
            'message' => '無法連線到 Nuxt 伺服器',
            'error' => $response->get_error_message()
        ));
        return;
    }
    
    // 取得回應內容
    $response_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    // 記錄日誌
    error_log('清除 Nuxt 快取 - 回應碼: ' . $response_code);
    error_log('清除 Nuxt 快取 - 回應內容: ' . $body);
    
    // 檢查回應
    if ($response_code === 200 && $data && isset($data['success']) && $data['success']) {
        wp_send_json_success($data);
    } else {
        wp_send_json_error(array(
            'message' => $data['message'] ?? '清除快取失敗',
            'response_code' => $response_code,
            'data' => $data
        ));
    }
}
add_action('wp_ajax_clear_nuxt_cache', 'handle_clear_cache_ajax');

// ========================================
// 自動清除快取功能
// ========================================

// 定義允許的 post types
$ALLOWED_POST_TYPES = array('page', 'work'); // 根據你的需求調整

/**
 * 當文章更新時自動清除 Nuxt 快取
 */
function auto_clear_nuxt_cache_on_save($post_id, $post, $update) {
    global $ALLOWED_POST_TYPES;
    
    // 檢查是否為自動儲存
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // 檢查是否為修訂版本
    if (wp_is_post_revision($post_id)) {
        return;
    }
    
    // 只處理允許的 post types
    if (!in_array($post->post_type, $ALLOWED_POST_TYPES)) {
        return;
    }
    
    // 只處理已發布的文章
    if ($post->post_status !== 'publish') {
        return;
    }
    
    // 取得 Nuxt API URL
    $API_URL = getenv('NUXT_API_URL') ?: 'http://nuxt-app:3000';
    
    // ========================================
    // Page post type 的處理（特殊邏輯）
    // ========================================
    if ($post->post_type === 'page') {
        // 檢查是否為首頁（ID 或 slug）
        $is_homepage = ($post->ID == get_option('page_on_front')) || ($post->post_name === 'index');
        
        if ($is_homepage) {
            // 首頁: 清除 / 路徑
            clear_route_cache('/', $post);
            // 首頁的 API 使用 slug=index
            clear_page_api_cache('index');
        } else {
            // 一般頁面: 使用 /{slug}
            $cache_path = '/' . $post->post_name;
            clear_route_cache($cache_path, $post);
            // 頁面的 API 使用 slug
            clear_page_api_cache($post->post_name);
        }
        
        return; // page 處理完畢，不繼續執行下面的邏輯
    }
    
    // ========================================
    // 其他 post types 的處理（work 等）
    // ========================================
    
    // 清除所有相關快取（Route Cache + API Cache）
    // clear_collection_api_cache 會處理：
    // 1. 單筆內容頁 Route Cache: /works/[slug]
    // 2. 單筆內容頁 API Cache: get_collection_work?slug=xxx
    // 3. 列表頁 Route Cache: /works
    // 4. 列表頁 API Cache: get_collection_work_list
    clear_collection_api_cache($post->post_type, $post->post_name);
}
add_action('save_post', 'auto_clear_nuxt_cache_on_save', 10, 3);

/**
 * 清除 Route Cache (SWR)
 */
function clear_route_cache($path, $post) {
    $API_URL = getenv('NUXT_API_URL') ?: 'http://nuxt-app:3000';
    $CLEAR_CACHE_API = $API_URL . '/api/cache/clear';
    
    // 不需要 encode，直接傳送原始路徑
    // API 會自己處理路徑匹配
    
    $response = wp_remote_post($CLEAR_CACHE_API, array(
        'method' => 'POST',
        'timeout' => 15,
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'path' => $path
        ))
    ));
    
    if (is_wp_error($response)) {
        error_log(sprintf(
            '[Nuxt Cache] 無法清除 Route Cache - 文章: %s (ID: %d), 路徑: %s, 錯誤: %s',
            $post->post_title,
            $post->ID,
            $path,
            $response->get_error_message()
        ));
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($response_code === 200 && $data && $data['success']) {
            error_log(sprintf(
                '[Nuxt Cache] ✓ 已清除 Route Cache - 文章: %s (ID: %d), 路徑: %s, 清除數量: %d',
                $post->post_title,
                $post->ID,
                $path,
                $data['count'] ?? 0
            ));
        } else {
            error_log(sprintf(
                '[Nuxt Cache] ✗ 清除 Route Cache 失敗 - 文章: %s (ID: %d), 路徑: %s, 訊息: %s',
                $post->post_title,
                $post->ID,
                $path,
                $data['message'] ?? '未知錯誤'
            ));
        }
    }
}

/**
 * 清除 Page API Cache (使用 Slug)
 * 統一使用 slug 查詢，包含首頁 (index)
 */
function clear_page_api_cache($slug) {
    $API_URL = getenv('NUXT_API_URL') ?: 'http://nuxt-app:3000';
    $CLEAR_CACHE_API = $API_URL . '/api/cache/clear';
    
    // Page 統一使用 get_page_custom?slug=xxx
    $api_path = '/wp-json/api/get_page_custom?slug=' . $slug;
    
    $response = wp_remote_post($CLEAR_CACHE_API, array(
        'method' => 'POST',
        'timeout' => 15,
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'path' => $api_path
        ))
    ));
    
    if (is_wp_error($response)) {
        error_log(sprintf(
            '[Nuxt Cache] 無法清除 Page API Cache - Slug: %s, API: %s, 錯誤: %s',
            $slug,
            $api_path,
            $response->get_error_message()
        ));
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($response_code === 200 && $data && $data['success']) {
            error_log(sprintf(
                '[Nuxt Cache] ✓ 已清除 Page API Cache - Slug: %s, API: %s, 清除數量: %d',
                $slug,
                $api_path,
                $data['totalCount'] ?? 0
            ));
        } else {
            error_log(sprintf(
                '[Nuxt Cache] ✗ 清除 Page API Cache 失敗 - Slug: %s, API: %s, 訊息: %s',
                $slug,
                $api_path,
                $data['message'] ?? '未知錯誤'
            ));
        }
    }
}

/**
 * 清除 Collection 相關的所有快取 (用於 work 等其他 post types)
 * 會清除：
 * 1. 單筆內容頁 Route Cache: /works/[slug]
 * 2. 單筆內容頁 API Cache: get_collection_[post_type]?slug=xxx
 * 3. 列表頁 Route Cache: /works
 * 4. 列表頁 API Cache: get_collection_[post_type]_list (所有 query 參數)
 */
function clear_collection_api_cache($post_type, $slug) {
    $API_URL = getenv('NUXT_API_URL') ?: 'http://nuxt-app:3000';
    $CLEAR_CACHE_API = $API_URL . '/api/cache/clear';
    
    // 根據 post type 決定列表頁路徑
    $list_route_path = '';
    if ($post_type === 'work') {
        $list_route_path = '/works';
    } else {
        $list_route_path = '/' . $post_type . 's'; // 預設加 s
    }
    
    // 根據 post type 決定單筆頁路徑
    $single_route_path = '';
    if ($post_type === 'work') {
        $single_route_path = '/works/' . $slug;
    } else {
        $single_route_path = '/' . $post_type . '/' . $slug;
    }
    
    // ========================================
    // 1. 清除單筆內容頁 Route Cache
    // ========================================
    $response = wp_remote_post($CLEAR_CACHE_API, array(
        'method' => 'POST',
        'timeout' => 15,
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'path' => $single_route_path
        ))
    ));
    
    if (is_wp_error($response)) {
        error_log(sprintf(
            '[Nuxt Cache] 無法清除單筆 Route Cache - Post Type: %s, Slug: %s, 路徑: %s, 錯誤: %s',
            $post_type,
            $slug,
            $single_route_path,
            $response->get_error_message()
        ));
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($response_code === 200 && $data && $data['success']) {
            error_log(sprintf(
                '[Nuxt Cache] ✓ 已清除單筆 Route Cache - Post Type: %s, Slug: %s, 路徑: %s, 清除數量: %d',
                $post_type,
                $slug,
                $single_route_path,
                $data['totalCount'] ?? 0
            ));
        } else {
            error_log(sprintf(
                '[Nuxt Cache] ✗ 清除單筆 Route Cache 失敗 - Post Type: %s, Slug: %s, 路徑: %s, 訊息: %s',
                $post_type,
                $slug,
                $single_route_path,
                $data['message'] ?? '未知錯誤'
            ));
        }
    }
    
    // ========================================
    // 2. 清除單筆內容頁 API Cache
    // ========================================
    $single_api_path = '/wp-json/api/get_collection_' . $post_type . '?slug=' . $slug;
    
    $response = wp_remote_post($CLEAR_CACHE_API, array(
        'method' => 'POST',
        'timeout' => 15,
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'path' => $single_api_path
        ))
    ));
    
    if (is_wp_error($response)) {
        error_log(sprintf(
            '[Nuxt Cache] 無法清除單筆 API Cache - Post Type: %s, Slug: %s, API: %s, 錯誤: %s',
            $post_type,
            $slug,
            $single_api_path,
            $response->get_error_message()
        ));
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($response_code === 200 && $data && $data['success']) {
            error_log(sprintf(
                '[Nuxt Cache] ✓ 已清除單筆 API Cache - Post Type: %s, Slug: %s, API: %s, 清除數量: %d',
                $post_type,
                $slug,
                $single_api_path,
                $data['totalCount'] ?? 0
            ));
        } else {
            error_log(sprintf(
                '[Nuxt Cache] ✗ 清除單筆 API Cache 失敗 - Post Type: %s, Slug: %s, API: %s, 訊息: %s',
                $post_type,
                $slug,
                $single_api_path,
                $data['message'] ?? '未知錯誤'
            ));
        }
    }
    
    // ========================================
    // 3. 清除列表頁 Route Cache
    // ========================================
    $response = wp_remote_post($CLEAR_CACHE_API, array(
        'method' => 'POST',
        'timeout' => 15,
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'path' => $list_route_path
        ))
    ));
    
    if (is_wp_error($response)) {
        error_log(sprintf(
            '[Nuxt Cache] 無法清除列表 Route Cache - Post Type: %s, 路徑: %s, 錯誤: %s',
            $post_type,
            $list_route_path,
            $response->get_error_message()
        ));
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($response_code === 200 && $data && $data['success']) {
            error_log(sprintf(
                '[Nuxt Cache] ✓ 已清除列表 Route Cache - Post Type: %s, 路徑: %s, 清除數量: %d',
                $post_type,
                $list_route_path,
                $data['totalCount'] ?? 0
            ));
        } else {
            error_log(sprintf(
                '[Nuxt Cache] ✗ 清除列表 Route Cache 失敗 - Post Type: %s, 路徑: %s, 訊息: %s',
                $post_type,
                $list_route_path,
                $data['message'] ?? '未知錯誤'
            ));
        }
    }
    
    // ========================================
    // 4. 清除列表頁 API Cache（所有 query 參數）
    // ========================================
    $list_api_path = '/wp-json/api/get_collection_' . $post_type . '_list';
    
    $response = wp_remote_post($CLEAR_CACHE_API, array(
        'method' => 'POST',
        'timeout' => 15,
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'path' => $list_api_path
        ))
    ));
    
    if (is_wp_error($response)) {
        error_log(sprintf(
            '[Nuxt Cache] 無法清除列表 API Cache - Post Type: %s, API: %s, 錯誤: %s',
            $post_type,
            $list_api_path,
            $response->get_error_message()
        ));
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($response_code === 200 && $data && $data['success']) {
            error_log(sprintf(
                '[Nuxt Cache] ✓ 已清除列表 API Cache - Post Type: %s, API: %s, 清除數量: %d',
                $post_type,
                $list_api_path,
                $data['totalCount'] ?? 0
            ));
        } else {
            error_log(sprintf(
                '[Nuxt Cache] ✗ 清除列表 API Cache 失敗 - Post Type: %s, API: %s, 訊息: %s',
                $post_type,
                $list_api_path,
                $data['message'] ?? '未知錯誤'
            ));
        }
    }
}

/**
 * 在 WordPress 後台顯示清除快取的通知
 */
function show_cache_clear_notice() {
    // 檢查是否有快取清除的 transient
    $cache_cleared = get_transient('nuxt_cache_cleared');
    
    if ($cache_cleared !== false) {
        $post_title = $cache_cleared['post_title'];
        $post_id = $cache_cleared['post_id'];
        $cache_path = $cache_cleared['cache_path'];
        
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p><strong>Nuxt 快取已更新</strong></p>';
        echo '<p>文章：' . esc_html($post_title) . ' (ID: ' . esc_html($post_id) . ')</p>';
        echo '<p>路徑：' . esc_html($cache_path) . '</p>';
        echo '</div>';
        
        delete_transient('nuxt_cache_cleared');
    }
}
add_action('admin_notices', 'show_cache_clear_notice');

