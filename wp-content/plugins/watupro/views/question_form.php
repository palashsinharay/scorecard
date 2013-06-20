<div class="wrap">
<h2><?php _e(ucfirst($action) . " Question", 'watupro'); ?></h2>

<div id="titlediv">
<input type="hidden" id="title" name="ignore_me" value="This is here for a workaround for a editor bug" />
</div>

<style type="text/css">
.qtrans_title, .qtrans_title_wrap {display:none;}
</style>
<script type="text/javascript">
var answer_count = <?php echo $answer_count?>;
var ans_type = "<?php print $ans_type?>";
function newAnswer() {
	answer_count++;
	var para = document.createElement("p");
	var textarea = document.createElement("textarea");
	textarea.setAttribute("name", "answer[]");
	textarea.setAttribute("rows", "3");
	textarea.setAttribute("cols", "50");
	para.appendChild(textarea);
	para.appendChild(document.createTextNode(' ') );
	var label = document.createElement("label");
	label.setAttribute("for", "correct_answer_" + answer_count);
	label.appendChild(document.createTextNode("<?php _e("Correct Answer ", 'watupro'); ?>"));
	para.appendChild(label);
	var input = document.createElement("input");
	chkType=(ans_type=='radio')?'radio':'checkbox';	
	input.setAttribute("type", chkType);
	input.setAttribute("name", "correct_answer[]");
	input.className = "correct_answer";
	input.setAttribute("value", answer_count);
	input.setAttribute("id", "correct_answer_" + answer_count);
	para.appendChild(input);
	var label2 = document.createElement("label");
	label2.setAttribute("style", 'margin-left:10px');
	label2.appendChild(document.createTextNode("<?php _e("Points: ", 'watupro'); ?>"));
	var point = document.createElement('input');
	point.setAttribute("name", "point[]");
	point.className = 'numeric';
	point.setAttribute("type", "text");
	point.setAttribute("size", "4");
	label2.appendChild(point);
	para.appendChild(label2);
	//$("extra-answers").innerHTML += code.replace(/%%NUMBER%%/g, answer_count);
	document.getElementById("extra-answers").appendChild(para);
}
function init() {
	jQuery("#post").submit(function(e) {
		// Make sure question is suplied
		var contents;
		if(window.tinyMCE && document.getElementById("content").style.display=="none") { // If visual mode is activated.
			contents = tinyMCE.get("content").getContent();
		} else {
			contents = document.getElementById("content").value;
		}

		if(!contents) {
			alert("<?php _e("Please enter the question", 'watupro'); ?>");
			e.preventDefault();
			e.stopPropagation();
			return true;
		}

		//A correct answer must be selected.
		var correct_answer_selected = false;
		jQuery(".correct_answer").each(function() {
			if(this.checked) {
				correct_answer_selected = true;
				return true;
			}
		});
		
		//The points will be numeric
		var ret= true;
		jQuery('.numeric').each(function(){
			var valid = (this.value>=0 || this.value<0);
			if(!valid) jQuery(this).css({'background-color': '#fcc'});
			if(ret) ret=(this.value>=0 || this.value<0);
		});
		if(!ret){ 
			alert('<?php _e("Please provide numeric values for the Grade from/to.", 'watupro')?>');
			e.preventDefault();
			e.stopPropagation();
		}
	});
	
	jQuery('input[name=answer_type]').click(function(){
		ans_type = (this.value=='radio')?'radio':'checkbox';
		 jQuery('.correct_answer').each(function(){
			this.removeAttribute('type');
			this.setAttribute('type', ans_type);
		});
	});
}
jQuery(document).ready(init);
</script>

<p><a href="admin.php?page=watupro_questions&amp;quiz=<?php echo $_GET['quiz']?>"><?php _e("Go to Questions Page", 'watupro') ?></a></p>

<form name="post" action="admin.php?page=watupro_questions&amp;quiz=<?php echo $_GET['quiz']; ?>" method="post" id="post">
<div id="poststuff">

<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
<div class="postbox">
<h3 class="hndle"><?php _e('Question', 'watupro') ?></h3>
<div class="inside">
<?php wp_editor(stripslashes($question->question), "content"); ?>
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php _e('Question Category', 'watupro') ?></span></h3>
<div class="inside">
	<select name="cat_id" onchange="WatuPRO.changeQCat(this);">
	<option value="0" <?php if(empty($question->cat_id)) echo "selected"?>><?php _e("- Uncategorized  ", 'watupro');?></option>
	<option value="-1"><?php _e("- Add new category -", 'watupro');?></option>
	<?php foreach($qcats as $cat):?>
		<option value="<?php echo $cat->ID?>" <?php if(@$question->cat_id==$cat->ID) echo "selected"?>><?php echo $cat->name;?></option>
	<?php endforeach;?>
	</select>
	
	<input type="text" name="new_cat" id="newCat" style="display:none;" placeholder="<?php _e('Enter category', 'watupro')?>">
</div>

