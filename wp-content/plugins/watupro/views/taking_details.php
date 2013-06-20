<link type="text/css" rel="stylesheet" href="<?php echo plugins_url('/watupro/style.css') ?>" />
<div class="wrap">
	<h2><?php _e('Details for taken exam ', 'watupro')?>"<?php echo $exam->name?>"</h2>
	<?php if(current_user_can(WATUPRO_MANAGE_CAPS) and empty($_GET['export'])):?>
		<p><?php _e('User:', 'watupro')?> <?php echo $taking->user_id?"<a href='user-edit.php?user_id=".$taking->user_id."&wp_http_referer=".urlencode("admin.php?page=watupro_takings&exam_id=".$exam->ID)."' target='_blank'>".$student->display_name."</a>":($taking->email?$taking->email:"<b>N/A</b>")?></p>
	<?php endif;?>
	<p><?php _e('Date:', 'watupro')?> <?php echo date(get_option('date_format'), strtotime($taking->date)) ?></p>
	<p><?php _e('Total points collected:', 'watupro')?> <b><?php echo $taking->points;?></b></p>
	<p><?php _e('Achieved grade:', 'watupro')?> <b><?php echo $taking->result;?></b></p>
	
	<?php if(empty($_GET['export'])):?>
		<p><?php _e('The textual details below show exact snapshot of the questions in the way that student have seen them when taking the exam. If you have added, edited or deleted questions since then you will not see these changes here.', 'watupro')?></p>
		<p><a href="#" onclick="jQuery('#detailsText').show();jQuery('#detailsTable').hide();return false;"><?php _e('Snapshot', 'watupro')?></a> &nbsp; <a href="#" onclick="jQuery('#detailsText').hide();jQuery('#detailsTable').show();return false;"><?php _e('Table format', 'watupro')?></a>
		&nbsp; <a href="admin-ajax.php?action=watupro_taking_details&noheader=1&id=<?php echo $taking->ID?>&export=1"><?php _e('Download', 'watupro')?></a></p>
	<?php endif;?>	
	
	<?php if(empty($_GET['export'])):?>
	<div id="detailsText" style="background:#EEE;padding:5px;">	
	<p><?php echo wpautop(WatuPRO::cleanup($taking->details, 'web')); ?></p>
	</div>
	<?php endif;?>
	
	
	<?php if(empty($_GET['export'])):?> <div id="detailsTable" style="display:none;"> <?php endif;?>	
	<table align="center" class="widefat">
	<tr><th><?php _e('Question', 'watupro')?></th><th><?php _e('Answer(s) given', 'watupro')?></th><th><?php _e('Points received', 'watupro')?></th></tr>
	<?php foreach($answers as $answer):?>
		<tr><td><?php echo wpautop(stripslashes($answer->question_text))?></td><td><?php echo nl2br($answer->answer)?></td>
		<td><?php echo $answer->points?></td></tr>
	<?php endforeach;?>
	</table>
	<?php if(empty($_GET['export'])):?></div><?php endif;?>
	
</div>