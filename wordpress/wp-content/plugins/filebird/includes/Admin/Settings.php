<?php
namespace FileBird\Admin;

use FileBird\Classes\Helpers;
use FileBird\Utils\Singleton;
use FileBird\Utils\Vite;

defined( 'ABSPATH' ) || exit;
/**
 * Settings Page
 */
class Settings {
	private $hook_suffix    = null;
	const SETTING_PAGE_SLUG = 'filebird-settings';

	use Singleton;

	private function doHooks() {
		add_filter( 'plugin_action_links_' . NJFB_PLUGIN_BASE_NAME, array( $this, 'addActionLinks' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		add_action( 'admin_menu', array( $this, 'settingsMenu' ) );
		add_action( 'in_admin_header', array( $this, 'in_admin_header' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	public function in_admin_header() {
		$screen = get_current_screen();

		if ( $screen->id === $this->hook_suffix ) {
			include NJFB_PLUGIN_PATH . '/views/settings/header.php';
		}
	}

	public function getSettingHookSuffix() {
		return $this->hook_suffix;
	}

	private function getInlineMenuIcon() {
		return 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCg0KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiDQoJeT0iMHB4IiB2aWV3Qm94PSIwIDAgNTAwIDQ0NC40NCIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNTAwIDQ0NC40NDsiIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KCTxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJCS5zdDAgew0KCQkJZmlsbDogI0E4QUFBRDsNCgkJfQ0KCTwvc3R5bGU+DQoJPHBhdGggY2xhc3M9InN0MCIgZD0iTTQ1My43LDY0LjgxSDI0NS40OEwyMDQuMDUsNi43OUMyMDEuMDEsMi41MywxOTYuMDksMCwxOTAuODYsMEg1MC4yQzIyLjQ3LDAsMCwyMi40NywwLDUwLjJ2MTQuNjJ2MC4wMnYzMzMuMzENCgljMCwyNS41NywyMC43Myw0Ni4zLDQ2LjMsNDYuM0g0NTMuN2MyNS41NywwLDQ2LjMtMjAuNzMsNDYuMy00Ni4zVjExMS4xMUM1MDAsODUuNTQsNDc5LjI3LDY0LjgxLDQ1My43LDY0LjgxeiBNMjUwLDEyNy45Mw0KCWMzMy44MSwwLDY0LjUsMTMuMjgsODcuMjEsMzQuODhjLTEuMDctMC4wMi0yLjE0LTAuMDQtMi40Mi0wLjA0Yy0xMy40LDAtMjQuMjcsMTAuODYtMjQuMjcsMjQuMjdzMzkuMTUsMzIuMTEsMTcuNjQsOTIuMTcNCglsLTE2LjQsNDYuODdsLTQ1LjYzLTEyOC45OWgxOC4zMXYtMTIuMjloLTc4LjJ2MTIuMjloMTcuNjZsMTkuODEsNTUuOTlsLTI1LjU1LDczbC00NS42My0xMjguOTloMTYuNzR2LTEyLjI5aC00NC45MQ0KCUMxNjcuMDEsMTUwLjUzLDIwNS44NiwxMjcuOTMsMjUwLDEyNy45M3ogTTEzMy40NSwyMDUuMDFjLTAuMDUsMC4xMy0wLjEyLDAuMjYtMC4xNywwLjRDMTMzLjM0LDIwNS4yOCwxMzMuMzksMjA1LjE0LDEzMy40NSwyMDUuMDENCgl6IE0xMjMuMzksMjU0LjUzYzAtMTcuMzgsMy41MS0zMy45NSw5Ljg1LTQ5LjAybDU2LjUsMTYwLjM5QzE1MC4yMiwzNDQuNDgsMTIzLjM5LDMwMi42NCwxMjMuMzksMjU0LjUzeiBNMjExLjA1LDM3NS4wMQ0KCWwzOS4xNC0xMDMuNThsMzYuODQsMTA0LjJjLTExLjcyLDMuNTgtMjQuMTUsNS41Mi0zNy4wMyw1LjUyQzIzNi40MSwzODEuMTQsMjIzLjMzLDM3OC45NywyMTEuMDUsMzc1LjAxeiBNMzA5LjUyLDM2Ni4yOA0KCWw0Mi44OS0xMTkuNTF2MC4yOWwzLTkuMzZjNC4yOS0xMy40LDYuMzctMjcuNDYsNS41MS00MS41Yy0wLjA3LTEuMDgtMC4xNS0yLjEzLTAuMjUtMy4xNWMxMC4xNCwxOC4yMSwxNS45MywzOS4xNywxNS45Myw2MS40OQ0KCUMzNzYuNjEsMzAyLjk0LDM0OS40NCwzNDQuOTgsMzA5LjUyLDM2Ni4yOHoiIC8+DQo8L3N2Zz4=';
	}

	public function settingsMenu() {
		$this->hook_suffix = add_menu_page(
			__( 'FileBird', 'filebird' ),
			__( 'FileBird', 'filebird' ),
			'manage_options',
			self::SETTING_PAGE_SLUG,
			array( $this, 'settingsPage' ),
			$this->getInlineMenuIcon(),
			81
		);
	}

	public function admin_enqueue_scripts( $hook_suffix ) {
		if ( $this->hook_suffix === $hook_suffix ) {
			$script_handle = Vite::enqueue_vite( 'admin.tsx' );

			$postTypes = apply_filters(
				'filebird_post_types',
				get_post_types(
					array(
						'public' => true,
					)
				)
			);

			if ( isset( $postTypes['attachment'] ) ) {
				unset( $postTypes['attachment'] );
			}

			foreach ( $postTypes as $key => $value ) {
				$postTypes[ $key ] = get_post_type_object( $key )->labels->singular_name;
			}

			$wpmlActiveLanguages = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0 ) );

			wp_localize_script(
				$script_handle,
				'fbv_admin',
				array(
					'post_types'         => $postTypes,
					'enabled_post_types' => array(),
					'rest_api_key'       => get_option( 'fbv_rest_api_key', '' ),
					'wpml'               => array(
						'display_sync' => ! empty( $wpmlActiveLanguages ),
					),
				)
			);
		}
	}

	public function settingsPage() {
		$notice = '';

		$filebird_activation_error = get_option( 'filebird_activation_error', '' );
		if ( $filebird_activation_error != '' ) {
			update_option( 'filebird_activation_error', '' );
		}

		$filebird_activation_old_domain = get_option( 'filebird_activation_old_domain', '' );
		if ( $filebird_activation_old_domain != '' ) {
			update_option( 'filebird_activation_old_domain', '' );
		}

		if ( '' !== $filebird_activation_error ) {
			$filebird_activation_error = apply_filters( 'filebird_activation_error', $filebird_activation_error );
			if ( 'no-purchase' == $filebird_activation_error ) {
				$filebird_activation_error = __( 'It seems you don\'t have any valid FileBird license. Please <a href="https://ninjateam.org/support" target="_blank"><strong>contact support</strong></a> to get help or <a href="https://1.envato.market/Get-FileBird" target="_blank"><strong>purchase a FileBird license</strong></a>', 'filebird' );
			} elseif ( 'code-is-used' == $filebird_activation_error ) {
				$filebird_activation_error = sprintf( __( 'This license was used with <i>%s</i>, please <a href="https://1.envato.market/Get-FileBird" target="_blank"><strong>purchase another license</strong></a>, or <a href="https://ninjateam.org/support" target="_blank"><strong>contact support</strong></a>', 'filebird' ), esc_html( $filebird_activation_old_domain ) );
			}
			$notice = '<div style="margin-bottom: 1rem !important;" class="njt-warning-notice filebird-notice notice notice-warning is-dismissible wp-md:fb-max-w-[1060px] fb-m-auto"><p>' . $filebird_activation_error . '</p><button type="button" class="notice-dismiss" onClick="jQuery(\'.njt-warning-notice\').remove()"><span class="screen-reader-text">' . __( 'Dismiss this notice.', 'filebird' ) . '</span></button></div>';
		}

		?>

		<div class="wrap">
			<?php echo $notice; ?>
			<h1 id="filebird-setting-heading"><?php esc_html_e( 'FileBird Settings', 'filebird' ); ?></h1>
			<div id="filebird-setting"/>
		</div>
		<style>
			.notice:not(.filebird-notice) {
				display: none;
			}
		</style>
		<?php
	}

	public function plugin_row_meta( $links, $file ) {
		if ( strpos( $file, 'filebird.php' ) !== false ) {
			$new_links = array(
				'doc' => '<a href="https://ninjateam.gitbook.io/filebird/" target="_blank">' . __( 'Documentation', 'filebird' ) . '</a>',
			);

			$links = array_merge( $links, $new_links );
		}

		return $links;
	}

	public function addActionLinks( $links ) {
		$settingsLinks = array(
			'<a href="' . admin_url( 'admin.php?page=' . self::SETTING_PAGE_SLUG ) . '">' . esc_html__( 'Settings', 'filebird' ) . '</a>',
		);

		return array_merge( $settingsLinks, $links );
	}
}