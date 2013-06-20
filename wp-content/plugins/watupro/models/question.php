<?php
// Watu PRO Question model
class WTPQuestion {
	static function add($vars) {
		global $wpdb;
		
		// get max sort order
		if(empty($vars['sort_order'])) {
			$sort_order=$wpdb->get_var($wpdb->prepare("SELECT MAX(sort_order) FROM {$wpdb->prefix}watupro_question
				WHERE exam_id=%d", $vars['quiz']));
			$sort_order++;
		}
		else $sort_order=$vars['sort_order'];	
		
		$sql = $wpdb->prepare("INSERT INTO {$wpdb->prefix}watupro_question (exam_id, question, answer_type, 
			cat_id, explain_answer, is_required, sort_order, correct_condition) 
			VALUES(%d, %s, %s, %d, %s, %d, %d, %s)", 
			$vars['quiz'], $vars['content'], $vars['answer_type'], $vars['cat_id'], 
			$vars['explain_answer'], $vars['is_required'], $sort_order, $vars['correct_condition']);		
		$wpdb->query($sql);
		
		$id = $wpdb->insert_id;
		
		if(watupro_intel()) {
			// extra fields in Intelligence module
			require_once(WATUPRO_PATH."/i/models/question.php");
			WatuPROIQuestion::edit($vars, $id);
		}
		
		return $id;
	}
	
	static function edit($vars, $id) {
		global $wpdb;		
		
		$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}watupro_question 
			SET question=%s, answer_type=%s, cat_id=%d, explain_answer=%s, is_required=%d,
			correct_condition=%s 	WHERE ID=%d", 
			$vars['content'], $vars['answer_type'], $vars['cat_id'], $vars['explain_answer'],
			$vars['is_required'],	$vars['correct_condition'], $id));
			$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}watupro_answer 
				WHERE question_id=%d", $id));
				
		if(watupro_intel()) {
			// extra fields in Intelligence module
			require_once(WATUPRO_PATH."/i/models/question.php");
			WatuPROIQuestion::edit($vars, $id);
		}		
	}	
	
	// backward compatibility. In old versions sort order was not given
	// so we'll make sure all questions have correct one when loading the page
	static function fix_sort_order($questions) {
		global $wpdb;
		$questions_table=$wpdb->prefix."watupro_question";
		
		foreach($questions as $cnt=>$question) {
			$cnt++;
			if($question->sort_order!=$cnt) {
				$wpdb->query("UPDATE $questions_table SET sort_order=$cnt WHERE ID={$question->ID}");
			}
		}
	}
	
	static function reorder($id, $exam_id, $dir) {
		global $wpdb;
		$questions_table=$wpdb->prefix."watupro_question";
		
		// select question
		$question=$wpdb->get_row($wpdb->prepare("SELECT * FROM $questions_table WHERE ID=%d", $id));
		
		if($dir=="up")
		{
			$new_order=$question->sort_order-1;
			if($new_order<0) $new_order=0;
			
			// shift others
			$wpdb->query($wpdb->prepare("UPDATE $questions_table SET sort_order=sort_order+1 
			  WHERE ID!=%d AND sort_order=%d AND exam_id=%d", $id, $new_order, $exam_id));
		}
		
		if($dir=="down") {
			$new_order=$question->sort_order+1;			
			
			// shift others
			$wpdb->query($wpdb->prepare("UPDATE $questions_table SET sort_order=sort_order-1 
			  WHERE ID!=%d AND sort_order=%d AND exam_id=%d", $id, $new_order, $exam_id));
		}		
			
		// change this one
		$wpdb->query($wpdb->prepare("UPDATE $questions_table SET sort_order=%d WHERE ID=%d", 
			$new_order, $id));
	}
	
	// to display a question
	function display($ques, $qct, $question_count, $in_progress, $exam = null) {
		global $wpdb;
		
		// fill in_progress once to avoid running multiple qiueries
		if(!empty($in_progress)) {
	  		// check if we already fetched the answers. if not, fetch
	  		// this is to avoid queries on every question
	  		if(empty($this->inprogress_details)) {
	  			$answers=$wpdb->get_results($wpdb->prepare("SELECT * FROM ".WATUPRO_STUDENT_ANSWERS." 
	  				WHERE taking_id=%d AND exam_id=%d", $in_progress->ID, $in_progress->exam_id));
	  				
	  			$this->inprogress_details=array();
	  			foreach($answers as $answer) {
	  					$this->inprogress_details[$answer->question_id]=unserialize($answer->answer);
	  					$this->inprogress_snapshots[$answer->question_id]=stripslashes($answer->snapshot);
	  			}	
	  		}
	  }   	
	  
	  // if there is snapshot, means we have called 'see answer'. In this case we should make the div below invisible
	  $nodisplay = '';
	  if(!empty($this->inprogress_snapshots[$ques->ID]) and $exam->live_result) {
	  	  $nodisplay = 'style="display:none;"';
	  }
		
		echo "<div id='questionWrap-$question_count' $nodisplay>
			<div class='question-content' $display_style>";
		
		if(watupro_intel() and $ques->answer_type=='gaps') {
			require_once(WATUPRO_PATH."/i/models/question.php");
			WatuPROIQuestion::display($ques, $qct, $question_count, $this->inprogress_details);
		}
		else echo wpautop(stripslashes("<span class='watupro_num'>$qct. </span>".$ques->question),0);
		 
		echo  "</div>"; // end question-content
 		echo "<input type='hidden' name='question_id[]' id='qID_{$question_count}' value='{$ques->ID}' />";
 		echo "<input type='hidden' id='answerType{$ques->ID}' value='{$ques->answer_type}'>";
 		
 		$this->display_choices($ques, $in_progress);
 		echo '</div>'; // end questionWrap
	}
	
	
	// display the radio, checkbox or text area for answering a question
    // also take care for pre-selecting anything in case we are continuing on unfinished exam
  function display_choices($ques, $in_progress=null) {
			global $wpdb, $answer_display;
		  
  	  $ans_type = $ques->answer_type;
      
      switch($ans_type) {
      	case 'textarea':
      		// open end question
          echo "<p><textarea name='answer-{$ques->ID}[]' id='textarea_q_{$ques->ID}' rows='3' cols='40'>";
          if(!empty($this->inprogress_details[$ques->ID][0])) echo stripslashes($this->inprogress_details[$ques->ID][0]); 
          echo "</textarea></p>";
      	break;
      	case 'radio':
      	case 'checkbox':
      		// radio and checkbox
      		foreach ($ques->q_answers as $ans) {
	        		if($answer_display == 2) {
	        			$answer_class = 'wrong-answer-label';
	        			if($ans->correct) $answer_class = 'correct-answer-label';
	        		}
	        		
	        		$checked="";
							if(!empty($this->inprogress_details[$ques->ID])) {
									if(is_array($this->inprogress_details[$ques->ID])) {
										if(in_array($ans->ID, $this->inprogress_details[$ques->ID])) $checked=" checked ";
									}
									else 
									{
										if($this->inprogress_details[$ques->ID]==$ans->ID) $checked=" checked ";
									}
							}	        		
	        		
	        		echo "<input type='$ans_type' name='answer-{$ques->ID}[]' id='answer-id-{$ans->ID}' class='answer answer-$question_count $answer_class answerof-{$ques->ID}' value='{$ans->ID}' $checked/>";
	        		echo "&nbsp;<label for='answer-id-{$ans->ID}' id='answer-label-{$ans->ID}' class='$answer_class answer label-$question_count'><span>" . stripslashes($ans->answer) . "</span></label><br />";
        	 }   
      	break;
      }      
    }
    
    // a small helper that will cleanup markup that shows correct/incorrect info
    // so unresolved questions can be displayed
    function display_unresolved($output) {
    	$output = WatuPRO::cleanup($output, 'web');
    	
    	// now remove correct-answer style
    	$output = str_replace('correct-answer','',$output);
    	$output = str_replace('user-answer','',$output);
    	
    	// remove hardcoded correct/incorrect images if any
    	// (for example we may have these in fill the gaps questions)
    	$output = str_replace('<img src="'.plugins_url("watupro").'/correct.png" hspace="5">', '', $output);
    	$output = str_replace('<img src="'.plugins_url("watupro").'/wrong.png" hspace="5">', '', $output);
    	
    	return $output;	
    }
    
    // figure out if a question is correctly answered accordingly to the requirements
    // $answer is single value or array depending on the question type
    // $choices are the possible choices of this question
    // returns array($points, $is_correct)
    static function calc_answer($question, $answer, $choices=-1) {
    	// negative points and unanswered questions are always incorrect
    	if(empty($answer)) return array(0, 0);
    	
    	global $wpdb;
    	
    	// when choices is -1 means we have not passed them and we have to select them
    	if($choices == -1) {
    		$choices = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".WATUPRO_ANSWERS." 
    			WHERE question_id=%d", $question->ID));
    	}
    	
    	// open-end questions
    	if($question->answer_type=='textarea' or $question->answer_type=='radio') {
    		// when they have no possible answers, they are always correct when answered
    		if(!sizeof($choices)) return array(0, 1); 
    		
    		// answers are given however. We need to figure out whether the answer is correct
    		$is_correct = 0;
    		$points = 0;
    		$answer = $answer[0];
    		foreach($choices as $choice) {
    			 $compare = ($question->answer_type=='textarea') ? strtolower($choice->answer) : $choice->ID;
					 
    			 if($compare == trim(strtolower($answer))) {
    			 		$points = $choice->point;
    			 		if($choice->correct) $is_correct = 1;    			 		
    			 		break;
    			 }	
				}
				
				return array($points, $is_correct);				
    	}
 
 			// multiple answer 			   	
			if($question->answer_type == 'checkbox') {
				// figure out maximum points and calculate received points
				$points = $max_points = 0;
				$is_correct = 0;
				
				foreach($choices as $choice) {
					if($choice->point > 0) $max_points += $choice->point;
					
					foreach($answer as $part) {
						 if($part == $choice->ID) $points += $choice->point;
					}
				}
				
				if(empty($question->correct_condition) or $question->correct_condition == 'any') {
					 if($points > 0) $is_correct = 1;
				}
				else {
					// max points required
					if($points >= $max_points) $is_correct = 1;
				}
				
				return array($points, $is_correct);
			}
			
			// fill the gaps
			if(watupro_intel() and $question->answer_type == 'gaps') {
				list($points, $html, $max_points) = WatuPROIQuestion::process($question, $answer);
				
				if(empty($question->correct_condition) or $question->correct_condition == 'any') {
					 if($points > 0) $is_correct = 1;
				}
				else {
					// max points required
					if($points >= $max_points) $is_correct = 1;
				}
				
				return array($points, $is_correct);
			}
			
			// return just in case
			return array(0, 0);   
    }
    
    // select all questions for an exam
    static function select_all($exam) {
    	global $wpdb;
    	
    	// order by
			$ob=($exam->randomize_questions or $exam->pull_random)?"RAND()":"sort_order,ID";
			$limit_sql="";
			if($exam->pull_random and !$exam->random_per_category) {
				$limit_sql=" LIMIT ".$exam->pull_random;
			}
			
			$q_exam_id = (watupro_intel() and $exam->reuse_questions_from) ? $exam->reuse_questions_from : $exam->ID;
			
			$questions = $wpdb->get_results($wpdb->prepare("SELECT tQ.*, tC.name as cat, tC.description as cat_description
			FROM ".WATUPRO_QUESTIONS." tQ LEFT JOIN ".WATUPRO_QCATS." tC
			ON tC.ID=tQ.cat_id
			WHERE tQ.exam_id=%d 
			ORDER BY $ob $limit_sql", $q_exam_id));
			
			return $questions;
    }
    
    // processes a question when submitting exam or toggling answer. Used in submit_exam and the toggle result button
    // $points is global because "fill the gaps" might change it    
    function process($_watu, $qct, $question_content, $ques, $ansArr, $correct) {
			global $points;
	    	
			$original_answer=""; // this var is used only for textareas    	
			$answer_text=""; // answers as text
			$unresolved_text = "";
		
			if($ques->answer_type == 'gaps') $question_content = preg_replace("/{{{([^}}}])*}}}/", "____", $question_content);
    	
    	$current_text = "<div class='show-question [[watupro-resolvedclass]]'><div class='show-question-content'>". wpautop(stripslashes("$qct. ".$question_content)) . "</div>\n";	
		$current_text .= "<ul>";		        

	  $class = 'answer';
	  $any_answers=false; // this is for textareas -is there any answer provided at all?
		
	  foreach ($ques->q_answers as $ans) {
			$class = 'answer';
			$inline_style='';
			if(  in_array($ans->ID , $ansArr) ) { $class .= ' user-answer'; $inline_style=' style="font-weight:bold;color:blue"';}
			if($ans->correct == 1 and $ques->answer_type!='textarea') $class .= ' correct-answer';
            
        if($ques->answer_type=='textarea'):
             // textarea answers have only 1 element. Make comparison case insensitive
					   $original_answer=$ansArr[0];
					   $ansArr[0]=strtolower(strip_tags(trim($ansArr[0])));
             $compare=strtolower($ans->answer);
             if(!empty($compare)): $any_answers=true; endif;
        else:
             $compare=$ans->ID;
             $current_text .= "<li class='$class'".$inline_style."><span class='answer'><!--WATUEMAIL".$class."WATUEMAIL-->" . stripslashes($ans->answer) . "</span></li>\n";
        endif;    
		} // end foreach choice;
		
     // open end will be displayed here
     if($ques->answer_type=='textarea') {
			 // repeat this line in case there were no answers to compare	
			 $answer_text=empty($original_answer)?$ansArr[0]:$original_answer;
			 $ansArr[0]=strtolower($ansArr[0]);
         $class .= ' user-answer';
         if($correct) $class .= ' correct-answer';
         $current_text .= "<li class='$class'><span class='answer'>" . nl2br(stripslashes($answer_text)) . "</span></li>\n";
     }
     
     if($ques->answer_type=='gaps' and watupro_intel()) {
     		list($points, $answer_text) = WatuPROIQuestion::process($ques, $ansArr);
     		$current_text .= $answer_text;
     }
     
     if(empty($answer_text)) $answer_text=$_watu->answer_text($ques->q_answers, $ansArr);
  		            
  		$current_text .= "</ul>";
  		if(!$_REQUEST["answer-" . $ques->ID]) $current_text .= "<p class='unanswered'>" . __('Question was not answered', 'watupro') . "</p>";
  		
  		if(!$correct) $unresolved_text = $this->display_unresolved($current_text)."</div>";
  
		// if explain_answer, display it
		if($ques->explain_answer) $current_text.="<div>".wpautop(stripslashes($ques->explain_answer))."</div>";    
    
  		$current_text .= "</div>";
  		
  		return array($answer_text, $current_text, $unresolved_text); 
    } // end process()
}