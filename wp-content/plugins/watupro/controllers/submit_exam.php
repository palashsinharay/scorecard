<?php
// called when exam is submitted
watupro_remove_filters();

$_question = new WTPQuestion();

if(watupro_intel()) require_once(WATUPRO_PATH."/i/models/question.php");
$taking_id=$_watu->add_taking($exam->ID);    

$score = 0; $achieved = 0; $result = $unresolved_questions = $current_text = '';
$total=sizeof($all_question);
$result = "";
$pre_result_text = "<p>" . __('All the questions in the exam along with their answers are shown below. Your answers are in blue. The correct answers have a green checkmark while the incorrect ones have a red crossed mark.', 'watupro') . "</p>";
    
$question_catids = array(); // used for category based pagination
foreach ($all_question as $qct=>$ques) {	
		// the two rows below are about the category headers	
		$result .= watupro_cat_header($exam, $qct, $ques, $question_catids, 'submit');
		if(!in_array($ques->cat_id, $question_catids)) $question_catids[] = $ques->cat_id;
		
      $qct++;
      $question_content = $ques->question;
      // fill the gaps need to replace gaps
      if($ques->answer_type=='gaps') $question_content = preg_replace("/{{{([^}}}])*}}}/", "_____", $question_content);

		$ansArr = is_array( $_POST["answer-" . $ques->ID] )? $_POST["answer-" . $ques->ID] : array();      
				
		// points and correct calculation
		list($points, $correct) = WTPQuestion::calc_answer($ques, $ansArr, $ques->q_answers);
				  		
  		list($answer_text, $current_text, $unresolved_text) = $_question->process($_watu, $qct, $question_content, $ques, $ansArr, $correct);
  		$unresolved_questions .= $unresolved_text;
  		
  		// replace the resolved class
  		if($correct) $current_text = str_replace('[[watupro-resolvedclass]]','watupro-resolved',$current_text);
  		else $current_text = str_replace('[[watupro-resolvedclass]]','watupro-unresolved',$current_text);
  		
  		$result .= $current_text;		 
  		
  		// insert taking data
  		$_watu->store_details($exam->ID, $taking_id, $ques->ID, $answer_text, $points, $ques->question, $correct, $current_text);
        
      if($correct) $score++;  
      $achieved += $points;   
}
    
// calculate percentage
if($total==0) $percent=0;
else $percent = number_format($score / $total * 100, 2);

// generic rating
$rating=$_watu->calculate_rating($total, $score, $percent);
	
// assign grade
require_once(WATUPRO_PATH.'/models/grade.php');
list($grade, $certificate_id, $do_redirect, $grade_obj) = WTPGrade::calculate($exam_id, $achieved, $percent);

// assign certificate if any
$certificate="";
if(!empty($certificate_id) and is_user_logged_in()) {
	$certificate = WatuPROCertificate::assign($exam, $taking_id, $certificate_id, $user_ID);
}

// category grades if any
$catgrades = WTPGrade::replace_category_grades($exam->final_screen, $taking_id, $exam->ID);

// replace some old confusingly named vars
$exam->final_screen = str_replace("%%SCORE%%", "%%CORRECT%%", $exam->final_screen);
	
// prepare output
$replace_these	= array('%%CORRECT%%', '%%TOTAL%%', '%%PERCENTAGE%%', '%%GRADE%%', '%%RATING%%', '%%CORRECT_ANSWERS%%', 
	'%%QUIZ_NAME%%', '%%DESCRIPTION%%', '%%POINTS%%', '%%CERTIFICATE%%', '%%GTITLE%%', '%%GDESC%%', 
	'%%UNRESOLVED%%', '%%ANSWERS%%', '%%CATGRADES%%');
$with_these= array($score, $total,  $percent,	$grade, $rating, $score, stripslashes($exam->name), stripslashes($exam->description), $achieved, $certificate, stripslashes($grade_obj->gtitle), stripslashes($grade_obj->gdescription),
$unresolved_questions, $result, $catgrades);

// Show the results    
$output="<div id='startOutput'>&nbsp;</div>";
$output.=str_replace($replace_these, $with_these, stripslashes($exam->final_screen));
$email_output=str_replace($replace_these, $with_these, stripslashes($exam->email_output));    	

if($answer_display == 1) { 
	$output.='<hr />' . $pre_result_text . $result;
	$email_output.='<hr />' . $pre_result_text . $result;
}    

$output = apply_filters('watupro_content', $output);	
$email_output = apply_filters('watupro_content', $email_output);

// show output on the screen
if(empty($do_redirect)) print WatuPRO::cleanup($output, 'web');
else echo "WATUPRO_REDIRECT:::".$do_redirect;
  
// store this taking
if(!empty($exam->email_output)) $output=$email_output; // here maybe replace output with email output
$_watu->update_taking($taking_id, $achieved, $grade, $output, $percent, $grade_obj, $catgrades);
    
// email details if required
$_watu->email_results($exam, $output);     

do_action('watupro_completed_exam', $taking_id);
exit;// Exit due to ajax call