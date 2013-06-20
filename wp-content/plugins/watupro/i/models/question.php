<?php
// Intelligence specific question queries
class WatuPROIQuestion {
	static function edit($vars, $id) {
		 global $wpdb;
		 
		 if(empty($vars['correct_gap_points'])) $vars['correct_gap_points'] = 0;
		 if(empty($vars['incorrect_gap_points'])) $vars['incorrect_gap_points'] = 0;
		 
	   $sql = $wpdb->prepare("UPDATE {$wpdb->prefix}watupro_question SET 
	   	correct_gap_points = %s, incorrect_gap_points=%s
	   	WHERE ID = %d", $vars['correct_gap_points'], $vars['incorrect_gap_points'], $id);
	   $wpdb->query($sql);	
	}
	
	// display a question like fill the gaps etc
	static function display($question, $qct, $question_count, $inprogress_details) {
			
			switch($question->answer_type) {
				case 'gaps':
					// parse {{{xxxx}}} into input fields - pattern {{{([^}}}])*}}}
					$html = $question->question;
					$matches = array();
					preg_match_all("/{{{([^}}}])*}}}/", $html, $matches);
					
					foreach($matches[0] as $cnt=>$match) {
						 $value = ""; // inprogress value
						 if(!empty($inprogress_details[$question->ID][$cnt])) $value = $inprogress_details[$question->ID][$cnt];						
						
						 $cnt++;
						 $input = '<input type="text" size="10" name="gap_'.$question->ID.'_'
						 	.$cnt.'" class="answer answerof-'.$question->ID.'" value="'.$value.'">';
						 $html = str_replace($match, $input, $html);
					}					
					
					echo wpautop(stripslashes("<span class='watupro_num'>$qct. </span>".$html),0);
				break;				
			}
	}
	
	// processes specific types of questions (like gaps) on submit
	static function process($question, $user_answers) {		
		global $wpdb;
		
		$html = stripslashes($question->question);
		$matches = array();
		preg_match_all("/{{{([^}}}])*}}}/", $html, $matches);
		$points = 0;	
		$max_points = sizeof($matches[0]) * $question->correct_gap_points;	
		
		foreach($matches[0] as $cnt=>$match) {
			$user_answer = $user_answers[$cnt];			
			// compare to know if it's correct or not
			if(strcasecmp("{{{".trim($user_answer)."}}}", trim($match)) == 0) {
				 $img='<img src="'.plugins_url("watupro").'/correct.png" hspace="5">';
				 $points += $question->correct_gap_points;
			}	 
			else {
				$img='<img src="'.plugins_url("watupro").'/wrong.png" hspace="5">';
				if(empty($user_answer)) $user_answer = __('[no answer]', 'watupro');
				$points += $question->incorrect_gap_points;
			}
			
			$html = str_replace($match, '<font color="blue"><b>'.$user_answer.'</b></font>&nbsp;'.$img, $html);
		}		
		$html = wpautop(stripslashes($html), 0);	
		return array($points, "<li>".$html."</li>", $max_points);	
	}
	
	// displays option to reuse questions from another quiz
	static function reuse_questions($exam, &$intelligence_display) {
		global $wpdb;
		
		if(!empty($_POST['ok'])) {
			// when the checkbox is unchecked, vanish the dropdown selection
			if(empty($_POST['reuse_questions'])) $_POST['reuse_questions_from'] = 0;			
			
			$wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_EXAMS." SET reuse_questions_from=%d 
				WHERE ID=%d", $_POST['reuse_questions_from'], $exam->ID));
				
			$exam->reuse_questions_from = $_POST['reuse_questions_from'];	
		}
		
		// select other existing exams
		$exams = $wpdb->get_results($wpdb->prepare("SELECT tE.* 
			FROM ".WATUPRO_EXAMS." tE JOIN ".WATUPRO_QUESTIONS." tQ ON tQ.exam_id = tE.ID 
			WHERE tE.ID!=%d AND tE.reuse_questions_from=0 GROUP BY tE.ID ORDER BY tE.name", $exam->ID));
			
		if($exam->reuse_questions_from) $intelligence_display = "style='display:none;'";	
		
		require(WATUPRO_PATH."/i/views/reuse_questions.php");
	}
}