<?php
class WatuPRODependency
{
	// store dependencies on a given exam
	static function store($exam_id)
	{
		global $wpdb;
		
		// delete old dependencies if there are any to delete
		if(!empty($_POST['del_dependencies']))
		{
			$wpdb->query("DELETE FROM {$wpdb->prefix}watupro_dependencies WHERE ID IN (".$_POST['del_dependencies'].")");
		}
		
		// select remaining old and update them ($_POST vars will have names postfixed with _id)
		$dependencies = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_dependencies
			WHERE exam_id=%d", $exam_id));
		
		foreach($dependencies as $dependency)
		{
			$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}watupro_dependencies SET
			depend_exam=%d, depend_points=%d WHERE ID=%d", $_POST['dependency'.$dependency->ID], 
				$_POST['depend_points'.$dependency->ID], $dependency->ID));  	
		}	
		
		
		// add new dependencies if any
		if(!empty($_POST['dependencies']) and is_array($_POST['dependencies']))
		{
			foreach($_POST['dependencies'] as $cnt => $dependency)
			{
				// skip 1st because this is the "Add new dependency" row that shouldn't be added
				if($cnt==0) continue;				
				
				$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}watupro_dependencies SET
					exam_id=%d, depend_exam=%d, depend_points=%d", $exam_id, $dependency, $_POST['depend_points'][$cnt]));
			}
		}
	}
	
	// select existing dependencies
	static function select($exam_id)
	{
		global $wpdb;
		
		$dependencies = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_dependencies 
			WHERE exam_id=%d ORDER BY ID", $exam_id));
			
		return $dependencies;	
	}
	
	// check dependencies on exam
	static function check($exam) {
		global $wpdb, $user_ID;
		
		// make sure exam requires login, otherwise just return true		
		if(!$exam->require_login) return false;
		
		// if user is admin return true        
		if(current_user_can('WATUPRO_MANAGE_CAPS')) return true;		
		
		// now check if there are any dependencies, if not - return true
		$dependencies = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_dependencies 
			WHERE exam_id=%d ORDER BY ID", $exam->ID));
		if(!sizeof($dependencies)) return true;	
		
		// if there are unsatisfied dependencies return false 
		// 1. select takings of this person
		$takings=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_taken_exams
	WHERE user_id=%d AND in_progress=0 ORDER BY ID DESC", $user_ID));
		
		// no takings but yes dependencies? return false
		if(!sizeof($takings)) return false;
		
		// finally, let's check
		foreach($dependencies as $dependency)
		{
			$satisfied = false;
			foreach($takings as $taking)
			{
				if($taking->exam_id == $dependency->depend_exam and $taking->points >= $dependency->depend_points) $satisfied = true;
			}
			
			// if satisfied still false no need fo check further
			if(!$satisfied) return false;
		} 
		
		return true;		
	}
	
	// calculate dependencies on a list of exams to display "Locked" message for these that
	// need to be taken before this one.
	static function mark($exams, $takings) {
		global $wpdb;
		
		// select all dependencies if any
		$dependencies = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}watupro_dependencies ORDER BY ID");
		
		// now for each exam check if there are dependencies and if even one is not satisfied, made locked
		foreach($exams as $cnt => $exam) {
			$locked = false;
			
			foreach($dependencies as $dependency) {
				if($dependency->exam_id != $exam->ID) continue;
				
				// we have dependence, we set locked = true
				// now let's check if it's satisfied by loop through $takings 
				// if yes, unlock				
				$locked = true;
				foreach($takings as $taking) {
					// satisfying taking found, unlock
					if($taking->exam_id == $dependency->depend_exam and $taking->points >= $dependency->depend_points) $locked = false;
				}					
			}
			
			$exams[$cnt]->locked = $locked;
		}
		
		return $exams;
	}
	
	// shows details on specific locked exam
	static function lock_details()
	{
		global $wpdb, $user_ID;
		
		$dependencies = $wpdb->get_results($wpdb->prepare("SELECT tE.name as exam, tE.final_screen as final_screen, tD.* 
				FROM {$wpdb->prefix}watupro_dependencies tD JOIN {$wpdb->prefix}watupro_master tE
				ON tD.depend_exam = tE.ID WHERE exam_id=%d
				ORDER BY tD.ID", $_REQUEST['exam_id']));
				
		// get my takings and figure out dependency status
		$takings=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_taken_exams
				WHERE user_id=%d AND in_progress=0 ORDER BY ID DESC", $user_ID));	
				
		foreach($dependencies as $cnt=>$dependency)
		{
			$satisfied = false;
			foreach($takings as $taking)
			{
				if($taking->exam_id == $dependency->depend_exam and $taking->points >= $dependency->depend_points) $satisfied = true;
			}
			
			$dependencies[$cnt]->satisfied = $satisfied;
		}		
		
		require(WATUPRO_PATH."/i/views/lock_details.php");
		exit;
	}
}