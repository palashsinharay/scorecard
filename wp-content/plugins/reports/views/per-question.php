<div class="wrap">
	<h2><?php echo $exam->name?> : <?php _e('Stats Per Question', 'watupro')?></h2>
	
	<p><a href="admin.php?page=watupro_takings&exam_id=<?php echo $exam->ID?>"><?php _e("Back to the users list", 'watupro')?></a></p>
	
	<?php foreach($questions as $cnt=>$question):
	$cnt++;?>
		<h3><?php echo $cnt.". ".$question->question?></h3>
		
		<table class="widefat">
			<tr><th><?php _e('Answer or metric', 'watupro')?></th><th><?php _e('Value', 'watupro')?></th></tr>
			<tr><td><?php _e('Number and % correct answers', 'watupro')?></td>
			<td><strong><?php echo $question->percent_correct?>%</strong> / <strong><?php echo $question->num_correct?></strong> <?php _e('correct answers from', 'watupro')?>
			<strong><?php echo $question->total_answers?></strong> <?php _e('total answers received', 'watupro')?> </td></tr>
			<?php foreach($question->choices as $choice):?><tr>
				<td><?php echo $choice->answer?></td><td><strong><?php echo $choice->times_selected?></strong> <?php _e('times selected', 'watupro')?> / <strong><?php echo $choice->percentage?>%</strong> </td>			
			</tr><?php endforeach;?>
		</table>
	<?php endforeach;?>
</div>	