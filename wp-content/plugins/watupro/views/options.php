<div class="wrap">
<h2><?php _e("Watu PRO Settings", 'watupro'); ?></h2>

<p><?php _e('Go to', 'watupro')?> <a href="admin.php?page=watupro_exams"><?php _e("Manage Your Exams", 'watupro')?></a></p>

<form name="post" action="" method="post" id="post">
<div id="poststuff">
<div id="postdiv" class="postarea">

<?php showWatuOption('single_page', 'Show all questions in a <strong>single page</strong> (This global setting can be overwritten for every exam)'); ?><br />

<?php showWatuOption('delete_db', 'Delete Watu PRO data when uninstalling the plugin. This will not delete anything if you only deactivate the plugin.'); ?><br />

<div class="postbox">
	<h3 class="hndle"><span><?php _e('Default Answer Display', 'watupro') ?></span></h3>
	<div class="inside">
	<input type="radio" name="show_answers" <?php if($answer_display == '0') echo 'checked="checked"'; ?> value="0" id="no-show" /> <label for="no-show"><?php _e("Don't show answers", 'watupro') ?></label><br />
	<input type="radio" name="show_answers" <?php if($answer_display == '1') echo 'checked="checked"'; ?> value="1" id="show-end" /> <label for="show-end"><?php _e("Show answers at the end of the Quiz", 'watupro') ?></label><br />
	
	</div>
</div>

<div class="postbox">
<h3 class="hndle"><span><?php _e('Default Answer Type', 'watupro') ?></span></h3>
<div class="inside" style="padding:8px">
<?php 
	$single = $multi = '';
	if( get_option('watupro_answer_type') =='radio') $single='checked="checked"';
    elseif( get_option('watupro_answer_type') =='open') $openend='checked="checked"';
	else $multi = 'checked="checked"';
?>
<label>&nbsp;<input type='radio' name='answer_type' <?php print $single?> id="answer_type_r" value='radio' /><?php _e("Single Answer", 'watupro')?> </label>
&nbsp;&nbsp;&nbsp;
<label>&nbsp;<input type='radio' name='answer_type' <?php print $multi?> id="answer_type_c" value='checkbox' /><?php _e("Multiple Answers", 'watupro')?></label>
&nbsp;&nbsp;&nbsp;
<label>&nbsp;<input type='radio' name='answer_type' <?php print $openend?> id="answer_type_c" value='checkbox' /><?php _e("Open End", 'watupro')?></label>
</div>
</div>

<div class="postbox">
	<h3 class="hndle"><span><?php _e('Roles', 'watupro') ?></span></h3>
	<div class="inside">		
	<h4><?php _e('Roles that can manage exams', 'watupro')?></h4>
	
	<p><?php _e('By default only Administrator and Super admin can manage Watu PRO exams. You can enable other roles here.', 'watupro')?></p>
	
	<input type="checkbox" name="manage_roles[]" value="editor" <?php if(!empty($editor_role->capabilities['watupro_manage_exams'])) echo "checked"?>> <?php _e('Editor', 'watupro')?>
		<input type="checkbox" name="manage_roles[]" value="author" <?php if(!empty($author_role->capabilities['watupro_manage_exams'])) echo "checked"?>> <?php _e('Author', 'watupro')?>
	<input type="checkbox" name="manage_roles[]" value="contributor" <?php if(!empty($contributor_role->capabilities['watupro_manage_exams'])) echo "checked"?>> <?php _e('Contributor', 'watupro')?>
	<input type="checkbox" name="manage_roles[]" value="subscriber" <?php if(!empty($subscriber_role->capabilities['watupro_manage_exams'])) echo "checked"?>> <?php _e('Subscriber', 'watupro')?>
	
	<p><?php _e('Only administrator or superadmin can change this!', 'watupro')?></p>
	</div>
</div>

