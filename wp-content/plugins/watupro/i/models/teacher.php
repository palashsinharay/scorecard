<?php
// class to handle manual grading of exams
class WatuPROITeacher {
	 // saves the grading details
	 // probably send email to student with the results
	 static function edit_details($exam, $taking, $answers) {
	 		global $wpdb;
	 		
	 		// update each answer
	 		$total_points = $total_answers = $correct_answers = $percent_correct = 0;
	 		foreach($answers as $answer) {
				 $wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_STUDENT_ANSWERS." SET
				 	points=%s, is_correct=%d, teacher_comments = %s WHERE id=%d", 
				 		$_POST['points'.$answer->ID], $_POST['is_correct'.$answer->ID], 
				 		$_POST['teacher_comments'.$answer->ID], $answer->ID));
				 	$total_points += $_POST['points'.$answer->ID];
				 	$total_answers++;
				 	if($_POST['is_correct'.$answer->ID]) $correct_answers++;
	 		}
	 		
	 		// now recalculate percent correct
	 		if($total_answers==0) $percent_correct=0;
			else $percent_correct = number_format($correct_answers / $total_answers * 100, 2);
			
			require_once(WATUPRO_PATH.'/models/grade.php');
			list($grade, $certificate_id, $do_redirect, $grade_obj) 
				= WTPGrade::calculate($exam->ID, $total_points, $percent_correct);
				
			// update taking details	
			$_POST['teacher_comments']=''; // for now empty
			$wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_TAKEN_EXAMS." SET
				points=%d, result=%s, grade_id=%d, percent_correct=%d, teacher_comments=%s
				WHERE id=%d",
				$total_points, $grade, $grade_obj->ID, $percent_correct, 
				$_POST['teacher_comments'], $taking->ID));
				
			// add student certificate
			if($taking->user_id) $certificate = WatuPROCertificate::assign($exam, $taking->ID, $certificate_id, $taking->user_id);
			
			// send email to the user
			if(!empty($_POST['send_email'])) {
				 $subject = stripslashes($_POST['subject']);
				 $message = stripslashes($_POST['message']);
				 
				 // replace vars
				 $subject = str_replace("%%QUIZ_NAME%%", $exam->name, $subject);
				 $message = str_replace("%%QUIZ_NAME%%", $exam->name, $message);
				 
				 // replace other vars from final screen
				 $message = str_replace("%%CORRECT%%", $correct_answers, $message);			 
				 $message = str_replace("%%TOTAL%%", $total_answers, $message);
				 $message = str_replace("%%POINTS%%", $total_points, $message);
				 $message = str_replace("%%PERCENTAGE%%", $percent_correct, $message);
				 $message = str_replace("%%GRADE%%", $grade, $message);
				 $message = str_replace("%%GTITLE%%", $grade_obj->gtitle, $message);
				 $message = str_replace("%%GDESC%%", $grade_obj->gdescription, $message);
				 $message = str_replace("%%CERTIFICATE%%", $certificate, $message);
				 
				 if(strstr($message, "%%ANSWERS%%")) {
				 		// prepare answers table
				 		$answers_table = "<table border='1' cellpadding='4'><tr><th>".__('Question', 'watupro')."</th><th>".
				 			__('Answer(s) given', 'watupro')."</th><th>".__('Points', 'watupro').
				 			"</th><th>".__('Is Correct?', 'watupro')."</th><th>".__('Comments', 'watupro')."</th></tr>";
				 			
						foreach($answers as $answer) {
							 $answers_table.= "<tr><td>".wpautop(stripslashes($answer->question))."</td><td>".
							 	wpautop(stripslashes($answer->answer))."</td><td>".$_POST['points'.$answer->ID].
							 	"</td><td>".($_POST['is_correct'.$answer->ID]?__('yes', 'watupro'):__('no','watupro'))."</td><td>".
							 	wpautop(stripslashes($_POST['teacher_comments'.$answer->ID]))."</td></tr>";
						}				 			
				 			
				 		$answers_table.="</table>";	
				 		
				 		$message = str_replace("%%ANSWERS%%", $answers_table, $message);
				 }
				 
				 // now do send
				 $headers  = 'MIME-Version: 1.0' . "\r\n";
				 $headers .= 'Content-type: text/html; charset=utf8' . "\r\n";
				 $message = apply_filters('watupro_content', stripslashes($message));
				 		
				 $output='<html><head><title>'.$subject.'</title>
				 </head>	<html><body>'.$message.'</body></html>';		
				 wp_mail($_POST['email'], $subject, $output, $headers);
				 
				 // update options to reuse subjetc & message next time
				 update_option('watupro_manual_grade_subject', $_POST['subject']);
				 update_option('watupro_manual_grade_message', $_POST['message']);
				 
			} // end sending mail
	 }
}