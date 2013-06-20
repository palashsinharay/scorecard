<?php
class WatuPROIExam {
	// update extra fields when adding and saving
	static function extra_fields($exam_id, $vars) {
		 global $wpdb;
		 $wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_EXAMS." SET retake_after=%d", $vars['retake_after']));
	}
	
	// check extra limitations for resubmitting the exam
	static function can_retake($exam) {		
		if($exam->retake_after == 0) return true;
				
		global $wpdb, $user_ID;
		
		// see if the latest attempt is "too recent"
		$recent_attempt = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_TAKEN_EXAMS." 
			WHERE exam_id=%d AND user_id=%d AND end_time > NOW() - INTERVAL %d HOUR", $exam->ID, $user_ID, $exam->retake_after));
					
		if(!empty($recent_attempt->ID)) {
			// if ratake_after is > 100, let's round to days
			$time = $exam->retake_after>100 ? __('days', 'watupro'):__('hours', 'watupro');
			$retake_after_time = $exam->retake_after>100 ? round($exam->retake_after / 24) : $exam->retake_after;			
			
			
			printf(__("You need to wait at least %d $time after your previous attempt on this test.", 'watupro'), $retake_after_time);
			return false;
		}			
			
		return true;	
	}
}