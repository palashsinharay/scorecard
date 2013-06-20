<div class="wrap">
	<h1><?php printf(__('Manage Grades in %s', 'watupro'), $exam->name)?></h1>

	<p><a href="admin.php?page=watupro_exams"><?php _e("Back to Exams List", 'watupro')?></a>
	| <a href="admin.php?page=watupro_exam&quiz=<?php echo $exam->ID?>&action=edit"><?php _e('Edit Exam', 'watupro')?></a>
	| <a href="admin.php?page=watupro_questions&quiz=<?php echo $exam->ID?>"><?php _e('Manage Questions', 'watupro')?></a>	
</p>

	<?php if(sizeof($cats)):?>
		<form method="post">
			<p><?php _e('Manage grades for', 'watupro')?> <select name="cat_id" onchange="this.form.submit();">
				<option value="0" <?php if($cat_id == 0) echo 'selected'?>><?php _e('- The Whole Exam -', 'watupro')?></option>
				<?php foreach($cats as $cat):?>
					<option value="<?php echo $cat->ID?>" <?php if($cat_id == $cat->ID) echo "selected"?>><?php echo __('Category:','watupro').' '.$cat->name?></option>
				<?php endforeach;?>			
			</select></p>	
			
			<?php if(!empty($cat_id)):?>
				<p><a href="#" onclick="jQuery('#gradecatDesign').toggle();return false;"><?php _e('Design the common category grade output for this quiz', 'watupro')?></a></p>
				<div id="gradecatDesign" style="display:<?php echo empty($_POST['save_design'])?'none':'block';?>;">
				   <h2><?php _e('Design the common category grade output for this quiz', 'watupro')?></h2>
					<p><strong><?php _e('Note: you are currently managing category-specific grades. These can be displayed at the final screen using the %%CATGRADES%% variable. All of them will be shown in loop at the place of the variable. In the box below you can design how each of the category grades will look.', 'watupro')?></strong></p>
					<p><strong><?php _e('This design is the same for all question categories in this exam.', 'watupro')?></strong></p>
					
					
					<?php echo wp_editor($exam->gradecat_design, 'gradecat_design');?>
					
					<p><?php _e('You can use several of the already known variables: <strong>%%CORRECT%%, %%TOTAL%%, %%POINTS%%, %%PERCENTAGE%%, %%GTITLE%%, %%GDESC%%</strong>, and the new variable <strong>%%CATEGORY%%</strong> that will be replaced by the category name', 'watupro')?></p>
					
					<p align="center"><input type="submit" value="<?php _e('Save the Design', 'watupro')?>" name="save_design"></p>
				</div>	
			<?php endif;?>	
		</form>
	<?php else:?>
		<p><?php _e('If you create <a href="admin.php?page=watupro_question_cats">question categories</a> you will be able to create category-based grades as well.', 'watupro')?></p>
	<?php endif;?>

	<hr>	
	
	
	<h2><?php _e('Add New Grade', 'watupro')?></h2>
	
	<p class="help"><strong><?php _e("Feel free to enter valid URL instead of grade title - I'll redirect to it instead of showing the final screen!", 'watupro')?></strong></p>
	<form method="post" onsubmit="return validateGrade(this);">
		<div class="watupro-padded">
			<div><?php _e('Grade Title:', 'watupro')?> <input type="text" name="gtitle" size="60"></div>
			<div><?php _e('Grade Description:', 'watupro')?> <?php echo wp_editor('', 'gdescription')?></div>
			<div><?php if($exam->grades_by_percent and watupro_intel()): _e('Assign this grade when % correct answers is from', 'watupro');				 
			else: _e('Assign this grade when  the points that user has collected are from', 'watupro');
			endif;?>
			<input type="text" name="gfrom" size="5"> <?php _e('to', 'watupro')?> <input type="text" name="gto" size="5"></div>
			<?php if($cnt_certificates):?>
				<div><label><?php _e('Upon achieving this grade assign the following certificate:', 'watupro')?></label> <select name="certificate_id">
				<option value="0"><?php _e("- Don't assign certificate", 'watupro')?></option>
				<?php foreach($certificates as $certificate):?>
					<option value="<?php echo $certificate->ID;?>"><?php echo $certificate->title;?></option>
				<?php endforeach;?>
				</select></div>
			<?php endif;?>
			<div align="center"><input type="submit" value="<?php _e('Add This Grade', 'watupro')?>"></div>
		</div>
		<input type="hidden" name="add" value="1">
		<input type="hidden" name="cat_id" value="<?php echo $cat_id?>">	
	</form>
	
	<hr>
	<?php if(sizeof($grades)):?>
	<h2><?php _e('Edit Existing Grades', 'watupro')?></h2>
	<?php endif;?>
	
	<?php foreach($grades as $grade):?>
		<form method="post" onsubmit="return validateGrade(this);">
			<div class="watupro-padded">
				<div><?php _e('Grade Title:', 'watupro')?> <input type="text" name="gtitle" size="80" value="<?php echo $grade->gtitle?>"></div>
				<div><?php _e('Grade Description:', 'watupro')?> <?php echo wp_editor(stripslashes($grade->gdescription), 'gdescription'.$grade->ID)?></div>
				<div><?php if($exam->grades_by_percent and watupro_intel()): _e('Assign this grade when % correct answers is from', 'watupro');				 
				else: _e('Assign this grade when  the points that user has collected are from', 'watupro');
				endif;?>
				<input type="text" name="gfrom" size="5" value="<?php echo $grade->gfrom?>"> <?php _e('to', 'watupro')?> <input type="text" name="gto" size="5" value="<?php echo $grade->gto?>"></div>
				<?php if($cnt_certificates):?>
					<div><label><?php _e('Upon achieving this grade assign the following certificate:', 'watupro')?></label> <select name="certificate_id">
					<option value="0" <?php if(empty($row->ID) or $row->certificate_id==0) echo "selected"?>><?php _e("- Don't assign certificate", 'watupro')?></option>
					<?php foreach($certificates as $certificate):?>
						<option value="<?php echo $certificate->ID;?>" <?php if(!empty($grade->ID) and $grade->certificate_id==$certificate->ID) echo "selected"?>><?php echo $certificate->title;?></option>
					<?php endforeach;?>
					</select></div>
				<?php endif;?>
				<div align="center"><input type="submit" value="<?php _e('Save Grade', 'watupro')?>">
				<input type="button" value="<?php _e('Delete Grade', 'watupro')?>" onclick="confirmDelGrade(this.form);"></div>
			</div>
			<input type="hidden" name="id" value="<?php echo $grade->ID?>">
			<input type="hidden" name="save" value="1">
			<input type="hidden" name="del" value="0">
			<input type="hidden" name="cat_id" value="<?php echo $cat_id?>">	
		</form>
		
		<hr>
	<?php endforeach;?>
</div>
<script type="text/javascript" >
function validateGrade(frm) {
	if(frm.gtitle.value=="") {
		alert("<?php _e('Please enter grade title','watupro')?>");
		frm.gtitle.focus();
		return false;
	}
	
	if(frm.gfrom.value=="" || isNaN(frm.gfrom.value)) {
		alert("<?php _e('Please enter number','watupro')?>");
		frm.gfrom.focus();
		return false;
	}
	
	if(frm.gto.value=="" || isNaN(frm.gto.value)) {
		alert("<?php _e('Please enter number','watupro')?>");
		frm.gto.focus();
		return false;
	}
	
	return true;
}

function confirmDelGrade(frm) {
	if(confirm("<?php _e('Are you sure?', 'watupro')?>")) {
		frm.del.value=1;
		frm.submit();
	}
}
</script>