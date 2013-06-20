<?php
class WTPCategory {
	// discovers ID of a category. If not found, creates the category
	// receives the array of all cats to avoid multiple queries because this function is used on import
	static function discover($name, $cats) {
		global $wpdb;
		
		if(empty($name)) return 0;
		
		foreach($cats as $cat) {
			if($cat->name==$name) return $cat->ID;
		}
		
		// create
		$wpdb->query($wpdb->prepare("INSERT INTO ".WATUPRO_QCATS." (name, description) VALUES (%s, %s)", $name, ''));
		return $wpdb->insert_id;
	}
	
	// what categories does this user have access to
	static function user_cats($uid) {
		global $wpdb;		
		
		$cat_ids=array(0); // Uncategorized are always in
		$user_groups=get_user_meta($uid, "watupro_groups", true);
		$cats=$wpdb->get_results("SELECT * FROM ".WATUPRO_CATS);

		$use_wp_roles = get_option('watupro_use_wp_roles');
		
		foreach($cats as $cat) {
			if($cat->ugroups=="||" or empty($cat->ugroups)) {
				$cat_ids[]=$cat->ID;
				continue;
			}			
			
			if($use_wp_roles) {
				$allowed_roles = explode("|", $cat->ugroups);
				foreach($allowed_roles as $role) {
					if(empty($role)) continue;
					if(current_user_can($role)) {
						$cat_ids[]=$cat->ID;
						break;
					}
				}  // end foreach role 
			} // end if using WP roles
			else { // using user groups
			  if(sizeof($user_groups)>0 and is_array($user_groups)) {
				  foreach($user_groups as $g) {
					  if(strstr($cat->ugroups, "|".$g."|")) {
						  $cat_ids[]=$cat->ID;
					  } // end if
				  } // end foreach group
			  } // end if there are any groups
			} // end if using user groups
		} // end foreach cats
		
		return $cat_ids;
	}
	
	// add question category, no duplicates
	static function add($name, $description='') {
		global $wpdb;
		
		// already exists?
		$exists = $wpdb->get_var($wpdb->prepare("SELECT ID FROM ".WATUPRO_QCATS." WHERE name=%s", $name));		
		if($exists) return false;
		
		$wpdb -> query( $wpdb -> prepare(" INSERT INTO ".WATUPRO_QCATS." SET name=%s, description=%s", 
			$name, $description) );		
		return $wpdb->insert_id;
	}
	
	// save category, no duplicates
	static function save($name, $id, $description='') {
		global $wpdb;
		
		// another one with this name already exists?
		$exists = $wpdb->get_var($wpdb->prepare("SELECT ID FROM ".WATUPRO_QCATS." 
			WHERE name=%s AND ID!=%d", $name, $id));		
		if($exists) return false;
		
		$wpdb -> query( $wpdb -> prepare(" UPDATE ".WATUPRO_QCATS." SET name=%s, description=%s 
		WHERE ID=%d", $name, $description, $id) );	
		
		return true;
	}
	
	// delete
	static function delete($id) {
		global $wpdb;
		
		$wpdb -> query( $wpdb->prepare("DELETE FROM ".WATUPRO_QCATS." WHERE id=%d", $id) );
	}	
	
	// user group checks - can user access this exam based on category/user group restrictions
	static function has_access($exam) {
		 global $wpdb, $user_ID;
		
    	 if(!$exam->cat_id) return true; // uncategorized exams are not restricted further
    	     	 
    	 // select exam category
    	 $cat=$wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_CATS." WHERE id=%d", $exam->cat_id));    	 
    	 if(empty($cat->ugroups) or $cat->ugroups=="||") return true;
    	 
    	 // restricted to certain groups
    	 $cat_groups=explode("|",$cat->ugroups);
    	 
    	 $use_wp_roles = get_option('watupro_use_wp_roles');    	 
    	 if($use_wp_roles) {    	 	
    	 	 $roles = $cat_groups;
			 foreach($roles as $role) {
			 	if(empty($role)) continue;
			 	if(current_user_can($role)) return true;
			 } // end foreach role
			 echo "<!-- WATUPROCOMMENT user role has no access to this category -->";
    	 } // end if using WP roles
    	 else {    	 	
	    	 $user_groups=get_user_meta($user_ID, "watupro_groups", true);    	
			 
			 if(!is_array($user_groups)) {
			 	echo "<!-- WATUPROCOMMENT not in any user groups -->";
			 	return false;
			 } // end if
			 
			 foreach($user_groups as $group) {
			 	if(empty($group)) continue;
			 	if(in_array($group, $cat_groups)) return true;
			 }  // end foreach group 
		} // end if using user groups
		
		return false;  	
	} // end has_access
}