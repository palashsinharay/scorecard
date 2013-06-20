<?php
// exam model, currently to handle copy exam function, but later let's wrap more methods here
class WTPExam {
	static function copy($id, $copy_to=0) {
		global $wpdb;
		
		// select exam
	   $exam=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_master WHERE id=%d", $id));
	   if(empty($exam->ID)) throw new Exception(__("Invalid exam ID", 'watupro'));
		
		// select grades
		$grades=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_grading WHERE exam_id=%d ORDER BY ID", $id));
		
		// select questions and choices
		$questions=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_question WHERE exam_id=%d 
			ORDER BY sort_order, ID", $id), ARRAY_A);
		$qids=array(0);
		foreach($questions as $question) $qids[]=$question['ID'];
		
		$choices=$wpdb->get_results("SELECT * FROM {$wpdb->prefix}watupro_answer WHERE question_id IN (".implode(",",$qids).") 
			ORDER BY sort_order, ID");
		
		// match choices to questions
		foreach($questions as $cnt=>$question) {
			$questions[$cnt]['choices']=array();
			foreach($choices as $choice)
			{
				if($choice->question_id==$question['ID']) $questions[$cnt]['choices'][]=$choice;
			}
		}
		
		// insert exam
		if(empty($copy_to)) {
			$new_exam_id=self::add(array("name"=>stripslashes($exam->name)." ".__("(Copy)", 'watupro'), 
			"description"=>stripslashes($exam->description),
			"content"=>stripslashes($exam->final_screen),
			"require_login"=>$exam->require_login,
			"take_again"=>$exam->take_again,
			"email_taker"=>$exam->email_taker,
			"email_admin"=>$exam->email_admin,
			"admin_email"=>$exam->admin_email,
			"randomize_questions"=>$exam->randomize_questions,
			"login_mode"=>$exam->login_mode,
			"time_limit"=>$exam->time_limit,
			"pull_random"=>$exam->pull_random,
			"show_answers"=>$exam->show_answers,
			"group_by_cat"=>$exam->group_by_cat,
			"num_answers"=>$exam->num_answers,
			"single_page"=>$exam->single_page,
			"cat_id"=>$exam->cat_id,
			"times_to_take"=>$exam->times_to_take,
			"mode" => $exam->mode,
			"require_captcha" => $exam->require_captcha,
			"grades_by_percent" => $exam->grades_by_percent,
			"disallow_previous_button" => $exam->disallow_previous_button,
			"random_per_category" => $exam->random_per_category,
			"email_output" => $exam->email_output,
			"live_result" => $exam->live_result,
			"fee" => $exam->fee,
			"is_scheduled" => $exam->is_scheduled,
      "schedule_from" => $exam->schedule_from,
      "schedule_to" => $exam->schedule_to,
      "submit_always_visible" => $exam->submit_always_visible ));
			
			// insert grades
			foreach($grades as $grade) {
				$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}watupro_grading SET
					exam_id=%d, gtitle=%s, gdescription=%s, gfrom=%d, gto=%d",
					$new_exam_id, stripcslashes($grade->gtitle), stripcslashes($grade->gdescription), $grade->gfrom, $grade->gto));
			}
		}		
		else $new_exam_id=$copy_to;
		
		// insert questions and choices
		foreach($questions as $question) {
			$to_copy = array(
				"quiz" => $new_exam_id,
				"content" => stripslashes($question['question']),
				"answer_type" => $question['answer_type'],			
				"cat_id" => $question['cat_id'],
				"explain_answer" => stripslashes($question['explain_answer']),
				"is_required" => $question['is_required'],
				"sort_order" => $question['sort_order'],
				"correct_gap_points" => $question['correct_gap_points'],
				"incorrect_gap_points" => $question['incorrect_gap_points']
			);			
			
			$new_question_id = WTPQuestion::add($to_copy);
			
			foreach($question['choices'] as $choice) {
				$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}watupro_answer(question_id,answer,correct,point, sort_order)
					VALUES(%d, %s, %s, %d, %d)", 
					$new_question_id, stripslashes($choice->answer), $choice->correct, $choice->point, $choice->sort_order));
			}
		}
	}
	
	// add exam
	static function add($vars) {
		global $wpdb;
		
		// normalize params
		if(empty($vars['fee'])) $vars['fee'] = "0.00";
		if(empty($vars['random_per_category'])) $vars['random_per_category'] = "0";
		if(empty($vars['schedule_from'])) $vars['schedule_from'] = "$vars[schedule_fromyear]-$vars[schedule_frommonth]-$vars[schedule_fromday] $vars[schedule_from_hour]:$vars[schedule_from_minute]:00";
		if(empty($vars['schedule_to']))  $vars['schedule_to'] = "$vars[schedule_toyear]-$vars[schedule_tomonth]-$vars[schedule_today] $vars[schedule_to_hour]:$vars[schedule_to_minute]:00";
				
		$wpdb->query($wpdb->prepare("INSERT INTO ".WATUPRO_EXAMS." SET			
			name=%s, description=%s, final_screen=%s,  added_on=NOW(), 
			require_login=%d, take_again=%d, email_taker=%d, 
			email_admin=%d, randomize_questions=%d, login_mode=%s, time_limit=%d, pull_random=%d, 
			show_answers=%s, group_by_cat=%d, num_answers=%d, single_page=%d, cat_id=%d, 
			times_to_take=%d, mode=%s, fee=%d, require_captcha=%d, grades_by_percent=%d,
			admin_email=%s, disallow_previous_button=%d, random_per_category=%d,
			email_output=%s, live_result=%d, is_scheduled=%d, schedule_from=%s, 
			schedule_to=%s, submit_always_visible=%d", 
			$vars['name'], $vars['description'], $vars['content'], $vars['require_login'], 
			$vars['take_again'], $vars['email_taker'],
			$vars['email_admin'], $vars['randomize_questions'], $vars['login_mode'],
			$vars['time_limit'], $vars['pull_random'], $vars['show_answers'], 
			$vars['group_by_cat'], $vars['num_answers'], $vars['single_page'], $vars['cat_id'], 
			$vars['times_to_take'], $vars['mode'], $vars['fee'], $vars['require_captcha'],
			$vars['grades_by_percent'], $vars['admin_email'], $vars['disallow_previous_button'],
			$vars['random_per_category'], $vars['email_output'], $vars['live_result'],
			$vars['is_scheduled'], $vars['schedule_from'], $vars['schedule_to'], $vars['submit_always_visible']));		
			$exam_id = $wpdb->insert_id;
		
		if(watupro_intel()) {
			 require_once(WATUPRO_PATH."/i/models/dependency.php");
			 require_once(WATUPRO_PATH."/i/models/exam_intel.php");
			 WatuPRODependency::store($exam_id);
			 WatuPROIExam::extra_fields($exam_id, $vars);
		} 
				
		return $exam_id;
	}
	
	// edit exam
	static function edit($vars, $exam_id) {
		global $wpdb;
		
		// normalize params
		if(empty($vars['fee'])) $vars['fee'] = "0.00";
		if(empty($vars['random_per_category'])) $vars['random_per_category'] = "0";
		$vars['schedule_from'] = "$vars[schedule_fromyear]-$vars[schedule_frommonth]-$vars[schedule_fromday] $vars[schedule_from_hour]:$vars[schedule_from_minute]:00";
		$vars['schedule_to'] = "$vars[schedule_toyear]-$vars[schedule_tomonth]-$vars[schedule_today] $vars[schedule_to_hour]:$vars[schedule_to_minute]:00";
		
		$wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_EXAMS." 
			SET name=%s, description=%s, final_screen=%s,require_login=%d, take_again=%d, 
			email_taker=%d, email_admin=%d, randomize_questions=%d, 
			login_mode=%s, time_limit=%d, pull_random=%d, show_answers=%s, 
			group_by_cat=%d, num_answers=%d, single_page=%d, cat_id=%d, times_to_take=%d,
			mode=%s, fee=%s, require_captcha=%d, grades_by_percent=%d, admin_email=%s,
			disallow_previous_button=%d, random_per_category=%d, email_output=%s, live_result=%d,
			is_scheduled=%d, schedule_from=%s, schedule_to=%s, submit_always_visible=%d
			WHERE ID=%d", $vars['name'], $vars['description'], $vars['content'],
		$vars['require_login'], $vars['take_again'], $vars['email_taker'],
		$vars['email_admin'], $vars['randomize_questions'], $vars['login_mode'],
		$vars['time_limit'], $vars['pull_random'], $vars['show_answers'], $vars['group_by_cat'],
		$vars['num_answers'], $vars['single_page'], $vars['cat_id'], $vars['times_to_take'],
		$vars['mode'], $vars['fee'], $vars['require_captcha'], $vars['grades_by_percent'], 
		$vars['admin_email'], $vars['disallow_previous_button'], $vars['random_per_category'], 
		$vars['email_output'], $vars['live_result'], $vars['is_scheduled'], $vars['schedule_from'], 
		$vars['schedule_to'], $vars['submit_always_visible'], 
		$exam_id));
		
		if(watupro_intel()) {
			 require_once(WATUPRO_PATH."/i/models/dependency.php");
			 require_once(WATUPRO_PATH."/i/models/exam_intel.php");
			 WatuPRODependency::store($exam_id);
			 WatuPROIExam::extra_fields($exam_id, $vars);
		} 
		
		return true;
	}
	
	// selects exams that user has access to along with taken data, post, and category
	// $cat_id_sql - categories that $uid has access to
	// returns array($my_exams, $takings, $num_taken);
	static function my_exams($uid, $cat_id_sql) {
		global $wpdb;
		
		$cat_id_sql = strlen($cat_id_sql)? "AND tE.cat_id IN ($cat_id_sql)" : "";
		
		// select all exams along with posts they have been embedded in
		$exams=$wpdb->get_results("SELECT tE.*, tC.name as cat 
			FROM ".WATUPRO_EXAMS." tE LEFT JOIN ".WATUPRO_CATS." tC
			ON tC.ID=tE.cat_id
			WHERE 1 $cat_id_sql ORDER BY tE.ID");
		
		// now select all posts that have watupro shortcode in them
		$posts=$wpdb->get_results("SELECT * FROM {$wpdb->posts} 
			WHERE post_content LIKE '%[WATUPRO %]%' 
			AND (post_type='post' OR post_type='page') AND post_status='publish' AND post_title!=''
			ORDER BY post_date DESC");
			
		// select all exams that I have taken
		# $wpdb->show_errors=true;
		$takings=$wpdb->get_results($wpdb->prepare("SELECT * FROM ".WATUPRO_TAKEN_EXAMS."
			WHERE user_id=%d AND in_progress=0 ORDER BY ID DESC", $uid));
		$tids=array();
		foreach($takings as $taking) $tids[]=$taking->exam_id;
		
		// final exams array - should contain only one post per exam, and we should know which one
		// is taken and which one is not
		$my_exams=array();
		$num_taken=0;
		
		foreach($exams as $cnt=>$exam) {
			$my_exam=$exam;
			if(in_array($exam->ID, $tids)) $my_exam->is_taken=1;
			else $my_exam->is_taken=0;
		
			$post_found=false;
			foreach($posts as $post) {
				if(strstr($post->post_content,"[WATUPRO ".$exam->ID."]")) {
					$my_exam->post=$post;
					$post_found=true;
					break;
				}
			}
		
			if($post_found) {
				// match latest taking and fill all takings
				$my_exam->takings = array();
				foreach($takings as $taking) {
					if($taking->exam_id!=$exam->ID) continue;
					
					if(empty($my_exam->taking)) { 
						$my_exam->taking=$taking;
						$num_taken++;
					}
					
					$my_exam->takings[] = $taking;
				}
		
				// add to the final array
				$my_exams[]=$my_exam;
			} // end if $post_found
		} // end foreach exam
		
		// primary returns $my_exams, but $takings may also be used as it's retrieved anyway
		return array($my_exams, $takings, $num_taken);
	}
	
	// lists all published exams or these within given category
	static function show_list($cat_id = 'ALL') {
		 global $wpdb, $user_ID;
		 $cat_id_sql = ($cat_id == 'ALL') ? "" : $cat_id;
		 		
		 list($exams) = WTPExam::my_exams($user_ID, $cat_id_sql);
		 
		 $content = "";
		 
		 foreach($exams as $exam) {
		 		$content .= "<p><a href=".get_permalink($exam->post->ID)." target='_blank'>".$exam->name."</a></p>";
		 }	 
		 
		 return $content;
	}
}