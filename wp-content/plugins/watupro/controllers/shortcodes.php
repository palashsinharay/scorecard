<?php
/**
 * This will scan all the content pages that wordpress outputs for our special code. If the code is found, it will replace the requested quiz.
 */
function watupro_shortcode( $attr ) {
	global $wpdb;
	$exam_id = $attr[0];

	$contents = '';
	if(is_numeric($exam_id)) { // Basic validiation - more on the show_exam.php file.
		ob_start();
		
	// select exam
	$exam=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_master WHERE id=%d", $exam_id));				
		
		if($exam->mode=='practice' and watupro_intel()) WatuPracticeController::show($exam);
		else include(WATUPRO_PATH . '/show_exam.php');
		$contents = ob_get_contents();
		ob_end_clean();
	}
	

	$contents = apply_filters('watupro_content', $contents);
	
	return $contents;
}

// shortcodes to list exams 
function watupro_listcode($attr) {
	$cat_id = $attr[0];
	
	require_once(WATUPRO_PATH."/models/exam.php");
	
	$content = WTPExam::show_list($cat_id);
	
	return $content;	
}

// outputs my exams page in any post or page
function watupro_myexams_code($attr) {
	$content = '';
	if(!is_user_logged_in()) return __('This content is only for logged in users', 'watupro');
	
	ob_start();
	watupro_my_exams();
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}

// outputs my certificates in any post or page
function watupro_mycertificates_code($attr) {
	$content = '';
	if(!is_user_logged_in()) return __('This content is only for logged in users', 'watupro');
	
	ob_start();
	watupro_my_certificates();
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}

// outputs generic leaderboard from all tests
function watupro_leaderboard($attr) {
	global $wpdb;
	
	$num = $attr[0]; // number of users to show
	if(empty($num) or !is_numeric($num)) $num = 10;
	
	// now select them ordered by total points
	$users = $wpdb -> get_results("SELECT SUM(tT.points) as points, tU.user_login as user_login 
		FROM {$wpdb->users} tU JOIN ".WATUPRO_TAKEN_EXAMS." tT ON tT.user_id = tU.ID
		WHERE tT.in_progress = 0 GROUP BY tU.ID ORDER BY points DESC LIMIT $num");
	
	$table = "<table class='watupro-leaderboard'><tr><th>".__('User', 'watupro')."</th><th>".__("Points", 'watupro')."</th></tr>";
	
	foreach($users as $user) {
		$table .= "<tr><td>".$user->user_login."</td><td>".$user->points."</td></tr>";
	}
	
	$table .= "</table>";
	
	return $table;
}

function watupro_remove_filters() {
	// remove filters from improperly written plugins  
	remove_filter( 'the_content', 'pmpro_membership_content_filter', 5 );
}