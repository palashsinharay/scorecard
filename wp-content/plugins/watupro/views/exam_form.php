<script type="text/javascript">
// stop this boring validation
function validate() {
	return true;
}
</script>
<div class="wrap">
<h1><?php _e(ucfirst($action) . " Exam", 'watupro'); ?></h1>

<?php watupro_display_alerts(); ?>

<p><a href="admin.php?page=watupro_exams"><?php _e("Back to Exams List", 'watupro')?></a> 
	<?php if(!empty($dquiz->ID)):?>| <a href="#" onclick="jQuery('#copyExam').toggle();return false;"><?php _e("Copy this exam", 'watupro')?></a>
	| <a href="admin.php?page=watupro_questions&quiz=<?php echo $dquiz->ID?>"><?php _e('Manage Questions', 'watupro')?></a>
	| <a href="admin.php?page=watupro_grades&quiz=<?php echo $dquiz->ID?>"><?php _e('Manage Grades', 'watupro')?></a><?php endif;?>
</p>

<form method="post" action="admin.php?page=watupro_exam&quiz=<?php echo $dquiz->ID?>&action=edit">
<div id="copyExam" class="postbox" style="display:none;">
	<div class="inside">
		<p><?php _e("This will copy the entire exam along with its grades and questions into another exam.", 'watupro')?></p>
		
		<p><input type="radio" name="copy_option" value="new" checked="true" onclick="jQuery('#otherExams').hide();"> <?php _e("Copy into a new exam. The exam will have the same name with '(Copy)' at the end. You can edit the exam, change its name, remove questions etc, just like with every other exam that you create.", 'watupro')?></p>

		<p><input type="radio" name="copy_option" value="exsiting" onclick="jQuery('#otherExams').show();"> <?php _e("Copy into existing exam. Selecting this will result in copying only the questions.", 'watupro')?> </p>		
		
		<div id="otherExams" style="display:none;"><?php _e("Select existing exam to copy questions to:", 'watupro')?> <select name="copy_to">
		<?php foreach($other_exams as $other_exam):?>
			<option value="<?php echo $other_exam->ID?>"><?php echo $other_exam->name?></option>
		<?php endforeach;?>		
		</select></div>
		
	<p align="center" class="submit"><input type="submit" name="copy_exam" value="<?php _e('OK, Copy This Exam', 'watupro')?>">
	<input type="button" value="<?php _e('Cancel', 'watupro');?>" onclick="jQuery('#copyExam').toggle();"></p>
	</div>
</div>
</form>

<form name="post" action="admin.php?page=watupro_exam" method="post" id="post" onsubmit="return validate()">
<div id="poststuff">

<div class="postbox" id="titlediv">
    <h3 class="hndle"><span><?php _e('Exam Name', 'watupro') ?></span></h3>
    
    <div class="inside">
    <input type='text' name='name' id="title" value='<?php echo stripslashes($dquiz->name); ?>' />
    </div>
</div>

