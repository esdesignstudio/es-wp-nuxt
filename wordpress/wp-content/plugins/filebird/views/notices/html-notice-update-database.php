<?php

use FileBird\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$update_link = add_query_arg(
    array(
        'page' => Settings::SETTING_PAGE_SLUG . '#/tools?autorun=true',
    ),
    admin_url( '/admin.php' )
);
?>

<div class="notice notice-warning is-dismissible njt-fb-update-db-noti filebird-notice" id="njt-fb-update-db-noti">
    <div class="njt-fb-update-db-noti-item">
        <h3><?php esc_html_e( 'FileBird 4 Update Required', 'filebird' ); ?></h3>
    </div>
    <div class="njt-fb-update-db-noti-item">
        <p>
            <?php esc_html_e( 'You are using the new FileBird 4. Please update database to view your folders correctly.', 'filebird' ); ?>
        </p>
    </div>
    <div class="njt-fb-update-db-noti-item">
        <p>
            <a class="button button-primary" href="<?php echo esc_url( $update_link ); ?>">
                <strong><?php esc_html_e( 'Update now', 'filebird' ); ?></strong>
            </a>
        </p>
    </div>
</div>