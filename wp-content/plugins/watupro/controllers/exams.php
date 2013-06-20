<?php
function watupro_my_exams() {
	global $wpdb, $user_ID;	
	
	// admin can see this for every student
	if(!empty($_GET['user_id']) and current_user_can(WATUPRO_MANAGE_CAPS)) $user_id = $_GET['user_id'];
	else $user_id = $user_ID;
		
	$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE ID=%d", $user_id));
	
	// this page is visible for students too
	require_once(WATUPRO_PATH."/models/exam.php");
	
	// select what categories I have access to 
	get_currentuserinfo();
	$cat_ids = WTPCategory::user_cats($user_id);
	
	$cat_id_sql=implode(",",$cat_ids);
	
	list($my_exams, $takings, $num_taken) = WTPExam::my_exams($user_id, $cat_id_sql);
	
	// intelligence dependencies	
	if(watupro_intel()) {
		require_once(WATUPRO_PATH."/i/models/dependency.php");
		$my_exams = WatuPRODependency::mark($my_exams, $takings);	
	}
	
	$num_to_take=sizeof($my_exams)-$num_taken;
	
	wp_enqueue_script('thickbox',null,array('jquery'));
	wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');
	wp_enqueue_style('style.css', plugins_url().'/watupro/style.css', null, '1.0');
	
	if(@file_exists(TEMPLATEPATH.'/watupro/my_exams.php')) require TEMPLATEPATH.'/watupro/my_exams.php';
	else require WATUPRO_PATH."/views/my_exams.php";   
}