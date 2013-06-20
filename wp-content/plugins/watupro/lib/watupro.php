<?php
// store some of the logic here to encapsulate the things a little bit 
class WatuPRO {
	 static $output_sent = false;
	 
    function add_taking($exam_id, $in_progress=0) {
        global $user_ID, $wpdb;   
        
        // existing incomplete taking with this exam and user ID?
        if(!empty($user_ID)) {
        		$exists=$wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->prefix}watupro_taken_exams 
        			WHERE user_id=%d AND exam_id=%d AND in_progress=1",$user_ID,$exam_id));
        		if(!empty($exists))  $taking_id=$exists;  
        		
        		// when completing the exam in_progress should become 0
        		if(!$in_progress) {
        			$wpdb->query("UPDATE {$wpdb->prefix}watupro_taken_exams SET in_progress=0 WHERE ID='$taking_id'");
        		}      		
        } 
        
        if(empty($taking_id)) {
					  // select exam
					  $exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_EXAMS." WHERE ID=%d", $exam_id));
					  
					  if($exam->single_page and !empty($_POST['start_time'])) $start_time = $_POST['start_time'];
					  else $start_time = date("Y-m-d H:i:s");      	
        	
        		$wpdb->insert("{$wpdb->prefix}watupro_taken_exams", array(
	            "user_id"=>$user_ID,
	            "exam_id"=>$exam_id, 
	            "date"=>date("Y-m-d"),
	            "start_time"=>$start_time,
	            "ip"=>$_SERVER['REMOTE_ADDR'],
	            "in_progress"=>$in_progress,
	            "details" => "",
	            "result" => "",
	            "end_time" => "2000-01-01 00:00:00",
	            "grade_id" => 0,
	            "percent_correct" => 0,
			   ),
		      array('%d','%d','%s','%s','%s','%s','%s','%s','%s','%d','%d'));
		        
		      // save the ID just in case
		      $taking_id=$wpdb->insert_id;
        }
        
        update_user_meta( $user_ID, "current_watupro_taking_id", $taking_id);
        
        return $taking_id;
    }

    // store results in the DB
    function update_taking($taking_id, $points, $grade, $details="", $percent = 0, $grade_obj = null, $catgrades = '') {
        // update existing taking   
         global $user_ID, $wpdb;        
                    
        $wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_TAKEN_EXAMS." SET 
            details=%s, points=%s, result=%s, end_time=%s, percent_correct=%d, grade_id=%d, email=%s, catgrades=%s 
            WHERE id=%d", 
			      $details, $points, $grade, date('Y-m-d H:i:s'), $percent, @$grade_obj->ID, 
			      $_POST['taker_email'], $catgrades, $taking_id ));
    }  
    
    // email exam details to where is selected
    function email_results($exam, $output) {
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=utf8' . "\r\n";
		
			$output=WatuPRO::cleanup($output);		
		
			$output='<html><head><title>'.__('Your results on ', 'watupro').$exam->name.'</title>
			</head>
			<html><body>'.$output.'</body></html>';
			// echo $output;
    	
			global $user_email;	
			if(!is_user_logged_in()) $user_email = $_POST['taker_email'];
			if($exam->email_taker) {
				if($user_email) wp_mail($user_email, __("Your results on ", 'watupro')."\"{$exam->name}\"", $output, $headers);				
			}
			
			if($exam->email_admin) {
				// if user is logged in, let admin know who is taking the test
				if(!empty($user_email)) $output="Details of $user_email:<br><br>".$output;			
				
				$admin_email = empty($exam->admin_email)?	get_settings('admin_email') : $exam->admin_email;
				
				wp_mail($admin_email, __("User results on ", 'watupro')."\"{$exam->name}\"", $output, $headers);
			}
	}
	
	// see if user still can take the exam depending on number of takings allowed
	// returns true if they can take and false if they can't 
	function can_retake($exam) {
		// no limits if login is not required
		if(!$exam->require_login) return true;		
		
		if($exam->take_again) {			
			// Intelligence limitations
			if(watupro_intel()) {
				require_once(WATUPRO_PATH."/i/models/exam_intel.php");
				if(!WatuPROIExam::can_retake($exam)) return false;
			}		
		
			if(empty($exam->times_to_take)) return true; // 0 = unlimited

            // now select number of takings
			global $wpdb, $user_ID;
			if(!is_user_logged_in()) {
				echo __("Sorry, you are not allowed to take exams.", 'watupro');				
				return false;
			}
			
			$cnt_takings=$wpdb->get_var($wpdb->prepare("SELECT COUNT(ID) FROM {$wpdb->prefix}watupro_taken_exams
				WHERE exam_id=%d AND user_id=%d AND in_progress=0", $exam->ID, $user_ID));
			if($cnt_takings >= $exam->times_to_take)
			{
				echo "<p><b>";
				printf(__("Sorry, you can take this exam only %d times.", 'watupro'), $exam->times_to_take);
				echo "</b></p>";
				return false;
			}				
		}
		else {
			// see if exam is already taken by this user
			$taking=$this->get_taking($exam);
						
			if(!empty($taking->ID) and !$taking->in_progress) {
				echo "<p><b>";
				_e("Sorry, you can take this exam only once!", 'watupro');
				echo "</b></p>";
				return false;
			}
		}		
		
		// just in case
		return true;
	}
	
	// get existing taking for given exam (only for logged in users)
	function get_taking($exam)	{
		global $wpdb, $user_ID;
		
		if(!is_user_logged_in()) return false;
		
		$taking=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_taken_exams
			WHERE exam_id=%d AND user_id=%d ORDER BY ID DESC LIMIT 1", $exam->ID, $user_ID));
			
		return $taking;	
	}
	
	// verifies if time limit is fine and there is no cheating
	// allow 15 seconds for submitting in case of server overload
	function verify_time_limit($exam, $in_progress = null) {
		global $user_ID;
		
		if(!$exam->time_limit) return true;
		
		if(is_user_logged_in() and $in_progress) {
			// compare with saved data
			$start=watupro_mktime($in_progress->start_time);			
			if($start and ($start+$exam->full_time_limit*60+10)<time()) return false;
		}
		else {
			// check based on post field			
			if(($_POST['start_time']+$exam->full_time_limit*60+10)<time()) return false;
		}
		
		return true;
	}
	
	// small helper to convert answer ID's into texts
	function answer_text($answers, $ansArr)
	{
		$answer_text="";
		foreach($answers as $answer)
		{
			if(in_array($answer->ID, $ansArr))
			{
				if(!empty($answer_text)) $answer_text.=", ";
				$answer_text.=$answer->answer;
			}
		}
		
		return $answer_text;
	}
	
    // INSERT specific details in watupro_student_answers 
    // done either in completing exam or while clicking next/prev
    // $points and question_text are not required for in_progress takings. As there we only need to store
    // what answer is given so student can continue
    // $answer is answer text when we are completing the exam. But it's stored as (ID, text, or array)
    // if we are storing in progress data - because it's easier to save&retrieve this way
    function store_details($exam_id, $taking_id, $question_id, $answer, $points=0, $question_text="", $is_correct=0, $snapshot = '') {
        global $wpdb, $user_ID;
        
        if(empty($points)) $points = "0.00";
        
        // remove hardcoded correct/incorrect images if any
	    	// (for example we may have these in fill the gaps questions)
	    	$answer = str_replace('<img src="'.plugins_url("watupro").'/correct.png" hspace="5">', '', $answer);
	    	$answer = str_replace('<img src="'.plugins_url("watupro").'/wrong.png" hspace="5">', '', $answer);	    	
                
        // if detail exists update
        $detail=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_student_answers 
         WHERE taking_id=%d AND exam_id=%d AND question_id=%d", $taking_id, $exam_id, $question_id));
         
        if(empty($detail->ID)) {
    		   $wpdb->insert("{$wpdb->prefix}watupro_student_answers",
    			array("user_id"=>$user_ID, "exam_id"=>$exam_id, "taking_id"=>$taking_id,
    				"question_id"=>$question_id, "answer"=>$answer,
    				"points"=>$points, "question_text"=>$question_text, 
    				"is_correct" => $is_correct, 'snapshot'=>$snapshot),
    			array("%d","%d","%d","%d","%s","%s","%s", "%d", "%s"));    			
        }
        else {
				// don't remove the snapshot
				if(empty($snapshot) and !empty($detail->snapshot)) $snapshot = stripslashes($detail->snapshot);        	
        	
            $wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_STUDENT_ANSWERS." SET
               answer=%s, points=%s, question_text=%s, is_correct=%d, snapshot=%s
               WHERE id=%d", $answer, $points, $question_text, $is_correct, $snapshot, $detail->ID));              
        } 
    }
    
    // regroup questions by category or pull random per category
    function group_by_cat($questions, $exam) {
			// pull random by category?    	
			if($exam->pull_random and $exam->random_per_category) {
				$cat_ids = array();
				$cats = array();
				
				foreach($questions as $cnt=>$question) {
					 if(!in_array($question->cat_id, $cat_ids)) {
					 		$cat_ids[] = $question->cat_id;
							$cats[$question->cat_id] = 0;
					 }
					 
					 // enough questions in the category? then skip this one						 
					 if($cats[$question->cat_id] >= $exam->pull_random) {
					 		unset($questions[$cnt]);
					 		continue;
					 }
					 
					 $cats[$question->cat_id]++;
				}
			}
    	
    	// now group by category if selected
    	if(!$exam->group_by_cat) return $questions;

			// now regroup
			$cats=array();
			foreach($questions as $question) {
				if(!in_array($question->cat, $cats)) $cats[]=$question->cat;
			}    	
			
			$regrouped_questions=array();
			
			foreach($cats as $cat) {
				foreach($questions as $question)
				{
					if($question->cat==$cat) $regrouped_questions[]=$question;
				}
			}			
    	
    	return $regrouped_questions;
    }
    
    // calculate generic rating
    function calculate_rating($total, $score, $percent) {
    	$all_rating = array(__('Failed', 'watupro'), __('Failed', 'watupro'), __('Failed', 'watupro'), __('Failed', 'watupro'), __('Just Passed', 'watupro'),
    	__('Satisfactory', 'watupro'), __('Competent', 'watupro'), __('Good', 'watupro'), __('Very Good', 'watupro'),__('Excellent', 'watupro'), __('Unbeatable', 'watupro'), __('Cheater', 'watupro'));
    	$rate = intval($percent / 10);
    	if($percent == 100) $rate = 9;
    	if($score == $total) $rate = 10;
    	if($percent>100) $rate = 11;
    	$rating = @$all_rating[$rate];
    	return $rating;
    }
    
    // match answers to questions and if required show only some of the answers
    function match_answers(&$all_question, $exam) {
    		global $wpdb, $ob;
    		
    		// if answers are limited, $ob is ignored and becomes random
    		// i.e. correct is selected first, then we'll shuffle the answers
    		$ob=($exam->randomize_questions==1)?"RAND()":"sort_order,ID";
    		if($exam->num_answers) $ob="correct DESC, RAND()";
    		
    	   $qids=array(0);
			foreach($all_question as $question) $qids[]=$question->ID;
			$qids=implode(",",$qids);
			
			$all_answers = $wpdb->get_results("SELECT *	FROM {$wpdb->prefix}watupro_answer 
			WHERE question_id IN ($qids) 
			ORDER BY $ob");
			
			foreach($all_question as $cnt=>$question) {
				$all_question[$cnt]->q_answers=array();
				$num_answers=0;	
				foreach($all_answers as $answer) {
					 if($answer->question_id==$question->ID) {
					 		$all_question[$cnt]->q_answers[]=$answer;
					 		if($exam->num_answers>0) {
					 			$num_answers++;
					 			if($num_answers>=$exam->num_answers) break;
					 		}
					 }
				}	
				
				// now shuffle if needed
				if($exam->num_answers) shuffle($all_question[$cnt]->q_answers);	
			}
    }
    
    // check if user can access exam
    static function can_access($exam) {    	
    	 // always access public exams
		 if(!$exam->require_login) return true;   
		 
		 if($exam->require_login and !is_user_logged_in()) return false;
		 
		 // admin can always access
		 if(current_user_can('manage_options') or current_user_can('watupro_manage_exams')) return true;
		 
    	     	 
    	 // USER GROUP CHECKS
		 $allowed = WTPCategory::has_access($exam);
		 
		 if(!$allowed) {
		 		echo "<!-- not in allowed user group -->";
		 		return false;
		 }
		 
		 // INTELLIGENCE MODULE RESTRICTIONS
		 if(watupro_intel()) {
			if($exam->fee > 0) {				
				require_once(WATUPRO_PATH."/i/models/payment.php");
				if(!WatuPROPayment::valid_payment($exam)) {					
					self::$output_sent = WatuPROPayment::render($exam);
					return false;					
				}				
			}		 	
		 	
		 	require_once(WATUPRO_PATH."/i/models/dependency.php");
		 	if(!WatuPRODependency::check($exam)) {
		 		echo "<!-- WATUPROCOMMENT unsatisfied dependencies -->";
		 		return false;
		 	}
		 }

    	 return true;
	 }
	 
	 // convert our special correct/wrong classes to 
	 // simple HTML so it can be visible in email and downloaded doc
	 static function cleanup($output, $media='email')
	 {
	 	// replace correct/wrong classes for the email
		$correct_style=' style="padding-right:20px;background:url('.plugins_url("watupro").'/correct.png) no-repeat right top;" ';
		$wrong_style=' style="padding-right:20px;background:url('.plugins_url("watupro").'/wrong.png) no-repeat right top;" ';
		
		// of blank == true just remove the comments (to avoid cluttering the HTML response)
		if($media=='web') $correct_style=$wrong_style="";		
		
		$output=str_replace('><!--WATUEMAILanswerWATUEMAIL--','',$output);
		$output=str_replace('><!--WATUEMAILanswer user-answer correct-answerWATUEMAIL--', $correct_style,$output);
		$output=str_replace('><!--WATUEMAILanswer correct-answerWATUEMAIL--',$correct_style,$output);
		$output=str_replace('><!--WATUEMAILanswer user-answerWATUEMAIL--', $wrong_style,$output);
		
		// shortcodes
		if($media=='web')  $output=do_shortcode($output);	
		else 	$output=strip_shortcodes($output);
				
		return $output;
	 }
}


/******************************** Procedure functions below ************************************/
function watupro_taking_details() {
		global $wpdb, $user_ID;
		
		// select taking
		$taking=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_taken_exams 
			WHERE id=%d", $_REQUEST['id']));
		
		// select user
		$student=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->users} 
			WHERE id=%d", $taking->user_id));

		// make sure I'm admin or that's me
		if(!current_user_can(WATUPRO_MANAGE_CAPS) and $student->ID!=$user_ID) {
			wp_die( __('You do not have sufficient permissions to access this page', 'watupro') );
		}
		
		// select detailed answers
		$answers=$wpdb->get_results($wpdb->prepare("SELECT tA.*, tQ.question as question
		FROM ".WATUPRO_STUDENT_ANSWERS." tA JOIN ".WATUPRO_QUESTIONS." tQ ON tQ.id=tA.question_id 
		WHERE taking_id=%d ORDER BY id", $taking->ID));
		
		// select exam
		$exam=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_master 
			WHERE id=%d", $taking->exam_id));
		
		// export?
		if(!empty($_GET['export']))
		{
			$now = gmdate('D, d M Y H:i:s') . ' GMT';
			header('Content-Type: ' . watupro_get_mime_type());
			header('Expires: ' . $now);
			header('Content-Disposition: attachment; filename="results.doc"');
			header('Pragma: no-cache');			
			echo "<html>";
			echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf8\">";
			echo "<body>";
			
			require(WATUPRO_PATH. '/views/taking_details.php');
			
			echo "</body></html>";
			exit;
		}
		
		require(WATUPRO_PATH. '/views/taking_details.php');   
		exit;
}

function watupro_define_newline() 
{
	// credit to http://yoast.com/wordpress/users-to-csv/
	$unewline = "\r\n";
	if (strstr(strtolower($_SERVER["HTTP_USER_AGENT"]), 'win')) {
	   $unewline = "\r\n";
	} else if (strstr(strtolower($_SERVER["HTTP_USER_AGENT"]), 'mac')) {
	   $unewline = "\r";
	} else {
	   $unewline = "\n";
	}
	return $unewline;
}

function watupro_get_mime_type() 
{
	// credit to http://yoast.com/wordpress/users-to-csv/
	$USER_BROWSER_AGENT="";

			if (ereg('OPERA(/| )([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
				$USER_BROWSER_AGENT='OPERA';
			} else if (ereg('MSIE ([0-9].[0-9]{1,2})',strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
				$USER_BROWSER_AGENT='IE';
			} else if (ereg('OMNIWEB/([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
				$USER_BROWSER_AGENT='OMNIWEB';
			} else if (ereg('MOZILLA/([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
				$USER_BROWSER_AGENT='MOZILLA';
			} else if (ereg('KONQUEROR/([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
		    	$USER_BROWSER_AGENT='KONQUEROR';
			} else {
		    	$USER_BROWSER_AGENT='OTHER';
			}

	$mime_type = ($USER_BROWSER_AGENT == 'IE' || $USER_BROWSER_AGENT == 'OPERA')
				? 'application/octetstream'
				: 'application/octet-stream';
	return $mime_type;
}

// calls $watu->store details
// called by ajax, add_action('wp_loaded','watupro_store_details'); is in main watupro.php
function watupro_store_details()
{
   // only for logged in users
   if(!is_user_logged_in()) exit;
   
   $_watu=new WatuPRO();
   $taking_id=$_watu->add_taking($_POST['exam_id'],1);
   $answer=serialize($_POST['answer-'.$_POST['question_id']]);
   $_watu->store_details($_POST['exam_id'], $taking_id, $_POST['question_id'], $answer);
   exit;
}

function watupro_submit()
{
	require(WATUPRO_PATH."/show_exam.php");
	exit;
}

function watupro_initialize_timer() {
	// set up timer and return time as ajax
	// to avoid cheating this won't happen if current $in_progress taking exists for this exam and user
	global $user_ID;
	$time=time();
	
	if(is_user_logged_in()) {
		update_user_meta( $user_ID, "start_exam_".$_REQUEST['exam_id'], $time);
	}
	
	echo "<!--WATUPRO_TIME-->".$time."<!--WATUPRO_TIME-->";
	exit;
}

// check if intelligence module is present
function watupro_intel() {
	if(file_exists(WATUPRO_PATH."/i/controllers/practice.php")) return true;
	else return false;
}

// similar to above but for other modules
function watupro_module($module) {
	if(@file_exists(WATUPRO_PATH."/modules/".$module."/controllers/init.php")) return true;
	else return false;
}