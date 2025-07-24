<?php

//add css
add_action( 'admin_enqueue_scripts', 'bhm_load_admin_style' );  

function bhm_load_admin_style() {
  ob_start();
  wp_enqueue_style( 'admin_css', plugin_dir_url( __FILE__ ) . 'css/style-admin.css', false, '1.0.2' );
}

function app_output_buffer() {
} // soi_output_buffer

//to solve the issue in health check
//add_action('init', 'app_output_buffer');

function bhm_get_menu_list(){

  ob_start(null, 0, 0);


  global $wp_session;
  //get all roles of user
  global $wp_roles;
  
  $roles = $wp_roles->roles;

  if (isset($_POST['save'])) {
 
    //check administrator access required
    if(current_user_can('administrator'))
    {
          //check wpnonce
      if(check_admin_referer('menu-remove')){
        
        //here we have to check this is array or not. (array check validation)
        $menu_list = isset( $_POST['menu_list'] ) ? (array) $_POST['menu_list'] : array();
        $sub_menu_list = isset( $_POST['sub_menu_list'] ) ? (array) $_POST['sub_menu_list'] : array();

        $new_menu_list = array(); //define array for side menu array
        $new_sub_menu_list = array(); //define array for side sub menu array
        foreach ($menu_list as $list_data_key => $list_data_role) {

            // data sanitization functions used for valid text (text validation)
            $list_data_role = array_map( 'sanitize_text_field', $list_data_role );
            foreach ($list_data_role as $list_data) {
              if (is_numeric($list_data)) { //validate the data
                 //we check input will be only number then after we process further. 
                 $new_menu_list[$list_data_key][] = $wp_session['all_side_menus'][$list_data][2];
              }
            }
        }

        foreach ($sub_menu_list as $sub_list_data_key => $sub_list_data_role) {
          
          $sub_list_data_role = array_map( 'sanitize_text_field', $sub_list_data_role );
          foreach ($sub_list_data_role as $sub_list_data) {
              //we get parent and child key 
              $key_data = explode('_',$sub_list_data);
              $parent_key = $key_data[0];
              $child_key = $key_data[1];
              //we find parent of child
              $parent_value = $wp_session['all_side_menus'][$parent_key][2];

              //we check input will be only number then after we process further. 
              $new_sub_menu_list[$sub_list_data_key][] = $parent_value.'__con__'.$wp_session['all_side_sub_menus'][$parent_value][$child_key][2];
          }                              
          
        }

        $remove_side_array = $new_menu_list;
        $json_remove_side_array = json_encode($remove_side_array); //json array.

        $remove_sub_side_array = $new_sub_menu_list;
        $json_remove_sub_side_array = json_encode($remove_sub_side_array); //json array.
        
        //remove menus form the admin top menu.
        //here we have to check this is array or not.(array check validation)
        $top_menu_list = isset( $_POST['top_menu_list'] ) ? (array) $_POST['top_menu_list'] : array();
        
        $new_menu_list = array(); //array define for top menu        

        foreach ($top_menu_list as $list_data_key => $list_data_role) {
            // data sanitization functions used for valid text (text validation)
            $list_data_role = array_map( 'sanitize_text_field', $list_data_role );
            foreach ($list_data_role as $list_data) {
               //we check input will be only number then after we process further. 
               $new_menu_list[$list_data_key][] = $wp_session['all_top_menus'][$list_data]->id;
            }
        }

        //echo '<pre>';print_r($new_menu_list);exit; 
        $remove_top_array = $new_menu_list;
        $json_remove_top_array = json_encode($remove_top_array); //json array.
        
        //update the values
        update_option('hide_menu_bh_plugin',$json_remove_side_array);
        update_option('hide_sub_menu_bh_plugin',$json_remove_sub_side_array);
        update_option('hide_top_menu_bh_plugin',$json_remove_top_array);
        wp_redirect('?page='.$_GET['page'].'&message=save');    

      }//end of wpnonce   
    }//end of administrator check 
    
   } //complete post

   if(isset($_POST['default'])){
      update_option('hide_menu_bh_plugin', '');
      update_option('hide_sub_menu_bh_plugin', '');
      update_option('hide_top_menu_bh_plugin', '');
      wp_redirect('?page='.$_GET['page'].'&message=default');    
   }
?>
<div class="wrap">
<h2>Hide Menus</h2>
<div class="tablenav top">

<br class="clear">
</div>
<?php
//set message after form submit
if(isset($_GET['message'])){

  $msg = '';

  if($_GET['message'] == 'save'){
    $msg =  '<div id="message" class="updated notice notice-success is-dismissible"><p>Your changes has been updated.</div>';
  }

  if($_GET['message'] == 'default'){
    $msg =  '<div id="message" class="updated notice notice-success is-dismissible"><p>Your default setting has been setup.</div>';
  }

  echo $msg;
}

//now we have to fetch all hide_menu_array from the db for side bar
$get_data = get_option('hide_menu_bh_plugin');
if($get_data!='null' && $get_data!='' ){
   $fetch_hide_menu_array = json_decode($get_data);
}
else{
   $fetch_hide_menu_array = array();
}

//now we have to fetch all hide_sub_menu_array from the db for side bar
$get_data = get_option('hide_sub_menu_bh_plugin');
if($get_data!='null' && $get_data!='' ){
   
   $get_data2 = json_decode($get_data);

   foreach ($get_data2 as $role_key => $get_data2_role) {     
     //now we remove the paren key
     foreach ($get_data2_role as $gets_data) {
       $new_get_data = explode('__con__', $gets_data);
       $fetch_hide_sub_menu_array[$role_key][] = $new_get_data['1']; 
     }
   }   

}
else{
   $fetch_hide_sub_menu_array = array();
}

//now we have to fetch all hide_menu_array from the db for top bar
$get_data = get_option('hide_top_menu_bh_plugin');
if($get_data!='null' && $get_data!='' ){
 $fetch_hide_top_menu_array = json_decode($get_data);
}
else{
  $fetch_hide_top_menu_array = array();
}

?>

<form method="post" >

<?php
  //create wpnonce
  wp_nonce_field( 'menu-remove' );
?>
<div class="container">  

  <div class="col-md-6">
    <input name="default" type="submit" class="button button-info button-large" value="Set Default Setting" >
    <br/><br/>
    <i>Hide menu according the role of user. Notes: This plugin will not able to show those menu which are not access by sub role of user according WordPress or other plugin. But it can hide those which was shown by default.</i>    
  </div>

  <div class="col-md-6" align="right">
   <input name="page" type="hidden" value="social-option-2">
   <input name="save" type="submit" class="button button-primary button-large" id="publish" value="Update">
  </div>
</div>

<br/>

<div class="col-md-6">
  <table class='wp-list-table widefat fixed striped posts table-second-td'>
 <tr>
 <th><b>Menu of Side Bar<b/></th>

 <?php
 foreach ($roles as $role) {
   ?>
    <th ><b><?php echo $role['name'] ?><b/></th>
   <?php
 }
 ?> 
 </tr>
  <?php

   $all_menu = $wp_session['all_side_menus'];
   $all_sub_menu = $wp_session['all_side_sub_menus'];
   
  foreach ($all_menu as $key=>$row) { 
   if(isset($row['6']) && $row['6']!=''){
   
    $sub_menu_array = isset($all_sub_menu[$row['2']]) ? $all_sub_menu[$row['2']] : array() ;
    //check it is array or not
    $sub_menu_array = isset( $sub_menu_array ) ? (array) $sub_menu_array : array();
   ?>
    <tr class='my_text'>
     <td class="primary_menu_seprator"><span class="dashicons-before  <?php echo esc_attr($row['6']); ?>"></span> 
      <span><?php echo  esc_attr(strip_tags($row['0'])); ?></span></td>
     <?php foreach ($roles as $role_key=>$role) {  ?>
     <td class="primary_menu_seprator">
     <input
      <?php if(in_array($row['2'], isset($fetch_hide_menu_array->$role_key) ? $fetch_hide_menu_array->$role_key : array() )) echo 'checked'; ?>
      type="checkbox" name="menu_list[<?php echo $role_key ?>][]" value="<?php echo esc_attr($key); ?>">
     </td>  
   <?php } ?>
    </tr>

    <?php
       //now we add the sub menu to parent menu

        foreach ($sub_menu_array as $keys=>$rows) {

          $fetch_hide_sub_menu_array = isset($fetch_hide_sub_menu_array) ?  $fetch_hide_sub_menu_array : array();
          ?>
          
          <tr class='my_text sub-menu'>

             <td ><span>-- </span> 
              <span><?php echo  esc_attr(strip_tags($rows['0'])); ?></span></td>
            <?php foreach ($roles as $role_key=>$role) { ?> 
             <td>
             <input <?php if(in_array($rows['2'], isset($fetch_hide_sub_menu_array[$role_key]) ? $fetch_hide_sub_menu_array[$role_key] : array() )) echo 'checked'; ?>
              type="checkbox" name="sub_menu_list[<?php echo $role_key ?>][]" value="<?php echo $key; ?>_<?php echo esc_attr($keys); ?>">
             </td>  
            <?php } ?>

          </tr>

        <?php
        }//end foreach
    }//if
   }  //for 
?>
</table>
</div>

<br/>
<div class="col-md-6">
   <table class='wp-list-table widefat fixed striped posts table-second-td'>
 <tr>
 <th><b>Menu of Top Bar<b/></th>
 <?php
 foreach ($roles as $role) {
   ?>
    <th ><b><?php echo $role['name'] ?><b/></th>
   <?php
 }
 ?> 
 </tr>
 </tr>
  <?php
   
   $all_menu = $wp_session['all_top_menus'];
   $all_parent_menu = array();
   $all_child_menu = array();
   foreach ($all_menu as $key => $value) {
     if($value->title != '' && $key != 'menu-toggle'){
       if($value->parent!='' && $value->parent!='top-secondary'){
          $all_child_menu[$key] = $value;
       }
       else{
          if($value->parent != 'wp-logo-external'){
            $all_parent_menu[$key] = $value;
          }
       }

     }
   }
   foreach ($all_child_menu as $key => $value) {
     if(isset($all_parent_menu[$value->parent])){
        $all_parent_menu[$value->parent]->child_menu[] = $value;
     }else{
        if($value->parent == 'wp-logo-external'){
          $all_parent_menu['wp-logo']->child_menu[] = $value;
        }
        else if($value->parent == 'user-actions'){
          $value->parent = 'my-account';
          $all_parent_menu['my-account']->child_menu[] = $value;   
        }
        else{
          $all_parent_menu[$value->parent] = $value;   
        }
     }
   }

  foreach ($all_parent_menu as $row) { 

   if($row->title!=''){
   ?>
    <tr class='my_text'>

     <td >
      <span id="wp-admin-bar-<?php echo esc_attr($row->id); ?>">
        <span class="ab-item">
          <span class="ab-icon"></span>
        </span> 
      </span> 
      <span># <?php echo  esc_attr(strip_tags($row->title)); ?></span></td>
     <?php foreach ($roles as $role_key=>$role) { ?>  
     <td>
     <input
      <?php if(in_array($row->id, isset($fetch_hide_top_menu_array->$role_key) ? $fetch_hide_top_menu_array->$role_key : array() )) echo 'checked'; ?>
      type="checkbox" name="top_menu_list[<?php echo $role_key ?>][]" value="<?php echo esc_attr($row->id); ?>">
     </td>  
     <?php } ?>
    </tr>

    <?php
      if(isset($row->child_menu)){
          foreach ($row->child_menu as $child_menu) {
            if($child_menu->title!=''){
              ?>
                <td ><span id="wp-admin-bar-<?php echo esc_attr($child_menu->id); ?>"></span> 
                  <span>-- <?php echo  esc_attr(strip_tags($child_menu->title)); ?></span></td>

                 <?php foreach ($roles as $role_key=>$role) { ?> 
                 <td>
                 <input
                  <?php if(in_array($child_menu->id, isset($fetch_hide_top_menu_array->role_key) ? $fetch_hide_top_menu_array->role_key : array() )) echo 'checked'; ?>
                  type="checkbox" name="top_menu_list[<?php echo $role_key ?>][]" value="<?php echo esc_attr($child_menu->id); ?>">
                 </td>
                 <?php } ?>  
                </tr>
              <?php   
            }//else
          }//foreach
      } //is (child menu isset)
    }//if
   }  //for
?>
</table>
</div>

<br/>

<div class="">
   <input name="page" type="hidden" value="social-option-2">
   <input name="save" type="submit" class="button button-primary button-large" id="publish" value="Update">
</div>

</form>
</div>

<div class="clear"></div>
<!-- donate form -->
<div class="wrap">
  <div id="poststuff">
    <div id="post-body" class="metabox-holder columns-2">
      <div id="postbox-container-1" style="float:left">
        <div  class="postbox">
          <h2 class="hndle"><span>Support the development</span></h2>
          <div class="inside" style="text-align: center">
                  <strong><?php esc_html_e( 'Donate by Paypal', 'hide-admin-menu' ); ?></strong>

                  <br/>
                  <a href="https://www.paypal.me/BThummar/10" target="_blank">
                    <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="<?php esc_html_e( 'Send your donation to the author of', 'hide-admin-menu' ); ?> Hide Admin Menu" style="margin-top:4px;">
                  </a>                  

          </div>
        </div>
      </div>  
    </div>
  </div>
</div>

<!-- dontate form -->

<?php }

