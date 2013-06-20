<?php
// functions that manage the users.php page in admin and maybe more
class WTPReportsUser {
	function add_status_column($columns) {	
		$columns['exam_reports'] = __('Exam Reports', 'watupro');
	 	return $columns;	
	}
}