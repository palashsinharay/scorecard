<form method="post">
	<p><input type="checkbox" name="reuse_questions" <?php if($exam->reuse_questions_from) echo "checked"?> onclick="watuProIReuseQuestions(this);" value="1"> <?php _e('Reuse questions from another exam:', 'watupro')?> <select name="reuse_questions_from">
		<option value="0"><?php _e('- Please select -', 'watupro')?></option>
		<?php foreach($exams as $ex):?>
			<option value="<?php echo $ex->ID?>" <?php if($ex->ID == $exam->reuse_questions_from) echo "selected"?>><?php echo $ex->name . ' (ID '.$ex->ID.')'?></option>
		<?php endforeach;?>
	</select>
	<input type="submit" value="<?php _e('Save', 'watupro')?>"></p>
	<input type="hidden" name="ok" value="1">
</form>

<script type="text/javascript" >
function watuProIReuseQuestions(chk) {
	if(chk.checked) {
		jQuery('#watuProQuestions').hide();
	}
	else {
		jQuery('#watuProQuestions').show();
	}
}
</script>