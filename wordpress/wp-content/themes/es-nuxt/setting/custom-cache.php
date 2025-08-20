<?php 


// 客製化更新按鈕
// 添加 admin bar 按鈕
add_action('admin_bar_menu', 'add_refresh_button', 100);

function add_refresh_button($wp_admin_bar) {
    // 檢查當前用戶是否為管理員
    if (current_user_can('administrator') || current_user_can('editor')) {
        $wp_admin_bar->add_node(array(
            'id'    => 'refresh-frontend',
            'title' => '更新前端快取',
            'href'  => '#',
            'meta'  => array('onclick' => 'startFrontendRefresh(); return false;'),
        ));
    }
}

// 共用參數
$ALLOWED_POST_TYPES = array('page', 'article', 'writer');
// 取得 API_URL
$API_URL = getenv('NUXT_API_URL') ?: 'http://nuxt-app:3000';
$API_URL = $API_URL . '/api/revalidate';

// 共用函數
function send_revalidate_request($body) {
    global $API_URL;
    
    $response = wp_remote_post($API_URL, array(
        'method' => 'POST',
        'body' => json_encode($body),
        'headers' => array('Content-Type' => 'application/json'),
        'timeout' => 15
    ));

    return $response;
}

function handle_refresh_frontend() {
    global $ALLOWED_POST_TYPES;

    if (!(current_user_can('administrator') || current_user_can('editor'))) {
        wp_send_json_error('您沒有權限執行此操作。');
    }

    check_ajax_referer('refresh_frontend_nonce', 'nonce');

    $batch_size = 10;
    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;

    $success_count = 0;
    $error_messages = array();

    // 在第一次請求時更新全局設定和 options 頁面
    if ($offset === 0) {
        // 更新全局設定
        $global_response = send_revalidate_request(array(
            'id' => 0,
            'type' => 'global',
        ));

        if (is_wp_error($global_response)) {
            $error_messages[] = sprintf('無法更新全局設定：%s', $global_response->get_error_message());
        } else {
            $success_count++;
        }

        // 更新每個允許的 post type 的 options 頁面
        foreach ($ALLOWED_POST_TYPES as $post_type) {
            $options_response = send_revalidate_request(array(
                'id' => $post_type . '_options',
                'type' => $post_type
            ));

            if (is_wp_error($options_response)) {
                $error_messages[] = sprintf('無法更新 %s options：%s', $post_type, $options_response->get_error_message());
            } else {
                $success_count++;
            }
        }
    }

    $all_posts = array();
    foreach ($ALLOWED_POST_TYPES as $post_type) {
        $posts = get_posts(array(
            'post_type' => $post_type,
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'fields' => 'ids'
        ));
        $all_posts = array_merge($all_posts, $posts);
    }

    $total_posts = count($all_posts);
    $current_batch = array_slice($all_posts, $offset, $batch_size);

    foreach ($current_batch as $post_id) {
        $post = get_post($post_id);
        $response = send_revalidate_request(array(
            'id' => $post_id,
            'type' => $post->post_type,
            'slug' => $post->post_name
        ));

        if (is_wp_error($response)) {
            $error_messages[] = sprintf('無法更新 %s（ID: %d）：%s', $post->post_title, $post_id, $response->get_error_message());
        } else {
            $success_count++;
        }
    }

    $next_offset = $offset + $batch_size;
    $is_completed = $next_offset >= $total_posts;

    wp_send_json_success(array(
        'success_count' => $success_count,
        'error_messages' => $error_messages,
        'next_offset' => $next_offset,
        'total_posts' => $total_posts,
        'is_completed' => $is_completed
    ));
}

// 添加 AJAX 處理
add_action('wp_ajax_refresh_frontend', 'handle_refresh_frontend');