<div class="postbox" id="settings">
    <h3 class="hndle"><span><?php _e('Exam Settings', 'watupro') ?></span></h3>
    
    <div class="inside">
    <h3><span><?php _e('General Settings', 'watupro') ?></span></h3>    
    <p> <?php _e('Randomization:', 'watupro')?> <select name="randomize_questions">
			<option value="0" <?php if(empty($dquiz->randomize_questions)) echo "selected"?>><?php _e('Display questions and answers in the way I entered them','watupro')?></option>    
			<option value="1" <?php if(!empty($dquiz->randomize_questions) and $dquiz->randomize_questions==1) echo "selected"?>><?php _e('Randomize questions and answers','watupro')?></option>
			<option value="2" <?php if(!empty($dquiz->randomize_questions) and $dquiz->randomize_questions==2) echo "selected"?>><?php _e('Randomize questions but NOT answers','watupro')?></option>
    </select>  </p>
	 <p><input type="checkbox" id="groupByCat" name="group_by_cat" value="1" <?php if($dquiz->group_by_cat) echo "checked"?>> <?php _e("Show questions grouped by category (useful if you have categorized your questions)", 'watupro')?></p>  
	 <p> <?php _e("Pagination:", 'watupro')?> <select name="single_page" onchange="watuPROChangePagination(this.value);">
	 	<option value="1" <?php if($dquiz->single_page==1) echo "selected"?>><?php _e('All questions on single page', 'watupro');?></option>
	 	<option value="2" <?php if($dquiz->single_page==2) echo "selected"?>><?php _e('One page per question category', 'watupro');?></option>
	 	<option value="0" <?php if($dquiz->single_page==0) echo "selected"?>><?php _e('Each question on its own page', 'watupro');?></option>
	 </select>
	 </p>  
	 
	 <div id="disallowPrevious" <?php if((empty($dquiz->ID) and $single_page) or $dquiz->single_page) echo "style='display:none;'"?>><input type="checkbox" name="disallow_previous_button" value="1" <?php if(!empty($dquiz->disallow_previous_button)) echo "checked"?>> <?php _e('Disallow previous button', 'watupro')?> &nbsp; 
	 <input type="checkbox" name="submit_always_visible" value="1" <?php if(!empty($dquiz->submit_always_visible)) echo "checked"?>> <?php _e('Show submit button on each page', 'watupro')?> &nbsp;
	 <input type="checkbox" name="live_result" value="1" <?php if(!empty($dquiz->live_result)) echo "checked"?>> <?php _e('Answer to each question can be seen immediatelly by pressing a button', 'watupro')?></div>
	 
	 <?php if(!empty($recaptcha_public) and !empty($recaptcha_private)):?>
	 	<p><input type="checkbox" name="require_captcha" value="1" <?php if(!empty($dquiz->require_captcha)) echo "checked"?>> <?php _e('Require image validation (reCaptcha) to submit this exam', 'watupro');?></p>  
	 <?php endif;?>
    
    <p><?php _e('Set time limit of', 'watupro')?> <input type="text" name="time_limit" size="4" value="<?php echo @$dquiz->time_limit?>"> <?php _e('minutes (Leave it blank or enter 0 to not set any time limit.)', 'watupro')?></p>
    <p><?php _e('Pull', 'watupro')?> <input type="text" name="pull_random" size="4" value="<?php echo @$dquiz->pull_random?>"> <?php _e('random questions', 'watupro')?> 
		[ <input type="checkbox" name="random_per_category" value="1" <?php if(!empty($dquiz->random_per_category)) echo "checked"?>> <?php _e('per category', 'watupro')?> ]   
    <?php _e('each time when showing the exam (Leave it blank or enter 0 to show all questions)', 'watupro')?></p>
    
    <p><?php _e('Show max', 'watupro')?> <input type="text" name="num_answers" size="4" value="<?php echo @$dquiz->num_answers?>"> <?php _e('random answers to each question. Leave blank or enter 0 to show all answers (default). The correct answer will always be shown.', 'watupro')?></p>
    
    <h3 class="hndle"><span><?php _e('User and Email Related Settings', 'watupro') ?></span></h3>
    <p><input id="requieLoginChk" type="checkbox" name="require_login" value="1" <?php if($dquiz->require_login) echo "checked"?> onclick="this.checked?jQuery('#loginMode').show():jQuery('#loginMode').hide();"> <?php _e("Require user log-in", 'watupro')?></p>
    <div id="loginMode" style="margin-left:20px;display:<?php echo $dquiz->require_login?'block':'none';?>"> 
    	<fieldset>
    	<legend><b><?php _e('Logged user options', 'watupro')?></b></legend>
    	<p><?php _e('Registered users from the <a href="users.php?role=student">students database</a> will be able to take this exam. You can add students yourself or let them register themselves. For the second option you need to make sure sure that "Anyone can register" is checked in your <a href="options-general.php" target="_blank">general settings</a> page.', 'watupro')?></p>
        
        <p><input type="checkbox" name="take_again" value="1" <?php if($dquiz->take_again) echo "checked"?> onclick="this.checked?jQuery('#timesToTake').show():jQuery('#timesToTake').hide();"> <?php _e('Allow users to submit the exam multiple times:', 'watupro')?> <span id='timesToTake'<?php if(!$dquiz->take_again) echo " style='display:none;'"?>><?php _e('allow', 'watupro')?> <input type="text" size="4" name="times_to_take" value="<?php echo @$dquiz->times_to_take?>"> <?php _e('times (For unlimited times enter 0)', 'watupro')?>
				<?php if(watupro_intel()):?>
					<?php _e("but require an interval of at least")?> <input type="text" size="4" name="retake_after" value="<?php echo $dquiz->retake_after?>"> <?php _e('hours befor the exam can be resubmitted.')?>
				<?php endif;?> </span>       
        </p>
        </fieldset>
	</div>    
	
	<p><input type="checkbox" name="email_admin" value="1" <?php if($dquiz->email_admin) echo "checked"?> onclick="this.checked?jQuery('#wadminEmail').show():jQuery('#wadminEmail').hide();"> <?php _e("Send me email with details when someone takes the exam", 'watupro')?>
		<div id="wadminEmail" style="display:<?php echo $dquiz->email_admin?'block':'none'?>"><?php _e('Email address(es) to send to:', 'watupro')?> <input type="text" name="admin_email" value="<?php echo $dquiz->email_admin?$dquiz->admin_email:get_settings('admin_email');?>" size="40"></div>
	</p>
	
	<p><input type="checkbox" name="email_taker" value="1" <?php if($dquiz->email_taker) echo "checked"?>> <?php _e('Send email to the user with their results', 'watupro')?></p>
    	
		<h3><span><?php _e('Correct Answer Display', 'watupro') ?></span></h3>
		
		<input type="radio" name="show_answers" <?php if($dquiz->show_answers == '0' or ($dquiz->show_answers=='' and $answer_display=='0')) echo 'checked="checked"'; ?> value="0" id="no-show" /> <label for="no-show"><?php _e("Don't show answers", 'watupro') ?></label> <?php if($dquiz->show_answers == '0' or ($dquiz->show_answers=='' and $answer_display=='0')) echo __('(You can still use the %%ANSWERS%% variable in the final screen)', 'watupro'); ?><br />
		<input type="radio" name="show_answers" <?php if($dquiz->show_answers == '1' or ($dquiz->show_answers=='' and $answer_display=='1')) echo 'checked="checked"'; ?> value="1" id="show-end" /> <label for="show-end"><?php _e("Show answers at the end of the Quiz", 'watupro') ?></label><br />
		<br>
		
		<h3><span><?php _e('Exam Category (Optional)', 'watupro') ?></span></h3>
		
		<label><?php _e('Select category:', 'watupro')?></label> <select name="cat_id">
			<option value="0" <?php if(empty($dquiz->ID) or $dquiz->cat_id==0) echo "selected"?>><?php _e('- Uncategorized -', 'watupro')?></option>
			<?php foreach($cats as $cat):?>
				<option value="<?php echo $cat->ID?>" <?php if(!empty($dquiz->ID) and $dquiz->cat_id==$cat->ID) echo "selected"?>><?php echo $cat->name;?></option>
			<?php endforeach;?>		
		</select>
		<br />		
		  <!--  scheduled exams updates 3.5.3 -->
                <h3><span><?php _e('Schedule exam (Optional)', 'watupro') ?></span></h3>
                <br>
                <input type="checkbox" name="is_scheduled" value="1" <?php if($dquiz->is_scheduled==1) echo "checked"?>> Schedule this exam<br>
                <br>               
		
		<label><?php _e('Schedule from:', 'watupro')?></label> &emsp;
                <?php echo WTPquickDD_date('schedule_from', $schedule_from, NULL, NULL, date("Y"), date("Y") + 10 )?>
                &nbsp;
                <select name="schedule_from_hour">
                    <?php $i=0;
                    while ($i<24): ?>
                        <option value="<?php echo $i?>" <?php if(date("G",strtotime($dquiz->schedule_from))==$i) echo "selected"?>><?php printf("%02d", $i); ?></option>
                    <?php  $i++;
                    endwhile; ?>
                    
                </select>:
                
                <select name="schedule_from_minute">
                    <?php $i=0;
                    while ($i<60):  ?>
                        <option value="<?php echo $i?>" <?php if(date("i",strtotime($dquiz->schedule_from))==$i) echo "selected"?>><?php printf("%02d", $i)?></option>
                    <?php $i++;
                    endwhile; ?>
                    
                </select>
						
					 &nbsp;&nbsp;&nbsp;	
                
                <label><?php _e('Schedule to:', 'watupro')?></label> &emsp;
                <?php echo WTPquickDD_date('schedule_to', $schedule_to, NULL, NULL, date("Y"), date("Y") + 10 )?>
                &nbsp;
                <select name="schedule_to_hour">
                    <?php $i=0;
                    while ($i<24):?>
                        <option value="<?php echo $i?>" <?php if(date("G",strtotime($dquiz->schedule_to))==$i) echo "selected"?>><?php printf("%02d", $i); ?></option>
                    <?php $i++;
                    endwhile; ?>
                    
                </select>:
                
                <select name="schedule_to_minute">
                    <?php $i=0;
                    while ($i<60): ?>
                        <option value="<?php echo $i?>" <?php if(date("i",strtotime($dquiz->schedule_to))==$i) echo "selected"?>><?php printf("%02d", $i)?></option>
                    <?php $i++;
                    endwhile; ?>                    
                </select>
		<br />
	</div>
