var el = wp.element.createElement;
    var __ = wp.i18n.__;
    var registerPlugin = wp.plugins.registerPlugin;
    var PluginPostStatusInfo = wp.editPost.PluginPostStatusInfo;
    var buttonControl = wp.components.Button;

    function dpGutenButton({}) {
        return el(
            PluginPostStatusInfo,
            {
                className: 'dt-duplicate-post-status-info'
            },
            el(
                buttonControl,
                {
                    isTertiary: true,
                    name: 'duplicate_page_link_guten',
                    isLink: true,
                    title: dt_params.dp_post_title,
                    href : dt_params.dp_duplicate_link+"&post="+dt_params.dp_post_id+"&nonce="+dt_params.dtnonce
                }, dt_params.dp_post_text
            )
        );
    }

    registerPlugin( 'dt-duplicate-post-status-info-plugin', {
        render: dpGutenButton
    } );