<h1><?php _e('Edit and Manually Grade Test Results', 'watupro')?></h1>

<div class="wrap">
	<p><?php _e('Student:', 'watupro')?> <?php echo $taking->user_id?$student->user_login:$taking->email?></p>
	<p><?php _e('Exam:', 'watupro')?> <a href="admin.php?page=watupro_takings&exam_id=<?php echo $exam->ID?>"><?php echo $exam->name?></a></p>
	
	<p><?php _e('You can use this page to manually edit and grade the submitted test details', 'watupro')?></p>
	
	<form method="post" onsubmit="return validateForm(this);">
	<table class="widefat">
		<tr><th><?php _e('Question', 'watupro')?></th><th><?php _e('Answer Given', 'watupro')?></th>
		<th><?php _e('Points', 'watupro')?></th><th><?php _e('Is correct?', 'watupro')?></th>
		<th><?php _e('Optional comments', 'watupro')?></th></tr>
		
		<?php foreach($answers as $answer):?>
			<tr><td><?php echo apply_filters('watupro_content', stripslashes($answer->question))?></td>
			<td><?php echo wpautop(stripslashes($answer->answer))?></td>
			<td><input type="text" size="6" value="<?php echo $answer->points?>" name="points<?php echo $answer->ID?>"></td>
			<td><input type="checkbox" name="is_correct<?php echo $answer->ID?>" value="1" <?php if($answer->is_correct) echo "checked"?>></td>
			<td><textarea rows="3" cols="30" name="teacher_comments<?php echo $answer->ID?>"><?php echo stripslashes($answer->teacher_comments)?></textarea></td></tr>
		<?php endforeach;?>	
	</table>
	
	<p><input type="checkbox" name="send_email" value="1" onclick="jQuery('#emailDetails').toggle();"> <?php _e("I want to sent email to the user with the updated details", 'watupro')?></p>
	
	<div id="emailDetails" style="display:none;" class="watupro">
		<div><label><?php _e('Receiver email', 'watupro');?></label> <input type="text" name="email" value="<?php echo $receiver_email?>"></div>
		<div><label><?php _e('Subject:', 'watupro')?></label> <input type="text" name="subject" size="60" value="<?php echo get_option('watupro_manual_grade_subject')?>"></div>
		<div><label><?php _e('Message:', 'watupro')?></label> <?php echo wp_editor(get_option('watupro_manual_grade_message'), 'message')?></div>
		<?php $edit_mode = true; 
		require(WATUPRO_PATH."/views/usable-variables.php")?>
	</div>
	
	<p align="center"><input type="submit" value="<?php _e('Update Test Results', 'watupro')?>"></p>
	<input type="hidden" name="ok" value="1">
	</form>
</div>

<script type="text/javascript" >
function validateForm(frm) {
		if(frm.send_email.checked) {
				if(frm.email.value=="") {
						alert("<?php _e('Please enter receiver email', 'watupro')?>");
						frm.email.focus();
						return false;
				}				
				
				if(frm.email.value=="") {
						alert("<?php _e('Please enter email subject', 'watupro')?>");
						frm.subject.focus();
						return false;
				}				
		}
		
		return true;
}
</script>