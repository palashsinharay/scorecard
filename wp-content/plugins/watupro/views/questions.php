<div class="wrap">
	<h1><?php _e("Manage Questions in ", 'watupro')?> <?php echo $exam_name; ?></h1>
	
	<p><a href="admin.php?page=watupro_exams"><?php _e('Back to exams list', 'watupro')?></a> 
	&nbsp;
	<a href="edit.php?page=watupro_exam&quiz=<?php echo $_GET['quiz']?>&action=edit"><?php _e('Edit this exam', 'watupro')?></a>
	&nbsp;
	<a href="admin.php?page=watupro_grades&quiz=<?php echo $_GET['quiz']?>"><?php _e('Manage Grades', 'watupro')?></a>
	&nbsp;
	<a href="#" onclick="jQuery('#importQuestions').toggle();"><?php _e('Import Questions', 'watupro')?></a>
	&nbsp;
	<a href="admin.php?page=watupro_questions&export=1&exam_id=<?php echo $_GET['quiz']?>&noheader=1&copy=1"><?php _e('Export To Copy', 'watupro')?></a>
	&nbsp;
	<a href="admin.php?page=watupro_questions&export=1&exam_id=<?php echo $_GET['quiz']?>&noheader=1"><?php _e('Export To Edit', 'watupro')?></a></p>
	
<p class="note"><?php _e('Note: Questions can be exported in a TAB delimited CSV file', 'watupro')?></p>
	
<div id="importQuestions" style="display:none;padding:10px;" class="widefat">
		<form method="post" enctype="multipart/form-data" onsubmit="return validateWatuproImportForm(this);" action="admin.php?page=watupro_questions&quiz=<?php echo $_REQUEST['quiz']?>&noheader=1">
		
			<h3><?php _e('What file are you importing?', 'watupro')?></h3>
			
			<div><input type="radio" name="file_type" value="new" checked="true" onclick="jQuery('#previousFileImport').hide();jQuery('#newFileImport').show();"> <?php _e("I'm importing new questions. (This is a file exported by clicking on 'Export to copy' or a file that you created yourself.)", 'watupro')?></div>
			<div id="newFileImport"><p><?php _e('In this case all questions and answers will be added as new. The format of the CSV file should be: <strong>Question; Answer Type: "radio", "checkbox", or "textarea", Order (leave "0" to auto-order the questions); Category (optional); Explanation/Feedback (optional); Required: "0" for not required, "1" for required question. After that answers start like this: answer; points; answer; points;', 'watupro')?></p>
			<p><?php _e('Optional columns should still be present but their values can be empty.', 'watupro')?></p></strong>
			<p><a href="http://calendarscripts.info/watupro/import-sample.csv" target="_blank"><?php _e('See a very basic sample.', 'watupro')?></a> <?php _e('This file is a semicolon delimited and contains one question with 4 answers', 'watupro')?></p></div>				
			
			
			<div><input type="radio" name="file_type" value="old" onclick="jQuery('#previousFileImport').show();jQuery('#newFileImport').hide();"> <?php _e("I'm importing a file with edited questions. (This file was created by clicking 'Export to edit' link)", 'watupro')?></div>
			<div id="previousFileImport"><p><?php _e('In this case we will assume you are keeping the same format that was exported. If you have added new questions or answers please make sure their ID columns contain "0". Questions or answers with unrecognized IDs will be ignored.', 'watupro')?></p></div>			
		</fieldset>	
	
		<p><label><?php _e('CSV File:', 'watupro')?></label> <input type="file" name="csv"></p>
		
		<p><label><?php _e('Fields Delimiter:', 'watupro')?></label> <select name="delimiter">
			<option value="tab"><?php _e('Tab', 'watupro')?></option>
			<option value=";"><?php _e('Semicolon', 'watupro')?></option>			
			<option value=","><?php _e('Comma', 'watupro')?></option>		
			</select>	
		
		<p><input type="submit" value="Import Questions">
		<input type="button" value="Cancel" onclick="jQuery('#importQuestions').hide();"></p>
		<input type="hidden" name="import" value="1">
	</form>
</div>

	<p style="color:green;"><?php _e('To add this exam to your blog, insert the code ', 'watupro') ?> <b>[WATUPRO <?php echo $_REQUEST['quiz'] ?>]</b> <?php _e('into any post or page.', 'watupro') ?></p>
	
	<?php $intelligence_display=""; // variable used to hide the div with own questions if required
	if(watupro_intel()):
	require_once(WATUPRO_PATH."/i/models/question.php");
	WatuPROIQuestion::reuse_questions($exam, $intelligence_display);
	endif;?>
	