<div class="postbox">
	<h3 class="hndle"><span><?php _e('ReCaptcha Settings', 'watupro') ?></span></h3>
	<div class="inside">
		<p><label><?php _e('ReCaptcha Public Key:', 'watupro')?></label> <input type="text" name='recaptcha_public' value="<?php echo get_option('watupro_recaptcha_public')?>" size="50"></p>
		<p><label><?php _e('ReCaptcha Private Key:', 'watupro')?></label> <input type="text" name='recaptcha_private' value="<?php echo get_option('watupro_recaptcha_private')?>" size="50"></p>
		<p><?php _e('Setting up <a href="http://www.google.com/recaptcha/whyrecaptcha" target="_blank">ReCaptcha</a> is optional. If you choose to do so you will be able to require image validation on chosen exams to avoid spam box submissions.', 'watupro');?></p>
	</div>
</div>

<?php if(watupro_intel()):?>
	<div class="postbox">
		<h3 class="hndle"><span><?php _e('Payment Settings For Exams That Require Payment', 'watupro') ?></span></h3>
		<div class="inside">
			<p><strong><?php _e("WatuPRO Intelligence module allows you to require payment to access selected exams.", 'watupro')?></strong></p>
			
			<p><label><?php _e("Payment currency:", 'watupro');?></label>
			<select name="currency">
			<?php foreach($currencies as $key=>$val):
            if($key==$currency) $selected='selected';
            else $selected='';?>
        		<option <?php echo $selected?> value='<?php echo $key?>'><?php echo $val?></option>
         <?php endforeach; ?>
			</select></p>
			
			<p><label><?php _e("Paypal Email ID:", 'watupro')?></label>
			<input type="text" name="paypal" value="<?php echo get_option('watupro_paypal');?>"></p>
			<p><?php _e("If you provide this, a Paypal payment button will be generated and automatically instant payment notification will enable the user's access to the exam they have paid for.", 'watupro')?></p>
			
			<label><?php _e("Other payment instructions or button code (optional):", 'watupro');?></label><br>
			
			<textarea name="other_payments" rows="7" cols="50"><?php echo stripslashes(get_option('watupro_other_payments'));?></textarea>
			
			<p><?php _e("Use this if you don't want to use Paypal or as additional manual or automated payment method. You can either textual instructions here, insert a link, or even payment button HTML code from a different payment system like 2Checkout.com, Stripe etc. ", 'watupro')?></p>
			<p><?php _e("In this case you can use the following shortcodes: <strong>[AMOUNT], [USER_ID], [EXAM_TITLE], and [EXAM_ID]</strong>", 'watupro')?></p>			
		</div>
	</div>
<?php endif;?>

<p class="submit">
<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<span id="autosave"></span>
<input type="submit" name="submit" value="<?php _e('Save Options', 'watupro') ?>" style="font-weight: bold;" />
</p>

</div>
</div>
</form>

<?php if(!empty($watu_exams) and sizeof($watu_exams)):?>
<div id="poststuff">
<div id="postdiv" class="postarea">
	<form method="post">
	<div class="postbox">
		<h3 class="hndle"><span><?php _e('Exams From Watu Basic', 'watupro') ?></span></h3>
		<div class="inside">
			<?php if(!empty($copy_message)):?>
				<p class="watupro-alert"><?php echo $copy_message?></p>
			<?php endif;?>		
		
			<p><?php echo __("You have ", 'watupro').sizeof($watu_exams).__(" exams created in the basic free Watu plugin. Do you want to copy these exams in Watu PRO? You can do this any time, and multiple times.", 'watupro')?></p>
			
			<p class="submit"><input type="submit" name="copy_exams" value="<?php _e('Copy These Exam(s) to WatuPRO', 'watupro')?>" style="font-weight: bold;"></p>
			
			<p><strong><?php _e("Note: Watu should not be activated along with Watu PRO. Please deactivate the basic plugin if you have not already done this.", 'watupro')?></strong></p>
		</div>
	</div>	
	</form>
</div>
</div>		
<?php endif;?>


</div>

<?php
function showWatuOption($option, $title) {
?>
<input type="checkbox" name="<?php echo $option; ?>" value="1" id="<?php echo $option?>" <?php if(get_option('watupro_'.$option)) print " checked='checked'"; ?> />
<label for="<?php echo $option?>"><?php _e($title, 'watupro') ?></label><br />

<?php
}