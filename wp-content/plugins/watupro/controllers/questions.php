<?php
// add/edit question - replaces the old question.php and question_form.php
function watupro_question() {
	global $wpdb;
	
	$action = 'new';
	if($_REQUEST['action'] == 'edit') $action = 'edit';
	
	$question= $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_question WHERE ID=%d", $_REQUEST['question']));
	$all_answers = $wpdb->get_results($wpdb->prepare("SELECT answer, correct, point FROM {$wpdb->prefix}watupro_answer WHERE question_id=%d ORDER BY sort_order", $_REQUEST['question']));
	$ans_type = ($action =='new') ? get_option('watupro_answer_type'): $question->answer_type;
	$answer_count = 4;
	if($action == 'edit' and $answer_count < count($all_answers)) $answer_count = count($all_answers) ;
	
	// select question categories
	$qcats=$wpdb->get_results("SELECT * FROM ".WATUPRO_QCATS." ORDER BY name");
	
	require(WATUPRO_PATH."/views/question_form.php");
}

function watupro_questions() {
	global $wpdb;
	
	if(!empty($_GET['export'])) watupro_export_questions();
	if(!empty($_POST['import'])) watupro_import_questions();
	
	$action = 'new';
	if($_REQUEST['action'] == 'edit') $action = 'edit';
	
	if(isset($_POST['submit'])) {
		// add new category?
		if(!empty($_POST['new_cat'])) {
			$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}watupro_qcats (name) VALUES (%s) ", $_POST['new_cat']));
			$_REQUEST['cat_id']=$wpdb->insert_id;
		}	
		
		if($action == 'edit') { 
			WTPQuestion::edit($_REQUEST, $_REQUEST['question']);			
		} 
		else  {
			$_REQUEST['question'] = WTPQuestion::add($_REQUEST);
			$action='edit';
		}
		
		// adding answers
		$question_id = $_REQUEST['question'];
		if($question_id>0) {
			// the $counter will skip over empty answers, $sort_order_counter will track the provided answers order.
			$counter = 1;
			$sort_order_counter = 1;
			$correctArry = $_REQUEST['correct_answer'];
			$pointArry = $_REQUEST['point'];
			foreach ($_REQUEST['answer'] as $key => $answer_text) {
				$correct=0;
				if( @in_array($counter, $correctArry) ) $correct=1;
				$point = $pointArry[$key];
				if($answer_text!=="") {
					if(empty($point)) $point = 0;
					$wpdb->query($wpdb->prepare("INSERT INTO ".WATUPRO_ANSWERS." (question_id,answer,correct,point, sort_order)
						VALUES(%d, %s, %s, %s, %d)", $question_id, $answer_text, $correct, $point, $sort_order_counter));
					$sort_order_counter++;
				}
				$counter++;
			}
		}
	}
	
	// delete question
	if($_REQUEST['action'] == 'delete') {
		$wpdb->query($wpdb->prepare("DELETE FROM ".WATUPRO_ANSWERS." WHERE question_id=%d", $_REQUEST['question']));
		$wpdb->query($wpdb->prepare("DELETE FROM ".WATUPRO_QUESTIONS." WHERE ID=%d", $_REQUEST['question']));	
	}
	
	// select exam
	$exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_EXAMS." WHERE ID=%d", $_GET['quiz']));
	$exam_name = stripslashes($exam->name);

	// reorder questions
	if(!empty($_GET['move'])) {
		WTPQuestion::reorder($_GET['move'], $_GET['quiz'], $_GET['dir']);
	}
	
	// filter by category SQL
	$filter_sql = "";
	if(!empty($_POST['filter_cat_id'])) {
		 if($_POST['filter_cat_id']==-1) $filter_sql = " AND Q.cat_id = 0 ";
		 else $filter_sql = $wpdb->prepare(" AND Q.cat_id = %d ", $_POST['filter_cat_id']);
	}
	
	// Retrieve the questions
	$all_question = $wpdb->get_results($wpdb->prepare("SELECT Q.ID,Q.question, C.name as cat,
		(SELECT COUNT(*) FROM ".WATUPRO_ANSWERS." WHERE question_id=Q.ID) AS answer_count
				FROM `".WATUPRO_QUESTIONS."` AS Q
				LEFT JOIN ".WATUPRO_QCATS." AS C ON C.ID=Q.cat_id 
				WHERE Q.exam_id=%d $filter_sql ORDER BY Q.sort_order, Q.ID", $_GET['quiz']));
	$num_questions=sizeof($all_question);			
	
	if(empty($filter_sql)) WTPQuestion::fix_sort_order($all_question);
	
	// select question categories
	$qcats = $wpdb -> get_results("SELECT * FROM ".WATUPRO_QCATS." ORDER BY name");
				
	require(WATUPRO_PATH."/views/questions.php");
}

// manage question categories
function watupro_question_cats() {
	global $wpdb;
	
	$error = false;
	
	if(!empty($_POST['add'])) {
		if(!WTPCategory::add($_POST['name'], $_POST['description'])) $error = __('Another category with this name already exists.', 'watupro');
	}
	
	if(!empty($_POST['save'])) {
		if(!WTPCategory::save($_POST['name'], $_POST['id'], $_POST['description'])) $error = __('Another category with this name already exists.', 'watupro');
	}
	
	if(!empty($_POST['del'])) {
		WTPCategory::delete($_POST['id']);
	}
	
	// select all question categories	
	$cats = $wpdb->get_results("SELECT * FROM ".WATUPRO_QCATS." ORDER BY ID");	
	
	require(WATUPRO_PATH."/views/question_cats.php");
}