<div id="watuProQuestions" <?php echo $intelligence_display;?>>
	<?php if(!empty($qcats) and sizeof($qcats)):?>
	<form method="post">
		<p><label><?php _e('Filter by category:')?></label> <select name="filter_cat_id" onchange="this.form.submit();">
		<option value=""><?php _e('- All categories -', 'watupro')?></option>
		<?php foreach($qcats as $cat):?>
			<option value="<?php echo $cat->ID?>"<?php if(!empty($_POST['filter_cat_id']) and $_POST['filter_cat_id']==$cat->ID) echo ' selected'?>><?php echo $cat->name?></option>
		<?php endforeach;?>
		<option value="-1"<?php if(!empty($_POST['filter_cat_id']) and $_POST['filter_cat_id']==-1) echo ' selected'?>><?php _e('Uncategorized', 'watupro')?></option>
		</select></p>	
	</form>
	<?php endif;?>	

	<table class="widefat">
		<thead>
		<tr>
			<th scope="col"><div style="text-align: center;">#</div></th>
			<th scope="col"><?php _e('Question', 'watupro') ?></th>
			<th scope="col"><?php _e('Category', 'watupro') ?></th>
			<th scope="col"><?php _e('Number Of Answers', 'watupro') ?></th>
			<th scope="col" colspan="3"><?php _e('Action', 'watupro') ?></th>
		</tr>
		</thead>
	
		<tbody id="the-list">
	<?php
	if (count($all_question)) {
		$bgcolor = '';
		$class = ('alternate' == $class) ? '' : 'alternate';
		$question_count = 0;
		foreach($all_question as $question) {
			$question_count++;
			print "<tr id='question-{$question->ID}' class='$class'>\n";
			?>
			<th scope="row" style="text-align: center;">
			<div style="float:left;<?php if(!empty($_POST['filter_cat_id'])) echo 'display:none;'?>">
				<?php if($question_count>1):?>
					<a href="admin.php?page=watupro_questions&quiz=<?php echo $_GET['quiz']?>&move=<?php echo $question->ID?>&dir=up"><img src="<?php echo plugins_url('watupro/img/arrow-up.png')?>" alt="<?php _e('Move Up', 'watupro')?>" border="0"></a>
				<?php else:?>&nbsp;<?php endif;?>
				<?php if($question_count<$num_questions):?>	
					<a href="admin.php?page=watupro_questions&quiz=<?php echo $_GET['quiz']?>&move=<?php echo $question->ID?>&dir=down"><img src="<?php echo plugins_url("watupro/img/arrow-down.png")?>" alt="<?php _e('Move Down', 'watupro')?>"></a>
				<?php else:?>&nbsp;<?php endif;?>
			</div>			
			<?php echo $question_count ?></th>
			<td><?php echo stripslashes($question->question) ?></td>
			<td><?php echo $question->cat?$question->cat:__("Uncategorized", 'watupro')?></td>
			<td><?php echo $question->answer_count ?></td>
			<td><a href='admin.php?page=watupro_question&amp;question=<?php echo $question->ID?>&amp;action=edit&amp;quiz=<?php echo $_REQUEST['quiz']?>' class='edit'><?php _e('Edit', 'watupro'); ?></a></td>
			<td><a href='admin.php?page=watupro_questions&amp;action=delete&amp;question=<?php echo $question->ID?>&amp;quiz=<?php echo $_REQUEST['quiz']?>' class='delete' onclick="return confirm('<?php echo addslashes(__("You are about to delete this question. This will delete the answers to this question. Press 'OK' to delete and 'Cancel' to stop.", 'watupro'))?>');"><?php _e('Delete', 'watupro')?></a></td>
			</tr>
	<?php
			}
		} else {
	?>
		<tr style='background-color: <?php echo $bgcolor; ?>;'>
			<td colspan="4"><?php _e('No questiones found.', 'watupro') ?></td>
		</tr>
	<?php
	}
	?>
		</tbody>
	</table>
	
	<a href="admin.php?page=watupro_question&amp;action=new&amp;quiz=<?php echo $_REQUEST['quiz'] ?>"><?php _e('Create New Question', 'watupro')?></a>
	
	<p><?php _e("Note: you can use the up/down arrows to reorder questions. This will take effect for exams whose questions are <b>not randomized</b>.", 'watupro');?></p>
	
</div>

<script type="text/javascript" >
function validateWatuproImportForm(frm)
{
	if(frm.csv.value=="")
	{
		alert("<?php _e('Please select CSV file.', 'watupro')?>");
		frm.csv.focus();
		return false;
	}
}
</script>