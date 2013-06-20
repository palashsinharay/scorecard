<?php
// object to handle "takings" - stored records of taken exams
class WTPRecord {
	 function export($takings, $exam) {
			global $wpdb;	 	
	 	
			$newline=watupro_define_newline();
			$rows=array();
			
			if(empty($_GET['details'])) $rows[]=__("Username;Email;IP;Date;Points;Grade", 'watupro');
			else {
				// exports with questions and answers
				$questions = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_question
					WHERE exam_id=%d ORDER BY ID", $exam->ID));
					
					$titlerow = __("Username;Email;IP;Date;", 'watupro');
					foreach($questions as $question) {
						 // strip tags and remove semicolon to protect the CSV sanity
						 $question_txt = strip_tags(str_replace(";",",",$question->question));
						 $question_txt = str_replace("\n", " ", $question_txt);
						 $question_txt = str_replace("\r", " ", $question_txt);
						 $titlerow .= $question_txt.";";
					}
					$titlerow .= __("Points;Grade", 'watupro');		
					$rows[] = $titlerow;		
					
					// we also have to get full details so they can be matched below
					$details = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_student_answers
						WHERE exam_id=%d", $exam->ID));	
			}
			
			foreach($takings as $taking) {
				$taking_email = ($taking->user_id)?$taking->user_email:$taking->email;
				$row = ($taking->user_id?$taking->display_name:"N/A").";".
					($taking_email?$taking_email:"N/A").";".
					$taking->ip.";".date(get_option('date_format'), strtotime($taking->date)).";";
					
			  if(!empty($_GET['details'])) {
			  	 foreach($questions as $question) {
			  	 		$answer = "";
			  	 		foreach($details as $detail) {
			  	 			 if($detail->taking_id==$taking->ID and $detail->question_id==$question->ID) {
			  	 			 		$answer = strip_tags(str_replace(";",",",$detail->answer));
			  	 			 		$answer = str_replace("\n", " ", $answer);
			  	 			 		$answer = str_replace("\r", " ", $answer);
			  	 			 } 
							}
							$row .= $answer.";";
			  	 }
			  }					
					
				$row .=	$taking->points.";".$taking->result;
					
				$rows[] = $row;	
			}
			
			$csv=implode($newline,$rows);
			
			// credit to http://yoast.com/wordpress/users-to-csv/	
			$now = gmdate('D, d M Y H:i:s') . ' GMT';
		
			header('Content-Type: ' . watupro_get_mime_type());
			header('Expires: ' . $now);
			header('Content-Disposition: attachment; filename="exam-'.$exam->ID.'.csv"');
			header('Pragma: no-cache');
			echo $csv;
			exit;
	 }
}