<?php

    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    
    
    class PTO_Interface 
        {
            
            var $functions;
            var $CPTO;
            
            /**
            * Constructor
            * 
            */
            function __construct() 
                {

                    $this->functions    =   new CptoFunctions();
                    
                    global $CPTO;
                    $this->CPTO         =   $CPTO;
                    
                }
                
                
            /**
            * Sort interfaces
            * 
            */
            function sort_page() 
                {
                    
                    $options          =     $this->functions->get_options();
                    
                    ?>
                    <div id="cpto" class="wrap">
                        <div class="icon32" id="icon-edit"><br></div>
                        <h2><?php echo esc_html( $this->CPTO->current_post_type->labels->singular_name . ' -  '. esc_html__('Re-Order', 'post-types-order') ); ?></h2>

                        <?php $this->functions->cpt_info_box(); ?>  
                        
                        <div id="ajax-response"></div>
                        
                        <noscript>
                            <div class="error message">
                                <p><?php esc_html_e('This plugin can\'t work without javascript, because it\'s use drag and drop and AJAX.', 'post-types-order'); ?></p>
                            </div>
                        </noscript>
                        
                        <p>&nbsp;</p>
           
                        <div id="order-objects">
           
                            <div id="nav-menu-header">
                                <div class="major-publishing-actions">

                                        
                                        <div class="alignright actions">
                                            <p class="actions">
              
                                                <span class="img_spacer">&nbsp;
                                                    <img alt="" src="<?php echo CPTURL ?>/images/wpspin_light.gif" class="waiting pto_ajax_loading" style="display: none;">
                                                </span>
                                                <a href="javascript:;" class="save-order button-primary"><?php _e('Update', 'atto') ?></a>
                                            </p>
                                        </div>
                                        
                                        <div class="clear"></div>

                                </div><!-- END .major-publishing-actions -->
                            </div><!-- END #nav-menu-header -->
           
            
                            <div id="post-body"> 
                                <ul id="sortable" class="sortable ui-sortable">
                                
                                    <?php $this->list_pages('hide_empty=0&title_li=&post_type=' . $this->CPTO->current_post_type->name ); ?>
                                    
                                </ul>
                            </div>
                            
                            <div id="nav-menu-footer">
                                <div class="major-publishing-actions">
                                        <div class="alignright actions">
                                            <img alt="" src="<?php echo CPTURL ?>/images/wpspin_light.gif" class="waiting pto_ajax_loading" style="display: none;">
                                            <a href="javascript:;" class="save-order button-primary"><?php _e('Update', 'atto') ?></a>
                                        </div>
                                        
                                        <div class="clear"></div>

                                </div><!-- END .major-publishing-actions -->
                            </div><!-- END #nav-menu-header -->
             
                        </div>
                                       
                        <?php wp_nonce_field( 'interface_sort_nonce', 'interface_sort_nonce' ); ?>
                        
                        <script type="text/javascript">
                            jQuery(document).ready(function() {
                                jQuery("#sortable").sortable({
                                    'tolerance':'intersect',
                                    'cursor':'pointer',
                                    'items':'li',
                                    'placeholder':'placeholder',
                                    'nested': 'ul'
                                });
                                
                                jQuery("#sortable").disableSelection();
                                jQuery(".save-order").bind( "click", function() {
                                    jQuery(this).parent().find('img').show();
                                    jQuery("html, body").animate({ scrollTop: 0 }, "fast");
                                    
                                    jQuery.post( ajaxurl, { action:'update-custom-type-order', order:jQuery("#sortable").sortable("serialize"), 'interface_sort_nonce' : jQuery('#interface_sort_nonce').val() }, function() {
                                        jQuery("#ajax-response").html('<div class="message updated fade"><p><?php esc_html_e('Items Order Updated', 'post-types-order') ?></p></div>');
                                        jQuery("#ajax-response div").delay(3000).hide("slow");
                                        jQuery('img.pto_ajax_loading').hide();
                                    });
                                });
                            });
                        </script>
                        
                        
                        
                        
                    </div>
                    <?php
                }

                
            /**
            * List pages
            * 
            * @param mixed $args
            */
            function list_pages($args = '') 
                {
                    $defaults = array(
                        'depth'             => -1, 
                        'date_format'       => get_option('date_format'),
                        'child_of'          => 0, 
                        'sort_column'       => 'menu_order',
                        'post_status'       =>  'any' 
                    );

                    $r = wp_parse_args( $args, $defaults );
                    extract( $r, EXTR_SKIP );

                    $output = '';

                    $r['exclude'] = implode( ',', apply_filters('wp_list_pages_excludes', array()) );
                    
                    // Query pages.
                    $r['hierarchical'] = 0;
                    $args = array(
                                'sort_column'       =>  'menu_order',
                                'post_type'         =>  $post_type,
                                'posts_per_page'    => -1,
                                'post_status'       =>  'any',
                                'orderby'            => array(
                                                            'menu_order'    => 'ASC',
                                                            'post_date'     =>  'DESC'
                                                            )
                    );
                    
                    //allow customisation of the query if necesarelly
                    $args   =   apply_filters('pto/interface/query/args', $args ); 
                    
                    $the_query  = new WP_Query( $args );
                    $pages      = $the_query->posts;

                    if ( !empty($pages) ) 
                        {
                            $output .= $this->walk_tree($pages, $r['depth'], $r);
                        }

                    echo    wp_kses_post    (   $output );
                }
            
            /**
            * Tree walker
            * 
            * @param mixed $pages
            * @param mixed $depth
            * @param mixed $r
            */
            function walk_tree($pages, $depth, $r) 
                {
                    $walker = new Post_Types_Order_Walker;

                    $args = array($pages, $depth, $r);
                    return call_user_func_array(array(&$walker, 'walk'), $args);
                }
            
            
        }

?>