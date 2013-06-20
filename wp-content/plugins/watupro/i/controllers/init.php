<?php
// Intelligence initialization actions
require_once(WATUPRO_PATH."/i/models/dependency.php");
require_once(WATUPRO_PATH."/i/models/payment.php");
require_once(WATUPRO_PATH."/i/models/i.php");
require_once(WATUPRO_PATH."/i/models/question.php");
require_once(WATUPRO_PATH."/i/controllers/teacher.php");
add_action('wp_ajax_watupro_lock_details', array("WatuPRODependency", "lock_details"));

// Paypal IPN
add_filter('query_vars', array("WatuPROPayment", "query_vars"));
add_action('parse_request', array("WatuPROPayment", "parse_request"));

// extra pages
add_action( 'admin_menu', array("WatuPROIntelligence", "admin_menu"));