</div>

<?php if(watupro_intel()): require(WATUPRO_PATH."/i/views/exam_form_intelligence.php"); endif;?>

<!-- let's make this invisible for the moment, quiz description doesn't seem useful at all -->
<div class="postbox" style="display:none;">
<h3 class="hndle"><span><?php _e('Description', 'watupro') ?></span></h3>
<div class="inside">
<textarea name='description' rows='5' cols='50' style='width:100%'><?php echo stripslashes($dquiz->description); ?></textarea>
</div></div>

<style type="text/css"> #gradecontent p{border-bottom:1px dotted #ccc;padding-bottom:3px;} #gradecontent label{padding: 5px 10px;} #gradecontent textarea{width:96%;margin-left:10px;} #gradecontent p img.gradeclose{ border:0 none; float:right; } </style>

<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea postbox">
<h3 class="hndle"><span><?php _e('Final Screen', 'watupro') ?></span></h3>

<div class="inside">
	<?php wp_editor($final_screen,"content"); ?>
	
	<p><input type="checkbox" name="use_different_email_output" value="1" <?php if(!empty($dquiz->email_output)) echo "checked"?> onclick="this.checked?jQuery('#emailOutput').show():jQuery('#emailOutput').hide()"> <?php _e('Use different output for the email that is sent to the user and the "view details" pop-up', 'watupro')?></p>
	
	<div id="emailOutput" style="display:<?php echo empty($dquiz->email_output)?'none':'block';?>">
		<?php wp_editor(stripslashes($dquiz->email_output),"email_output"); ?>
	</div>
	
	<?php require(WATUPRO_PATH."/views/usable-variables.php");?>
</div>
</div>

<p class="submit">
<?php wp_nonce_field('watupro_create_edit_quiz'); ?>
<input type="hidden" name="action" value="<?php echo $action; ?>" />
<input type="hidden" name="quiz" value="<?php echo $_REQUEST['quiz']; ?>" />
<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<span id="autosave"></span>
<input type="submit" name="submit" value="<?php _e('Save', 'watupro') ?>" style="font-weight: bold;" tabindex="4" />
</p>

</div>
</form>

</div>

<script type="text/javascript" >
function watuPROChangePagination(val) {
	if(val==0) jQuery('#disallowPrevious').show();
	else jQuery('#disallowPrevious').hide();
	
	if(val==2) jQuery('#groupByCat').attr('checked', true);
}
</script>