<div class="wrap">
	<h1><?php _e("My Certificates", 'watupro');?></h1>
	
	<?php if($user_id != $user_ID):?>
		<p><?php _e('Showing certificates of ', 'watupro')?> <strong><?php echo $user->user_login?></strong></p>
	<?php endif;?>
	
	<?php if(sizeof($certificates)):?>
	
	<table class="widefat">
		<tr><th><?php _e('Exam title', 'watupro')?></th><th><?php _e('Completed on', 'watupro')?></th><th><?php _e('With result', 'watupro')?></th>
		<th><?php _e('View/Print', 'watupro')?></th></tr>
		<?php foreach($certificates as $certificate):?>
			<tr><td><strong><?php echo $certificate->exam_name;?></strong></td>
			<td><?php echo date(get_option('date_format'), strtotime($certificate->end_time)) ?></td>
			<td><?php echo $certificate->grade?></td>
			<td><?php echo "<a href='".admin_url("admin.php?page=watupro_view_certificate&taking_id=".$certificate->taking_id."&id=".$certificate->ID."&noheader=1")."' target='_blank'>".__('print your certificate', 'watupro')."</a>"?></td></tr>
		<?php endforeach;?>
	</table>
	
	<?php else:?>
		<p><?php _e('There are no accessible certificates at the moment.', 'watupro')?></p>
	<?php endif;?>
	
	<?php do_action('watupro_my_certificates');?>
</div>