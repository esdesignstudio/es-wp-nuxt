<?php
/**
 * Plugin Name: FileBird Lite
 * Plugin URI: https://ninjateam.org/wordpress-media-library-folders/
 * Description: Organize thousands of WordPress media files into folders/ categories at ease.
 * Version: 6.4.7
 * Author: Ninja Team
 * Author URI: https://ninjateam.org
 * Text Domain: filebird
 * Domain Path: /i18n/languages/
 */

namespace FileBird;

defined( 'ABSPATH' ) || exit;

$php_version_valid = \version_compare( phpversion(), '7.4', '>=' );

if ( function_exists( 'FileBird\\init' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/Fallback.php';
	add_action(
		'admin_init',
		function() {
			deactivate_plugins( plugin_basename( __FILE__ ) );
		}
	);
	return;
}

if ( ! defined( 'NJFB_PREFIX' ) ) {
	define( 'NJFB_PREFIX', 'filebird' );
}

if ( ! defined( 'NJFB_VERSION' ) ) {
	define( 'NJFB_VERSION', '6.4.7' );
}

if ( ! defined( 'NJFB_PLUGIN_FILE' ) ) {
	define( 'NJFB_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'NJFB_PLUGIN_URL' ) ) {
	define( 'NJFB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'NJFB_PLUGIN_PATH' ) ) {
	define( 'NJFB_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'NJFB_PLUGIN_BASE_NAME' ) ) {
	define( 'NJFB_PLUGIN_BASE_NAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'NJFB_REST_URL' ) ) {
	define( 'NJFB_REST_URL', 'filebird/v1' );
}

if ( ! defined( 'NJFB_REST_PUBLIC_URL' ) ) {
	define( 'NJFB_REST_PUBLIC_URL', 'filebird/public/v1' );
}

if ( ! defined( 'NJFB_UPLOAD_DIR' ) ) {
	define( 'NJFB_UPLOAD_DIR', 'filebird-uploads' );
}

if ( file_exists( NJFB_PLUGIN_PATH . '/vendor/autoload.php' ) && $php_version_valid ) {
	require_once NJFB_PLUGIN_PATH . '/vendor/autoload.php';
}

if ( ! defined( 'NJFB_DEVELOPMENT' ) ) {
	define( 'NJFB_DEVELOPMENT', false );
}

spl_autoload_register(
	function ( $class ) {
		$prefix   = __NAMESPACE__;
		$base_dir = __DIR__ . '/includes';

		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}

		$relative_class_name = substr( $class, $len );
		$file                = $base_dir . str_replace( '\\', '/', $relative_class_name ) . '.php';
		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);

if ( ! function_exists( 'FileBird\\init' ) ) {
	function init() {
		Plugin::getInstance();
		new I18n();
		new Classes\Feedback();
		new Classes\Review();
		new Classes\ActivePro();

		Admin\Settings::getInstance();
		Classes\Convert::getInstance();
		Classes\Core::getInstance();
		Rest\RestApi::getInstance();

		// Support 3rd plugins
		new Support\SupportController();

		// Import from 3rd plugins
		new Controller\Import\ImportController();

		// Schedule
		new Classes\Schedule();

		// Gutenberg Blocks
		new Blocks\BlockController();

		if ( function_exists( 'register_block_type' ) ) {
			require plugin_dir_path( __FILE__ ) . 'blocks/filebird-gallery/init.php';
		}
	}
}
add_action( 'plugins_loaded', 'FileBird\\init' );

register_activation_hook( __FILE__, array( 'FileBird\\Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'FileBird\\Plugin', 'deactivate' ) );