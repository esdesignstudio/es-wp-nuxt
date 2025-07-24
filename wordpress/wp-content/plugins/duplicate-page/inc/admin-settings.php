<?php if (!defined('ABSPATH') && !current_user_can('manage_options')) {
    exit;
} 
$this->custom_assets();
?>
<div class="wrap duplicate_page_settings">
<?php
$this->load_help_desk(); ?>
<h1><?php _e('Duplicate Page Settings ', 'duplicate-page'); ?><a href="https://duplicatepro.com/pro/" target="_blank" class="button button-primary"><?php _e('Buy PRO', 'duplicate-page'); ?></a></h1>
<?php 
$msg = isset($_GET['msg']) ? intval($_GET['msg']) : '';
if (current_user_can('manage_options') && isset($_POST['submit_duplicate_page']) && wp_verify_nonce(sanitize_text_field($_POST['duplicatepage_nonce_field']), 'duplicatepage_action')):
    _e('<div class="saving-txt"><strong>Saving Please wait...</strong></div>','duplicate-page');
        $duplicatepageoptions = array(
            "duplicate_post_editor" => sanitize_text_field(htmlentities($_POST["duplicate_post_editor"])),
            "duplicate_post_status" => sanitize_text_field(htmlentities($_POST["duplicate_post_status"])),
            "duplicate_post_redirect" => sanitize_text_field(htmlentities($_POST["duplicate_post_redirect"])),
            "duplicate_post_suffix" => sanitize_text_field(htmlentities($_POST["duplicate_post_suffix"]))
        );
       
        $saveSettings = update_option('duplicate_page_options', $duplicatepageoptions);
        if ($saveSettings) {
            duplicate_page::dp_redirect('options-general.php?page=duplicate_page_settings&msg=1');
        } else {
            duplicate_page::dp_redirect('options-general.php?page=duplicate_page_settings&msg=2');
        }
endif;

$opt = get_option('duplicate_page_options');
if (!empty($msg) && $msg == 1):
    _e('<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated"> 
    <p><strong>Settings saved.</strong></p><button class="notice-dismiss button-custom-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice</span></button></div>','duplicate-page');
elseif (!empty($msg) && $msg == 2):
  _e('<div class="error settings-error notice is-dismissible" id="setting-error-settings_updated"> 
  <p><strong>Settings not saved.</strong></p><button class="notice-dismiss button-custom-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice</span></button></div>','duplicate-page');
endif;
?> 
<div id="poststuff">
<div id="post-body" class="metabox-holder columns-2">
<div id="post-body-content" style="position: relative;">
<form action="" method="post" name="duplicate_page_form">
<?php  wp_nonce_field('duplicatepage_action', 'duplicatepage_nonce_field'); ?>
<table class="form-table">
<tbody>
<tr>
<th scope="row"><label for="duplicate_post_editor"><?php _e('Choose Editor', 'duplicate-page'); ?></label></th>
<td>
    <select id="duplicate_post_editor" name="duplicate_post_editor">
        <option value="all" <?php echo (isset($opt['duplicate_post_editor']) && $opt['duplicate_post_editor'] == 'all') ? "selected = 'selected'" : ''; ?>><?php _e('All Editors', 'duplicate-page'); ?></option>
    	<option value="classic" <?php echo (isset($opt['duplicate_post_editor']) && $opt['duplicate_post_editor'] == 'classic') ? "selected = 'selected'" : ''; ?>><?php _e('Classic Editor', 'duplicate-page'); ?></option>
    	<option value="gutenberg" <?php echo (isset($opt['duplicate_post_editor']) && $opt['duplicate_post_editor'] == 'gutenberg') ? "selected = 'selected'" : ''; ?>><?php _e('Gutenberg Editor', 'duplicate-page'); ?></option>
        </select>
    <p><?php _e('Please select which editor you are using.<strong> Default: </strong> Classic Editor', 'duplicate-page'); ?></p>
</td>
</tr>	
<tr>
<th scope="row"><label for="duplicate_post_status"><?php _e('Duplicate Post Status', 'duplicate-page'); ?></label></th>
<td>
    <select id="duplicate_post_status" name="duplicate_post_status">
    	<option value="draft" <?php echo($opt['duplicate_post_status'] == 'draft') ? "selected = 'selected'" : ''; ?>><?php _e('Draft', 'duplicate-page'); ?></option>
    	<option value="publish" <?php echo($opt['duplicate_post_status'] == 'publish') ? "selected = 'selected'" : ''; ?>><?php _e('Publish', 'duplicate-page'); ?></option>
    	<option value="private" <?php echo($opt['duplicate_post_status'] == 'private') ? "selected = 'selected'" : ''; ?>><?php _e('Private', 'duplicate-page'); ?></option>
    	<option value="pending" <?php echo($opt['duplicate_post_status'] == 'pending') ? "selected = 'selected'" : ''; ?>><?php _e('Pending', 'duplicate-page'); ?></option>
        </select>
    <p><?php _e('Please select any post status you want to assign for duplicate post.<strong> Default: </strong> Draft','duplicate-page'); ?></p>
</td>
</tr>
<tr>
<th scope="row"><label for="duplicate_post_redirect"><?php _e('Redirect to after click on <strong>Duplicate This Link</strong>', 'duplicate-page'); ?></label></th>
<td><select id="duplicate_post_redirect" name="duplicate_post_redirect">
	<option value="to_list" <?php echo($opt['duplicate_post_redirect'] == 'to_list') ? "selected = 'selected'" : ''; ?>><?php _e('To All Posts List', 'duplicate-page'); ?></option>
	<option value="to_page" <?php echo($opt['duplicate_post_redirect'] == 'to_page') ? "selected = 'selected'" : ''; ?>><?php _e('To Duplicate Edit Screen', 'duplicate-page'); ?></option>
    </select>
    <p><?php  _e('Please select any post redirection, redirect you to selected after click on duplicate this link.<strong> Default: </strong>To All Posts List','duplicate-page'); ?></p>
</td>
</tr>
<tr>
<th scope="row"><label for="duplicate_post_suffix"><?php _e('Duplicate Post Suffix', 'duplicate-page'); ?></label></th>
<td>
 <input type="text" class="regular-text" value="<?php echo !empty($opt['duplicate_post_suffix']) ? esc_attr($opt['duplicate_post_suffix']) : ''; ?>" id="duplicate_post_suffix" name="duplicate_post_suffix">
    <p><?php _e('Add a suffix for duplicate or clone post as Copy, Clone etc. It will show after title.', 'duplicate-page'); ?></p>
</td>
</tr>
</tbody></table>
<p class="submit"><input type="submit" value="<?php _e('Save Changes','duplicate-page'); ?>" class="button button-primary" id="submit" name="submit_duplicate_page"></p>
</form>
</div>
</div>
</div>
</div>
