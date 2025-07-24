<?php

use FileBird\Classes\Helpers;

if ( '' !== $filebird_activation_error ) {
	$filebird_activation_error = apply_filters( 'filebird_activation_error', $filebird_activation_error );
	if ( 'no-purchase' == $filebird_activation_error ) {
		$filebird_activation_error = __( 'It seems you don\'t have any valid FileBird license. Please <a href="https://ninjateam.org/support" target="_blank"><strong>contact support</strong></a> to get help or <a href="https://1.envato.market/Get-FileBird" target="_blank"><strong>purchase a FileBird license</strong></a>', 'filebird' );
	} elseif ( 'code-is-used' == $filebird_activation_error ) {
		$filebird_activation_error = sprintf( __( 'This license was used with <i>%s</i>, please <a href="https://1.envato.market/Get-FileBird" target="_blank"><strong>purchase another license</strong></a>, or <a href="https://ninjateam.org/support" target="_blank"><strong>contact support</strong></a>', 'filebird' ), esc_html( $filebird_activation_old_domain ));
	}
	?>
<div class="notice notice-warning is-dismissible">
    <h3><?php esc_html_e( 'Oops! Activation failed.', 'filebird' ); ?></h3>
    <p>
        <?php echo Helpers::wp_kses_i18n( $filebird_activation_error ); ?>
    </p>
</div>
<?php
}
?>