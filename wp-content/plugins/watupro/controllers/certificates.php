<?php
function watupro_certificates() {
	global $wpdb;
	wp_enqueue_style('style.css', plugins_url('/watupro/style.css'), null, '1.0');
	
	switch(@$_GET['do']) {
		case 'add':
			if(!empty($_POST['ok'])) {
				$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}watupro_certificates (title, html)
					VALUES (%s, %s)", $_POST['title'], $_POST['html']));
				echo "<meta http-equiv='refresh' content='0;url=admin.php?page=watupro_certificates' />"; 
				exit;
			}
		
			require(WATUPRO_PATH. '/views/certificate.php');   
		break;
	
		case 'edit':
			if(!empty($_POST['del'])) {
	           $wpdb->query($wpdb->prepare("DELETE FROM 
					{$wpdb->prefix}watupro_certificates WHERE ID=%d", $_GET['id']));
	
				echo "<meta http-equiv='refresh' content='0;url=admin.php?page=watupro_certificates' />"; 
				exit;
			}
	
			if(!empty($_POST['ok'])) {
				$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}watupro_certificates SET
					title=%s, html=%s WHERE ID=%d", $_POST['title'], $_POST['html'], $_GET['id']));
				echo "<meta http-equiv='refresh' content='0;url=admin.php?page=watupro_certificates' />"; 
				exit;
			}
	
			$certificate=$wpdb->get_row($wpdb->prepare("SELECT * FROM 
					{$wpdb->prefix}watupro_certificates WHERE ID=%d", $_GET['id']));
	
			require(WATUPRO_PATH. '/views/certificate.php');   
		break;
	
		default:
			// select my certificates
			$certificates=$wpdb->get_results("SELECT * FROM {$wpdb->prefix}watupro_certificates
				ORDER BY title");
	
			require(WATUPRO_PATH. '/views/certificates.php');   
		break;
	}
}

// shows the certificates earned by a student
function watupro_my_certificates() {
	global $wpdb, $user_ID;
	
	// admin can see this for every student
	if(!empty($_GET['user_id']) and current_user_can(WATUPRO_MANAGE_CAPS)) $user_id = $_GET['user_id'];
	else $user_id = $user_ID;
	
	$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE ID=%d", $user_id));
	
	$certificates = $wpdb->get_results($wpdb->prepare("SELECT tC.*, tE.name as exam_name, tG.gtitle as grade, 
		tT.points as points, tT.end_time as end_time, tT.id as taking_id
		FROM ".WATUPRO_CERTIFICATES." tC JOIN ".WATUPRO_GRADES." tG ON tG.certificate_id=tC.id
		JOIN ".WATUPRO_TAKEN_EXAMS." tT ON tT.grade_id = tG.ID
		JOIN ".WATUPRO_EXAMS." tE ON tE.ID = tT.exam_id
		WHERE tT.user_id = %d ORDER BY tT.ID DESC", $user_id));
		
	// cleanup duplicates - we only need certificates shown for the latest taking
	$final_certificates = array();	
	$certificate_ids = array();
	
	foreach($certificates as $certificate) {
		if(in_array($certificate->ID, $certificate_ids)) continue;
		
		$final_certificates[] = $certificate;
		$certificate_ids[] = $certificate->ID;
	}
	
	$certificates = $final_certificates;
		
	if(@file_exists(TEMPLATEPATH.'/watupro/my_certificates.php')) require TEMPLATEPATH.'/watupro/my_certificates.php';
	else require WATUPRO_PATH."/views/my_certificates.php";   
}


function watupro_view_certificate() {
	global $wpdb, $user_ID;
	watupro_remove_filters();
		
	// select certificate
	$certificate=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_certificates
			WHERE ID=%d", $_GET['id']));
	$output=stripslashes($certificate->html);

	if(empty($certificate->ID)) {
		wp_die(__("no such certificate", "watupro"));
	}
	
	// no taking id? only admin can see it then
	if(empty($_GET['taking_id'])) {
		if(!current_user_can(WATUPRO_MANAGE_CAPS)) 
			wp_die( __('You do not have sufficient permissions to access this page', 'watupro').' 1' );
	}
	else {
		// find taking and see if the current user is allowed to see the certificate
		$taking=$wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_TAKEN_EXAMS."
			WHERE ID=%d", $_GET['taking_id']));

		if($taking->user_id!=$user_ID and !current_user_can(WATUPRO_MANAGE_CAPS)) {
			wp_die( __('You do not have sufficient permissions to access this page', 'watupro').' 2' );
		}
		
		$user_id = $taking->user_id;
	
		// select exam
		$exam=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_master 
				WHERE id=%d", $taking->exam_id));
		
		$user_info=get_userdata($user_id);
		$name=(empty($user_info->first_name) or empty($user_info->last_name))?$user_info->display_name:
		$user_info->first_name." ".$user_info->last_name;
	
		$output=str_replace("%%USER_NAME%%", $name, $output);
		$output=str_replace("%%POINTS%%", $taking->points, $output);
		$output=str_replace("%%GRADE%%", $taking->result, $output);
		$output=str_replace("%%QUIZ_NAME%%", $exam->name, $output);
		$output=str_replace("%%DESCRIPTION%%", $exam->description, $output);
		$taken_date = date(get_option('date_format'), strtotime($taking->date));
	   $output=str_replace("%%DATE%%", $taken_date, $output);	  	  	  
	}
	?>
	<html>
	<head><title><?php echo $certificate->title;?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head>
	<body><?php echo apply_filters('watupro_content', $output);?></body>
	</html>
	<?php 
	exit;
}