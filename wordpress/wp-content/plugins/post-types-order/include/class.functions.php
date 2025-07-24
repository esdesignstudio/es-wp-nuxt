<?php

    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    
    class CptoFunctions
        {
            
            /**
            * Return the user level
            * 
            * This is deprecated, will be removed in the next versions
            * 
            * @param mixed $return_as_numeric
            */
            function userdata_get_user_level($return_as_numeric = FALSE)
                {
                    global $userdata;
                    
                    $user_level = '';
                    for ($i=10; $i >= 0;$i--)
                        {
                            if (current_user_can('level_' . $i) === TRUE)
                                {
                                    $user_level = $i;
                                    if ($return_as_numeric === FALSE)
                                        $user_level = 'level_'.$i;    
                                    break;
                                }    
                        }        
                    return ($user_level);
                }
                
            
            /**
            * Retrieve the plugin options
            * 
            */
            static public function get_options()
                {
                    //make sure the vars are set as default
                    $options = get_option('cpto_options');
                    
                    $defaults   = array (
                                            'show_reorder_interfaces'           =>  array(),
                                            'allow_reorder_default_interfaces'  =>  array(),
                                            'autosort'                          =>  1,
                                            'adminsort'                         =>  1,
                                            'use_query_ASC_DESC'                =>  '',
                                            'capability'                        =>  'manage_options',
                                            'edit_view_links'                   =>  '',
                                            'navigation_sort_apply'             =>  1,
                                            
                                        );
                    $options          = wp_parse_args( $options, $defaults );
                    
                    $options            =   apply_filters('pto/get_options', $options);
                    
                    return $options;            
                }
            
            
            /**
            * General messages box
            *     
            */
            function cpt_info_box()
                {
                    ?>
                        <div id="cpt_info_box">
                            <p><?php esc_html_e('Did you find this plugin useful? Please support our work by purchasing the advanced version or write an article about this plugin in your blog with a link to our site', 'post-types-order') ?> <a href="https://www.nsp-code.com/" target="_blank"><strong>https://www.nsp-code.com/</strong></a>.</p>
                            <h4><a href="https://www.nsp-code.com/premium-plugins/advanced-post-types-order/" target="_blank"><img width="151" src="<?php echo CPTURL ?>/images/logo.png" class="attachment-large size-large wp-image-36927" alt=""></a> <?php esc_html_e('Did you know there is available an Advanced version of this plug-in?', 'post-types-order') ?> <a target="_blank" href="https://www.nsp-code.com/premium-plugins/advanced-post-types-order/"><?php _e('Read more', 'post-types-order') ?></a></h4>
                            <p><?php esc_html_e('Check our', 'post-types-order') ?> <a target="_blank" href="https://wordpress.org/plugins/taxonomy-terms-order/">Category Order - Taxonomy Terms Order</a> <?php esc_html_e('plugin which allow to custom sort categories and custom taxonomies terms', 'post-types-order') ?> </p>
                            <p><span style="color:#CC0000" class="dashicons dashicons-megaphone" alt="f488">&nbsp;</span> <?php esc_html_e('Check our', 'post-types-order') ?> <a href="https://wordpress.org/plugins/wp-hide-security-enhancer/" target="_blank"><b>WP Hide & Security Enhancer</b></a> <?php esc_html_e('an extra layer of security for your site. The easy way to completely hide your WordPress core files, themes and plugins', 'post-types-order') ?>.</p>
                            
                            <div class="clear"></div>
                        </div>
                    
                    <?php   
                }

                
            /**
            * Gte previous post WHERE
            * 
            * @param mixed $where
            * @param mixed $in_same_term
            * @param mixed $excluded_terms
            */
            function cpto_get_previous_post_where($where, $in_same_term, $excluded_terms)
                {
                    global $post, $wpdb;

                    if ( empty( $post ) )
                        return $where;
                    
                    //?? WordPress does not pass through this varialbe, so we presume it's category..
                    $taxonomy = 'category';
                    if(preg_match('/ tt.taxonomy = \'([^\']+)\'/i',$where, $match)) 
                        $taxonomy   =   $match[1];
                    
                    $_join = '';
                    $_where = '';
                    
                    if ( $in_same_term || ! empty( $excluded_terms ) ) 
                        {
                            $_join = " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";
                            $_where = $wpdb->prepare( "AND tt.taxonomy = %s", $taxonomy );

                            if ( ! empty( $excluded_terms ) && ! is_array( $excluded_terms ) ) 
                                {
                                    // back-compat, $excluded_terms used to be $excluded_terms with IDs separated by " and "
                                    if ( false !== strpos( $excluded_terms, ' and ' ) ) 
                                        {
                                            _deprecated_argument( __FUNCTION__, '3.3', sprintf( esc_html__( 'Use commas instead of %s to separate excluded terms.' ), "'and'" ) );
                                            $excluded_terms = explode( ' and ', $excluded_terms );
                                        } 
                                    else 
                                        {
                                            $excluded_terms = explode( ',', $excluded_terms );
                                        }

                                    $excluded_terms = array_map( 'intval', $excluded_terms );
                                }

                            if ( $in_same_term ) 
                                {
                                    $term_array = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );

                                    // Remove any exclusions from the term array to include.
                                    $term_array = array_diff( $term_array, (array) $excluded_terms );
                                    $term_array = array_map( 'intval', $term_array );
                            
                                    $_where .= " AND tt.term_id IN (" . implode( ',', $term_array ) . ")";
                                }

                            if ( ! empty( $excluded_terms ) ) {
                                $_where .= " AND p.ID NOT IN ( SELECT tr.object_id FROM $wpdb->term_relationships tr LEFT JOIN $wpdb->term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) WHERE tt.term_id IN (" . implode( ',', $excluded_terms ) . ') )';
                            }
                        }
                        
                    $current_menu_order = $post->menu_order;
                    
                    $query = $wpdb->prepare( "SELECT p.* FROM $wpdb->posts AS p
                                $_join
                                WHERE p.post_date < %s  AND p.menu_order = %d AND p.post_type = %s AND p.post_status = 'publish' $_where" ,  $post->post_date, $current_menu_order, $post->post_type);
                    $results = $wpdb->get_results($query);
                            
                    if (count($results) > 0)
                            {
                                $where .= $wpdb->prepare( " AND p.menu_order = %d", $current_menu_order );
                            }
                        else
                            {
                                $where = str_replace("p.post_date < '". $post->post_date  ."'", "p.menu_order > '$current_menu_order'", $where);  
                            }
                    
                    return $where;
                }
            
            
            /**
            * Get the previous post sort
            *     
            * @param mixed $sort
            */
            function cpto_get_previous_post_sort($sort)
                {
                    global $post, $wpdb;
                    
                    $sort = 'ORDER BY p.menu_order ASC, p.post_date DESC LIMIT 1';

                    return $sort;
                }

                
            /**
            * Get the next post WHERE
            * 
            * @param mixed $where
            * @param mixed $in_same_term
            * @param mixed $excluded_terms
            */
            function cpto_get_next_post_where($where, $in_same_term, $excluded_terms)
                {
                    global $post, $wpdb;

                    if ( empty( $post ) )
                        return $where;
                    
                    $taxonomy = 'category';
                    if(preg_match('/ tt.taxonomy = \'([^\']+)\'/i',$where, $match)) 
                        $taxonomy   =   $match[1];
                    
                    $_join = '';
                    $_where = '';
                                
                    if ( $in_same_term || ! empty( $excluded_terms ) ) 
                        {
                            $_join = " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";
                            $_where = $wpdb->prepare( "AND tt.taxonomy = %s", $taxonomy );

                            if ( ! empty( $excluded_terms ) && ! is_array( $excluded_terms ) ) 
                                {
                                    // back-compat, $excluded_terms used to be $excluded_terms with IDs separated by " and "
                                    if ( false !== strpos( $excluded_terms, ' and ' ) ) 
                                        {
                                            _deprecated_argument( __FUNCTION__, '3.3', sprintf( esc_html__( 'Use commas instead of %s to separate excluded terms.' ), "'and'" ) );
                                            $excluded_terms = explode( ' and ', $excluded_terms );
                                        } 
                                    else 
                                        {
                                            $excluded_terms = explode( ',', $excluded_terms );
                                        }

                                    $excluded_terms = array_map( 'intval', $excluded_terms );
                                }

                            if ( $in_same_term ) 
                                {
                                    $term_array = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );

                                    // Remove any exclusions from the term array to include.
                                    $term_array = array_diff( $term_array, (array) $excluded_terms );
                                    $term_array = array_map( 'intval', $term_array );
                            
                                    $_where .= " AND tt.term_id IN (" . implode( ',', $term_array ) . ")";
                                }

                            if ( ! empty( $excluded_terms ) ) {
                                $_where .= " AND p.ID NOT IN ( SELECT tr.object_id FROM $wpdb->term_relationships tr LEFT JOIN $wpdb->term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) WHERE tt.term_id IN (" . implode( ',', $excluded_terms ) . ') )';
                            }
                        }
                        
                    $current_menu_order = $post->menu_order;
                    
                    //check if there are more posts with lower menu_order
                    $query = $wpdb->prepare( "SELECT p.* FROM $wpdb->posts AS p
                                $_join
                                WHERE p.post_date > %s AND p.menu_order = %d AND p.post_type = %s AND p.post_status = 'publish' $_where", $post->post_date, $current_menu_order, $post->post_type );
                    $results = $wpdb->get_results($query);
                            
                    if (count($results) > 0)
                            {
                                $where .= $wpdb->prepare(" AND p.menu_order = %d", $current_menu_order );
                            }
                        else
                            {
                                $where = str_replace("p.post_date > '". $post->post_date  ."'", "p.menu_order < '$current_menu_order'", $where);  
                            }
                    
                    return $where;
                }

            
            /**
            * Get next post sort
            * 
            * @param mixed $sort
            */
            function cpto_get_next_post_sort($sort)
                {
                    global $post, $wpdb; 
                    
                    $sort = 'ORDER BY p.menu_order DESC, p.post_date ASC LIMIT 1';
                    
                    return $sort;    
                }

            
            
            /**
            * Clear any cache plugins
            *     
            */
            static public function site_cache_clear()
                {
                    wp_cache_flush();
                    
                    $cleared_cache  =   FALSE;
                    
                    if ( function_exists('wp_cache_clear_cache'))
                        {
                            wp_cache_clear_cache();
                            $cleared_cache  =   TRUE;
                        }
                    
                    if ( function_exists('w3tc_flush_all'))
                        {
                            w3tc_flush_all();
                            $cleared_cache  =   TRUE;
                        }
                        
                    if ( function_exists('opcache_reset')    &&  ! ini_get( 'opcache.restrict_api' ) )
                        {
                            @opcache_reset();
                            $cleared_cache  =   TRUE;
                        }
                    
                    if ( function_exists( 'rocket_clean_domain' ) )
                        {
                            rocket_clean_domain();
                            $cleared_cache  =   TRUE;
                        }
                        
                    if ( function_exists('wp_cache_clear_cache')) 
                        {
                            wp_cache_clear_cache();
                            $cleared_cache  =   TRUE;
                        }
                
                    global $wp_fastest_cache;
                    if ( method_exists( 'WpFastestCache', 'deleteCache' ) && !empty( $wp_fastest_cache ) )
                        {
                            $wp_fastest_cache->deleteCache();
                            $cleared_cache  =   TRUE;
                        }
                
                    //If your host has installed APC cache this plugin allows you to clear the cache from within WordPress
                    if ( function_exists('apc_clear_cache'))
                        {
                            apc_clear_cache();
                            $cleared_cache  =   TRUE;
                        }
                        
                    if ( function_exists('fvm_purge_all'))
                        {
                            fvm_purge_all();
                            $cleared_cache  =   TRUE;
                        }
                    
                    if ( class_exists( 'autoptimizeCache' ) )     
                        {
                            autoptimizeCache::clearall();
                            $cleared_cache  =   TRUE;
                        }

                    //WPEngine
                    if ( class_exists( 'WpeCommon' ) ) 
                        {
                            if ( method_exists( 'WpeCommon', 'purge_memcached' ) )
                                WpeCommon::purge_memcached();
                            if ( method_exists( 'WpeCommon', 'clear_maxcdn_cache' ) )
                                WpeCommon::clear_maxcdn_cache();
                            if ( method_exists( 'WpeCommon', 'purge_varnish_cache' ) )
                                WpeCommon::purge_varnish_cache();
                            
                            $cleared_cache  =   TRUE;
                        }
                        
                    if (class_exists('Cache_Enabler_Disk') && method_exists('Cache_Enabler_Disk', 'clear_cache'))
                        {
                            Cache_Enabler_Disk::clear_cache();
                            $cleared_cache  =   TRUE;
                        }
                        
                    //Perfmatters
                    if ( class_exists('Perfmatters\CSS') && method_exists('Perfmatters\CSS', 'clear_used_css') )
                        {
                            Perfmatters\CSS::clear_used_css();
                            $cleared_cache  =   TRUE;
                        }
                    
                    if ( defined( 'BREEZE_VERSION' ) )
                        {
                            do_action( 'breeze_clear_all_cache' );
                            $cleared_cache  =   TRUE;
                        }
                        
                    if ( function_exists('sg_cachepress_purge_everything'))
                        {
                            sg_cachepress_purge_everything();
                            $cleared_cache  =   TRUE;
                        }
                    
                    if ( defined ( 'FLYING_PRESS_VERSION' ) )
                        {
                            do_action('flying_press_purge_everything:before');

                            @unlink(FLYING_PRESS_CACHE_DIR . '/preload.txt');

                            // Delete all files and subdirectories
                            FlyingPress\Purge::purge_everything();

                            @mkdir(FLYING_PRESS_CACHE_DIR, 0755, true);

                            do_action('flying_press_purge_everything:after');
                            
                            $cleared_cache  =   TRUE;
                        }
                        
                    if (class_exists('\LiteSpeed\Purge'))
                        {
                            \LiteSpeed\Purge::purge_all();
                            $cleared_cache  =   TRUE;
                        }
                        
                    return $cleared_cache;
                        
                }    
                
        }