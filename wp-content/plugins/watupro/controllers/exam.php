<?php
// add/edit exam
function watupro_exam() {
	global $wpdb;
	require_once(WATUPRO_PATH."/models/exam.php");
	
	if(!empty($_POST['copy_exam'])) {		
		try {
			$copy_to=($_POST['copy_option']=='new')?0:$_POST['copy_to'];
			WTPExam::copy($_GET['quiz'], $copy_to);
			$_SESSION['flash'] =__("The exam was successfully copied!", 'watupro');
			watupro_redirect("admin.php?page=watupro_exams");
		}
		catch(Exception $e) {
			$error=$e->getMessage();
		}	 
	}

	if(isset($_REQUEST['submit'])) {
		if($_REQUEST['action'] == 'edit') { //Update goes here
			$exam_id = $_REQUEST['quiz'];
			if(empty($_POST['use_different_email_output'])) $_POST['email_output']='';
			WTPExam::edit($_POST, $exam_id);
			$wp_redirect = admin_url('admin.php?page=watupro_exams&message=updated');	
		} else {
			// add new exam
			$exam_id=WTPExam::add($_POST);
			
			if($exam_id == 0 ) $wp_redirect = admin_url('admin.php?page=watupro_exams&message=fail');
			$wp_redirect = admin_url('admin.php?page=watupro_questions&message=new_quiz&quiz='.$exam_id);
		}
		
    echo "<meta http-equiv='refresh' content='0;url=$wp_redirect' />"; 
    exit;
	}
	
	$action = 'new';
	if($_REQUEST['action'] == 'edit') $action = 'edit';
	
	// global answer_display
	$answer_display=get_option('watupro_show_answers');
	// global single page display
	$single_page=get_option('watupro_single_page');
	
	$dquiz = array();
	$grades = array();
	
	if($action == 'edit') {
		$dquiz = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_EXAMS." WHERE ID=%d", $_REQUEST['quiz']));
		$grades = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".WATUPRO_GRADES." WHERE  exam_id=%d order by ID ", $_REQUEST['quiz']) );
		$final_screen = stripslashes($dquiz->final_screen);
		$schedule_from = $dquiz->schedule_from;
		$schedule_to = $dquiz->schedule_to;
	} else {
		$final_screen = __("<p>You have completed %%QUIZ_NAME%%.</p>\n\n<p>You scored %%SCORE%% correct out of %%TOTAL%% questions.</p>\n\n<p>You have collected %%POINTS%% points.</p>\n\n<p>Your obtained grade is <b>%%GRADE%%</b></p>", 'watupro');
		$schedule_from = date("Y-m-d");
		$schedule_to = date("Y-m-d");
	}
	
	// select certificates if any
	$certificates=$wpdb->get_results("SELECT * FROM ".WATUPRO_CERTIFICATES." ORDER BY title");
	$cnt_certificates=sizeof($certificates);
	
	// categories if any
	$cats=$wpdb->get_results("SELECT * FROM ".WATUPRO_CATS." ORDER BY name");
	
	// select other exams
	$other_exams=$wpdb->get_results("SELECT * FROM ".WATUPRO_EXAMS." WHERE ID!='{$dquiz->ID}' ORDER BY name");
	
	if(watupro_intel()) {
		require_once(WATUPRO_PATH."/i/models/dependency.php");
		$dependencies = WatuPRODependency::select($dquiz->ID);	
	}
	
	// check if recaptcha keys are in place
	$recaptcha_public = get_option('watupro_recaptcha_public');
	$recaptcha_private = get_option('watupro_recaptcha_private');
	
	require(WATUPRO_PATH."/views/exam_form.php");
}

// list exams
function watupro_exams() {
	global $wpdb;

	if($_REQUEST['action'] == 'delete') {
		$wpdb->get_results("DELETE FROM {$wpdb->prefix}watupro_master WHERE ID='$_REQUEST[quiz]'");
		$wpdb->get_results("DELETE FROM {$wpdb->prefix}watupro_answer WHERE question_id IN (SELECT ID FROM {$wpdb->prefix}watupro_question WHERE exam_id='$_REQUEST[quiz]')");
		$wpdb->get_results("DELETE FROM {$wpdb->prefix}watupro_question WHERE exam_id='$_REQUEST[quiz]'");		
	}
	
	$exams = $wpdb->get_results("SELECT Q.*, tC.name as cat,
	(SELECT COUNT(*) FROM {$wpdb->prefix}watupro_question WHERE exam_id=Q.ID) AS question_count,
	(SELECT COUNT(*) FROM {$wpdb->prefix}watupro_taken_exams WHERE exam_id=Q.ID AND in_progress=0) AS taken
	FROM ".WATUPRO_EXAMS." AS Q LEFT JOIN {$wpdb->prefix}watupro_cats as tC ON tC.id=Q.cat_id");
	
	// now select all posts that have watupro shortcode in them
	$posts=$wpdb->get_results("SELECT * FROM {$wpdb->posts} 
		WHERE post_content LIKE '%[WATUPRO %]%' 
		AND (post_type='post' OR post_type='page') 
		AND (post_status='publish' OR post_status='private')
		AND post_title!=''
		ORDER BY post_date DESC");	
		
	// match posts to exams
	foreach($exams as $cnt=>$exam) {
		foreach($posts as $post) {
			if(strstr($post->post_content,"[WATUPRO ".$exam->ID."]")) {
				$exams[$cnt]->post=$post;			
				break;
			}
		}
	}
	
	require(WATUPRO_PATH."/views/exams.php");
}