<?php 
class WTPGrade {
	// calculate grade
	static function calculate($exam_id, $achieved, $percent, $cat_id = 0) {
		global $wpdb;		
		
		$grade = __('None', 'watupro');
		$grade_obj = (object)array("title"=>__('None', 'watupro'), "description"=>"");
		$do_redirect = false;
		$certificate_id=0;
		$allGrades = $wpdb->get_results(" SELECT * FROM `".WATUPRO_GRADES."` WHERE exam_id=$exam_id AND cat_id=$cat_id ");
		
		// for the sake of grade calculation, $achieved won't be below zero
		if($achieved < 0 ) $achieved = 0; 
		
		if( count($allGrades) ){
			
			// calculate by percentage in Intelligence
			if(watupro_intel()) {
				$exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_EXAMS." WHERE ID=%d", $exam_id));
			}			
			
			foreach($allGrades as $grow ) { 
				$match_criteria = $achieved;
				   
				// from Intelligence - calculate by %   
				if(!empty($exam->grades_by_percent)) $match_criteria = $percent;
			
				if( $grow->gfrom <= $match_criteria and $match_criteria <= $grow->gto ) {
					$grade_obj = $grow;
					
					$grade = $grow->gtitle;
					
					// redirect?
					if(preg_match("/^http:\/\//i", $grade) or preg_match("/^https:\/\//i", $grade)) {
						$do_redirect = $grade;
					}				
					
		      $certificate_id=$grow->certificate_id;
					if(!empty($grow->gdescription)) $grade.="<p>".stripslashes($grow->gdescription)."</p>";     
					               
					break;
				}
			}
		}
		
		return array($grade, $certificate_id, $do_redirect, $grade_obj);
	}
	
	// if %%CATGRADES%% is used, this calculates and replaces them on the final screen
	static function replace_category_grades($final_screen, $taking_id, $exam_id) {
		global $wpdb;
		
		if(!strstr($final_screen, '%%CATGRADES%%')) return false;
		
		$exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_EXAMS." WHERE ID=%d", $exam_id));
		
		if(empty($exam->gradecat_design)) return false; // no need to go further if gradecat design is not created
		
		$catgrades = "";
		
		// select the student_answers details of this taking and group by category
		$answers = $wpdb->get_results( $wpdb->prepare("SELECT tA.*, tQ.cat_id as cat_id 
			FROM ".WATUPRO_STUDENT_ANSWERS." tA JOIN ".WATUPRO_QUESTIONS." tQ ON tA.question_id=tQ.ID 
			WHERE tA.taking_id=%d", $taking_id) ); 
		$cat_ids = array(0);
		foreach($answers as $answer) {
			if(!in_array($answer->cat_id, $cat_ids)) $cat_ids[] = $answer->cat_id;
		}	
		
		// now select the categories
		$cats = $wpdb -> get_results("SELECT * FROM ".WATUPRO_QCATS." WHERE ID IN (".implode(",", $cat_ids).") ORDER BY name");
		
		// for each category calculate the grade and add to $catgrades
		foreach($cats as $cat) {
			$total = $correct = $percentage = $points = 0;
			$catgrade = $exam->gradecat_design;
			
			foreach($answers as $answer) {
				if($answer->cat_id != $cat->ID) continue;
				$total ++;
				if($answer->is_correct) $correct++;
				$points += $answer->points;
			}
			
			// percentage and grade
			$percent = $total ? round($correct / $total, 2) * 100 : 0;
			list($grade, $certificate_id, $do_redirect, $grade_obj) = self::calculate($exam_id, $points, $percent, $cat->ID);
			
			// now replace in the $catgrade text
			$catgrade = str_replace("%%CATEGORY%%", $cat->name, $catgrade);
			$catgrade = str_replace("%%CORRECT%%", $correct, $catgrade);
			$catgrade = str_replace("%%TOTAL%%", $total, $catgrade);
			$catgrade = str_replace("%%POINTS%%", $points, $catgrade);
			$catgrade = str_replace("%%PERCENTAGE%%", $percent, $catgrade);
			$catgrade = str_replace("%%GTITLE%%", $grade_obj->gtitle, $catgrade);
			$catgrade = str_replace("%%GDESC%%", $grade_obj->gdescrition, $catgrade);
			
			// add to $catgrades
			$catgrades .= $catgrade;
		}
		
		return $catgrades;
	}
}