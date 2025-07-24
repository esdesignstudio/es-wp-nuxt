<?php

    /**
    * Compatibility     : Endfold
    * Introduced at     : 5.6.2
    */

    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    
    class PTO_Enfold
        {
            
            /**
            * Constructor
            *             
            */
            function __construct()
                {
                    
                    add_filter( 'pto/posts_orderby/ignore', array ( $this, 'ignore_post_types_order_sort' ), 10, 3 );
                }
            
                
            /**
            * Ignore a specific post type
            *     
            * @param TRUE $ignore
            * @param mixed $orderBy
            * @param mixed $query
            * @return TRUE
            */
            function ignore_post_types_order_sort( $ignore, $orderBy, $query ) 
                {
                    if ( isset($query->query_vars)  &&  !empty( $query->query_vars['post_type'] )) 
                        {
                            $query_post_types   =   array();
                            foreach ( (array)$query->query_vars['post_type'] as $_post_type ) 
                                {
                                    $query_post_types[] =   $_post_type;
                                }
                            
                            if  ( in_array("avia_framework_post", $query_post_types ) )
                                $ignore =   TRUE; 
                        }
                    
                    return $ignore;
                }                        
                                
        }
        
    new PTO_Enfold();


?>