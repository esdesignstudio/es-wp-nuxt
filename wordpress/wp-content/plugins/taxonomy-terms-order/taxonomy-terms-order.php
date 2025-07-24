<?php
/*
* Plugin Name: Category Order and Taxonomy Terms Order
* Plugin URI: http://www.nsp-code.com
* Description: Order Categories and all custom taxonomies terms (hierarchically) and child terms using a Drag and Drop Sortable javascript capability. 
* Version: 1.9
* Author: Nsp-Code
* Author URI: https://www.nsp-code.com
* Author Email: contact@nsp-code.com
* Text Domain: taxonomy-terms-order
* Domain Path: /languages/ 
*/


    define('TOPATH',    plugin_dir_path(__FILE__));
    define('TOURL',     plugins_url('', __FILE__));
    
    define('TTO_VERSION',          '1.9');
    
    include_once    (   TOPATH . '/include/class.tto.php'   );
    include_once    (   TOPATH . '/include/class.functions.php'   );
    include_once    (   TOPATH . '/include/class.addons.php'  );

    register_activation_hook(__FILE__, 'TTO_activated');
    function TTO_activated( $network_wide ) 
        {
            global $wpdb;
                 
            // check if it is a network activation
            if ( $network_wide ) 
                {                   
                    // Get all blog ids
                    $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
                    foreach ( $blogids as $blog_id ) 
                        {
                            switch_to_blog( $blog_id );
                            TTO_functions::check_table_column();
                            restore_current_blog();
                        }
                    
                    return;
                }
                else
                TTO_functions::check_table_column();
        }
        
        
    add_action( 'wp_initialize_site', 'TTO_wp_initialize_site', 99, 2 );       
    function TTO_wp_initialize_site( $blog_data, $args )
        {
            global $wpdb;
         
            if ( is_plugin_active_for_network('taxonomy-terms-order/taxonomy-terms-order.php') ) 
                {
                    switch_to_blog( $blog_data->blog_id );
                    TTO_functions::check_table_column();                    
                    restore_current_blog();
                }
        }
    
    
    /**
    * Load the textdomain    
    */
    add_action( 'plugins_loaded', 'TTO_load_textdomain'); 
    function TTO_load_textdomain() 
        {
            load_plugin_textdomain('taxonomy-terms-order', FALSE, dirname( plugin_basename( __FILE__ ) ) . '/languages');
        }
        
    
    /**
    * Initialize the main class
    * 
    */
    function TTO_class_load()
        {
            new TTO();
        }
    add_action( 'plugins_loaded', 'TTO_class_load'); 
    
    
    /**
    * Temporary placeholder function to prevent fatal errors when using the Uncode theme.
    * 
    * @param mixed $clauses
    * @param mixed $taxonomies
    * @param mixed $args
    */
    function TO_apply_order_filter( $clauses, $taxonomies, $args )
        {
            return $clauses;    
        }
        

?>