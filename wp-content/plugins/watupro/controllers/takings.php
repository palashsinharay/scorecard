<?php
function watupro_takings() {
	global $wpdb, $wp_roles;
	$roles = $wp_roles->roles;	
	
	// shows data for a taken exam
	$ob=empty($_GET['ob'])?"id":$_GET['ob'];
	$dir=!empty($_GET['dir'])?$_GET['dir']:"DESC";
	$odir=($dir=='ASC')?'DESC':'ASC';
	$offset=empty($_GET['offset'])?0:$_GET['offset'];
	
	// select exam
	$exam=$wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_EXAMS." WHERE ID=%d",
		$_GET['exam_id']));
		
	// search/filter
	$filters=array();
	$joins=array();
	$filter_sql = $left_join_sql = $role_join_sql = "";
	$join_sql="LEFT JOIN {$wpdb->users} tU ON tU.ID=tT.user_id";
	
	// add filters and joins
	
	// display name
	if(!empty($_GET['dn'])) {
		switch($_GET['dnf']) {
			case 'contains': $like="%$_GET[dn]%"; break;
			case 'starts': $like="$_GET[dn]%"; break;
			case 'ends': $like="%$_GET[dn]"; break;
			case 'equals':
			default: $like=$_GET['dn']; break;			
		}
		
		$joins[]=$wpdb->prepare(" display_name LIKE %s ", $like);
	}
	
	// email
	if(!empty($_GET['email'])) {
		switch($_GET['emailf']) {
			case 'contains': $like="%$_GET[email]%"; break;
			case 'starts': $like="$_GET[email]%"; break;
			case 'ends': $like="%$_GET[email]"; break;
			case 'equals':
			default: $like=$_GET['email']; break;			
		}
		
		$joins[]=$wpdb->prepare(" user_email LIKE %s ", $like);
		$filters[]=$wpdb->prepare(" ((user_id=0 AND email LIKE %s) OR (user_id!=0 AND user_email LIKE %s)) ", $like, $like);
		$left_join = 'LEFT'; // when email is selected, do left join because it might be without logged user
	}
	
	// WP user role - when selected role the join always becomes right join
	if(!empty($_GET['role'])) {
		$left_join = '';
		$blog_prefix = $wpdb->get_blog_prefix();
		$role_join_sql = "JOIN {$wpdb->usermeta} tUM ON tUM.user_id = tU.id 
			AND tUM.meta_key = '{$blog_prefix}capabilities' AND tUM.meta_value LIKE '%:".'"'.$_GET['role'].'"'.";%'";
	}
	
	// IP
	if(!empty($_GET['ip'])) {
		switch($_GET['ipf']) {
			case 'contains': $like="%$_GET[ip]%"; break;
			case 'starts': $like="$_GET[ip]%"; break;
			case 'ends': $like="%$_GET[ip]"; break;
			case 'equals':
			default: $like=$_GET['ip']; break;			
		}
		
		$filters[]=$wpdb->prepare(" ip LIKE %s ", $like);
	}
	
	// Date
	if(!empty($_GET['date'])) {
		switch($_GET['datef']) {
			case 'after': $filters[]=$wpdb->prepare(" date>%s ", $_GET['date']); break;
			case 'before': $filters[]=$wpdb->prepare(" date<%s ", $_GET['date']); break;
			case 'equals':
			default: $filters[]=$wpdb->prepare(" date=%s ", $_GET['date']); break;
		}
	}
	
	// Points
	if(!empty($_GET['points'])) {
		switch($_GET['pointsf']) {
			case 'less': $filters[]=$wpdb->prepare(" points<%d ", $_GET['points']); break;
			case 'more': $filters[]=$wpdb->prepare(" points>%d ", $_GET['points']); break;
			case 'equals':
			default: $filters[]=$wpdb->prepare(" points=%d ", $_GET['points']); break;
		}
	}
	
	
	// Grade
	if(!empty($_GET['grade'])) {
		$filters[]=$wpdb->prepare(" result=%s ", $_GET['grade']);
	}
	
	// construct filter & join SQLs
	if(sizeof($filters)) {
		$filter_sql=" AND ".implode(" AND ", $filters);
	}
	
	if(sizeof($joins)) {
		$join_sql=" $left_join JOIN {$wpdb->users} tU ON tU.ID=tT.user_id AND "
			.implode(" AND ", $joins);
	}
	
	$limit_sql="LIMIT $offset,10";
	
	if(!empty($_GET['export'])) $limit_sql="";
		
	// select takings
	$q="SELECT SQL_CALC_FOUND_ROWS tT.*, tU.display_name as display_name, tU.user_email as user_email
	FROM ".WATUPRO_TAKEN_EXAMS." tT 
	$join_sql $role_join_sql
	WHERE tT.exam_id={$exam->ID} AND tT.in_progress=0 $filter_sql
	ORDER BY $ob $dir $limit_sql";
	// echo $q;
	$takings=$wpdb->get_results($q);
	
	if(!empty($_GET['export'])) {
		require_once(WATUPRO_PATH."/models/record.php");
		$_record = new WTPRecord();
		$_record->export($takings, $exam);
	}
	
	$count=$wpdb->get_var("SELECT FOUND_ROWS()");
	
	// grades for the dropdown
	$grades=$wpdb->get_results($wpdb->prepare(" SELECT * FROM `".WATUPRO_GRADES."` 
		WHERE exam_id=%d ORDER BY gtitle", $exam->ID));
		
	// this var will be added to links at the view
	$filters_url="dn=".@$_GET['dn']."&dnf=".@$_GET['dnf']."&email=".@$_GET['email']."&emailf=".
		@$_GET['emailf']."&ip=".@$_GET['ip']."&ipf=".@$_GET['ipf']."&date=".@$_GET['date'].
		"&datef=".@$_GET['datef']."&points=".@$_GET['points']."&pointsf=".@$_GET['pointsf'].
		"&grade=".@$_GET['grade']."&role=".@$_GET['role']."&ugroup=".@$_GET['ugroup'];			
		
	$display_filters=(!sizeof($filters) and !sizeof($joins) and empty($role_join_sql))?false:true;	
	
	wp_enqueue_script('thickbox',null,array('jquery'));
	wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');
	require(WATUPRO_PATH. '/views/takings.php');   
}

function watupro_delete_taking() {
	global $wpdb;
	
	$wpdb->query($wpdb->prepare("DELETE FROM ".WATUPRO_TAKEN_EXAMS." WHERE id=%d", $_GET['id']));
		
	// delete from student_answers
	$wpdb->query($wpdb->prepare("DELETE FROM ".WATUPRO_STUDENT_ANSWERS." WHERE taking_id=%d", $_GET['id']));	
	exit;	
}