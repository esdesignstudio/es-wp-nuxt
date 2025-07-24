<?php

    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
            
    class Post_Types_Order_Walker extends Walker 
        {

            var $db_fields = array (    
                                        'parent' => 'post_parent', 
                                        'id' => 'ID'
                                        );

        


            /**
            * Start element
            * 
            * @param mixed $output
            * @param mixed $page
            * @param mixed $depth
            * @param mixed $args
            * @param mixed $id
            */
            function start_el( &$output, $object, $depth = 0, $args = array(), $id = 0) 
                {                   
                    $options    =   CptoFunctions::get_options();
                    
                    if ( $depth )
                        $indent = str_repeat("\t", $depth);
                    else
                        $indent = '';

                    extract($args, EXTR_SKIP);

                    $item_title     =   apply_filters( 'the_title', $object->post_title, $object->ID );
                    
                    $item_details   =   apply_filters('pto/interface_item_data', '', $object );
                     
                    $output .= $indent . '<li id="item_' . $object->ID . '">
                                                <span>' . $item_title . ' ' . $item_details .'</span>';
                    
                    if ( $options['edit_view_links']    ===  1 )
                        $output .=  '<span class="options ui-sortable-handle"><a href="' . get_edit_post_link( $object ) .'"><span class="dashicons dashicons-edit"></span></a></span>';                
                    
                    $output .=  '</li>';
                    
                    $output .=  apply_filters( 'pto/interface/table/tbody', '', $object );
               
                }


            /**
            * End element
            * 
            * @param mixed $output
            * @param mixed $page
            * @param mixed $depth
            * @param mixed $args
            */
            function end_el(&$output, $page, $depth = 0, $args = array()) 
                {
                    $output .= "</tr>\n";
                }

        }