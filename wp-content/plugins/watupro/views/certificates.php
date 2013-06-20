<div class="wrap">
	<h1><?php _e('Watu PRO Certificates', 'watupro')?></h1>

	<p><?php _e('These certificates are optional and can be assigned to grades. Then when <b>logged in</b> user takes an exam and receives a grade which has assigned certificate, they will see a link to print this certificate, optionally personalized with their details.', 'watupro')?></p>

	<?php if(sizeof($certificates)):?>
		<table class="widefat">
        <tr><td colspan="2"><a href="admin.php?page=watupro_certificates&do=add"><?php _e('Click here to add new certificate', 'watupro')?></a></td></tr>
		<tr><th><?php _e('Certificate Title', 'watupro')?></th><th><?php _e('Edit', 'watupro')?></th></tr>
		<?php foreach($certificates as $certificate):?>
		<tr><td><a href="admin.php?page=watupro_view_certificate&id=<?php echo $certificate->ID?>&noheader=1" target="_blank"><?php echo $certificate->title;?></a></td><td><a href="admin.php?page=watupro_certificates&do=edit&id=<?php echo $certificate->ID?>"><?php _e('Edit', 'watupro')?></a></td></tr>
		<?php endforeach;?>
		</table>
	<?php else:?>
    <p><?php _e('You have not created any certificates yet.', 'watupro')?> <a href="admin.php?page=watupro_certificates&do=add"><?php _e('Click here', 'watupro')?></a> <?php _e('to create one.', 'watupro')?></p>
	<?php endif;?>
</div>