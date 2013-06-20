<?php
// manage grades 
function watupro_grades() {
	global $wpdb;

	// change the common gradecat design	
	if(!empty($_POST['save_design'])) {
		$wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_EXAMS." SET gradecat_design=%s WHERE id=%d", $_POST['gradecat_design'], $_GET['quiz']));
	}
	
	// select this exam
	$exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_EXAMS." WHERE ID=%d", $_GET['quiz']));
	
	// need to assign default gradecat design?
	if(empty($exam->gradecat_design)) {
		$gradecat_design="<p>".__('For category <strong>%%CATEGORY%%</strong> you got grade <strong>%%GTITLE%%</strong>.', 'watupro')."</p>
		<p>%%GDESC%%</p><hr>";
		
		$wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_EXAMS." SET gradecat_design=%s WHERE id=%d", $gradecat_design, $exam->ID));
		
		$exam->gradecat_design = $gradecat_design;
	}
	
	// select question categories
	$cats = $wpdb->get_results("SELECT * FROM ".WATUPRO_QCATS." ORDER BY name"); 
	
	if(!empty($_POST['add'])) {
		$wpdb->query($wpdb->prepare("INSERT INTO ".WATUPRO_GRADES." SET
			exam_id=%d, gtitle=%s, gdescription=%s, gfrom=%d, gto=%d, certificate_id=%d, cat_id=%d",
			$exam->ID, $_POST['gtitle'], $_POST['gdescription'], $_POST['gfrom'], $_POST['gto'], $_POST['certificate_id'], $_POST['cat_id']));
	}
	
	if(!empty($_POST['del'])) {
		$wpdb->query($wpdb->prepare("DELETE FROM ".WATUPRO_GRADES." WHERE ID=%d", $_POST['id']));
	}
	
	if(!empty($_POST['save'])) {
		$wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_GRADES." SET
			gtitle=%s, gdescription=%s, gfrom=%d, gto=%d, certificate_id=%d
			WHERE ID=%d",
			$_POST['gtitle'], $_POST['gdescription'.$_POST['id']], $_POST['gfrom'], $_POST['gto'], 
			$_POST['certificate_id'], $_POST['id']));
	}
	
	$cat_id = empty($_POST['cat_id'])?0:$_POST['cat_id'];
	
	// select all grades of the selected category
	$grades = $wpdb->get_results( $wpdb->prepare("SELECT * FROM ".WATUPRO_GRADES." WHERE exam_id=%d AND cat_id=%d", 
		$exam->ID, $cat_id) );
	
	// for the moment certificates will be used only on non-category grades	
	if(!$cat_id) {	
		// select certificates if any
		$certificates=$wpdb->get_results("SELECT * FROM ".WATUPRO_CERTIFICATES." ORDER BY title");
		$cnt_certificates=sizeof($certificates);
	}	
	
	require(WATUPRO_PATH."/views/grades.php");
}