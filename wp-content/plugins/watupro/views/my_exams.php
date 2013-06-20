<h1><?php _e("My Exams", 'watupro');?></h1>

<?php if($user_id != $user_ID):?>
	<p><?php _e('Showing exams of ', 'watupro')?> <strong><?php echo $user->user_login?></strong></p>
<?php endif;?>

<h2><?php _e("Exams to complete", 'watupro')?></h2>
<?php if($num_to_take):?>
	<table class="widefat">
	<tr><th><?php _e('Exam title', 'watupro')?></th><th><?php _e("In post/page", 'watupro')?></th>
	<th><?php _e("Category", 'watupro')?></th></tr>
	<?php foreach($my_exams as $exam):
		if($exam->is_taken) continue;?>
		<tr><td><?php echo $exam->name;?></td>
		<td><?php if(empty($exam->locked)):?>
			<a href="<?php echo get_permalink($exam->post->ID)?>" target="_blank"><?php echo $exam->post->post_title?></a>
		<?php else:?> 
			<a href="#" onclick="WatuPRODep.lockDetails(<?php echo $exam->ID?>, '<?php echo admin_url()?>');return false;"><b><?php _e("Locked", 'watupro')?></b></a> 
		<?php endif;?></td>
		<td><?php echo $exam->cat?$exam->cat:__('Uncategorized', 'watupro');?></td></tr>
	<?php endforeach;?>
	</table>
<?php else:?>
	<p><?php _e('There are no open exams to complete at this time.', 'watupro')?></p>
<?php endif;?>

<h2><?php _e('Completed exams', 'watupro')?></h2>
<?php if($num_taken):?>
	<table class="widefat">
	<tr><th><?php _e('Exam title', 'watupro')?></th><th><?php _e('In post/page', 'watupro')?></th><th><?php _e('Points', 'watupro')?></th>
	<th><?php _e('Result/Grade', 'watupro')?></th><th><?php _e('Details', 'watupro')?></th></tr>
	<?php foreach($my_exams as $exam):
		if(!$exam->is_taken) continue;?>
		<tr><td><?php echo $exam->name;?></td><td><a href="<?php echo get_permalink($exam->post->ID)?>" target="_blank"><?php echo $exam->post->post_title?></a></td>
		<td><?php echo $exam->taking->points;?></td>
		<td><?php echo $exam->taking->result;?></td>
		<td><a href="#" onclick="WatuPRO.takingDetails('<?php echo $exam->taking->ID?>','<?php echo admin_url()?>');return false;"><?php _e('view', 'watupro')?></a></td></tr>
	<?php endforeach;?>
	</table>
<?php else:?>
	<p><?php _e('There are no completed exams yet.', 'watupro')?></p>
<?php endif;?>