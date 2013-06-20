<?php
// handles manual actions that the teacher does
class WatuPROITeacherController {
	// edits the points in already taken exam
	static function edit_taking() {
		global $wpdb;
	
		// select this taking
		$taking = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_TAKEN_EXAMS." WHERE ID=%d", $_GET['id']));
		
		$exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_EXAMS." WHERE id=%d", $taking->exam_id));
		
		// select answers in details
		$answers=$wpdb->get_results($wpdb->prepare("SELECT tA.*, tQ.question as question
			FROM ".WATUPRO_STUDENT_ANSWERS." tA JOIN ".WATUPRO_QUESTIONS." tQ ON tQ.id=tA.question_id 
			WHERE taking_id=%d ORDER BY id", $taking->ID));
		
		if(!empty($_POST['ok'])) {
			require_once(WATUPRO_PATH."/i/models/teacher.php");
			WatuPROITeacher::edit_details($exam, $taking, $answers);
			
			// reselect taking and answers?
			watupro_redirect("admin.php?page=watupro_takings&exam_id=".$exam->ID."&msg=Details edited");
		}
		
		// if there is logged in user of this taking, select them
		if(!empty($taking->user_id)) {
			$student = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE ID=%d", $taking->user_id));
			$receiver_email = $student->user_email;
		}
		else $receiver_email = $taking->email;
		
		require(WATUPRO_PATH."/i/views/teacher-edit-details.php");
	}
}