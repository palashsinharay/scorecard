<div class="wrap">
<h2><?php _e("Manage Exams", 'watupro'); ?></h2>

<?php watupro_display_alerts(); ?>

<p><?php _e('Go to', 'watupro')?> <a href="admin.php?page=watupro_options"><?php _e('Watu PRO Settings', 'watupro')?></a></p>

<p><a href="admin.php?page=watupro_exam&amp;action=new"><?php _e("Create New Exam", 'watupro')?></a></p>

<table class="widefat">
	<thead>
	<tr>
		<th scope="col"><div style="text-align: center;"><?php _e('ID', 'watupro') ?></div></th>
		<th scope="col"><?php _e('Title', 'watupro') ?></th>
        <th scope="col"><?php _e('Embed Code', 'watupro') ?></th>
		<th scope="col"><?php _e('Number Of Questions', 'watupro') ?></th>
		<th scope="col"><?php _e('Created on', 'watupro') ?></th>
		<th scope="col"><?php _e('Category', 'watupro') ?></th>
		<th scope="col"><?php _e('Taken', 'watupro') ?></th>
		<th scope="col"><?php _e('Manage Questions', 'watupro') ?></th>
		<th scope="col"><?php _e('Manage Grades', 'watupro') ?></th>
		<th scope="col" colspan="2"><?php _e('Edit/Delete', 'watupro') ?></th>
	</tr>
	</thead>

	<tbody id="the-list">
<?php
if (count($exams)):
	foreach($exams as $quiz):
		$class = ('alternate' == $class) ? '' : 'alternate';
		print "<tr id='quiz-{$quiz->ID}' class='$class'>\n";
		?>
		<th scope="row" style="text-align: center;"><?php echo $quiz->ID ?></th>
		<td><?php if(!empty($quiz->post)) echo "<a href='".get_permalink($quiz->post->ID)."' target='_blank'>"; 
		echo stripslashes($quiz->name);
		if(!empty($quiz->post)) echo "</a>";?></td>
        <td>[WATUPRO <?php echo $quiz->ID ?>]</td>
		<td><?php echo empty($quiz->reuse_questions_from) ? $quiz->question_count : __('Reuses from other test')?></td>
		<td><?php echo date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($quiz->added_on)) ?></td>
		<td><?php echo $quiz->cat?$quiz->cat:__("Uncategorized", 'watupro');?></td>
		<td><a href="admin.php?page=watupro_takings&exam_id=<?php echo $quiz->ID;?>"><?php echo $quiz->taken?> times</a></td>
		<td><a href='admin.php?page=watupro_questions&amp;quiz=<?php echo $quiz->ID?>' class='edit'><?php _e('Questions', 'watupro')?></a></td>
		<td><a href='admin.php?page=watupro_grades&amp;quiz=<?php echo $quiz->ID?>' class='edit'><?php _e('Grades', 'watupro')?></a></td>
		<td><a href='admin.php?page=watupro_exam&amp;quiz=<?php echo $quiz->ID?>&amp;action=edit' class='edit'><?php _e('Edit', 'watupro'); ?></a></td>		
		<td><a href='admin.php?page=watupro_exams&amp;action=delete&amp;quiz=<?php echo $quiz->ID?>' class='delete' onclick="return confirm('<?php echo  addslashes(__("You are about to delete this quiz? This will delete all the questions and answers within this quiz. Press 'OK' to delete and 'Cancel' to stop.", 'watupro'))?>');"><?php _e('Delete', 'watupro')?></a></td>
		</tr>
<?php endforeach;?>
	<tr><td colspan="8"><p><strong><?php _e('To publish any of the existing tests simply copy the "Embed code" shown in the table above and paste it in a post or page of your blog.', 'watupro')?></strong></p></td></tr>	
<?php else:?>
	<tr>
		<td colspan="7"><?php _e('No tests found.', 'watupro') ?></td>
	</tr>
<?php endif;?>
	</tbody>
</table>
</div>