// 添加 JavaScript 到管理後台
function add_frontend_refresh_script() {
    ?>
    <script>
    function startFrontendRefresh() {
        var totalSuccess = 0
        var allErrors = []

        function performBatch(offset) {
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'refresh_frontend',
                    nonce: '<?php echo wp_create_nonce('refresh_frontend_nonce') ?>',
                    offset: offset
                },
                success: function(response) {
                    if (response.success) {
                        totalSuccess += response.data.success_count
                        allErrors = allErrors.concat(response.data.error_messages)

                        var progress = Math.round((response.data.next_offset / response.data.total_posts) * 100)
                        jQuery('#refresh-progress').text(progress + '%')

                        if (!response.data.is_completed) {
                            performBatch(response.data.next_offset)
                        } else {
                            showFinalNotification(totalSuccess, allErrors)
                        }
                    } else {
                        alert('更新過程中生錯誤')
                    }
                },
                error: function() {
                    alert('更新請求失敗')
                }
            })
        }

        jQuery('body').append('<div id="refresh-overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;"><div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:20px;border-radius:5px;"><p>正在更新前端快取...</p><p id="refresh-progress">0%</p></div></div>')

        performBatch(0)
    }

    function showFinalNotification(successCount, errorMessages) {
        jQuery('#refresh-overlay').remove()
        
        var message = '已成功更新 ' + successCount + ' 篇文章的前端快取。'
        if (errorMessages.length > 0) {
            message += '\n\n以下文章更新失敗：\n' + errorMessages.join('\n')
        }
        
        alert(message)
    }
    </script>
    <?php
}
add_action('admin_footer', 'add_frontend_refresh_script');

function notify_frontend_on_update($post_id = null, $post = null, $update = null) {
    global $ALLOWED_POST_TYPES;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['action']) && $_POST['action'] === 'acf/validate_save_post') return;

    // 只在更新文章時觸發
    if ($post && $update === false) return;


    // 處理刪除操作或私密文章
    if (doing_action('wp_trash_post') || 
        ($post && $post->post_status === 'private')) {
        $post = get_post($post_id);
        if (!$post || !in_array($post->post_type, $ALLOWED_POST_TYPES)) return;

        $body = array(
            'id' => $post_id,
            'type' => $post->post_type,
            'slug' => $post->post_name,
            'action' => 'delete'
        );

        $response = send_revalidate_request($body);
        
        if (is_wp_error($response)) {
            error_log('無法通知前端應用刪除文章：' . $response->get_error_message());
        }
        return;
    }

    $body = array(
        'id' => 0,
        'type' => 'global',
    );

    // 檢查是否為 post-type_options 格式
    if (is_string($post_id) && strpos($post_id, '_options') !== false) {
        $type = str_replace('_options', '', $post_id);
        if (in_array($type, $ALLOWED_POST_TYPES)) {
            $body = array(
                'id' => $post_id,
                'type' => $type
            );
        }
    } elseif ($post !== null) {
        // if ($post->post_status !== 'publish') return;
        if (!in_array($post->post_type, $ALLOWED_POST_TYPES)) return;

        // 取得所有語言版本的文章ID
        if (function_exists('pll_get_post_translations')) {
            $post_translations = pll_get_post_translations($post_id);
            
            // 更新每個語言版本的快取
            foreach ($post_translations as $translated_id) {
                $translated_post = get_post($translated_id);
                $translated_body = array(
                    'id' => $translated_id,
                    'type' => $translated_post->post_type,
                    'slug' => $translated_post->post_name
                );
                
                $response = send_revalidate_request($translated_body);
                
                if (is_wp_error($response)) {
                    $error_message = $response->get_error_message();
                    set_transient('frontend_notification_failed', array(
                        'post_id' => $translated_id,
                        'error_message' => $error_message
                    ), 60);
                    error_log('無法通知前端應用：' . $error_message);
                } else {
                    $response_code = wp_remote_retrieve_response_code($response);
                    $response_body = wp_remote_retrieve_body($response);
                    set_transient('frontend_notification_success', array(
                        'post_id' => $translated_id,
                        'response_code' => $response_code,
                        'response_body' => $response_body
                    ), 60);
                }
            }
            return; // 已處理所有翻譯，直接返回
        }

        $body = array(
            'id' => $post_id,
            'type' => $post->post_type,
            'slug' => $post->post_name
        );
    }

    $response = send_revalidate_request($body);

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        set_transient('frontend_notification_failed', array(
            'post_id' => $post_id,
            'error_message' => $error_message
        ), 60);
        error_log('無法通知前端應用：' . $error_message);
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        set_transient('frontend_notification_success', array(
            'post_id' => $post_id,
            'response_code' => $response_code,
            'response_body' => $response_body
        ), 60);
        error_log('已通知前端應用重新驗證：' . ($post_id ? $post_id : 'global') . '，回應：' . $response_body);
    }
}

