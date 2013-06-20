<?php
// Initial setup for ajax.
if(isset($_REQUEST['action']) and $_REQUEST['action']=='watupro_submit' ) $exam_id = $_REQUEST['quiz_id'];

$_question = new WTPQuestion();
global $wpdb, $post, $user_ID;

// select exam
$exam=$wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_EXAMS." WHERE id=%d", $exam_id));

// in progress taking of this exam?
$in_progress = null;
$exam->full_time_limit = $exam->time_limit; // store this for the verify_timer calculations
if(is_user_logged_in()) {
	$in_progress=$wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_TAKEN_EXAMS." 
		WHERE user_id=%d AND exam_id=%d AND in_progress=1 ORDER BY ID DESC LIMIT 1", $user_ID, $exam_id));
		
	if($exam->time_limit > 0 and !empty($in_progress->ID)) {		
		// recalculate time limit
		$start_time = watupro_mktime($in_progress->start_time);
		$limit_in_seconds = intval($exam->time_limit*60);
		$time_elapsed = time() - $start_time;	
		$new_limit_seconds = $limit_in_seconds - $time_elapsed;
		// echo $new_limit_seconds;
		if($new_limit_seconds < 0) {		
			unset($in_progress); // unset this so we will submit empty the results 	
			$exam->time_limit = 0.006;
			$timer_warning = __("Warning: your unfinished attempt was recorded. You ran out of time and your answers will be submitted automatically.", 'watupro');	
		}		 	
		else {			
			$exam->time_limit = round($new_limit_seconds/60, 1);
			$timer_warning = __("Warning: you have started this test earlier and the timer is running behind the scene!", 'watupro');
		}
	}	
}

$GLOBALS['wpframe_plugin_name'] = basename(dirname(__FILE__));

if(!WTPUser::check_access($exam, $post)) return false;

// is scheduled?
if($exam->is_scheduled==1) {	 
    $now= time();
    $schedule_from = strtotime($exam->schedule_from);
    $schedule_to = strtotime($exam->schedule_to);
    if ($now < $schedule_from or $now > $schedule_to) {
        printf(__('This test will be available between %s and %s.', 'watupro'), date(get_option('date_format').' '.get_option('time_format'), $schedule_from), date(get_option('date_format').' '.get_option('time_format'), $schedule_to));
        if(current_user_can(WATUPRO_MANAGE_CAPS)) echo ' '.__('You can still see it only because you are administrator or manager.', 'watupro').' ';
        else return false; // students can't take this test
    }
}

// logged in or login not required here		
$_watu=new WatuPRO();    
  
// re-taking allowed?       
$ok=$_watu->can_retake($exam);
 
// check time limits on submit
if($ok and $exam->time_limit>0 and !empty($_REQUEST['action'])) {
	$ok=$_watu->verify_time_limit($exam, $in_progress);
	if(!$ok) { 
		echo "<p><b>".__("Time limit exceeded! We cannot accept your results.", 'watupro')."</b></p>";
		if(!empty($in_progress['id'])) $wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_TAKEN_EXAMS." 
			SET in_progress=0 WHERE id=%d", $in_progress['id']));
	}
	
	// $ok, so clear the time limit for the future takings
	update_user_meta( $user_ID, "start_exam_".$exam->ID, 0);
}
            
if(!$ok) return false;

if(!is_singular() and !empty($GLOBALS['watupro_client_includes_loaded'])) { #If this is in the listing page - and a quiz is already shown, don't show another.
	printf(__("Please go to <a href='%s'>%s</a> to view this test", 'watupro'), get_permalink(), get_the_title());
	return false;
} 
            
// now select and display questions			
$answer_display = $exam->show_answers==""?get_option('watupro_show_answers'):$exam->show_answers;			

// when submitting the exam form we don't have to do this unless session is empty for some reason					
if(empty($_REQUEST['action'])) {
	$all_question = WTPQuestion::select_all($exam);
	
	// regroup by cats?
	$all_question=$_watu->group_by_cat($all_question, $exam);	
 		
	// now match answers to non-textarea questions
	$_watu->match_answers($all_question, $exam);				
}    					
else { 
	$all_question=watupro_unserialize_questions($_REQUEST['watupro_questions']);			
}			

// get required question ids as string
$rids=array(0);
foreach($all_question as $q)  {
	if($q->is_required) $rids[]=$q->ID;
}
$required_ids_str=implode(",",$rids);

// requires captcha?
if($exam->require_captcha) {
	$recaptcha_public = get_option("watupro_recaptcha_public");
	$recaptcha_private = get_option("watupro_recaptcha_private");
	if(!function_exists('recaptcha_get_html')) {
		 require(WATUPRO_PATH."/lib/recaptcha/recaptchalib.php");					 
	}
	$recaptcha_style = $exam->single_page==1?"":"style='display:none;'";
	$recaptcha_html = "<div id='WTPReCaptcha' $recaptcha_style><p>".recaptcha_get_html($recaptcha_public)."</p></div>";
	
	// check captcha
	if(!empty($_POST['action'])) {
		$resp = recaptcha_check_answer ($recaptcha_private,
                          $_SERVER["REMOTE_ADDR"],
                          $_POST["recaptcha_challenge_field"],
                          $_POST["recaptcha_response_field"]);
      if (!$resp->is_valid) die('WATUPRO_CAPTCHA:::'.__('Invalid image validation code', 'watupro'));			
	}
} // end recaptcha code
			
if($all_question) {
	$GLOBALS['watupro_client_includes_loaded'] = true;
			
	if(empty($_REQUEST['action'])) {
		// show we hide the submit button? by default yes which means $submit_button_style is empty
		$submit_button_style = '';
		if(($exam->single_page == 0 and !$exam->submit_always_visible and sizeof($all_question)>1)
			or $exam->single_page == 2) $submit_button_style="style='display:none;'";
		
		require(WATUPRO_PATH.'/views/show_exam.php');
	}
	else require(WATUPRO_PATH.'/controllers/submit_exam.php'); 
}  // end if $all_question 