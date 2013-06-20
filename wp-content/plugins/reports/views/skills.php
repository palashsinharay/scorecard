<h2 class="nav-tab-wrapper">
	<a class='nav-tab' href="admin.php?page=watupro_reports&user_id=<?php echo $report_user_id?>"><?php _e('Overview', 'watupro')?></a>
	<a class='nav-tab' href='admin.php?page=watupro_reports&tab=tests&user_id=<?php echo $report_user_id?>'><?php _e('Tests', 'watupro')?></a>
	<a class='nav-tab-active'><?php _e('Skills/Categories', 'watupro')?></a>
	<a class='nav-tab' href='admin.php?page=watupro_reports&tab=history&user_id=<?php echo $report_user_id?>'><?php _e('History', 'watupro')?></a>
</h2>

<div class="wrap">
	 <form class="watupro" method="post">
		 <div class="postbox watupro-padded">
				<p>Exam category: <select name="cat">
					<option value="-1">All categories</option>		
					<?php foreach($exam_cats as $cat):?>
						<option value="<?php echo $cat->ID?>" <?php if($cat->ID==$_POST['cat']) echo "selected"?>><?php echo $cat->name;?></option>
					<?php endforeach;?>	
					<option value="0" <?php if($_POST['cat']==="0") echo 'selected'?>><?php _e('Uncategorized', 'watupro')?></option>	
				</select> &nbsp;
				Skill (Question category): <select name="q_cat">
					<option value="-1">All categories</option>
					<?php foreach($q_cats as $cat):?>
						<option value="<?php echo $cat->ID?>" <?php if($cat->ID==$_POST['q_cat']) echo "selected"?>><?php echo $cat->name;?></option>
					<?php endforeach;?>					
				</select></p>				
				<p>View skills: <select name="skill_filter" onchange="WatuPRO.changeSkillFilter(this.value);">
						<option value="">All</option>
						<option value="practiced" <?php if(!empty($_POST['skill_filter']) and $_POST['skill_filter']=='practiced') echo "selected"?>>Practiced only</option>
						<option value="proficient" <?php if(!empty($_POST['skill_filter']) and $_POST['skill_filter']=='proficient') echo "selected"?>>Proficient only</option>
					</select> 
					<span id="proficiencyGoal" <?php if(empty($_POST['skill_filter']) or $_POST['skill_filter']!='proficient'):?>style="display:none;"<?php endif;?>><?php _e('Proficiency goal: at least', 'watupro')?> <input type="text" size="4" name="proficiency_goal" value="<?php echo @$_POST['proficiency_goal']?>"><?php _e('% correct answers', 'watupro')?></span>			
				</p>
				<p><input type="submit" value="<?php _e('Show Report', 'watupro')?>"></p>
		 </div>
	 </form>
	 
	 <p class="watupro-note"><strong><?php _e('These reports are based on the latest attempt made for every exam.', 'watupro')?></strong></p>
	 
	 <?php if(!empty($_POST['skill_filter']) and $_POST['skill_filter']=='proficient'):?>
		 <h2><?php _e('Proficiency summary', 'watupro')?></h2>
		 
		 <p>You are proficient in <?=$num_proficient?> skills.</p>
	 <?php endif;?>
	 
	 <h2><?php _e('Proficiency by skill', 'watupro')?></h2>
	 <table class="widefat">
	 	<tr><th><?php _e('Skill (question category) and exams', 'watupro')?></th><th><?php _e('Proficiency (% correct answers)', 'watupro')?></th></tr>
			 <?php foreach($skills as $skill):?>
			 	 <tr><th colspan="2"><?php echo $skill['category']->name?></th></tr>
			 	 <?php foreach($skill['exams'] as $exam):?>
			 	 		<tr><td style="padding-left:25px;"><?php if(!empty($exam->post)) echo "<a href='".get_permalink($exam->post->ID)."' target='_blank'>"; 
						echo stripslashes($exam->name);
						if(!empty($exam->post)) echo "</a>";?></td>
						<td><?php if(empty($exam->taking->ID)) echo "-";
						else echo $exam->taking->percent_correct."%"?></td></tr>
			 	 <?php endforeach;
			 endforeach;?>
	 </table>
</div>

<script type="text/javascript" >
WatuPRO.changeSkillFilter = function(val) {
	if(val=='proficient') {
		jQuery('#proficiencyGoal').show();
	}
	else jQuery('#proficiencyGoal').hide();
}
</script>