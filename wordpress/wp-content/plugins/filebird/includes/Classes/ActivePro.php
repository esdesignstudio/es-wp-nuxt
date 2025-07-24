<?php
namespace FileBird\Classes;

use FileBird\Classes\Helpers;

defined( 'ABSPATH' ) || exit;

class ActivePro {
	private $envato_login_url = 'https://active.ninjateam.org/envato-login/';

	const FB_SLUG = 'filebird_pro';

	public function __construct() {
		add_filter( 'fbv_data', array( $this, 'localize_fbv_data' ) );
	}

	public function resPermissionsCheck() {
		return current_user_can( 'upload_files' );
   	}

	public function localize_fbv_data( $data ) {
		$return_args = array(
			'action' => 'fb_login_envato_success',
			'nonce'  => wp_create_nonce( 'njt_filebird_login_envato' ),
		);

		$return_url               = add_query_arg( $return_args, admin_url( 'admin-ajax.php' ) );
		$domain                   = $this->get_domain();
		$data['login_envato_url'] = esc_url(
			add_query_arg(
				array(
					'domain'     => $domain,
					'plugin'     => 'filebird',
					'return_url' => urlencode( $return_url ),
					'ip'         => Helpers::getIp(),
				),
				$this->envato_login_url
			)
		);

		$data['license']['status'] = false;

		$data['deactivate_license_nonce'] = wp_create_nonce( 'deactivate_license_nonce' );
		if ( ! isset( $data['i18n'] ) ) {
			$data['i18n'] = array();
		}
		$data['i18n']['active_to_update']                   = esc_html__( 'Please activate FileBird license to use this feature.', 'filebird' );
		$data['i18n']['deactivate_license_confirm_title']   = esc_html__( 'Deactivating license', 'filebird' );
		$data['i18n']['deactivate_license_confirm_content'] = esc_html__( 'Are you sure to deactivate the current license key? You will not get regular updates or any support for this site.', 'filebird' );
		$data['i18n']['deactivate_license_try_again']       = esc_html__( 'Please try again later!', 'filebird' );
		$data['i18n']['update_error']                       = esc_html__( 'Update failed: Your current FileBird license is being used on another site: {site}. To get this update, please deactivate the license on the other site first.', 'filebird' );
		return $data;
	}
	private function get_domain() {
		return Helpers::getDomain();
	}
}