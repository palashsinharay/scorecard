<?php
function watupro_options() {
    global $wpdb;
		
		if(isset($_REQUEST['submit']) and $_REQUEST['submit']) {
			
			$options = array('show_answers', 'single_page', 'answer_type', 'delete_db', 
				'paypal', 'other_payments', 'currency', 'recaptcha_public', 'recaptcha_private');
			foreach($options as $opt) {
				if(!empty($_POST[$opt])) update_option('watupro_' . $opt, $_POST[$opt]);
				else update_option('watupro_' . $opt, 0);
			}
			
			// add/remove capabilities
			if(current_user_can('manage_options')) {
				$roles=array("editor", "author","contributor","subscriber");
				foreach($roles as $role) {
					$r=get_role($role);
					
					// use roles - DEPRECATED
					if(!empty($r->capabilities['watupro_exams'])) {
						if(!@in_array($role, $_POST['roles'])) $r->remove_cap("watupro_exams");
					}
					else {
						if(@in_array($role, $_POST['roles'])) $r->add_cap("watupro_exams");
					}
					
					// manage roles
					if(!empty($r->capabilities['watupro_manage_exams'])) {
						if(!@in_array($role, $_POST['manage_roles'])) $r->remove_cap("watupro_manage_exams");
					}
					else {
						if(@in_array($role, $_POST['manage_roles'])) $r->add_cap("watupro_manage_exams");
					}
				}	
			} // end if administrator	
		}
		$answer_display = get_option('watupro_show_answers');

		if(watupro_intel()) {
			$currency = get_option('watupro_currency');
			$currencies=array('USD'=>'$', "EUR"=>"&euro;", "GBP"=>"&pound;", "JPY"=>"&yen;", "AUD"=>"AUD",
		   "CAD"=>"CAD", "CHF"=>"CHF", "CZK"=>"CZK", "DKK"=>"DKK", "HKD"=>"HKD", "HUF"=>"HUF",
		   "ILS"=>"ILS", "MXN"=>"MXN", "NOK"=>"NOK", "NZD"=>"NZD", "PLN"=>"PLN", "SEK"=>"SEK",
		   "SGD"=>"SGD");
		}
		
			
		// get the 4 default roles
		$author_role=get_role("author");
		$editor_role=get_role("editor");
		$contributor_role=get_role("contributor");
		$subscriber_role=get_role("subscriber");
		
		// exams in watu light?
		if($wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix. "watu_master"."'") == $wpdb->prefix. "watu_master") {	
			$watu_exams=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix. "watu_master ORDER BY ID");
			
			if(!empty($_POST['copy_exams']))
			{
				$num_copied=0;
				foreach($watu_exams as $exam)
				{
					$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."watupro_master SET 
						name=%s, description=%s, final_screen=%s, added_on=%s, is_active=1,
						show_answers=%d", stripslashes($exam->name), stripslashes($exam->description), 
						stripslashes($exam->final_screen), date("Y-m-d"), $answer_display));
						
					$id=$wpdb->insert_id;
					
					if($id)
					{
						$num_copied++;
						
						// copy questions and choices
						$questions=$wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."watu_question 
							WHERE exam_id=%d ORDER BY ID", $exam->ID));
						foreach($questions as $question)
						{
							$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."watupro_question SET
								exam_id=%d, question=%s, answer_type=%s, sort_order=%d", 
								$id, stripslashes($question->question), stripslashes($question->answer_type), $question->sort_order));
							$qid=$wpdb->insert_id;
							
							if($qid)
							{
								$choices=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watu_answer 
									WHERE question_id=%d ORDER BY ID", $question->ID));
								foreach($choices as $choice)
								{
									$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}watupro_answer SET
										question_id=%d, answer=%s, correct=%s, point=%d, sort_order=%d",
										$qid, stripslashes($choice->answer), $choice->correct, $choice->point, $choice->sort_order));
								}	
							}	
						}				
						
						// copy grades
						$grades=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watu_grading WHERE exam_id=%d ORDER BY ID", $exam->ID));
						
						foreach($grades as $grade)
						{
							$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}watupro_grading SET
								exam_id=%d, gtitle=%s, gdescription=%s, gfrom=%d, gto=%d",  
								$id, stripslashes($grade->gtitle), stripslashes($grade->gdescription), $grade->gfrom, $grade->gto));
						} // end foreach grade
					} // end if exam $id	
				} // end foreach exam
		
				$copy_message="$num_copied Exams successfully copied.";		
				
			} // end if copy exams
		} // end if there is watu table
		
		require(WATUPRO_PATH."/views/options.php");
}