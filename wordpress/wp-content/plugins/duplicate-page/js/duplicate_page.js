jQuery(window).on('load',function() {
    jQuery('.dpmrs').delay(10000).slideDown('slow');
});
jQuery(document).ready(function () {
    jQuery('.close_dp_help').on('click', function (e) {
        var what_to_do = jQuery(this).data('ct');
        var nonce = dt_params.nonce
        jQuery.ajax({
            type: "post",
            url: dt_params.ajax_url,
            data: {
                action: "mk_dp_close_dp_help",
                what_to_do: what_to_do,
                nonce:nonce
            },
            success: function (response) {
                jQuery('.dpmrs').slideUp('slow');
            }
        });
    });
});
jQuery(document).ready(function() {
    var admin_page_url = "options-general.php?page=duplicate_page_settings";
    window.history.replaceState({}, document.title, admin_page_url);
});
jQuery(document).on('click', '.button-custom-dismiss', function(e) {
    jQuery(this).parent().hide();
})