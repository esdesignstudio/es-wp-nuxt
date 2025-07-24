<?php
/* 強制關閉後台登入首頁的小工具 */ 
function wpc_dashboard_widgets() {
    global $wp_meta_boxes;
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_site_health']);        
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);       
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);       
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']); 
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);         
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);      
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts']);     
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);        
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);        
    // unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard-widgets']);
}
add_action('wp_dashboard_setup', 'wpc_dashboard_widgets');
add_filter( 'wp_mail_smtp_admin_dashboard_widget', '__return_false' ); // WP Mail SMTP

remove_action( 'welcome_panel', 'wp_welcome_panel' );

function wpex_wp_welcome_panel() { ?>

	<div class="custom_welcome">
        <div class="custom_welcome__title">
            <h2><?php _e( 'Hey! Welcome to ES WP CMS' ); ?></h2>
            <p class="about-description"><?php _e( '此為 ES design 專為客戶客製化設計的主題，網站程式碼有需要更動建議請聯繫 <a href="https://e-s.tw">ES design</a> 設計團隊。我們不負責未經過我們同意，擅自調整程式碼、擅自安裝有資安危險之外掛等等，所造成的損害。' ); ?></p>
        </div>
	<div>

    <style>
        h1 {
            display: none;
        }
        .custom_welcome {
            width: 500px;
            margin: 0 auto;
        }
        .custom_welcome h2 {
            font-size: 50px;
        }
        .custom_welcome p {
            color: #bcbcbc;
        }
        #wpwrap {
            position: relative;
            background-color: #cec8c2;
            background-image: url(<?php echo get_template_directory_uri().'/asset/imgs/dash_bg.jpg'; ?>);
            background-size: 2500px 1471px;
            background-position: center;
        }
        #wpwrap:before {
            /* content: '';
            left: calc(50% + 100px);
            top: 50%;
            transform: translate(-50%, -50%);
            width: 40vw;
            height: 40vw;
            border-radius: 50vw;
            position: absolute;
            background-color: #fff;
            filter: blur(20px); */
        }
    </style>
<?php }
add_action( 'welcome_panel', 'wpex_wp_welcome_panel' );