add_action('save_post', 'notify_frontend_on_update', 10, 3);
add_action('delete_post', 'notify_frontend_on_update', 10, 3);
add_action('wp_trash_post', 'notify_frontend_on_update', 10, 3);

function check_frontend_notification() {
    $success_notification = get_transient('frontend_notification_success');
    $failed_notification = get_transient('frontend_notification_failed');
    if ($success_notification !== false) {
        $post_id = $success_notification['post_id'];
        $response_code = $success_notification['response_code'];
        $response_body = $success_notification['response_body'];
        $post_title = get_the_title($post_id);
        $message = $response_code === 200 ? 
            sprintf('前端快取功能已成功更新文章：%s（ID: %d）', $post_title, $post_id) :
            sprintf('前端快取功能已成功更新文章：%s（ID: %d）<br>回應狀態碼：%d<br>回應內容：%s', 
                $post_title, $post_id, $response_code, $response_body);
        echo '<div class="notice notice-success is-dismissible"><p>' . wp_kses_post($message) . '</p></div>';
        delete_transient('frontend_notification_success');
    }

    if ($failed_notification !== false) {
        $post_id = $failed_notification['post_id'];
        $error_message = $failed_notification['error_message'];
        $post_title = get_the_title($post_id);
        $message = sprintf('無法通知前端應用更新文章：%s（ID: %d）<br>錯誤訊息：%s', $post_title, $post_id, $error_message);
        echo '<div class="notice notice-error is-dismissible"><p>' . wp_kses_post($message) . '</p></div>';
        delete_transient('frontend_notification_failed');
    }
}
add_action('admin_notices', 'check_frontend_notification');

// 添加選項更新的鉤子
add_action('updated_option', 'notify_frontend_on_option_update', 10, 3);

function notify_frontend_on_option_update($option_name, $old_value, $new_value) {
    // 檢查是否為 ACF options 欄位
    if (strpos($option_name, 'options_') === 0) {
        global $ALLOWED_POST_TYPES;
        
        // 檢查每個允許的 post type
        foreach ($ALLOWED_POST_TYPES as $post_type) {
            if (strpos($option_name, 'options_' . $post_type) === 0) {
                // 更新特定 post type 的 options
                notify_frontend_on_update($post_type . '_options');
                return;
            }
        }
        
        // 如果不屬於特定 post type，則更新全局設定
        notify_frontend_on_update();
    }
}

// 隱藏預覽變更按鈕
function hide_preview_button() {
    echo '<style>
        #preview-action {
            display: none !important;
        }
    </style>';
}
add_action('admin_head', 'hide_preview_button');

add_filter('map_meta_cap', 'custom_manage_privacy_options', 1, 4);
function custom_manage_privacy_options($caps, $cap, $user_id, $args)
{
  if (!is_user_logged_in()) return $caps;

  $user_meta = get_userdata($user_id);
  if (array_intersect(['editor', 'administrator'], $user_meta->roles)) {
    if ('manage_privacy_options' === $cap) {
      $manage_name = is_multisite() ? 'manage_network' : 'manage_options';
      $caps = array_diff($caps, [ $manage_name ]);
    }
  }
  return $caps;
}

