<h2 class="nav-tab-wrapper">
	<a class='nav-tab' href="admin.php?page=watupro_reports&user_id=<?php echo $report_user_id?>"><?php _e('Overview', 'watupro')?></a>
	<a class='nav-tab' href='admin.php?page=watupro_reports&tab=tests&user_id=<?php echo $report_user_id?>'><?php _e('Tests', 'watupro')?></a>
	<a class='nav-tab' href='admin.php?page=watupro_reports&tab=skills&user_id=<?php echo $report_user_id?>'><?php _e('Skills/Categories', 'watupro')?></a>
	<a class='nav-tab-active'><?php _e('History', 'watupro')?></a>
</h2>

<div class="wrap">
	<h2><?php _e('Usage summary', 'watupro')?></h2>
	
	<div>
			<div class="postarea">
				<table>
					<tr><td><?php _e('Total exam sessions:', 'watupro')?></td><td><strong><?php echo $total_sessions;?></strong></td></tr>
					<tr><td><?php _e('Average time spent per session:', 'watupro')?></td><td><strong><?php echo self::time_spent_human($avg_time_spent);?></strong></td></tr>
					<tr><td><?php _e('Total question answered:', 'watupro')?></td><td><strong><?php echo $total_problems;?></strong></td></tr>
					<tr><td><?php _e('Average questions per session:', 'watupro')?></td><td><strong><?php echo $avg_problems;?></strong></td></tr>
					<tr><td><?php _e('Average skills per session:', 'watupro')?></td><td><strong><?php echo $avg_skills;?></strong></td></tr>
				</table>
			</div>
	</div>		
	
	<h2><?php _e('Number of tests attempted over time', 'watupro')?></h2>
	
	<div>
			<table id="chart" cellpadding="7">
				<tr>
					<?php foreach($chartlogs as $log):?>
						<td align="center" valign="bottom"><div style="width:50px;background-color:blue;height:<?php echo round($log['num_exams'] * $one_exam_height)?>px;padding:10px;font-size:2em;color:white;">
							<?php echo $log['num_exams']?>
						</div></td>						
					<?php endforeach;?>
				</tr>
				<tr>
					<?php foreach($chartlogs as $log):?>
						<td align="center"><?php echo $log['period']?></td>						
					<?php endforeach;?>
				</tr>
			</table>
	</div>	
	
	<h2><?php _e('Usage log', 'watupro')?></h2>
	
	<div>
		<table class="widefat">
			<tr><th width="50%"><?php _e('Date', 'watupro')?></th><th><?php _e('Session start time', 'watupro')?></th><th><?php _e('Session end time', 'watupro')?></th>
			<th><?php _e('Time spent', 'watupro')?></th><th><?php _e('Problems attempted', 'watupro')?></th>
			<th><?php _e('Skills/Question categories', 'watupro')?></th></tr>
			<?php foreach($logs as $log):?>
				<tr><td colspan="6" style="background:#EEE;"><?php echo $log['period']?></td></tr>
				<?php foreach($log['exams'] as $exam):?>
					<tr><td> &nbsp; - <?php echo date($date_format, $exam->start_time)?></td>
					<td><?php echo date(__('g:i A', 'watupro'),$exam->start_time)?></td>
					<td><?php echo date(__('g:i A', 'watupro'),$exam->end_time)?></td>
					<td><?php echo self::time_spent_human($exam->time_spent)?></td>
					<td align="center"><?php echo $exam->num_problems?></td>
					<td align="center"><?php echo $exam->num_skills?></td></tr>
			<?php endforeach; 
			endforeach;?>	
		</table>	
	</div>
</div>