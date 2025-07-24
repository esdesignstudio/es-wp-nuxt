<?php

    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    
    class TTO 
        {            
            var $functions;
            
            /**
            * Constructor
            * 
            */
            function __construct() 
                {
                    add_action('admin_print_scripts',               array ( $this,  'admin_print_scripts' ) );
                    add_action('admin_print_styles',                array ( $this,  'admin_print_styles' ) );
                    add_action('admin_menu',                        array ( $this,  'admin_menu' ), 99);
                    add_filter('terms_clauses',                     array ( $this,  'apply_order_filter' ), 10, 3);
                    add_filter('get_terms_orderby',                 array ( $this,  'get_terms_orderby' ), 1, 2);
                    
                    add_action( 'wp_ajax_update-taxonomy-order',    array ( $this, 'saveAjaxOrder' ) );
                    
                    if ( is_admin() )
                        TTO_functions::check_table_column();                    
                }
                
            
            /**
            * Admin Scripts
            *     
            */
            function admin_print_scripts()
                {
                    wp_enqueue_script('jquery');                    
                    wp_enqueue_script('jquery-ui-sortable');
                    wp_register_script('to-javascript', TOURL . '/js/to-javascript.js', array(), TTO_VERSION );
                    wp_enqueue_script( 'to-javascript');
                }
                
            
            /**
            * Admin styles
            * 
            */
            function admin_print_styles()
                {
                    wp_register_style('to.css', TOURL . '/css/to.css', array(), TTO_VERSION );
                    wp_enqueue_style( 'to.css');
                }
                
                
            /**
            * Plugin menus
            *     
            */
            function admin_menu() 
                {
                    include (TOPATH . '/include/class.interface.php');
                    include (TOPATH . '/include/class.terms_walker.php');
                    include (TOPATH . '/include/class.options.php');
                    
                    $TTO_plugin_options =   new TTO_plugin_options(); 
                    add_options_page('Taxonomy Terms Order', '<img class="menu_tto" src="'. TOURL .'/images/menu-icon.png" alt="" />' . __('Taxonomy Terms Order', 'taxonomy-terms-order'), 'manage_options', 'to-options', array ( $TTO_plugin_options, 'plugin_options' ) );
                            
                    $options = TTO_functions::get_settings();
                    
                    if(isset($options['capability']) && !empty($options['capability']))
                        $capability = $options['capability'];
                    else if (is_numeric($options['level']))
                        {
                            //maintain the old user level compatibility
                            $capability = TTO_functions::userdata_get_user_level();
                        }
                        else
                            {
                                $capability = 'manage_options';  
                            } 
                            
                     //put a menu within all custom types if apply
                    $post_types = get_post_types();
                    foreach( $post_types as $post_type) 
                        {
                                
                            //check if there are any taxonomy for this post type
                            $post_type_taxonomies = get_object_taxonomies($post_type);
                            
                            foreach ($post_type_taxonomies as $key => $taxonomy_name)
                                {
                                    $taxonomy_info = get_taxonomy($taxonomy_name);  
                                    if (empty($taxonomy_info->hierarchical) ||  $taxonomy_info->hierarchical !== TRUE) 
                                        unset($post_type_taxonomies[$key]);
                                }
                                
                            if (count($post_type_taxonomies) == 0)
                                continue;
                                
                            if  ( isset ( $options['show_reorder_interfaces'][ $post_type ]) && $options['show_reorder_interfaces'][ $post_type ] == 'hide' )
                                continue;
                            
                            $TTO_Interface =    new TTO_Interface();
                            
                            if ($post_type == 'post')
                                add_submenu_page('edit.php', __('Taxonomy Order', 'taxonomy-terms-order'), __('Taxonomy Order', 'taxonomy-terms-order'), $capability, 'to-interface-'.$post_type, array ( $TTO_Interface, 'Interface' ) );
                                elseif ($post_type == 'attachment')
                                add_submenu_page('upload.php', __('Taxonomy Order', 'taxonomy-terms-order'), __('Taxonomy Order', 'taxonomy-terms-order'), $capability, 'to-interface-'.$post_type, array ( $TTO_Interface, 'Interface' ) );   
                                else
                                add_submenu_page('edit.php?post_type='.$post_type, __('Taxonomy Order', 'taxonomy-terms-order'), __('Taxonomy Order', 'taxonomy-terms-order'), $capability, 'to-interface-'.$post_type, array ( $TTO_Interface, 'Interface' ) );
                        }
                }
                
            
            /**
            * Apply order filter
            *     
            * @param mixed $clauses
            * @param mixed $taxonomies
            * @param mixed $args
            */
            function apply_order_filter( $clauses, $taxonomies, $args)
                {
                    if ( apply_filters('to/get_terms_orderby/ignore', FALSE, $clauses['orderby'], $args) )
                        return $clauses;
                    
                    $options = TTO_functions::get_settings();
                    
                    //if admin make sure use the admin setting
                    if (is_admin())
                        {
                            
                            //return if use orderby columns
                            if (isset($_GET['orderby']) && $_GET['orderby'] !=  'term_order')
                                return $clauses;
                            
                            if ( $options['adminsort'] == "1" &&  (!isset($args['ignore_term_order']) ||  (isset($args['ignore_term_order'])  &&  $args['ignore_term_order']  !== TRUE) ) )
                                {
                                    if ( $clauses['orderby']    ==  'ORDER BY t.name' )
                                        $clauses['orderby'] =   'ORDER BY t.term_order '. $clauses['order'] .', t.name';
                                        else
                                        $clauses['orderby'] =   'ORDER BY t.term_order';
                                    
                                }
                                
                            return $clauses;    
                        }
                    
                    //if autosort, then force the menu_order
                    if ($options['autosort'] == "1"   &&  (!isset($args['ignore_term_order']) ||  (isset($args['ignore_term_order'])  &&  $args['ignore_term_order']  !== TRUE) ) )
                        {
                            $clauses['orderby'] =   'ORDER BY t.term_order';
                        }
                        
                    return $clauses; 
                }
                
                
            /**
            * Get terms orderby
            * 
            * @param mixed $orderby
            * @param mixed $args
            * @return mixed
            */
            function get_terms_orderby($orderby, $args)
                {
                    if ( apply_filters('to/get_terms_orderby/ignore', FALSE, $orderby, $args) )
                        return $orderby;
                        
                    if (isset($args['orderby']) && $args['orderby'] == "term_order" && $orderby != "term_order")
                        return "t.term_order";
                        
                    return $orderby;
                }
                
            
            /**
            * Save the AJAX order update
            *     
            */
            function saveAjaxOrder()
                {
                    global $wpdb;
                    
                    if  ( ! wp_verify_nonce( $_POST['nonce'], 'update-taxonomy-order' ) )
                        die();
                     
                    $data               = stripslashes($_POST['order']);
                    $unserialised_data  = json_decode($data, TRUE);
                            
                    if (is_array($unserialised_data))
                    foreach($unserialised_data as $key => $values ) 
                        {
                            //$key_parent = str_replace("item_", "", $key);
                            $items = explode("&", $values);
                            unset($item);
                            foreach ($items as $item_key => $item_)
                                {
                                    $items[$item_key] = trim(str_replace("item[]=", "",$item_));
                                }
                            
                            if (is_array($items) && count($items) > 0)
                                {
                                    foreach( $items as $item_key => $term_id ) 
                                        {
                                            $wpdb->update( $wpdb->terms, array('term_order' => ($item_key + 1)), array('term_id' => $term_id) );
                                        }
                                    clean_term_cache($items);
                                } 
                        }
                        
                    do_action('tto/update-order');
                    
                    wp_cache_flush();
                        
                    die();
                }
           
        }