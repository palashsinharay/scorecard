<div class="wrap">
	<h1><?php echo empty($certificate->ID)?__("Create New", 'watupro'):__("Edit", 'watupro')?> <?php _e('Certificate', 'watupro')?></h1>

	<form method="post" onsubmit="return validate(this);">
	<div class="postbox" id="titlediv">
		<h3 class="hndle"><span><?php _e('Certificate Title', 'watupro') ?></span></h3>
		
		<div class="inside">
		<input type='text' name='title' id="title" value='<?php echo stripslashes(@$certificate->title); ?>' />
		</div>
	</div>

	<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea postbox">
	<h3 class="hndle"><span><?php _e('Certificate Text/HTML', 'watupro') ?></span></h3>
	<div class="inside">
	<?php wp_editor(stripslashes(@$certificate->html), 'html'); ?>

	<p><strong><?php _e('Usable Variables...', 'watupro') ?></strong></p>
	<table>
	<tr><th style="text-align:left;"><?php _e('Variable', 'watupro') ?></th><th style="text-align:left;"><?php _e('Value', 'watupro') ?></th></tr>
	<tr><td>%%USER_NAME%%</td><td><?php _e('Full user name, if provided, otherwise login will be used', 'watupro') ?></td></tr>
	<tr><td>%%POINTS%%</td><td><?php _e('Total points collected', 'watupro') ?></td></tr>
	<tr><td>%%GRADE%%</td><td><?php _e('The assigned grade after taking the exam', 'watupro') ?>.</td></tr>
	<tr><td>%%QUIZ_NAME%%</td><td><?php _e('The name of the exam', 'watupro') ?></td></tr>
	<tr><td>%%DESCRIPTION%%</td><td><?php _e('The optional description.', 'watupro') ?></td></tr>
	<tr><td>%%DATE%%</td><td><?php _e('Date when exam is submitted.', 'watupro') ?></td></tr>
	</table>

	<p>&nbsp;</p>
	<h3><?php _e('Important!', 'watupro')?></h3>
	<ol>
        <li><?php _e("The certificate will be available only for logged in users, and when it's assigned to the grade they have achieved.", 'watupro')?></li>
        <li><?php _e("No CSS styles or header from your blog will be applied. Please include all the styling in the certificate.", 'watupro')?></li>
	</ol>

	<p class="submit">	
	<span id="autosave"></span>
	<input type="submit" name="ok" value="<?php _e('Save Certificate', 'watupro') ?>" style="font-weight: bold;" tabindex="4" />
	<?php if(!empty($certificate->ID)):?>
		<input type="button" value="Delete Certificate" onclick="confirmDelete(this.form);">
	<?php endif;?>
	</p>
	</div>
	<input type="hidden" name="del" value="0">
	</form>
</div>

<script type="text/javascript">
function validate(frm)
{
	if(frm.title.value=='')
	{
		alert("<?php _e('Please enter title of this certificate', 'watupro')?>");
		frm.title.focus();
		return false;
	}

	return true;
}

function confirmDelete(frm)
{
	if(confirm("<?php _e('Are you sure?', 'watupro')?>"))
	{
		frm.del.value=1;
		frm.submit();
	}
}
</script>