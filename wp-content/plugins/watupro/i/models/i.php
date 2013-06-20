<?php
class WatuPROIntelligence {
	static function activate() {
		 // DB queries that will run only if Intelligence module is installed
		 global $wpdb;
		 
		 // extra fields in questions			 
		 watupro_add_db_fields( array( array("name" => "correct_gap_points", "type" => "DECIMAL(6,2) NOT NULL DEFAULT '0.00'"),
		 	array("name" => "incorrect_gap_points", "type" => "DECIMAL(6,2) NOT NULL DEFAULT '0.00'") ), 
		 		WATUPRO_QUESTIONS);
		 	
		 // extra fields in exams - 3.1
		 watupro_add_db_fields( array( array("name" => "retake_after", "type" => "INT UNSIGNED NOT NULL DEFAULT 0"),
		 	array("name" => "reuse_questions_from", "type" => "INT UNSIGNED NOT NULL DEFAULT 0")),
		 	WATUPRO_EXAMS );	
		 	
		 // extra field - teacher comments in taking and taking details
		 watupro_add_db_fields(array(
    		array("name"=>"teacher_comments", "type"=>"TEXT")
			), WATUPRO_TAKEN_EXAMS);	
			
		 watupro_add_db_fields(array(
    		array("name"=>"teacher_comments", "type"=>"TEXT")
			), WATUPRO_STUDENT_ANSWERS);		
	}
	
	static function admin_menu() {
		add_submenu_page(NULL, __("Manually Grade Test Results", 'watupro'), __("Manually Grade Test Results", 'watupro'), WATUPRO_MANAGE_CAPS, 'watupro_edit_taking', array('WatuPROITeacherController', 'edit_taking'));
	}
	
	// small helper to add extra DB fields if they don't exist
	// DEPRECATED? probably we should use watupro_add_db_fields() instead
	static function add_db_fields($fields, $table) {
		global $wpdb;
		
		// check fields
		$table_fields = $wpdb->get_results("SHOW COLUMNS FROM `$table`");
		$table_field_names = array();
		foreach($table_fields as $f) $table_field_names[] = $f->Field;		
		$fields_to_add=array();
		
		foreach($fields as $field) {
			 if(!in_array($field['name'], $table_field_names)) {
			 	  $fields_to_add[] = $field;
			 } 
		}
		
		// now if there are fields to add, run the query
		if(!empty($fields_to_add)) {
			 $sql = "ALTER TABLE `$table` ";
			 
			 foreach($fields_to_add as $cnt => $field) {
			 	 if($cnt > 0) $sql .= ", ";
			 	 $sql .= "ADD $field[name] $field[type]";
			 } 
			 
			 $wpdb->query($sql);
		}
	}
}