<?php
// inits the plugin - activate, menus etc

/// Initialize this plugin. Called by 'init' hook.
function watupro_init() {
	global $user_ID, $wpdb;
	$wpdb-> show_errors ();
	
	require(WATUPRO_PATH."/helpers/htmlhelper.php");
	
	if (!session_id()) @session_start();
	
	// define table names
	define('WATUPRO_EXAMS', $wpdb->prefix."watupro_master");
	define('WATUPRO_TAKEN_EXAMS', $wpdb->prefix."watupro_taken_exams");
	define('WATUPRO_QUESTIONS', $wpdb->prefix."watupro_question");
	define('WATUPRO_STUDENT_ANSWERS', $wpdb->prefix."watupro_student_answers");
	define('WATUPRO_USER_CERTIFICATES', $wpdb->prefix."watupro_user_certificates");
	define('WATUPRO_CATS', $wpdb->prefix."watupro_cats");
	define('WATUPRO_QCATS', $wpdb->prefix."watupro_qcats");
	define('WATUPRO_GRADES', $wpdb->prefix."watupro_grading");
	define('WATUPRO_CERTIFICATES', $wpdb->prefix."watupro_certificates");
	define('WATUPRO_ANSWERS', $wpdb->prefix."watupro_answer");
	define('WATUPRO_GROUPS', $wpdb->prefix."watupro_groups");
	define('WATUPRO_DEPENDENCIES', $wpdb->prefix."watupro_dependencies");
	define('WATUPRO_PAYMENTS', $wpdb->prefix."watupro_payments");
    
	load_plugin_textdomain('watupro', false, WATUPRO_RELATIVE_PATH . '/languages/' );    
	
	// need to redirect the user?
	if(!empty($user_ID)) {
		$redirect=get_user_meta($user_ID, "watupro_redirect", true);		
		
		update_user_meta($user_ID, "watupro_redirect", "");
		
		if(!empty($redirect)) {
			 echo "<meta http-equiv='refresh' content='0;url=$redirect' />"; 
			 exit;
		}
	}	

    $manage_caps = current_user_can('manage_options')?'manage_options':'watupro_manage_exams';
    define('WATUPRO_MANAGE_CAPS', $manage_caps);
   
   add_shortcode( 'WATUPRO-LEADERBOARD', 'watupro_leaderboard' ); 
   add_shortcode( 'WATUPRO-MYEXAMS', 'watupro_myexams_code' );
	add_shortcode( 'WATUPRO-MYCERTIFICATES', 'watupro_mycertificates_code' );
	add_shortcode( 'WATUPROLIST', 'watupro_listcode' );
	add_shortcode( 'WATUPRO', 'watupro_shortcode' );
	
	// prepare the custom filter on the content
	watupro_define_filters();
}