//remove menu from the admin at side bar
function bhm_custom_side_menu_page_removing() {
  
  global $wp_session;
  global $menu;
  global $submenu;

  $login_user = wp_get_current_user();
  $login_user_roles = (array) $login_user->roles;

  $wp_session['all_side_menus']  = $menu;
  $wp_session['all_side_sub_menus']  = $submenu;
  
  $all_menu = $wp_session['all_side_menus'];
  //now we have to fetch all hide_menu_array from the db
  $get_data = get_option('hide_menu_bh_plugin'); 
  if($get_data!='null' && $get_data!='' ){
   $fetch_hide_menu_array = json_decode($get_data);
  }
  else{
    $fetch_hide_menu_array = array();
  }

  //now fetch sub menu data
  $get_data = get_option('hide_sub_menu_bh_plugin'); 
  if($get_data!='null' && $get_data!='' ){
   $fetch_hide_sub_menu_array = json_decode($get_data);
  }
  else{
    $fetch_hide_sub_menu_array = array();
  }

  foreach ($fetch_hide_menu_array as $role_key => $hide_menu_array_role) {
    if(in_array($role_key, $login_user_roles)){
      foreach ($hide_menu_array_role as $hide_menu_array) {
         remove_menu_page( $hide_menu_array );
      }
    }
  }

  foreach ($fetch_hide_sub_menu_array as $role_key => $hide_menu_array) {
     //now we ge the parent key and child key
    if(in_array($role_key, $login_user_roles)){
      foreach ($hide_menu_array as $hide_menu_role_array) {
        $pare_child = explode('__con__', $hide_menu_role_array);
        //this is the patch for the wordpress 4 or may be latetest version for the customize menu only
        if($pare_child[0] == 'themes.php'){
          $parse_data = parse_url($pare_child[1]);
          if($parse_data['path'] == 'customize.php'){
              unset($submenu['themes.php'][6]); // Customize
          }
          //echo '<pre>';
        }
        remove_submenu_page($pare_child[0], $pare_child[1] );
      }
    }
  }

}
add_action( 'admin_menu', 'bhm_custom_side_menu_page_removing' ,'9999');

  //remove menu from the admin at top bar
