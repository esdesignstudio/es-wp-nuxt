<?php
    
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

    class TTO_addons
        {
            
            function __construct() 
                {
                    add_action ('to/get_terms_orderby/ignore', array ( $this, 'to_get_terms_orderby_ignore_coauthors' ), 10, 3 ); 
                    add_action ('to/get_terms_orderby/ignore', array ( $this, 'to_get_terms_orderby_ignore_polylang' ), 10, 3);
                    add_action ('to/get_terms_orderby/ignore', array ( $this, 'to_get_terms_orderby_ignore_woocommerce' ), 10, 3);       
                }
            
            
            /**
            * Co-Authors Plus fix
            * 
            * @param mixed $ignore
            * @param mixed $orderby
            * @param mixed $args
            */
            function to_get_terms_orderby_ignore_coauthors( $ignore, $orderby, $args )
                {
                    if( !function_exists('is_plugin_active') )
                        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                    
                    if( !   is_plugin_active( 'co-authors-plus/co-authors-plus.php' ))
                        return $ignore;
                    
                    if ( ! isset($args['taxonomy']) ||  count($args['taxonomy']) !==    1 ||    array_search('author', $args['taxonomy'])   === FALSE )
                        return $ignore;    
                        
                    return TRUE;
                    
                }
            
            
            /**
            * Polylang fix
            * 
            * @param mixed $ignore
            * @param mixed $orderby
            * @param mixed $args
            */
            function to_get_terms_orderby_ignore_polylang( $ignore, $orderby, $args )
                {
                    if( !function_exists('is_plugin_active') )
                        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                    
                    if( !   is_plugin_active( 'polylang/polylang.php' ))
                        return $ignore;
                    
                    if ( ! isset( $args['taxonomy'] ) ||  count( $args['taxonomy'] ) <    1  )
                        return $ignore;
                        
                    if( in_array( 'language', $args['taxonomy'] ) )
                        return TRUE;    
                        
                    return $ignore;
                    
                }    
            

            /**
            * WooCommerce Attribute order
            * 
            * @param mixed $ignore
            * @param mixed $orderby
            * @param mixed $args
            */
            function to_get_terms_orderby_ignore_woocommerce( $ignore, $orderby, $args )
                {
                    if( !function_exists('is_plugin_active') )
                        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                    
                    if( !   is_plugin_active( 'woocommerce/woocommerce.php' ))
                        return $ignore;
                    
                    if ( ! function_exists ( 'wc_get_attribute_taxonomies' ) )
                        return $ignore;
                        
                    //create a list of attribute taxonomies
                    $attributes =   wc_get_attribute_taxonomies();
                    $found_attributex_tax   =   array();
                    foreach ( $attributes    as  $attribute ) 
                        {
                            $found_attributex_tax[] =   'pa_'   .   $attribute->attribute_name;
                        }
                    
                    if ( ! isset($args['taxonomy']) ||  count($args['taxonomy']) !==    1 )
                        return $ignore; 
                        
                    if ( count  ( array_intersect( $found_attributex_tax, $args['taxonomy']) )  <   1   )
                        return $ignore;       
                        
                    return TRUE;
                    
                }
        }
        
        
    new TTO_addons(); 