// actual activation & installation
function watupro_activate() {
	global $wpdb;
	
	watupro_init();

	// Initial options.
	add_option('watupro_show_answers', 1);
	add_option('watupro_single_page', 0);
	add_option('watupro_answer_type', 'radio');
    
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');    
    
    $wpdb->show_errors();
        
        // exams
        if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_EXAMS."'") != WATUPRO_EXAMS) {  
            $sql = "CREATE TABLE `".WATUPRO_EXAMS."`(
						`ID` int(11) unsigned NOT NULL auto_increment,
						`name` varchar(255) NOT NULL DEFAULT '',
						`description` TEXT NOT NULL,
						`final_screen` TEXT NOT NULL,
						`added_on` datetime NOT NULL,
	          `is_active` TINYINT UNSIGNED NOT NULL DEFAULT '1',
	          `require_login` TINYINT UNSIGNED NOT NULL DEFAULT '0',
	          `take_again` TINYINT UNSIGNED NOT NULL DEFAULT '0', 
	          `email_taker` TINYINT UNSIGNED NOT NULL DEFAULT '0', 
	          `email_admin` TINYINT UNSIGNED NOT NULL DEFAULT '0', 
	          `randomize_questions` TINYINT UNSIGNED DEFAULT '0', 
	          `login_mode` VARCHAR(100) NOT NULL DEFAULT 'open',
	          `time_limit` INT UNSIGNED NOT NULL DEFAULT '0',
						`pull_random` INT UNSIGNED NOT NULL DEFAULT '0',
						PRIMARY KEY  (ID)
					) ENGINE=INNODB CHARACTER SET utf8;";
            $wpdb->query($sql);   
        }    
        
        // questions
        if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_QUESTIONS."'") != WATUPRO_QUESTIONS) {  
            $sql = "CREATE TABLE `".WATUPRO_QUESTIONS."` (
							ID int(11) unsigned NOT NULL auto_increment,
							exam_id int(11) unsigned NOT NULL DEFAULT '0',
							question mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
							answer_type char(15) COLLATE utf8_unicode_ci NOT NULL default '',
							sort_order int(3) NOT NULL default 0,
							PRIMARY KEY  (ID),
							KEY quiz_id (exam_id)
						) ENGINE=INNODB CHARACTER SET utf8;";
            $wpdb->query($sql);    
        }    
        
        // answers
        if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_ANSWERS."'") != WATUPRO_ANSWERS) {  
            $sql = "CREATE TABLE `".WATUPRO_ANSWERS."` (
						ID int(11) unsigned NOT NULL auto_increment,
						question_id int(11) unsigned NOT NULL default '0',
						answer TEXT NOT NULL,
						correct enum('0','1') NOT NULL default '0',
						point int(11) NOT NULL default 0,
						sort_order int(3) NOT NULL default 0,
						PRIMARY KEY  (ID)
					) ENGINE=INNODB CHARACTER SET utf8;";
            dbDelta($sql);               
        }  
        
		// grades
		if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_GRADES."'") != WATUPRO_GRADES) {  
            $sql = "CREATE TABLE `".WATUPRO_GRADES."` (
				 `ID` int(11) NOT NULL AUTO_INCREMENT,
				 `exam_id` int(11) NOT NULL default 0,
				 `gtitle` varchar (255) NOT NULL default '',
				 `gdescription` mediumtext COLLATE utf8_unicode_ci NOT NULL,
				 `gfrom` int(11) NOT NULL default 0,
				 `gto` int(11) NOT NULL default 0,
				 `certificate_id` INT UNSIGNED NOT NULL default 0,
				 PRIMARY KEY (`ID`)
				) ENGINE=INNODB CHARACTER SET utf8";
            dbDelta($sql);              
        }   
        
        // taken exams
        if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_TAKEN_EXAMS."'") != WATUPRO_TAKEN_EXAMS) {  
            $sql = "CREATE TABLE `".WATUPRO_TAKEN_EXAMS."` (
				  	`ID` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `user_id` INT UNSIGNED NOT NULL ,
            `exam_id` INT UNSIGNED NOT NULL ,
            `date` DATE NOT NULL ,
            `points` DECIMAL(6,2) NOT NULL ,
            `details` TEXT NOT NULL ,
            `result` TEXT NOT NULL ,
            `start_time` DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00',
				  `ip` VARCHAR(20) NOT NULL
				) ENGINE=INNODB CHARACTER SET utf8";
            dbDelta($sql);              
        }   
        
        // links to taken_exams
        if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_STUDENT_ANSWERS."'") != WATUPRO_STUDENT_ANSWERS) {  
            $sql = "CREATE TABLE `".WATUPRO_STUDENT_ANSWERS."` (
				  `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                  `user_id` INT UNSIGNED NOT NULL default 0, 
                  `exam_id` INT UNSIGNED NOT NULL default 0,
                  `taking_id` INT UNSIGNED NOT NULL default 0,
                  `question_id` INT UNSIGNED NOT NULL default 0,
                  `answer` TEXT NOT NULL,
				  `points` DECIMAL(6,2) NOT NULL default '0.00',
				  `question_text` TEXT  NOT NULL
				) ENGINE=INNODB CHARACTER SET utf8";
            $wpdb->query($sql);              
        }

		// certificates
        if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_CERTIFICATES."'") != WATUPRO_CERTIFICATES) {  
            $sql = "CREATE TABLE `".WATUPRO_CERTIFICATES."` (
				  `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				  `title` VARCHAR(255) NOT NULL default '', 
           `html` TEXT NOT NULL 
				) ENGINE=INNODB CHARACTER SET utf8";
            $wpdb->query($sql);         
        }
       
      // question categories
      if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_QCATS."'") != WATUPRO_QCATS) {  
            $sql = "CREATE TABLE `".WATUPRO_QCATS."` (
				  `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				  `name` VARCHAR(255) NOT NULL default ''
				) ENGINE=INNODB CHARACTER SET utf8";
            $wpdb->query($sql);         
      } 
      
      // exam categories
      if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_CATS."'") != WATUPRO_CATS) {  
            $sql = "CREATE TABLE `".WATUPRO_CATS."` (
				  `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				  `name` VARCHAR(255) NOT NULL DEFAULT '',
				  `ugroups` VARCHAR(255) NOT NULL DEFAULT ''
				) ENGINE=INNODB CHARACTER SET utf8";
            $wpdb->query($sql);         
      } 
		      
      // user groups - optionally user can have a group
      if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_GROUPS."'") != WATUPRO_GROUPS) {  
            $sql = "CREATE TABLE `".WATUPRO_GROUPS."` (
				  `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				  `name` VARCHAR(255) NOT NULL DEFAULT '',
				  `is_def` TINYINT UNSIGNED NOT NULL DEFAULT 0
				) ENGINE=INNODB CHARACTER SET utf8";
            $wpdb->query($sql);         
      }
      
      // keep track about user's certificates
      if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_USER_CERTIFICATES."'") != WATUPRO_USER_CERTIFICATES) {  
            $sql = "CREATE TABLE `".WATUPRO_USER_CERTIFICATES."` (
						  `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
						  `user_id` INT UNSIGNED NOT NULL default 0,
						  `certificate_id` INT UNSIGNED NOT NULL default 0
						) ENGINE=INNODB CHARACTER SET utf8";
            $wpdb->query($sql);              
      }      
      
       // intelligence tables
      if(watupro_intel()) { 
      	// exam dependencies
	      if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_DEPENDENCIES."'") != WATUPRO_DEPENDENCIES) {  
	            $sql = "CREATE TABLE `".WATUPRO_DEPENDENCIES."` (
					  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
					  `exam_id` int(10) unsigned NOT NULL default 0,
					  `depend_exam` int(10) unsigned NOT NULL default 0,
					  `depend_points` int(11) NOT NULL default 0,
					  PRIMARY KEY (`ID`)
					) ENGINE=INNODB CHARACTER SET utf8";
	        $wpdb->query($sql);           
	      }
	       
	      // exam fee payments
	      if($wpdb->get_var("SHOW TABLES LIKE '".WATUPRO_PAYMENTS."'") != WATUPRO_PAYMENTS) {  
	            $sql = "CREATE TABLE `".WATUPRO_PAYMENTS."` (
					  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
					  `exam_id` int(10) unsigned NOT NULL default 0,
					  `user_id` int(10) unsigned NOT NULL default 0,
					  `date` DATE NOT NULL,
					  `amount` DECIMAL(8,2) NOT NULL default '0.00',
					  `status` VARCHAR(100) NOT NULL default '',
					  `paycode` VARCHAR(100) NOT NULL default '',
					  PRIMARY KEY (`ID`)
					) ENGINE=INNODB CHARACTER SET utf8";
	          $wpdb->query($sql);       
	            
	            // add also the USD option by default
					update_option("watupro_currency", "USD");         
	      }  
	   }
       
		# $wpdb->print_error();				
		update_option( "watupro_delete_db", '' );
        
      // add student role if not exists
      $res = add_role('student', 'Student', array(
            'read' => true, // True allows that capability
            'watupro_exams' => true));   
      if(!$res) {
      	// role already exists, check the capability
      	$role = get_role('student');
      	if(!$role->has_cap('watupro_exams')) $role->add_cap('watupro_exams');
      }       
            
      // database upgrades - version 1.1
      $db_version=get_option("watupro_db_version");
      
      watupro_add_db_fields(array(
      		array("name"=>"in_progress", "type"=>"TINYINT UNSIGNED NOT NULL default 0"),
					array("name"=>"end_time", "type"=>"DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00'"),
					array("name"=>"grade_id", "type"=>"INT UNSIGNED NOT NULL default 0"),
					array("name"=>"percent_correct", "type"=>"TINYINT UNSIGNED NOT NULL default 0"),
					array("name"=>"email", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''"),					
					array("name"=>"catgrades", "type"=>"TEXT")
				), WATUPRO_TAKEN_EXAMS);
	
		 watupro_add_db_fields(array(
   		array("name"=>"show_answers", "type"=>"VARCHAR(10) NOT NULL default ''"),
   		array("name"=>"random_per_category", "type"=>"TINYINT UNSIGNED NOT NULL default 0"),
   		array("name"=>"group_by_cat", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
   		array("name"=>"num_answers", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"),
   		array("name"=>"single_page", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
   		array("name"=>"cat_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"),
   		array("name"=>"times_to_take", "type"=>"SMALLINT UNSIGNED NOT NULL DEFAULT 0"),
   		array("name"=>"mode", "type"=>"VARCHAR(100) DEFAULT 'live'"),
			array("name"=>"fee", "type"=>"DECIMAL(8,2) NOT NULL DEFAULT '0.00'"),
			array("name"=>"require_captcha", "type"=>"TINYINT NOT NULL DEFAULT '0'"),
			array("name"=>"grades_by_percent", "type"=>"TINYINT NOT NULL DEFAULT '0'"),
			array("name"=>"admin_email", "type"=>"VARCHAR(255) NOT NULL default ''"),
			array("name"=>"disallow_previous_button", "type"=>"TINYINT UNSIGNED NOT NULL default 0"),
			array("name"=>"email_output", "type"=>"TEXT NOT NULL"),
			array("name"=>"live_result", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
			array("name"=>"gradecat_design", "type"=>"TEXT"),
			array("name"=>"is_scheduled", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
      array("name"=>"schedule_from", "type"=>"DATETIME"),
      array("name"=>"schedule_to", "type"=>"DATETIME"),
      array("name"=>"submit_always_visible", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0")
		), WATUPRO_EXAMS);	
				
			 watupro_add_db_fields(array(
	    		array("name"=>"cat_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"),
	    		array("name"=>"random_per_category", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
	    		array("name"=>"explain_answer", "type"=>"TEXT NOT NULL"),
	    		array("name"=>"is_required", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
	    		array("name"=>"correct_condition", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''")
			), WATUPRO_QUESTIONS);
			
			watupro_add_db_fields(array(
	    		array("name"=>"exam_id", "type"=>"INT UNSIGNED NOT NULL default 0"),
	    		array("name"=>"taking_id", "type"=>"INT UNSIGNED NOT NULL default 0")
			), WATUPRO_USER_CERTIFICATES);
			
			watupro_add_db_fields(array(
    			array("name"=>"description", "type"=>"TEXT NOT NULL")
			), WATUPRO_QCATS);
			
			watupro_add_db_fields(array(
    			array("name"=>"cat_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0") // question category ID
			), WATUPRO_GRADES);
			
			watupro_add_db_fields(array(
    			array("name"=>"is_correct", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
				array("name"=>"snapshot", "type"=>"TEXT NOT NULL")
			), WATUPRO_STUDENT_ANSWERS);
			
			
			// db updates 2.9.9
			if(empty($db_version) or $db_version<2.99) {
				$sql = "ALTER TABLE ".WATUPRO_TAKEN_EXAMS." CHANGE `points` `points` DECIMAL(6,2) DEFAULT '0.00'";
				$wpdb->query($sql);
				
					$sql = "ALTER TABLE ".WATUPRO_STUDENT_ANSWERS." CHANGE `points` `points` DECIMAL(6,2) DEFAULT '0.00'";
				$wpdb->query($sql);
			}
			
			// db updates 3.0
			if(empty($db_version) or $db_version<3) {
				$sql = "ALTER TABLE ".WATUPRO_ANSWERS." CHANGE `point` `point` DECIMAL(6,2) DEFAULT '0.00'";
				$wpdb->query($sql);
			}
			
			// db updates 3.4
			if(empty($db_version) or $db_version<3.41) {
				$sql = "ALTER TABLE ".WATUPRO_EXAMS." CHANGE `name` `name` VARCHAR(255) DEFAULT ''";
				$wpdb->query($sql);
			}
			
			// Intelligence specific fields
			if(watupro_intel()) {
				 require_once(WATUPRO_PATH."/i/models/i.php");
				 WatuPROIntelligence::activate();
			}
      
      // set current DB version
      update_option("watupro_db_version", 3.41);
}

// assign the role
function watupro_register_role($user_id, $password="", $meta=array()) {
   $userdata = array();
   $userdata['ID'] = $user_id;
   $userdata['role'] = $_POST['role'];

   //only allow if user role is my_role
   if ($userdata['role'] == "student"){
      wp_update_user($userdata);
      
      // also update redirection so we can go back to the exam after login
      if(!empty($_POST['redirect_to'])) {
      	update_user_meta($user_id, "watupro_redirect", $_POST['redirect_to']);
      }
   }
}

// output role field
function watupro_role_field() {
    // thanks to http://www.jasarwebsolutions.com/2010/06/27/how-to-change-a-users-role-on-the-wordpress-registration-form/
    ?>
    <input id="role" type="hidden" tabindex="20" size="25" value="student"  name="role" />
    <input id="role" type="hidden" tabindex="20" size="25" value="student"  name="redirect_to" value="<?php echo $_GET['redirect_to']?>" />
    <?php
}

// add settings link in the plugins page
function watupro_plugin_action_links($links, $file) {		
	if ( strstr($file, "watupro/" )) {
		$settings_link = '<a href="admin.php?page=watupro_options">' . __( 'Settings', 'watupro' ) . '</a>';
		array_unshift( $links, $settings_link );
	}
	return $links;
}

/**
 * Add jQuery Validation script on posts.
 */
function watupro_vc_scripts() {
    // thanks to http://www.problogdesign.com/wordpress/validate-forms-in-wordpress-with-jquery/
    wp_enqueue_script('jquery');
	
		wp_enqueue_script(
			'jquery-validate',
			plugins_url().'/watupro/lib/jquery.validate.min.js',
			array('jquery'),
			'1.9.0');
        
    wp_enqueue_style(
			'watupro-style',
			plugins_url().'/watupro/style.css',
			array(),
			'3.0.2');
		
		wp_enqueue_script(
			'watupro-script',
			plugins_url().'/watupro/lib/main-min.js',
			array(),
			'3.5.9');
			
		$translation_array = array('answering_required' => __('Answering this question is required', 'watupro'),
			'did_not_answer' => __('You did not select or enter any answer. Are you sure you want to continue?', 'watupro'),
			'missed_required_question' => __('You have missed to answer a required question', 'watupro'),
			'please_wait' => __('Please wait...', 'watupro'),
			'try_again' => __('Try again', 'watupro'),
			'time_over' => __("Sorry, your time is over! I'm submitting your results... Done!", 'watupro'),
			'seconds' => __('seconds', 'watupro'),
			'minutes_and' => __('minutes and', 'watupro'),
			'hours' => __('hours,', 'watupro'),
			'time_left' => __('Time left:', 'watupro'),
			'email_required' => __('Please enter your email address', 'watupro'),
			'not_last_page' => __('You are not on the last page. Are you sure you want to submit the quiz?', 'watupro'),
			'please_answer' => __('Please first answer the question', 'watupro'));	
		wp_localize_script( 'watupro-script', 'watupro_i18n', $translation_array );	
		
		if(watupro_intel()) {
			 wp_enqueue_style(
				'watupro-intelligence-css',
				plugins_url().'/watupro/i/css/main.css',
				array(),
				'3.0.3');
				
			wp_enqueue_script(
				'watupro-intelligence',
				plugins_url().'/watupro/i/js/main.js',
				array(),
				'3.4');
		} // endif intel
}

// admin menu
function watupro_add_menu_links() {
	global $wp_version, $_registered_pages;
	$page = 'edit.php';
	if($wp_version >= '2.7') $page = 'tools.php';
	
	$code_pages = array('question_form.php', 'question.php');
	foreach($code_pages as $code_page) {
		$hookname = get_plugin_page_hookname("watupro/$code_page", '' );
		$_registered_pages[$hookname] = true;
	}
	
	$student_caps = current_user_can(WATUPRO_MANAGE_CAPS)?WATUPRO_MANAGE_CAPS:'read'; // used to be watupro_exams
	
	// students part
	add_menu_page(__('My Exams', 'watupro'), __('My Exams', 'watupro'), $student_caps, "my_watupro_exams", 'watupro_my_exams');
	add_submenu_page('my_watupro_exams', __("My Certificates", 'watupro'), __("My Certificates", 'watupro'), $student_caps, 'watupro_my_certificates', 'watupro_my_certificates');
	
	// not visible in menu but to allow showing certificates
	add_submenu_page(NULL, __("View certificate", 'watupro'), __("View certificate", 'watupro'), $student_caps, 'watupro_view_certificate', 'watupro_view_certificate');

	// admin menus
    add_menu_page(__('Watu PRO', 'watupro'), __('Watu PRO', 'watupro'), WATUPRO_MANAGE_CAPS, "watupro_exams", 'watupro_exams');  
	 add_submenu_page('watupro_exams', __("Watu PRO Certificates", 'watupro'), __("Certificates", 'watupro'), WATUPRO_MANAGE_CAPS, 'watupro_certificates', 'watupro_certificates');
	 add_submenu_page('watupro_exams',__('Exam Categories', 'watupro'), __('Exam Categories', 'watupro'), WATUPRO_MANAGE_CAPS, "watupro_cats", "watupro_cats"); 
	 add_submenu_page('watupro_exams',__('User Groups', 'watupro'), __('User Groups', 'watupro'), WATUPRO_MANAGE_CAPS, "watupro_groups", "watupro_groups"); 
	 add_submenu_page('watupro_exams',__('Question Categories', 'watupro'), __('Question Categories', 'watupro'), WATUPRO_MANAGE_CAPS, "watupro_question_cats", "watupro_question_cats"); 
	 add_submenu_page('watupro_exams',__('Modules', 'watupro'), __('Modules', 'watupro'), 'manage_options', "watupro_modules", "watupro_modules"); 
	 add_submenu_page('watupro_exams',__('Settings', 'watupro'), __('Settings', 'watupro'), WATUPRO_MANAGE_CAPS, "watupro_options", "watupro_options"); 
	 add_submenu_page('watupro_exams',__('Help', 'watupro'), __('Help', 'watupro'), WATUPRO_MANAGE_CAPS, "watupro_help", "watupro_help"); 
	 	 
	 	 // not visible in menu - add/edit exam
	 	 add_submenu_page(NULL,__('Add/Edit Exam', 'watupro'), __('Add/Edit Exam', 'watupro'), WATUPRO_MANAGE_CAPS, "watupro_exam", "watupro_exam"); 
	 	 add_submenu_page(NULL,__('Add/Edit Question', 'watupro'), __('Add/Edit Question', 'watupro'), WATUPRO_MANAGE_CAPS, "watupro_question", "watupro_question");  // add/edit question
	 	 add_submenu_page(NULL,__('Manage Questions', 'watupro'), __('Manage Questions', 'watupro'), WATUPRO_MANAGE_CAPS, "watupro_questions", "watupro_questions");  // manage questions
	 	 add_submenu_page(NULL,__('Taken Exam Data', 'watupro'), __('Taken Exam Data', 'watupro'), WATUPRO_MANAGE_CAPS, "watupro_takings", "watupro_takings");  // view takings
	 	 add_submenu_page(NULL,__('Manage Grades', 'watupro'), __('Manage Grades', 'watupro'), WATUPRO_MANAGE_CAPS, "watupro_grades", "watupro_grades");  // manage grades
}

// function to conditionally add DB fields
function watupro_add_db_fields($fields, $table) {
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

// manually apply Wordpress filters on the content
// to avoid calling apply_filters('the_content')	
function watupro_define_filters() {
	add_filter( 'watupro_content', 'watupro_autop' );	
	add_filter( 'watupro_content', 'wptexturize' );
	add_filter( 'watupro_content', 'convert_smilies' );
	add_filter( 'watupro_content', 'convert_chars' );
	add_filter( 'watupro_content', 'shortcode_unautop' );
	add_filter( 'watupro_content', 'do_shortcode' );	
}	

function watupro_autop($content) {
	return wpautop($content, false);
}