<div class="postbox" id="atdiv">
	<h3 class="hndle"><span><?php _e('Answer Type', 'watupro') ?></span></h3>
	<div class="inside" style="padding:8px">
		<?php 
			$single = $multi = $openend = '';
			if( $ans_type =='radio') $single='checked="checked"';
		    elseif( $ans_type == 'textarea' ) $openend='checked="checked"';
			else $multi = 'checked="checked"';
		?>
		<label>&nbsp;<input type='radio' name='answer_type' <?php print $single?> id="answer_type_r" value='radio' onclick="jQuery('#openEndText').hide();jQuery('#answersArea').show();jQuery('#questionCorrectCondition').hide();" /> <?php _e("Single Answer", 'watupro')?> </label>
		&nbsp;&nbsp;&nbsp;
		
		<label>&nbsp;<input type='radio' name='answer_type' <?php print $multi?> id="answer_type_c" value='checkbox' onclick="jQuery('#openEndText').hide();jQuery('#answersArea').show();jQuery('#questionCorrectCondition').show();" /> <?php _e('Multiple Answers', 'watupro')?></label>
		&nbsp;&nbsp;&nbsp;
		
		<label>&nbsp;<input type='radio' name='answer_type' <?php print $openend?> id="answer_type_o" value='textarea' onclick="jQuery('#openEndText').show();jQuery('#answersArea').show();jQuery('#questionCorrectCondition').hide();" /> <?php _e('Open End', 'watupro')?></label>
		&nbsp;&nbsp;&nbsp;
		<p id="openEndText" style="display:<?php echo ($ans_type == 'textarea')?'block':'none'?>;"><?php _e("In open-end questions you can also add any number of answers but none of them will be shown to the end user. Instead of that if the answer they typed matches any of your answers the matching points will be assigned. Please note this comparison is case insensitive but anything else - punctuation, spaces, letters - should match exactly.", 'watupro')?></p>
		
			<?php if(watupro_intel()): require(WATUPRO_PATH."/i/views/question_form.php"); endif; ?>
		
		<p>&nbsp;<input type="checkbox" name="is_required" value="1" <?php if(!empty($question->is_required)) echo "checked"?>> <?php _e("Answering this question is required", 'watupro');?></p>	
		
		<div id="questionCorrectCondition" style="display:<?php echo (empty($question->ID) or $question->answer_type=='radio' or $question->answer_type=='textarea')?'none':'block'?>;">
			<p><strong><?php _e('Answering this question will be considered CORRECT when:', 'watupro')?></strong></p>
			
			<p><input type="radio" name="correct_condition" value="any" <?php if($question->correct_condition!='all') echo 'checked'?>> <?php _e('Positive number of points is achieved (so at least one correct answer is given)', 'watupro')?></p>
			<p><input type="radio" name="correct_condition" value="all" <?php if($question->correct_condition=='all') echo 'checked'?>> <?php _e('The maximum number of points is achieved (so all correct answers are given and none is incorrect.)', 'watupro')?></p>
		</div>
	  
</div>
</div>

<div class="postbox" id="answersArea" style="display:<?php echo (empty($question) or $question->answer_type!='gaps')?'block':'none';?>">
<h3 class="hndle"><span><?php _e('Answers', 'watupro') ?></span></h3>
<div class="inside">

<?php
for($i=1; $i<=$answer_count; $i++) { ?>
<p style="border-bottom:1px dotted #ccc"><textarea name="answer[]" class="answer" rows="3" cols="50"><?php if($action == 'edit') echo stripslashes($all_answers[$i-1]->answer); ?></textarea>
<label for="correct_answer_<?php echo $i?>"><?php _e("Correct Answer", 'watupro'); ?></label>
<input type="<?php print ($ans_type=='radio')?'radio':'checkbox'?>" class="correct_answer" id="correct_answer_<?php echo $i?>" <?php if($all_answers[$i-1]->correct == 1) echo 'checked="checked"';?> name="correct_answer[]" value="<?php echo $i?>" />
<label style="margin-left:10px"><?php _e('Points:', 'watupro')?> <input type="text" class="numeric" size="4" name="point[]" value="<?php if($action == 'edit') echo stripslashes($all_answers[$i-1]->point); ?>"></label>
</p>
<?php } ?>
<style>#extra-answers p{border-bottom:1px dotted #ccc;}</style>
<div id="extra-answers"></div>
<a href="javascript:newAnswer();"><?php _e("Add New Answer", 'watupro'); ?></a>

</div>
</div>

	<div class="postbox">
		<h3 class="hndle"><span><?php _e('Optional Answer Explanation', 'watupro') ?></span></h3>
		<div class="inside">		
			<?php echo wp_editor(stripslashes($question->explain_answer), "explain_answer");?>
			<br />
			<p><?php _e('You can use this field to explain the correct answer. This will be shown only at the end of the quiz if you have choosen to display correct answers.', 'watupro') ?></p>
		</div>
	</div>

</div>


<p class="submit">
<input type="hidden" name="quiz" value="<?php echo $_REQUEST['quiz']?>" />
<input type="hidden" name="question" value="<?php echo stripslashes($_REQUEST['question'])?>" />
<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<input type="hidden" name="action" value="<?php echo $action ?>" />
<span id="autosave"></span>
<input type="submit" name="submit" value="<?php _e('Save', 'watupro') ?>" style="font-weight: bold;" />
</p>
<a href="admin.php?page=watupro_questions&amp;quiz=<?php echo $_REQUEST['quiz']?>"><?php _e("Go to Questions Page", 'watupro') ?></a>
</div>
</form>

</div>