function bhm_custom_top_menu_page_removing() {

  global $wp_session;
  global $wp_admin_bar;

  $login_user = wp_get_current_user();
  $login_user_roles = (array) $login_user->roles;

  $wp_session['all_top_menus']  = $wp_admin_bar->get_nodes();
  
  $all_menu = $wp_session['all_top_menus'];

  //now we have to fetch all hide_menu_array from the db
  $get_data = get_option('hide_top_menu_bh_plugin');
  if($get_data!='null' && $get_data!='' ){
   $fetch_hide_menu_array = json_decode($get_data);
  }
  else{
    $fetch_hide_menu_array = array();
  }

  foreach ($fetch_hide_menu_array as $role_key => $hide_menu_array_role) {
    if(in_array($role_key, $login_user_roles)){
      foreach ($hide_menu_array_role as $hide_menu_array) {
         $wp_admin_bar->remove_node( $hide_menu_array );
      }
    }
  }

}
add_action( 'admin_bar_menu', 'bhm_custom_top_menu_page_removing' ,'9999');

function admin_footer_text ($footer_text) {

  global $wp_session;
  
  $screen = get_current_screen();
  $is_hide_admin_menu = strpos( $screen->id, 'hide-admin-menu' );
  // Check to make sure we're on a hide admin menu admin page.
  if ( FALSE !== $is_hide_admin_menu ) {
      // Change the footer text
      $footer_text = sprintf( __( 'If you like %1$s please leave us a %2$s rating. A huge thanks in advance!', 'hide-admin-menu' ), sprintf( '<strong>%s</strong>', esc_html__( 'Hide Admin Menu', 'hide-admin-menu' ) ), '<a href="https://wordpress.org/support/plugin/hide-admin-menu/reviews?rate=5#new-post" target="_blank" class="is-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'hide-admin-menu' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>' );
  }
  return $footer_text;

}

add_filter('admin_footer_text', 'admin_footer_text', 1 );