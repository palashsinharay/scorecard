<?php
// functions that manage the users.php page in admin and maybe more
class WTPUser {
	function add_custom_column($columns) {	
		$columns['watu_exams'] = __('Exams Data', 'watupro');
	 	return $columns;	
	}
	
	function manage_custom_column($empty='', $column_name, $id) {		
		if( $column_name == 'watu_exams' ) {
			return "<a href='admin.php?page=my_watupro_exams&user_id=$id' target='_blank'>".__('Exams', 'watupro')."</a> |
			<a href='admin.php?page=watupro_my_certificates&user_id=$id' target='_blank'>".__('Certificates', 'watupro')."</a>";
	  }
	  
	  // this is used only from Reporting module
	  if( $column_name == 'exam_reports' ) {
			return "<a href='admin.php?page=watupro_reports&user_id=$id' target='_blank'>".__('View reports', 'watupro')."</a>";
	  }
	}
	
	// checks if user can access exam and outputs the proper strings
	// for now calls can_access() from lib/watupro.php
	static function check_access($exam, $post) {
		WatuPRO::$output_sent = false; // change this var from class method to avoid outputting the generic message
		if(!WatuPRO::can_access($exam)) {
			 // not logged in error
			 if(!is_user_logged_in()) {
		      echo "<p><b>".__('You need to be registered and logged in to take this exam.', 'watupro'). 
		      	" <a href='".site_url()."/wp-login.php?redirect_to=".get_permalink( $post->ID )."'>".__('Log in', 'watupro')."</a>";
		      if(get_option("users_can_register")) {
						echo " ".__('or', 'watupro')." <a href='".site_url()."/wp-login.php?watupro_register=1&action=register&redirect_to=".get_permalink( $post->ID )."'>".__('Register', 'watupro')."</a></b>";        
					}
					echo "</p>";
		   }	
		   else { // logged in but no rights to access
		  	if(!WatuPRO::$output_sent) echo "<p>".__('You are not allowed to access this exam at the moment.', 'watupro')."</p><!-- logged in but no rights to access-->";
		  } 
		  return false;  // can_access returned false  
		}
		
		return true;
	}
}