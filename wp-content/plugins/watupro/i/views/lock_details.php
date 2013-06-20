<div class="wrap">
	<h2><?php _e('This exam is currently locked.', 'watupro')?></h2>
	<p><?php _e("To take this exam you need first to complete the following dependencies:", 'watupro')?></p>
	
	<table class="widefat" align="center">
		<tr><th><?php _e("Dependency", 'watupro')?></th> <th><?php _e("Status", 'watupro')?></th></tr>
		<?php foreach($dependencies as $dependency):?>
			<tr><td><?php _e('The exam', 'watupro')?> <strong><?php echo $dependency->exam?></strong> must be completed 
				<strong><?php if(strstr($dependency->final_screen, "%%POINTS%%")): printf(__('with at least %d points', 'watupro'), $dependency->depend_points);
				else: _e('successfully', 'watupro');
				endif;?></strong>. </td>
				<td><?php if($dependency->satisfied):?>
					<img src="<?php echo plugins_url('watupro').'/correct.png'?>">
				<?php else:?>
					<img src="<?php echo plugins_url('watupro').'/wrong.png'?>">
				<?php endif;?></td></tr>
		<?php endforeach;?>
	</table>
</div>