<?php

    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    
    class TO_Terms_Walker extends Walker 
        {

            var $db_fields = array ('parent' => 'parent', 'id' => 'term_id');


            function start_lvl(&$output, $depth = 0, $args = array() )
                {
                    extract($args, EXTR_SKIP);
                    
                    $indent = str_repeat("\t", $depth);
                    $output .= "\n$indent<ul class='children sortable'>\n";
                }


            function end_lvl(&$output, $depth = 0, $args = array())
                {
                    extract($args, EXTR_SKIP);
                        
                    $indent = str_repeat("\t", $depth);
                    $output .= "$indent</ul>\n";
                }


            function start_el(&$output, $term, $depth = 0, $args = array(), $current_object_id = 0) 
                {
                    if ( $depth )
                        $indent = str_repeat("\t", $depth);
                    else
                        $indent = '';
                    
                    $currentScreen = get_current_screen();
                    
                    $term_link  =   isset ( $currentScreen->post_type ) ?   get_edit_term_link( $term, $term->taxonomy, $currentScreen->post_type ) :   get_edit_term_link( $term );
                    
                    $output .= $indent . '<li class="term_type_li" id="item_'.$term->term_id.'"><div class="item"><span class="title">'.apply_filters( 'to/term_title', $term->name, $term ).' </span> <span class="options ui-sortable-handle"><a href="' . $term_link .'"><span class="dashicons dashicons-edit"></span></a></span></div>';
                }


            function end_el(&$output, $object, $depth = 0, $args = array()) 
                {
                    $output .= "</li>\n";
                }

        }

?>