<?php
// exam categories
function watupro_cats() {	
	global $wpdb, $wp_roles;
	$cats_table=WATUPRO_CATS;	
	$groups_table=WATUPRO_GROUPS;
	
	// are we using WP Roles or Watupro groups
	$use_wp_roles = get_option('watupro_use_wp_roles');
	
	// select all groups
	if(!$use_wp_roles) $groups=$wpdb->get_results("SELECT * FROM $groups_table ORDER BY name");
	else $roles = $wp_roles->roles;		
	
	switch(@$_GET['do']) {
		case 'add':
			if(!empty($_POST['ok'])) {
				$wpdb->query($wpdb->prepare("INSERT INTO $cats_table (name, ugroups)
					VALUES (%s, %s)", $_POST['name'], "|".@implode("|",$_POST['ugroups'])."|"));
				echo "<meta http-equiv='refresh' content='0;url=admin.php?page=watupro_cats' />"; 
				exit;
			}
		
			require(WATUPRO_PATH.'/views/cat.php');   
		break;
	
		case 'edit':
			if(!empty($_POST['del'])) {
	           $wpdb->query($wpdb->prepare("DELETE FROM $cats_table WHERE ID=%d", $_GET['id']));
	
				echo "<meta http-equiv='refresh' content='0;url=admin.php?page=watupro_cats' />"; 
				exit;
			}
			
			if(!empty($_POST['ok'])) {
				$wpdb->query($wpdb->prepare("UPDATE $cats_table SET
					name=%s, ugroups=%s WHERE ID=%d", $_POST['name'], "|".@implode("|",$_POST['ugroups'])."|", $_GET['id']));
				echo "<meta http-equiv='refresh' content='0;url=admin.php?page=watupro_cats' />"; 
				exit;
			}
	
			$cat=$wpdb->get_row($wpdb->prepare("SELECT * FROM $cats_table WHERE ID=%d", $_GET['id']));
				
			require(WATUPRO_PATH. '/views/cat.php');   
		break;
	
		default:
			// select my cats
			$cats=$wpdb->get_results("SELECT * FROM $cats_table ORDER BY name");
			
			require(WATUPRO_PATH. '/views/cats.php');   
		break;
	}
}