// 添加 Taxonomy 更新時的通知功能
function notify_frontend_on_taxonomy_update($term_id, $tt_id, $taxonomy) {
    global $API_URL;
    
    // 只處理特定的 taxonomy
    $allowed_taxonomies = ['posts-category', 'posts-tag'];
    if (!in_array($taxonomy, $allowed_taxonomies)) {
        return;
    }
    
    // 檢查是否需要忽略此操作
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    
    // 獲取當前分類法信息
    $term = get_term($term_id, $taxonomy);
    if (!$term || is_wp_error($term)) return;
    
    // 只更新 article post type
    $post_type = 'article';
    
    // 檢查 taxonomy 是否與 article post type 相關
    $taxonomy_object = get_taxonomy($taxonomy);
    if ($taxonomy_object && in_array($post_type, $taxonomy_object->object_type)) {
        // 更新該 post type 的 options 頁面
        notify_frontend_on_update($post_type . '_options');
        
        error_log('開始查詢與分類 '. $term->name .' 相關的 ' . $post_type . ' 文章');
        
        // 獲取與此 taxonomy 相關的所有文章
        $args = array(
            'post_type' => $post_type,
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'tax_query' => array(
                array(
                    'taxonomy' => $taxonomy,
                    'field' => 'term_id',
                    'terms' => $term_id
                )
            ),
            'fields' => 'all'
        );
        
        $posts = get_posts($args);
        error_log('找到 ' . count($posts) . ' 篇與分類 '. $term->name .' 相關的 ' . $post_type . ' 文章');
        
        // 更新每篇相關文章的快取
        foreach ($posts as $post) {
            // 直接發送請求到前端，繞過 notify_frontend_on_update 函數
            $post_body = array(
                'id' => $post->ID,
                'type' => $post->post_type,
                'slug' => $post->post_name
            );
            
            $post_response = wp_remote_post($API_URL, array(
                'method' => 'POST',
                'body' => json_encode($post_body),
                'headers' => array('Content-Type' => 'application/json'),
                'timeout' => 15
            ));
            
            if (is_wp_error($post_response)) {
                error_log('無法通知前端應用更新與分類 '. $term->name .' 相關的文章：' . $post->post_title . '（ID: ' . $post->ID . '）：' . $post_response->get_error_message());
            } else {
                $post_response_code = wp_remote_retrieve_response_code($post_response);
                $post_response_body = wp_remote_retrieve_body($post_response);
                error_log('已通知前端應用更新與分類 '. $term->name .' 相關的文章：' . $post->post_title . '（ID: ' . $post->ID . '）- 回應碼：' . $post_response_code);
            }
        }
    }
}

// 將 posts-category 設定為單選
function make_article_category_single_select() {
    // 確認當前頁面是 article 編輯頁面
    global $post_type;
    if ($post_type !== 'article') {
        return;
    }
    ?>
    <style>
        /* 將 posts-category 的 checkbox 樣式改為 radio button 樣式 */
        #taxonomy-posts-category .categorychecklist input[type="checkbox"] {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            width: 16px;
            height: 16px;
            border: 2px solid #8c8f94;
            border-radius: 50%;
            background: #fff;
            margin: 0 6px 0 0;
            position: relative;
            cursor: pointer;
            vertical-align: middle;
        }
        
        #taxonomy-posts-category .categorychecklist input[type="checkbox"]:checked {
            background: #2271b1;
            border-color: #2271b1;
        }
        
        #taxonomy-posts-category .categorychecklist input[type="checkbox"]:checked::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #fff;
        }
        
        #taxonomy-posts-category .categorychecklist label {
            cursor: pointer;
        }
    </style>
    
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // 針對 posts-category 實現單選功能
            $('#taxonomy-posts-category .categorychecklist input[type="checkbox"]').on('change', function() {
                if (this.checked) {
                    // 取消其他所有的選擇
                    $('#taxonomy-posts-category .categorychecklist input[type="checkbox"]').not(this).prop('checked', false);
                }
            });
        });
    </script>
    <?php
}
add_action('admin_footer-post.php', 'make_article_category_single_select');
add_action('admin_footer-post-new.php', 'make_article_category_single_select');

// 確保 posts-category 只能選擇一個 - 後端驗證
function validate_single_article_category($post_id, $post, $update) {
    // 只處理 article post type
    if ($post->post_type !== 'article') {
        return;
    }
    
    // 檢查是否為自動儲存
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // 檢查使用者權限
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // 獲取當前文章的 posts-category
    $terms = wp_get_post_terms($post_id, 'posts-category', array('fields' => 'ids'));
    
    // 如果有多個分類，只保留第一個
    if (count($terms) > 1) {
        wp_set_post_terms($post_id, array($terms[0]), 'posts-category');
    }
}

// 添加到分類法相關的鉤子
add_action('created_term', 'notify_frontend_on_taxonomy_update', 10, 3);
add_action('edited_term', 'notify_frontend_on_taxonomy_update', 10, 3);
add_action('delete_term', 'notify_frontend_on_taxonomy_update', 10, 3);
add_action('save_post', 'validate_single_article_category', 10, 3);