<?php
// class handling payment restrictions, IPN etc
class WatuPROPayment
{	
	// render payment button and info if any	
	static function render($exam)
	{
		global $post, $user_ID;
		
		$paypal_email = get_option("watupro_paypal");
		$other_payments = get_option("watupro_other_payments");
		$currency = get_option('watupro_currency');
		
		if(empty($paypal_email) and empty($other_payments)) 
		{
			echo "<!-- WATUPROCOMMENT: there is exam fee but no Paypal ID or other payment info has been set in WatuPRO Settings page -->";
			return false;
		}
		
		// replace shortcodes
		if(!empty($other_payments))
		{
			$other_payments = str_replace("[AMOUNT]", $exam->fee, $other_payments);
			$other_payments = str_replace("[USER_ID]", $user_ID, $other_payments);
			$other_payments = str_replace("[EXAM_TITLE]", $exam->name, $other_payments);
			$other_payments = str_replace("[EXAM_ID]", $exam->ID, $other_payments);
		}
		
		require(WATUPRO_PATH."/i/views/payment.php");
		return true;
	}
	
	// check if there is payment made from this user for this exam
	static function valid_payment($exam)
	{
		global $wpdb, $user_ID;
		
		$payment = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_payments 
			WHERE exam_id=%d AND user_id=%d AND status='completed'", $exam->ID, $user_ID));
		if(empty($payment->ID)) return false;
		
		return true;	
	}
	
	// handle query vars
	static function query_vars($vars)
	{
		// http://www.james-vandyne.com/process-paypal-ipn-requests-through-wordpress/
		$new_vars = array('watupro');
		$vars = array_merge($new_vars, $vars);
	   return $vars;
	} 
	
	// handle Paypal IPN request
	static function parse_request($wp) {
		// only process requests with "watupro=paypal"
	   if (array_key_exists('watupro', $wp->query_vars) 
	            && $wp->query_vars['watupro'] == 'paypal') {
	        self::paypal_ipn($wp);
	   }	
	}
	
	// process paypal IPN
	static function paypal_ipn($wp) {
		global $wpdb;
		echo "<!-- WATUPROCOMMENT paypal IPN -->";
		
	   $paypal_email = get_option("watupro_paypal");
		
		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';
		foreach ($_POST as $key => $value) { 
		  $value = urlencode(stripslashes($value)); 
		  $req .= "&$key=$value";
		}		
		
		// post back to PayPal system to validate
		$header="";
		$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
		$fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);
		
		if($fp) {
			fputs ($fp, $header . $req);
		   while (!feof($fp)) {
		      $res = fgets ($fp, 1024);
		     
		      if (strstr ($res, "200 OK")) {
		      	// check the payment_status is Completed
			      // check that txn_id has not been previously processed
			      // check that receiver_email is your Primary PayPal email
			      // process payment
				   $payment_completed = false;
				   $txn_id_okay = false;
				   $receiver_okay = false;
				   $payment_currency_okay = false;
				   $payment_amount_okay = false;
				   
				   if($_POST['payment_status']=="Completed") {
				   	$payment_completed = true;
				   } 
				   else self::log_and_exit("Payment status: $_POST[payment_status]");
				   
				   // check txn_id
				   $txn_exists = $wpdb->get_var($wpdb->prepare("SELECT paycode FROM {$wpdb->prefix}watupro_payments 
					   WHERE paycode=%s", $_POST['txn_id']));
					if(empty($txn_id)) $txn_id_okay = true; 
					else self::log_and_exit("TXN ID exists: $txn_id");  
					
					// check receiver email
					if($_POST['business']==$paypal_email) {
						$receiver_okay = true;
					}
					else self::log_and_exit("Business email is wrong: $_POST[business]");
					
					// check payment currency
					if($_POST['mc_currency']==get_option("watupro_currency")) {
						$payment_currency_okay = true;
					}
					else self::log_and_exit("Currency is $_POST[mc_currency]"); 
					
					// check amount
					$exam = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_master WHERE id=%d", $_POST['item_number']));
					if($_POST['mc_gross']>=$exam->fee) {
						$payment_amount_okay = true;
					}
					else self::log_and_exit("Wrong amount: $_POST[mc_gross] when price is {$exam->fee}"); 
					
					// everything OK, insert payment
					if($payment_completed and $txn_id_okay and $receiver_okay and $payment_currency_okay 
							and $payment_amount_okay) {						
						$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}watupro_payments SET 
							exam_id=%d, user_id=%s, date=CURDATE(), amount=%s, status='completed', paycode=%s", 
							$exam->ID, $_GET['user_id'], $exam->fee, $_POST['txn_id']));
						exit;
					}
		     	}
		     	else self::log_and_exit("Paypal result is not 200 OK: $res");
		   }  
		   fclose($fp);  
		} 
		else self::log_and_exit("Can't connect to Paypal");
		
		exit;
	}
	
	// log paypal errors
	static function log_and_exit($msg) {
		// log
		$errorlog=get_option("watupro_errorlog");
		$errorlog = $msg."\n".$errorlog;
		update_option("watupro_errorlog",$errorlog);
		
		// throw exception as there's no need to contninue
		exit;
	}
}