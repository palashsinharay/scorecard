<?php
// global stats for the exam - for the moment means reports per question.  
class WatuPROStats {
	static function per_question() {
		global $wpdb;
		
		// select exam
		$exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_EXAMS." WHERE ID=%d", $_GET['exam_id']));
		
		// select questions
		$source_id = $exam->reuse_questions_from ? $exam->reuse_questions_from : $exam->ID;
		$questions = $wpdb -> get_results($wpdb->prepare("SELECT * FROM ".WATUPRO_QUESTIONS."
			 WHERE exam_id=%d ORDER BY sort_order, ID", $source_id));
		$qids = array(0);
		foreach($questions as $question) $qids[] = $question->ID;
		$qid_sql = implode(", ", $qids);
		
		// select choices
		$choices = $wpdb->get_results("SELECT * FROM ".WATUPRO_ANSWERS." WHERE question_id IN ($qid_sql)");
		
		// select student answers
		$student_answers = $wpdb->get_results($wpdb->prepare("SELECT tA.* FROM ".WATUPRO_STUDENT_ANSWERS." tA 
			JOIN ".WATUPRO_TAKEN_EXAMS." tT ON tT.id = tA.taking_id
			WHERE tT.exam_id=%d", $exam->ID));
		
		// now do the matches
		foreach($questions as $cnt=>$question) {
			$question_choices = array();
			$total_answers = $num_correct = 0; // total answers/choices on this question
			$question_answers = $question_correct_answers = 0;
			
			// fill choices along with times and % selected
			foreach($choices as $ct=>$choice) {
				if($choice->question_id != $question->ID) continue;
				
				$choice->times_selected = $choice->percentage = 0;
				
				foreach($student_answers as $answer) {
					if($answer->question_id != $question->ID) continue;
					
					// single answer and textarea correct check
					if(($question->answer_type=='radio' or $question->answer_type=='textarea') 
						and $answer->answer != $choice->answer) continue;
					
					// multiple answer
					if($question->answer_type=='checkbox') {
							$sub_choices = explode(", ", $answer->answer);
							$subchoice_found = false;
							foreach($sub_choices as $sub_choice) {
								if($choice->answer == $sub_choice) {
									$subchoice_found = true;
									break;
								}
							}
							
							if(!$subchoice_found) continue;
					}
					
					$choice->times_selected++;
					if($question->answer_type!='textarea') $total_answers++;
					if($choice->correct) $num_correct++;
				}
				
				$question_choices[] = $choice;
			}
			
			// now calculate the overall stats for the whole question
			foreach($student_answers as $answer) {
				if($answer->question_id == $question->ID) {
					$question_answers++;
					if($answer->is_correct) $question_correct_answers++;
				}
			}
						
			// now we have all times_selected. Let's calculate % for each choice
			foreach($question_choices as $ct=>$choice) {
				// if total answers is < $question_answers, means we are in textarea question
				// so always choose the bigger
				if($total_answers < $question_answers) $total_answers = $question_answers;								
				
				if($total_answers) $percent = round(($choice->times_selected / $total_answers) * 100);
				else $percent = 0;
				
				$question_choices[$ct]->percentage = $percent;
			}
			
			$questions[$cnt]->choices = $question_choices;
			
			if(!$question_answers) $perc_correct = 0;
			else $perc_correct = round(($question_correct_answers / $question_answers) * 100);
			
			$questions[$cnt]->percent_correct = $perc_correct; 
			$questions[$cnt]->num_correct = $question_correct_answers;
			$questions[$cnt]->total_answers = $question_answers;
		}
		
		require(WATUPRO_PATH."/modules/reports/views/per-question.php");
	} // end per_question stats
}