<?php
// Init controller of the Reporting tool
// even if empty, this file should be there so we can check if module exists
require_once(WATUPRO_PATH."/modules/reports/models/reports.php");
require_once(WATUPRO_PATH."/modules/reports/models/user.php");
require_once(WATUPRO_PATH."/modules/reports/models/stats.php");
add_action( 'admin_menu', array("WTPReports", "admin_menu"));
add_action('init', array("WTPReports", "register_scripts"));
add_filter('manage_users_columns', array('WTPReportsUser', 'add_status_column'));