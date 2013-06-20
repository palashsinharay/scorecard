<?php
function watupro_export_questions() {
	global $wpdb;
	$newline=watupro_define_newline();
	$questions_table=$wpdb->prefix."watupro_question";
	$answers_table=$wpdb->prefix."watupro_answer";
	$cats_table=$wpdb->prefix."watupro_qcats";
	
	// select questions
	$questions=$wpdb->get_results($wpdb->prepare("SELECT tQ.*, tC.name as category 
		FROM $questions_table tQ LEFT JOIN $cats_table tC ON tC.ID=tQ.cat_id 
		WHERE tQ.exam_id=%d ORDER BY tQ.sort_order, tQ.ID", $_GET['exam_id']), ARRAY_A);
		
		
	$qids=array(0);
	foreach($questions as $question) $qids[]=$question['ID'];
	$qid_sql=implode(",", $qids);
		
	// select all answers in the exam
	$answers=$wpdb->get_results("SELECT * FROM $answers_table WHERE question_id IN ($qid_sql)");
	
	// match answers to questions
	foreach($questions as $cnt=>$question) {
		$questions[$cnt]['answers']=array();
		foreach($answers as $answer) {
			if($answer->question_id==$question['ID']) $questions[$cnt]['answers'][]=$answer;
		}
	}
	
	// run last query to define the max number of answers
	$num_ans=$wpdb->get_row("SELECT COUNT(ID) as num_answers FROM $answers_table WHERE question_id IN ($qid_sql)
			GROUP BY question_id ORDER BY num_answers DESC");
			
	$rows=array();
	
	if(empty($_GET['copy'])) {
		$titlerow="Question ID\tQuestion\tAnswer Type\tOrder\tCategory\tExplanation/Feedback\tRequired?";
		for($i=1;$i<=$num_ans->num_answers;$i++) $titlerow.="\tAnswer ID\tAnswer\tPoints";
	}
	else {
		$titlerow="Question;Answer Type\tOrder\tCategory\tExplanation/Feedback\tRequired?";
		for($i=1;$i<=$num_ans->num_answers;$i++) $titlerow.="\tAnswer\tPoints";
	}		
	
	$rows[]=$titlerow;
	
	foreach($questions as $question) {
		$row = "";
		if(empty($_GET['copy'])) $row .= $question['ID']."\t";
		$row .= '"'.stripslashes($question['question']).'"'."\t".$question['answer_type']."\t".$question['sort_order'].
			"\t".$question['category']."\t".stripslashes($question['explain_answer'])."\t".$question['is_required'];
			
		foreach($question['answers'] as $answer) {
			if(empty($_GET['copy'])) $row .= "\t".$answer->ID;
			$row .= "\t".'"'.stripslashes($answer->answer).'"'."\t".$answer->point;
		}		
		
		$rows[]=$row;
	}
	
	$csv=implode($newline,$rows);
	
	// credit to http://yoast.com/wordpress/users-to-csv/	
	$now = gmdate('D, d M Y H:i:s') . ' GMT';
	
	if(empty($_GET['copy'])) $filename = 'exam-'.$_GET['exam_id'].'-questions-edit.csv';
	else $filename = 'exam-'.$_GET['exam_id'].'-questions.csv';

	header('Content-Type: ' . watupro_get_mime_type());
	header('Expires: ' . $now);
	header('Content-Disposition: attachment; filename="'.$filename.'"');
	header('Pragma: no-cache');
	echo $csv;
	exit;
}


function watupro_import_questions() {
	global $wpdb;
	$questions_table=$wpdb->prefix."watupro_question";
	$answers_table=$wpdb->prefix."watupro_answer";
	$cats_table=$wpdb->prefix."watupro_qcats";	
	
	$row = 0;
	ini_set("auto_detect_line_endings", true);
	if (($handle = fopen($_FILES['csv']['tmp_name'], "r")) !== FALSE) {
		
		 // select all current questions and answers in the exam. it's required to make fast checks
		 // if a given ID exists or not		
		 $questions=$wpdb->get_results($wpdb->prepare("SELECT * FROM $questions_table WHERE exam_id=%d", $_GET['quiz']));
		 $qids=array(0);
		 foreach($questions as $question) $qids[]=$question->ID;
		 $qid_sql=implode(",", $qids);
			
		 // select all answers in the exam
		 $answers=$wpdb->get_results("SELECT * FROM $answers_table WHERE question_id IN ($qid_sql)");
		 		
		 // select all categories so we can see if given one exists or not
		 $cats=$wpdb->get_results("SELECT * FROM $cats_table");
		 		
		 $delimiter=$_POST['delimiter'];
		 if($delimiter=="tab") $delimiter="\t";		
	    while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {	    	  
	    	  $row++;	
	        if(empty($data) or empty($data[0])) continue;
	        
	        if($_POST['file_type']=='new') {
						$cat_id=WTPCategory::discover($data[3], $cats);		  	
								  	
			  		// only new questions and answers
			  		$qid=WTPQuestion::add(array("content"=>$data[0], "answer_type"=>$data[1], "sort_order"=>$data[2],
			  				"cat_id"=>$cat_id, "explain_answer"=>$data[4], "is_required"=>$data[5], "quiz"=>$_GET['quiz']));
			  					  		
			  		// extract answers
			  		$data=array_slice($data, 6);
			  		$answers=array();
			  		$step=1;
			  		foreach($data as $cnt=>$d)
			  		{			  			
			  			if($step==1)
			  			{
			  				$answer=array();
			  				$answer['answer']=$d;
			  				$answer['is_correct']=0;
			  				$step=2;
			  			}
			  			else 
			  			{
			  				$answer['points']=$d;
			  				$step=1;
			  				$answers[]=$answer;
			  			}
			  		}		
			  		
			  		// now we have the answers in the array, let's identify which one is correct
					$top_points=0;
					foreach($answers as $answer)
					{
						if($answer['points']>$top_points) $top_points=$answer['points'];
					}		
					// once again
					foreach($answers as $cnt=>$answer) {
						if($answer['points']==$top_points) 
						{
							$answers[$cnt]['is_correct']=1;
							break;
						}
					}	  
					
					// finally insert them	
					$vals=array();
					foreach($answers as $cnt=>$answer)
					{
						if($answer['answer']==='') continue;
						$cnt++;
						$vals[]=$wpdb->prepare("(%d, %s, %s, %s, %d)", $qid, $answer['answer'], 
							$answer['is_correct'], $answer['points'], $cnt);
					}
					$values_sql=implode(",",$vals);
					
					if(sizeof($answers)) { $wpdb->query("INSERT INTO $answers_table (question_id,answer,correct,point, sort_order) 
						VALUES $values_sql"); }
			  }			   
			  else {
			  		// for old files import	
			  		if($row==1 or empty($data[1])) continue; // skip first line
			  		$cat_id=WTPCategory::discover($data[4], $cats);
			  					  		
			  		if(empty($data[0]))
			  		{
			  			$qid=WTPQuestion::add(array("content"=>$data[1], "answer_type"=>$data[2], "sort_order"=>$data[3],
			  				"cat_id"=>$cat_id, "explain_answer"=>$data[5], "is_required"=>$data[6], 
			  				"quiz"=>$_GET['quiz']));			  		
			  		}
			  		else 
			  		{
			  			$wpdb->query($wpdb->prepare("UPDATE $questions_table SET question=%s, answer_type=%s,
			  				sort_order=%d, cat_id=%d, explain_answer=%s, is_required=%d
			  				WHERE ID=%d", $data[1], $data[2],
			  					$data[3], $cat_id, $data[5], $data[6], $data[0]));
			  			$qid=$data[0];
			  		}
			  		
			  		// now answers, first extract them similar to the "new file" option
			  		$data=array_slice($data, 7);
			  		$answers=array();
			  		$step=1;

			  		foreach($data as $cnt=>$d) {			  			
			  			switch($step) {
			  				case 1:
			  					$answer=array();
			  					$answer['id']=$d;
			  					$step=2;
			  				break;
			  				case 2:			  					
				  				$answer['answer']=$d;			  			
				  				$step=3;
			  				break;
			  				case 3:
			  					$answer['points']=$d;
			  					$step=1;
			  					$answers[]=$answer;
			  				break;
			  			}			  			
			  		}
			  		
			  		// now insert or update
			  		foreach($answers as $cnt=>$answer) {
			  			if($answer['answer']==='') continue;
			  			$cnt++;
						
						// assume 1st is correct
						if($cnt==1) $correct=1;
						else $correct=0;			  			
			  			
			  			if($answer['id']) {
			  				$wpdb->query($wpdb->prepare("UPDATE $answers_table SET answer=%s, point=%d WHERE ID=%d",
			  					$answer['answer'], $answer['points'], $answer['id']));
			  			}
			  			else 
			  			{
			  				$wpdb->query($wpdb->prepare("INSERT INTO $answers_table 
			  						(question_id,answer,correct,point, sort_order) VALUES (%d, %s, %s, %s, %d) ",
			  						$qid, $answer['answer'], $correct, $answer['points'], $cnt));
			  			}
			  		}	
			  }
	    }
	    fclose($handle);
	}	
	
	wp_redirect("admin.php?page=watupro_questions&quiz=$_GET[quiz]");
}