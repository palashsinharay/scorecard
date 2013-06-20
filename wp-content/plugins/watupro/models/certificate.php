<?php
class WatuPROCertificate {
	// returns certificate link and inserts the certificate in user-certificates table
	static function assign($exam, $taking_id, $certificate_id, $user_id) {
		global $wpdb;		
		
		$certificate = "<p>".__('You can now ', 'watupro')."<a href='".admin_url("admin.php?page=watupro_view_certificate&taking_id=$taking_id&id=".$certificate_id."&noheader=1")."' target='_blank'>".__('print your certificate', 'watupro')."</a></p>";
       
    // store in user certificates   
    $sql = "INSERT INTO ".WATUPRO_USER_CERTIFICATES." (user_id, certificate_id, exam_id, taking_id) 
    	VALUES (%d, %d, %d, %d) ";
    $wpdb->query($wpdb->prepare($sql, $user_id, $certificate_id, $exam->ID, $taking_id));
    
    return $certificate;
	}
}