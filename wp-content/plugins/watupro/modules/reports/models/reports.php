<?php
class WTPReports {
	public static $add_scripts = false;	
	public static $user = array();
	
	static function admin_menu() {
		$cap_level = current_user_can(WATUPRO_MANAGE_CAPS)?WATUPRO_MANAGE_CAPS:'watupro_exams';		
		
		add_submenu_page('my_watupro_exams', __("Exam Reports", 'watupro'), __("Exam Reports", 'watupro'), $cap_level, 'watupro_reports', 
				array(__CLASS__, "dispatch"));		
				
		// hidden page
		add_submenu_page(NULL, __("Stats Per Question", 'watupro'), __("Status Per Question", 'watupro'), $cap_level, 'watupro_question_stats',
			array('WatuPROStats', 'per_question'));		
	}
	
	// decides which tab to load
	static function dispatch() {
		global $user_ID;
		
		// define user ID
		if(!empty($_GET['user_id']) and is_numeric($_GET['user_id']) and current_user_can(WATUPRO_MANAGE_CAPS)) $report_user_id = intval($_GET['user_id']);	
		else $report_user_id = $user_ID;	
		
		// select user to display info
		$user = get_userdata($report_user_id);
		if($user_ID != $report_user_id) echo '<p>'.__('Showing exam reports for ', 'watupro').' <b>'.$user->data->user_nicename.'</b></p>';
		
		switch(@$_GET['tab']) {
			case 'tests': self::tests($report_user_id); break; // exams taken
			case 'skills': self::skills($report_user_id); break; // question categories
			case 'time': self::time($report_user_id); break;
			case 'history': self::history($report_user_id); break;
			default: self::overview($report_user_id); break;
		}
	}
	
