<?php $single_page = $exam->single_page;
// force start if we are continuing on limited time exam     
if($exam->time_limit>0 and !empty($timer_warning)): echo "<p class='watupro-warning' id='timerRuns'>".$timer_warning."</p>"; endif;
if($exam->time_limit>0):?>
	    <p id="timeNag"><?php printf(__('This exam must be completed in %d minutes.', 'watupro'), $exam->time_limit)?> <a href="#" onclick="WatuPRO.InitializeTimer(<?php echo $exam->time_limit*60?>);return false;"><?php _e('Click here to start the exam', 'watupro')?></a></p>
	    <div id="timerDiv" style="display:none;color:green;"><?php _e('Time left:', 'watupro')?> <?php echo $exam->time_limit*60;?></div>
<?php endif;?>
    
<div <?php if($exam->time_limit>0):?>style="display:none;"<?php endif;?> id="watupro_quiz" class="quiz-area <?php if($single_page) echo 'single-page-quiz'; ?>">
<p id="submittingExam<?php echo $exam->ID?>" style="display:none;text-align:center;"><img src="<?php echo plugins_url('watupro/loading.gif')?>"></p>
<form action="" method="post" class="quiz-form" id="quiz-<?php echo $exam_id?>">
<?php
if($exam->email_taker and !is_user_logged_in()) watupro_ask_for_email();
// the exam is shown below
$question_count = $cat_count = 1;
$question_ids = '';
$total=sizeof($all_question);
$question_catids = array(); // used for category based pagination
foreach ($all_question as $qct=>$ques):        
   echo watupro_cat_header($exam, $qct, $ques, $question_catids);
   $qct++;
	echo "<div class='watu-question' id='question-$question_count'>";
		 echo $_question->display($ques, $qct, $question_count, $in_progress, $exam);		 	
		 $question_ids .= $ques->ID.',';     
	   if(!$single_page) echo "<p><i>".sprintf(__("Question %d of %d", 'watupro'), $qct, $total)."</i></p>";
	   
	   if($exam->live_result):
		   if(empty($_question->inprogress_snapshots[$ques->ID])):?>
				<div style="display:none;" id='liveResult-<?php echo $question_count?>'>		   
					<img src="<?php echo plugins_url('watupro/loading.gif')?>" width="16" height="16" alt="<?php _e('Loading...', 'watu', 'watupro')?>" title="<?php _e('Loading...', 'watu', 'watupro')?>" />&nbsp;<?php _e('Loading...', 'watu', 'watupro')?>
				</div>	
			<?php else: echo stripslashes($_question->inprogress_snapshots[$ques->ID]); endif; // end if displaying snapshot	
		endif; // end if live_result       
   echo "</div>";
      
   if(!in_array($ques->cat_id, $question_catids)) $question_catids[] = $ques->cat_id; 
   $question_count++;        
endforeach;
if($single_page == 2 and $exam->group_by_cat) echo "</div>"; // close last category div 
$num_cats = sizeof($question_catids); ?>
<div style='display:none' id='question-<?php echo $question_count?>'>
	<div class='question-content'>
		<img src="<?php echo plugins_url('watupro/loading.gif')?>" width="16" height="16" alt="<?php _e('Loading...', 'watu', 'watupro')?>" title="<?php _e('Loading...', 'watu', 'watupro')?>" />&nbsp;<?php _e('Loading...', 'watu', 'watupro')?>
	</div>
</div>

<?php 
$question_ids = preg_replace('/,$/', '', $question_ids );
echo $recaptcha_html;?><br />
	<?php if(empty($exam->disallow_previous_button)):?>
		<input type="button" id="prev-question" value="&lt; <?php _e('Previous', 'watupro') ?>" style="display:none;" onclick="WatuPRO.nextQuestion(event, 'previous');"/> &nbsp;
	<?php else: // to prevent JS error just output empty hidden field?><input type="hidden" id="prev-question"><?php endif;?>
  <input type="button" id="next-question" value="<?php _e('Next', 'watupro') ?> &gt;"  />
  <?php if($exam->live_result and $exam->single_page==0):?> &nbsp;<input type="button" id="liveResultBtn" value="<?php _e('See Answer', 'watupro')?>" onclick="WatuPRO.liveResult();"><?php endif;?>
  <?php if($single_page==2 and $num_cats>1 and $exam->group_by_cat):?>
  	<input type="button" id="watuproPrevCatButton" onclick="WatuPRO.nextCategory(<?=$num_cats?>, false);" value="<?php _e('Previous page', 'watupro');?>" style="display:none;"> <input type="button" id="watuproNextCatButton" onclick="WatuPRO.nextCategory(<?=$num_cats?>, true);" value="<?php _e('Next page', 'watupro');?>"> 
  <?php endif; // endif paginate per category ?>
	<input type="button" name="action" onclick="WatuPRO.submitResult(event)" id="action-button" value="<?php _e('Submit', 'watupro') ?>" <?php echo $submit_button_style?>/>
	<input type="hidden" name="quiz_id" value="<?php echo  $exam_id ?>" />
	<input type="hidden" name="start_time" id="startTime" value="<?php echo date('Y-m-d H:i:s');?>" />
	<input type="hidden" name="question_ids" value="<?php echo $qidstr?>" />
	<input type="hidden" name="watupro_questions" value="<?php echo watupro_serialize_questions($all_question);?>" />
	</form>
	<p>&nbsp;</p>
</div>
<script type="text/javascript">
jQuery(function(){
var question_ids = "<?php print $question_ids ?>";
WatuPRO.qArr = question_ids.split(',');
WatuPRO.exam_id = <?php print $exam_id ?>;	    
WatuPRO.requiredIDs="<?php echo $required_ids_str?>".split(",");
var url = "<?php print plugins_url('watupro/'.basename(__FILE__) ) ?>";
WatuPRO.examMode = <?php echo $exam->single_page?>;
<?php if($single_page==2 and $num_cats>1 and $exam->group_by_cat): echo 'WatuPRO.numCats ='. $num_cats.";\n"; endif;?>
WatuPRO.siteURL="<?php echo admin_url( 'admin-ajax.php' ); ?>";<?php if($exam->time_limit>0):?>
WatuPRO.secs=0;
WatuPRO.timerID = null;
WatuPRO.timerRunning = false;		
WatuPRO.delay = 1000;
<?php endif;?>});    	 
</script>