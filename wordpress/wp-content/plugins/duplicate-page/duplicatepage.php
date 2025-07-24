<?php
/*
Plugin Name: Duplicate Page
Plugin URI: https://wordpress.org/plugins/duplicate-page/
Description: Duplicate Posts, Pages and Custom Posts using single click.
Author: mndpsingh287
Version: 4.5.5
Author URI: https://profiles.wordpress.org/mndpsingh287/
License: GPLv2
Text Domain: duplicate-page
*/
if (!defined('DUPLICATE_PAGE_PLUGIN_DIRNAME')) {
    define('DUPLICATE_PAGE_PLUGIN_DIRNAME', plugin_basename(dirname(__FILE__)));
}
if (!defined('DUPLICATE_PAGE_PLUGIN_VERSION')) {
    define('DUPLICATE_PAGE_PLUGIN_VERSION', '4.5.5');
}
if (!class_exists('duplicate_page')):
    class duplicate_page
    {
        /*
        * AutoLoad Hooks
        */        
        public function __construct() 
        {
            $opt = get_option('duplicate_page_options');
            register_activation_hook(__FILE__, array(&$this, 'duplicate_page_install'));
            add_action('admin_menu', array(&$this, 'duplicate_page_options_page'));
            add_filter('plugin_action_links', array(&$this, 'duplicate_page_plugin_action_links'), 10, 2);
            add_action('admin_action_dt_duplicate_post_as_draft', array(&$this, 'dt_duplicate_post_as_draft'));
            
            add_filter('post_row_actions', array(&$this, 'dt_duplicate_post_link'), 10, 2);
            add_filter('page_row_actions', array(&$this, 'dt_duplicate_post_link'), 10, 2);
            if (isset($opt['duplicate_post_editor']) && $opt['duplicate_post_editor'] == 'gutenberg') {
                add_action('admin_head', array(&$this, 'duplicate_page_custom_button_guten'));
            } elseif(isset($opt['duplicate_post_editor']) && $opt['duplicate_post_editor'] == 'all'){
                add_action('admin_head', array(&$this, 'duplicate_page_custom_button_guten'));
                add_action('post_submitbox_misc_actions', array(&$this, 'duplicate_page_custom_button_classic'));
            } else {
                add_action('post_submitbox_misc_actions', array(&$this, 'duplicate_page_custom_button_classic'));
            }
            add_action('wp_before_admin_bar_render', array(&$this, 'duplicate_page_admin_bar_link'));
            add_action('init', array(&$this, 'duplicate_page_load_text_domain'));
            add_action('wp_ajax_mk_dp_close_dp_help', array($this, 'mk_dp_close_dp_help'));
        }
       
        /*
        * Localization - 19-dec-2016
        */
        public function duplicate_page_load_text_domain()
        {
            load_plugin_textdomain('duplicate-page', false, DUPLICATE_PAGE_PLUGIN_DIRNAME.'/languages');
        }

        /*
        * Activation Hook
        */
        public function duplicate_page_install()
        {
            $defaultsettings = array(
                'duplicate_post_status' => 'draft',
                'duplicate_post_redirect' => 'to_list',
                'duplicate_post_suffix' => '',
                'duplicate_post_editor' => 'classic',
            );
            $opt = get_option('duplicate_page_options');
            if (!isset($opt['duplicate_post_status'])) {
                update_option('duplicate_page_options', $defaultsettings);
            }
        }
        /*
        Action Links
        */
        public function duplicate_page_plugin_action_links($links, $file)
        {
            if ($file == plugin_basename(__FILE__)) {
                $duplicate_page_links = '<a href="'.esc_url(get_admin_url()).'options-general.php?page=duplicate_page_settings">'.__('Settings', 'duplicate-page').'</a>';
                $duplicate_page_donate = '<a href="https://www.webdesi9.com/donate/?plugin=duplicate-page" title="'.__('Donate Now','duplicate-page').'" target="_blank" style="font-weight:bold">'.__('Donate', 'duplicate-page').'</a>';
                array_unshift($links, $duplicate_page_donate);
                array_unshift($links, $duplicate_page_links);
            }

            return $links;
        }

        /*
        * Admin Menu
        */
        public function duplicate_page_options_page()
        {
            add_options_page(__('Duplicate Page', 'duplicate-page'), __('Duplicate Page', 'duplicate-page'), 'manage_options', 'duplicate_page_settings', array(&$this, 'duplicate_page_settings'));
        }

        /*
        * Duplicate Page Admin Settings
        */
        public function duplicate_page_settings()
        {
            if (current_user_can('manage_options')) {
                include 'inc/admin-settings.php';
            }
        }

        /*
        * Main function
        */
        public function dt_duplicate_post_as_draft()
        {
           /*
           * get Nonce value
           */
           $nonce = sanitize_text_field($_REQUEST['nonce']);
            /*
            * get the original post id
            */
           
           $post_id = (isset($_GET['post']) ? intval($_GET['post']) : intval($_POST['post']));
           $post = get_post($post_id);
           $current_user_id = get_current_user_id();
           if(wp_verify_nonce( $nonce, 'dt-duplicate-page-'.$post_id)) {
            if (current_user_can('manage_options') || current_user_can('edit_others_posts')) {
              $this->duplicate_edit_post($post_id);
            }
            else if (current_user_can('contributor') && $current_user_id == $post->post_author){
                $this->duplicate_edit_post($post_id, 'pending');
            }
            else if (current_user_can('edit_posts') && $current_user_id == $post->post_author ){
              $this->duplicate_edit_post($post_id);
            }
            else {
                wp_die(__('Unauthorized Access.','duplicate-page'));
			}
          }
         
          else {
            wp_die(__('Security check issue, Please try again.','duplicate-page'));
          } 
          
        }
        /**
         * Duplicate edit post
         */
        public function duplicate_edit_post($post_id,$post_status_update='')
        {
            global $wpdb;
            $opt = get_option('duplicate_page_options');
            $suffix = isset($opt['duplicate_post_suffix']) && !empty($opt['duplicate_post_suffix']) ? ' -- '.esc_attr($opt['duplicate_post_suffix']) : '';
                if($post_status_update == ''){
                    $post_status = !empty($opt['duplicate_post_status']) ? esc_attr($opt['duplicate_post_status']) : 'draft';
                }else{
                    $post_status =  $post_status_update;
                }
            $redirectit = !empty($opt['duplicate_post_redirect']) ? esc_attr($opt['duplicate_post_redirect']) : 'to_list';
            if (!(isset($_GET['post']) || isset($_POST['post']) || (isset($_REQUEST['action']) && 'dt_duplicate_post_as_draft' == sanitize_text_field($_REQUEST['action'])))) {
                wp_die(__('No post to duplicate has been supplied!','duplicate-page'));
            }
            $returnpage = '';            
            /*
            * and all the original post data then
            */
            $post = get_post($post_id);
            /*
            * if you don't want current user to be the new post author,
            * then change next couple of lines to this: $new_post_author = $post->post_author;
            */
            $current_user = wp_get_current_user();
            $new_post_author = $current_user->ID;
            /*
            * if post data exists, create the post duplicate
            */
            if (isset($post) && $post != null) {
                /*
                * new post data array
                */
                $args = array(
                     'comment_status' => $post->comment_status,
                     'ping_status' => $post->ping_status,
                     'post_author' => $new_post_author,
                     'post_content' => (isset($opt['duplicate_post_editor']) && $opt['duplicate_post_editor'] == 'gutenberg') ? wp_slash($post->post_content) : $post->post_content,
                     'post_excerpt' => $post->post_excerpt,
                     'post_parent' => $post->post_parent,
                     'post_password' => $post->post_password,
                     'post_status' => $post_status,
                     'post_title' => $post->post_title.$suffix,
                     'post_type' => $post->post_type,
                     'to_ping' => $post->to_ping,
                     'menu_order' => $post->menu_order,
                 );
                /*
                * insert the post by wp_insert_post() function
                */
                $new_post_id = wp_insert_post($args);
	            if(is_wp_error($new_post_id)){
		            wp_die(__($new_post_id->get_error_message(),'duplicate-page'));
	            }
               
                /*
                * get all current post terms ad set them to the new post draft
                */
                $taxonomies = array_map('sanitize_text_field',get_object_taxonomies($post->post_type));
                if (!empty($taxonomies) && is_array($taxonomies)):
                 foreach ($taxonomies as $taxonomy) {
                     $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
                     wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
                 }
                endif;
                /*
                * duplicate all post meta
                */
                $post_meta_keys = get_post_custom_keys( $post_id );
                if(!empty($post_meta_keys)){
                    foreach ( $post_meta_keys as $meta_key ) {
                        $meta_values = get_post_custom_values( $meta_key, $post_id );
                        foreach ( $meta_values as $meta_value ) {
                            $meta_value = maybe_unserialize( $meta_value );
                            update_post_meta( $new_post_id, $meta_key, wp_slash( $meta_value ) );
                        }
                    }
                }
                   
                /**
                 * Elementor compatibility fixes
                 */
                 if(is_plugin_active( 'elementor/elementor.php' )){
                        $css = Elementor\Core\Files\CSS\Post::create( $new_post_id );
                        $css->update();
                 } 
                /*
                * finally, redirecting to your choice
                */
                if ($post->post_type != 'post'){
                    $returnpage = '?post_type='.$post->post_type;
                }
                if (!empty($redirectit) && $redirectit == 'to_list'){
                    wp_redirect(esc_url_raw(admin_url('edit.php'.$returnpage))); 
                } elseif (!empty($redirectit) && $redirectit == 'to_page'){
                    wp_redirect(esc_url_raw(admin_url('post.php?action=edit&post='.$new_post_id))); 
                } else {
                    wp_redirect(esc_url_raw(admin_url('edit.php'.$returnpage)));
                }
             exit;
            } 
            else {
                wp_die(__('Error! Post creation failed, could not find original post: ','duplicate-page').$post_id);
            }
          }

        /*
         * Add the duplicate link to action list for post_row_actions
         */
        public function dt_duplicate_post_link($actions, $post)
        {
			// Skip acf-field-group post type
			if($post->post_type == 'acf-field-group'){
				return $actions;
			}

            $opt = get_option('duplicate_page_options');
            $post_status = !empty($opt['duplicate_post_status']) ? esc_attr($opt['duplicate_post_status']) : 'draft';
            if (current_user_can('edit_posts')) {
                $actions['duplicate'] = isset($post) ? '<a href="admin.php?action=dt_duplicate_post_as_draft&amp;post='.intval($post->ID).'&amp;nonce='.wp_create_nonce( 'dt-duplicate-page-'.intval($post->ID) ).'" title="'.__('Duplicate this as ', 'duplicate-page').$post_status.'" rel="permalink">'.__('Duplicate This', 'duplicate-page').'</a>' : '';
            }
            
            return $actions;
        }

        /*
          * Add the duplicate link to edit screen - classic editor
          */
        public function duplicate_page_custom_button_classic()
        {
            global $post;
            $opt = get_option('duplicate_page_options');
            $post_status = !empty($opt['duplicate_post_status']) ? esc_attr($opt['duplicate_post_status']) : 'draft';
            $html = '<div id="major-publishing-actions">';
            $html .= '<div id="export-action">';
            $html .= isset($post) ? '<a href="admin.php?action=dt_duplicate_post_as_draft&amp;post='.intval($post->ID).'&amp;nonce='.wp_create_nonce( 'dt-duplicate-page-'.$post->ID ).'" title="'.__('Duplicate this as ','duplicate-page').$post_status.'" rel="permalink">'.__('Duplicate This', 'duplicate-page').'</a>' :'';
            $html .= '</div>';
            $html .= '</div>';
            $content = apply_filters('wpautop', $html);
            $content = str_replace(']]>', ']]>', $content);
            echo $content;
        }

        /*
         * Add the duplicate link to edit screen - gutenberg
         */
        public function duplicate_page_custom_button_guten()
        {
            global $post;
            if ($post) {
                $opt = get_option('duplicate_page_options');
                $post_status = !empty($opt['duplicate_post_status']) ? esc_attr($opt['duplicate_post_status']) : 'draft';
                if (isset($opt['duplicate_post_editor']) && ($opt['duplicate_post_editor'] == 'gutenberg' || $opt['duplicate_post_editor'] == 'all')) {
                    wp_enqueue_style('dp-main-style', plugin_dir_url(__FILE__) . 'css/dp_gutenberg.css');
                    wp_register_script( "dt_duplicate_post_script", plugins_url( '/js/editor-script.js', __FILE__ ), array( 'wp-edit-post', 'wp-plugins', 'wp-i18n', 'wp-element' ), DUPLICATE_PAGE_PLUGIN_VERSION);
                    wp_localize_script( 'dt_duplicate_post_script', 'dt_params', array(
                        'dp_post_id' => intval($post->ID),
                        'dtnonce' => wp_create_nonce( 'dt-duplicate-page-'.intval($post->ID)),
                        'dp_post_text' => __("Duplicate This",'duplicate-page'),
                        'dp_post_title'=> __('Duplicate this as ','duplicate-page').$post_status,
                        'dp_duplicate_link' => "admin.php?action=dt_duplicate_post_as_draft"
                        )
                    );        
                    wp_enqueue_script( 'dt_duplicate_post_script' );
                }
            }
        }

        /*
        * Admin Bar Duplicate This Link
        */
        public function duplicate_page_admin_bar_link()
        {
            global $wp_admin_bar, $post;
            $opt = get_option('duplicate_page_options');
            $post_status = !empty($opt['duplicate_post_status']) ? esc_attr($opt['duplicate_post_status']) : 'draft';
            $current_object = get_queried_object();
            if (empty($current_object)) {
                return;
            }
            if (!empty($current_object->post_type)
            && ($post_type_object = get_post_type_object($current_object->post_type))
            && ($post_type_object->show_ui || $current_object->post_type == 'attachment')) {
                $wp_admin_bar->add_menu(array(
                'parent' => 'edit',
                'id' => 'duplicate_this',
                'title' => __('Duplicate This as ', 'duplicate-page').$post_status,
                'href' => isset($post) ? esc_url_raw(admin_url().'admin.php?action=dt_duplicate_post_as_draft&amp;post='.intval($post->ID).'&amp;nonce='.wp_create_nonce( 'dt-duplicate-page-'.intval($post->ID))) :'',
                ));
            }
        }

        /*
         * Redirect function
        */
        public static function dp_redirect($url)
        {        
            $web_url = esc_url_raw($url);
            wp_register_script( 'dp-setting-redirect', '');
            wp_enqueue_script( 'dp-setting-redirect' );
            wp_add_inline_script(
            'dp-setting-redirect',
            ' window.location.href="'.$web_url.'" ;'
        );
        }
        /*
         Load Help Desk
        */
        public function load_help_desk() {
            $mkcontent = '';
            $mkcontent .= '<div class="dpmrs" style="display:none;">';
            $mkcontent .= '<div class="l_dpmrs">';
            $mkcontent .= '';
            $mkcontent .= '</div>';
            $mkcontent .= '<div class="r_dpmrs">';
            $mkcontent .= '<a class="close_dp_help fm_close_btn" href="javascript:void(0)" data-ct="rate_later" title="'.__('close','duplicate-page').'">X</a><strong>'.__('Duplicate Page','duplicate-page').'</strong><p>'.__('We love and care about you. Our team is putting maximum efforts to provide you the best functionalities. It would be highly appreciable if you could spend a couple of seconds to give a Nice Review to the plugin to appreciate our efforts. So we can work hard to provide new features regularly :)','duplicate-page').'</p><a class="close_dp_help fm_close_btn_1" href="javascript:void(0)" data-ct="rate_later" title="'.__('Remind me later','duplicate-page').'">'.__('Later','duplicate-page').'</a> <a class="close_dp_help fm_close_btn_2" href="https://wordpress.org/support/plugin/duplicate-page/reviews/?filter=5" data-ct="rate_now" title="'.__('Rate us now','duplicate-page').'" target="_blank">'.__('Rate Us','duplicate-page').'</a> <a class="close_dp_help fm_close_btn_3" href="javascript:void(0)" data-ct="rate_never" title="'.__('Not interested','duplicate-page').'">'.__('Never','duplicate-page').'</a>';
            $mkcontent .= '</div></div>';
            if (false === ($mk_dp_close_dp_help_c = get_option('mk_fm_close_fm_help_c'))) {
                echo apply_filters('the_content', $mkcontent);
            }
        }
        /*
         Close Help
        */
        public function mk_dp_close_dp_help() {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
            if (wp_verify_nonce($nonce, 'nc_help_desk')) {
            if (false === ($mk_fm_close_fm_help_c = get_option('mk_fm_close_fm_help_c'))) {
                $set = update_option('mk_fm_close_fm_help_c', 'done');
                if ($set) {
                    echo 'ok';
                } else {
                    echo 'oh';
                }
            } else {
                echo 'ac';
            }
        }else {
            echo 'ac';
        }
            die;
        }
        /*
        Custom Assets
        */
        public function custom_assets() {
            wp_enqueue_style( 'duplicate-page', plugins_url( '/css/duplicate_page.css', __FILE__ ) );
            wp_enqueue_script( 'duplicate-page', plugins_url( '/js/duplicate_page.js', __FILE__ ) );
            wp_localize_script( 'duplicate-page', 'dt_params', array(
                'ajax_url' => admin_url( 'admin-ajax.php'),
                'nonce' => wp_create_nonce( 'nc_help_desk' ))
            );
        }
    }
new duplicate_page();
endif;