	static function overview($report_user_id) {
		 global $wpdb;
		 
		 // all exams taken
		 $taken_exams = $wpdb->get_results($wpdb->prepare("SELECT tT.*, tE.cat_id as cat_id 
		 	FROM ".WATUPRO_TAKEN_EXAMS." tT JOIN ".WATUPRO_EXAMS." tE ON tT.exam_id = tE.id
		 	WHERE user_id=%d ORDER BY date", $report_user_id));	
		 	
		 // tests attempted var
		 $num_attempts = sizeof($taken_exams);	
		 	
		 // skills practiced (question categories)
		 $skills = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT(cat_id) FROM ".
		 	WATUPRO_QUESTIONS." WHERE ID IN (SELECT question_id FROM ".WATUPRO_STUDENT_ANSWERS." WHERE user_id=%d)", $report_user_id));
		 $num_skills = sizeof($skills);
		 		 
		 // certificates earned
		 $cnt_certificates = $wpdb->get_var($wpdb->prepare("SELECT COUNT(ID) FROM ".WATUPRO_USER_CERTIFICATES."
		 WHERE user_id=%d", $report_user_id));
		 
		 // figure out num exams taken by exam category - select categories I have access to
		 $cat_ids = WTPCategory::user_cats($report_user_id);
		 $cats = $wpdb->get_results("SELECT * FROM ".WATUPRO_CATS." WHERE ID IN(".implode(",", $cat_ids).")", ARRAY_A);
		 $cats = array_merge( array(array("ID"=>0, "name"=>__('Uncategorized', 'watupro'))), $cats);
		 		 
		 $report_cats = array();
		 // for any categories that don't have zero, add them to report_cats along with time_spent
		 foreach($cats as $cnt=>$cat) {
		 		$num_attempts = 0;
		 		foreach($taken_exams as $taken_exam) {
		 				if($taken_exam->cat_id == $cat['ID']) $num_attempts++;
		 		}
		 		
		 		$cats[$cnt]['num_attempts'] = $num_attempts;
		 		if($num_attempts) $report_cats[] = $cats[$cnt];
		 }
		 	
		 self::$add_scripts = true;		 	
		 require(WATUPRO_PATH."/modules/reports/views/overview.php");		 	
		 self::print_scripts();
	}
	
	static function tests($report_user_id) {
		// details about taken exams
		global $wpdb;
		
		// select all taken exams along with exam data
		$sql = "SELECT COUNT(tA.ID) as cnt_answers, tT.*, tE.name as name, tT.exam_id as exam_id 
		  FROM ".WATUPRO_TAKEN_EXAMS." tT, ".WATUPRO_EXAMS." tE, ".WATUPRO_STUDENT_ANSWERS." tA 
			WHERE tT.user_id=%d AND tT.in_progress=0 AND tT.exam_id=tE.ID AND tA.taking_id = tT.id
			GROUP BY tT.ID ORDER BY tT.ID DESC";
		$exams = $wpdb->get_results($wpdb->prepare($sql, $report_user_id));
		
		$posts=$wpdb->get_results("SELECT * FROM $wpdb->posts 
		WHERE post_content LIKE '%[WATUPRO %]%' 
		AND (post_type='post' OR post_type='page') AND post_status='publish'
		ORDER BY post_date DESC"); 
		
		// match posts to exams
		foreach($exams as $cnt=>$exam) {
			$exams[$cnt]->time_spent = self::time_spent($exam);
			foreach($posts as $post) {
				if(strstr($post->post_content,"[WATUPRO ".$exam->exam_id."]")) {
					$exams[$cnt]->post=$post;			
					break;
				}
			}
		}
		
		require(WATUPRO_PATH."/modules/reports/views/tests.php");
	}
	
	static function skills($report_user_id) {
		global $wpdb;
		require_once(WATUPRO_PATH."/models/exam.php");
		
		// select exam categories that I can access
		$cat_ids = WTPCategory::user_cats($report_user_id);
		$cat_id_sql=implode(",",$cat_ids);	
		$exam_cats = $wpdb->get_results("SELECT * FROM ".WATUPRO_CATS." WHERE ID IN ($cat_id_sql) ORDER BY name");
		
		// question categories
		$q_cats = $wpdb->get_results("SELECT * FROM ".WATUPRO_QCATS." ORDER BY name");		
		// add uncategorized
		$q_cats[] = (object)array("ID"=>0, "name"=>__('Uncategorized', 'watupro'));
		
		// exam category filter?
		$exam_cat_sql = ($_POST['cat'] < 0)? "" : $_POST['cat'];
		
		// now select all exams I have access to
		list($my_exams) = WTPExam::my_exams($report_user_id, $exam_cat_sql);
		
		$skill_filter = empty($_POST['skill_filter'])?"all":$_POST['skill_filter'];
		
		// practiced only?
		if($skill_filter == 'practiced') {
			 $final_exams = array();
			 foreach($my_exams as $exam) {
			 	  if(!empty($exam->taking->ID)) $final_exams[] = $exam;
			 }
			 $my_exams = $final_exams;
		}
		
		// proficiency filter selected? If yes, we'll need to limit exams
		// to those that are taken with at least $_POST['proficiency_goal'] % correct answers		
		if($skill_filter == 'proficient') {				
				$final_exams = array();
				foreach($my_exams as $exam) {					 
					 if(!empty($exam->taking->ID) and $exam->taking->percent_correct >= $_POST['proficiency_goal']) {
					 		$final_exams[] = $exam;
					 }
				} // end exams loop		 
				
				$my_exams = $final_exams;
		}
		
		// group exams by question category
		$skills = array(); // skills equal question categories
		$num_proficient = 0;
		foreach($q_cats as $q_cat) {
			// skill filter (question category) selected in the drop-down?
			if(($_POST['q_cat']>-1) and $q_cat->ID != $_POST['q_cat']) continue;
			
			// now construct array of this category along with the exams in it
			// then add in $skills. $skills is the final array that we'll use in the view
			$exams = array();
			foreach($my_exams as $exam) {
				 $has_questions = $wpdb->get_var($wpdb->prepare("SELECT ID FROM ".WATUPRO_QUESTIONS." 
				 	WHERE exam_id=%d AND cat_id=%d", $exam->ID, $q_cat->ID));
				 	
				 if(!$has_questions) continue;
				 
				 $exams[] = $exam;	
			}	
			
			$skills[] = array("category"=>$q_cat, "exams"=>$exams, "id"=>$q_cat->ID);
			if(sizeof($exams)) $num_proficient++; // proficient in X skills
		}	
		
		// by default $skills is ordered by category (name). Do we have to reorder?
		// NOT SURE THIS MAKES SENSE, SO FOR NOW NYI
		if(!empty($_POST['sort_skills']) and $_POST['sort_skills']=='proficiency') {
			// Sort by sum of proficiency of latest taking of the exams in this category
			// let's create an array that'll contain only cat ID and cumulative proficiency
			// for easier sorting
			$cat_ids = array();
			foreach($skills as $skill) {
				 // NYI	
			}			
		}
		
		require(WATUPRO_PATH."/modules/reports/views/skills.php");
	}
	
	// history
	static function history($report_user_id) {
		global $wpdb;
		
		// select taken exams and fill the details for them
		$taken_exams = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".WATUPRO_TAKEN_EXAMS."
			WHERE user_id=%d ORDER BY end_time DESC", $report_user_id));		
		$details = $wpdb->get_results($wpdb->prepare("SELECT tA.*, tQ.cat_id as cat_id 
			FROM ".WATUPRO_STUDENT_ANSWERS." tA JOIN ".WATUPRO_QUESTIONS." tQ
			ON tQ.ID = tA.question_id
			WHERE tA.user_id=%d", $report_user_id));
			
		$total_time = $total_problems = $total_skills = 0;
			
		foreach($taken_exams as $cnt=>$exam) {
			// add details
			$taken_exams[$cnt]->details = array();
			$taken_exams[$cnt]->num_problems = 0;
			$taken_exams[$cnt]->skills_practiced = array();
			foreach($details as $detail) {
				if($detail->taking_id != $exam->ID) continue; 
				$taken_exams[$cnt]->details[] = $detail;
				$taken_exams[$cnt]->num_problems++;
				if(!in_array($detail->cat_id, $taken_exams[$cnt]->skills_practiced)) $taken_exams[$cnt]->skills_practiced[] = $detail->cat_id; 
			}
			
			// calculate start time			
			list($date, $time) = explode(" ", $exam->start_time);
			$date = explode("-",$date);
			$time = explode(":", $time);			
			$start_time = mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);
			$taken_exams[$cnt]->start_time = $start_time;
			
			// fill the period property for later use (month, year)
			$taken_exams[$cnt]->period = date('F', $start_time)." ".$date[0];			
			$taken_exams[$cnt]->period_morris = date("Y-m", $start_time); 
			
			// calculate end time
			list($date, $time) = explode(" ", $exam->end_time);
			$date = explode("-",$date);
			$time = explode(":", $time);			
			$end_time = mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);
			$taken_exams[$cnt]->end_time = $end_time;
			
			$time_spent = ($end_time - $start_time) ? ($end_time - $start_time) : 0;
			
			$taken_exams[$cnt]->time_spent = $time_spent;
			$total_time += $time_spent;
			
			$total_problems += $taken_exams[$cnt]->num_problems;
			
			// num skills
			$taken_exams[$cnt]->num_skills = sizeof($taken_exams[$cnt]->skills_practiced);
			$total_skills += $taken_exams[$cnt]->num_skills;
		}	
		
		// summary calculations
		$total_sessions = sizeof($taken_exams);
		$avg_time_spent = $total_sessions? ($total_time / $total_sessions) : 0;
		$avg_problems = round($total_sessions? ($total_problems / $total_sessions) : 0);
		$avg_skills = round($total_sessions? ($total_skills / $total_sessions) : 0);
		
		// group takings by month/year for the chart and table
		$periods = array();
		foreach($taken_exams as $exam) {
			if(!in_array($exam->period, $periods)) $periods[] = $exam->period;
		}
		
		// now fill logs array which is actually periods with exams in them
		$logs = array();
		$max_exams = 0; // max exams in a period, so we can build the chart
		foreach($periods as $period) {
			 $period_exams = array();
			 $time_spent = 0;			 
			 foreach($taken_exams as $exam) {
			 		if($exam->period != $period) continue; 
			 		$period_exams[] = $exam;
			 		$time_spent += $exam->time_spent;
			 }
			 
			 $num_exams = sizeof($period_exams);
			 if($num_exams > $max_exams) $max_exams = $num_exams;
			 $logs[] = array("period"=>$period, "exams"=>$period_exams, "time_spent"=>$time_spent, 
			 		"num_exams"=> $num_exams);
		}
		
		// for the char we need reversed logs and no more than 12		
		$chartlogs = array_reverse($logs);
		if(sizeof($chartlogs)>12) $chartlogs = array_slice($chartlogs, sizeof($chartlogs) - 12);
		
		// let's keep the chart up to 200px high. Find height in px for 1 exam in chart
		$one_exam_height = $max_exams ? (200 / $max_exams) : 0;
		
		$date_format = get_option("date_format");
				 	
		require(WATUPRO_PATH."/modules/reports/views/history.php");		 	
	}
	
	// helper to calculate time spent in exam
	static function time_spent($exam) {
		list($date, $time) = explode(" ", $exam->start_time);
		list($y, $m, $d) = explode("-", $date);
		list($h, $min, $s) = explode(":", $time);		 		
 		$start_time = mktime($h, $min, $s, $m, $d, $y);
 		
 		list($date, $time) = explode(" ", $exam->end_time);
 		list($y, $m, $d) = explode("-", $date);
 		list($h, $min, $s) = explode(":", $time);
 		$end_time = mktime($h, $min, $s, $m, $d, $y);
 		
 		$diff = $end_time - $start_time;
 		
 		if($diff < 0) $diff = 0;
 		
 		return $diff;
	} 
	
	static function time_spent_human($time_spent) {
		$time_spent = ($time_spent > 60) ? gmdate("H:i", $time_spent) : gmdate("H:i:s", $time_spent);
		return $time_spent;
	}	
	
	// register javascripts
	static function register_scripts() {
		wp_register_script('raphael', plugins_url('watupro/modules/reports/js/raphael-min.js'), null, '1.0', true);
		wp_register_script('g.raphael', plugins_url('watupro/modules/reports/js/g.raphael-min.js'), null, '1.0', true);
		wp_register_script('g.bar', plugins_url('watupro/modules/reports/js/g.bar-min.js'), null, '1.0', true);
		wp_register_script('g.line', plugins_url('watupro/modules/reports/js/g.line-min.js'), null, '1.0', true);
		wp_register_script('g.pie', plugins_url('watupro/modules/reports/js/g.pie-min.js'), null, '1.0', true);
		wp_register_script('g.dot', plugins_url('watupro/modules/reports/js/g.dot-min.js'), null, '1.0', true);
	}
	
	static function print_scripts() {		
		if ( ! self::$add_scripts ) return false; 
		wp_print_scripts('raphael');
		wp_print_scripts('g.raphael');
		wp_print_scripts('g.bar');
		wp_print_scripts('g.line');
		wp_print_scripts('g.pie');
		wp_print_scripts('g.dot');
	}
}