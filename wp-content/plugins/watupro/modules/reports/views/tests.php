<h2 class="nav-tab-wrapper">
	<a class='nav-tab' href="admin.php?page=watupro_reports&user_id=<?php echo $report_user_id?>"><?php _e('Overview', 'watupro')?></a>
	<a class='nav-tab-active'><?php _e('Tests', 'watupro')?></a>
	<a class='nav-tab' href='admin.php?page=watupro_reports&tab=skills&user_id=<?php echo $report_user_id?>'><?php _e('Skills/Categories', 'watupro')?></a>
	<a class='nav-tab' href='admin.php?page=watupro_reports&tab=history&user_id=<?php echo $report_user_id?>'><?php _e('History', 'watupro')?></a>
</h2>

<div class="wrap">
	 <table class="widefat">
			<tr><th><?php _e("Exam name", 'watupro');?></th><th><?php _e('Time spent', 'watupro')?></th>
			<th><?php _e('Problems attempted', 'watupro')?></th><th><?php _e('Score and Grade', 'watupro')?></th></tr>
			<?php foreach($exams as $exam):?>
				<tr><td><?php if(!empty($exam->post)) echo "<a href='".get_permalink($exam->post->ID)."' target='_blank'>"; 
				echo stripslashes($exam->name);
				if(!empty($exam->post)) echo "</a>";?></td>
				<td><?php echo self::time_spent_human($exam->time_spent);?></td>
				<td><?php echo $exam->cnt_answers?></td>
				<td><?php echo wpautop($exam->result)?> <p><strong><?php printf(__("(with %d points)", 'watupro'), $exam->points)?></strong></p></td></tr>
			<?php endforeach;?>	 
	 </table>
</div>