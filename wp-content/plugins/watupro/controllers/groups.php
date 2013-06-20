<?php
// user groups
function watupro_groups() {
	global $wpdb;
	$groups_table=WATUPRO_GROUPS;
	
	if(!empty($_POST['roles_to_groups'])) {
		update_option('watupro_use_wp_roles', $_POST['use_wp_roles']);
	}		
		
	if(!empty($_POST['add'])) {
		$wpdb->query($wpdb->prepare("INSERT INTO $groups_table (name, is_def)
			VALUES (%s, %d)", $_POST['name'], $_POST['is_def']));
	}
	
	if(!empty($_POST['save']))
	{
		$wpdb->query($wpdb->prepare("UPDATE $groups_table SET
			name=%s, is_def=%d WHERE ID=%d", $_POST['name'], $_POST['is_def'], $_POST['id']));
	}
	
	if(!empty($_POST['del']))
	{
		$wpdb->query($wpdb->prepare("DELETE FROM $groups_table WHERE ID=%d",$_POST['id']));
	}
	
	// select current groups
	$groups=$wpdb->get_results("SELECT * FROM $groups_table ORDER BY name");
	
	$use_wp_roles = get_option('watupro_use_wp_roles');	
	
	require(WATUPRO_PATH."/views/groups.php");
}

// registers the default groups for everyone, not just for students
// this is required because admin may want to allow other roles also take exams	
function watupro_register_group($user_id)
{
	global $wpdb;
	$groups_table=$wpdb->prefix."watupro_groups";		
	
	// any default groups?
	$groups=$wpdb->get_results("SELECT * FROM $groups_table WHERE is_def=1");
	$gids=array();
	foreach($groups as $group) $gids[]=$group->ID;
	
	update_user_meta($user_id, "watupro_groups", $gids);
}


// user profile custom fields functions
// http://wordpress.stackexchange.com/questions/4028/how-to-add-custom-form-fields-to-the-user-profile-page#4029
function watupro_user_fields($user)
{
	global $wpdb;

    if(!current_user_can(WATUPRO_MANAGE_CAPS)) return false;

	$groups_table=$wpdb->prefix."watupro_groups";		
	
	$groups=$wpdb->get_results("SELECT * FROM $groups_table ORDER BY name");
	
	$user_groups=get_user_meta($user->ID, "watupro_groups", true);
	?>
	<h3><?php _e("Watu PRO Fields", 'watupro'); ?></h3>
  <table class="form-table">
    <tr>
      <th><label for="phone"><?php _e("User Groups", 'watupro'); ?></label></th>
      <td>
      	<select name="watupro_groups[]" multiple="multiple" size="4">
      	<option>-------------------</option>
      	<?php foreach($groups as $group):
      	if(@in_array($group->ID, $user_groups)) $selected="selected";
      	else $selected="";?>
      		<option value="<?php echo $group->ID?>" <?php echo $selected;?>><?php echo $group->name?></option>
      	<?php endforeach;?>
      	</select> 
    </td>
    </tr>
  </table>
	<?php
}

function watupro_save_extra_user_fields($user_id)
{
  $saved = false;  
  if ( current_user_can( WATUPRO_MANAGE_CAPS ) ) {
    update_user_meta( $user_id, 'watupro_groups', $_POST['watupro_groups'] );
    $saved = true;
  }
  return true;
}