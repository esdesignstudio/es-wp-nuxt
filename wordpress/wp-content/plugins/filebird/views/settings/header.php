<?php

$navigation = array(
    array(
        'label' => __( 'Activation', 'filebird' ),
        'link'  => 'activation',
        'icon'  => 'dashicons-awards',
    ),
    array(
        'label' => __( 'Settings', 'filebird' ),
        'link'  => 'settings',
        'icon'  => 'dashicons-admin-generic',
    ),
    array(
        'label' => __( 'Tools', 'filebird' ),
        'link'  => 'tools',
        'icon'  => 'dashicons-admin-tools',
    ),
    array(
        'label' => __( 'Import/Export', 'filebird' ),
        'link'  => 'import-export',
        'icon'  => 'dashicons-database',
    ),
);
?>

<div id="filebird-admin-header">
    <div id="filebird-admin-logo">
        <img src="<?php echo esc_attr( NJFB_PLUGIN_URL . 'assets/img/logo.svg' ); ?>" alt="filebird logo" />
        <div id="filebird-admin-version">
          <h3 class="wp-heading-inline">
            FileBird Lite
          </h3>
          <span class="divider"></span>
          <span><?php esc_html_e( 'Version', 'filebird' ); ?> <span><?php echo esc_html( NJFB_VERSION ); ?></span></span>
        </div>
    </div>
    <div id="filebird-admin-actions">
        <a href="https://ninjateam.gitbook.io/filebird/features/interface" target="_blank" rel="noopener noreferrer">
            <?php esc_html_e( 'Docs', 'filebird' ); ?>
        </a>
        <a href="https://ninjateam.org/support/" target="_blank" rel="noopener noreferrer">
            <?php esc_html_e( 'Support', 'filebird' ); ?>
        </a>
        <a href="https://ninjateam.gitbook.io/filebird/other-links/changelog" target="_blank" rel="noopener noreferrer">
            <?php esc_html_e( 'Changelog', 'filebird' ); ?>
        </a>
    </div>
</div>