<div class="postbox" id="titlediv">
    <h3 class="hndle"><span><?php _e('Intelligence Module Settings', 'watupro') ?></span></h3>    
    <div class="inside">
			<label><?php _e('Set exam mode:', 'watupro')?></label> <select name="mode">
				<option value="live"<?php if(empty($dquiz->mode) or $dquiz->mode=='live') echo " selected"?>><?php _e('Live exam', 'watupro')?></option>		
				<option value="practice"<?php if(!empty($dquiz->mode) and $dquiz->mode=='practice') echo " selected"?>><?php _e('Practice mode', 'watupro')?></option>
			</select>	
			<br><br>
			
			<label><input type="checkbox" name="grades_by_percent" value="1" <?php if(!empty($dquiz->grades_by_percent)) echo 'checked'?>> <?php _e('Calculate grades by % correct answers instead of points collected', 'watupro')?></label>
			
			<br><br>
			<?php if(sizeof($other_exams)):?>
		   <h3><?php _e("Dependencies", 'watupro')?></h3>
		   
		   <div id="dependencyWarning" style="display:<?php echo (empty($dquiz->ID) or !$dquiz->require_login)?'block':'none'; ?>">
		   	<p><?php _e("This feature becomes active only when <strong>Require user log-in</strong> option in the <strong>Exam Settings</strong> section is selected.", 'watupro')?></p>
		   </div>
		   
		   <div id="dependencyDiv" style="display:<?php echo (empty($dquiz->ID) or !$dquiz->require_login)?'none':'block'; ?>">
				<p><?php _e("Let the user access this exam only after they have completed the following ones:", 'watupro')?></p>
				
				<?php foreach($dependencies as $dependency):?>
					<div id="oldDependencyRow<?php echo $dependency->ID?>">
						<?php _e("Exam:", 'watupro')?> <select name="dependency<?php echo $dependency->ID?>" class="watupro-depend-exam">
							<option value=""><?php _e("- Select exam -", 'watupro')?></option>			
							<?php foreach($other_exams as $oexam):?>
								<option value="<?php echo $oexam->ID?>"<?php if($oexam->ID==$dependency->depend_exam) echo " selected"?>><?php echo $oexam->name?></option>
							<?php endforeach;?>	
						</select> <?php _e("is completed with at least", 'watupro')?> <input type="text" name="depend_points<?php echo $dependency->ID?>" value="<?php echo $dependency->depend_points?>" onblur="WatuPRODep.forceNumber(this)" size="4"> <?php _e("points achieved", 'watupro')?>.</span>
						<span class="submit" id="delDepBtn<?php echo $dependency->ID?>"><input type="button" value="<?php _e('Mark To Delete', 'watupro')?>" onclick="WatuPRODep.del(true, <?php echo $dependency->ID?>);"></span>
						
						<span class="submit" id="restoreDepBtn<?php echo $dependency->ID?>" style="display:none;"><input type="button" value="<?php _e('Restore', 'watupro')?>" onclick="WatuPRODep.del(false, <?php echo $dependency->ID?>);"></span>
					</div>
				<?php endforeach;?>
				
				<div id="dependencyRow"><span id="dependencySpan"><?php _e("Exam:", 'watupro')?> <select name="dependencies[]" id="dependExam">
					<option value=""><?php _e("- Select exam -", 'watupro')?></option>			
					<?php foreach($other_exams as $oexam):?>
						<option value="<?php echo $oexam->ID?>"><?php echo $oexam->name?></option>
					<?php endforeach;?>	
				</select> <?php _e("is completed with at least", 'watupro')?> <input type="text" name="depend_points[]" value="0" onblur="WatuPRODep.forceNumber(this)" size="4" id="dependPoints"> <?php _e("points achieved", 'watupro')?>.</span>
				<span class="submit"><input type="button" value="<?php _e('Add New Dependency', 'watupro')?>" id="addDependencyButton"></span>
				</div>		   
		   </div>
		   <input type="hidden" id="delDependencies" name="del_dependencies" value="">
		   <?php endif;?>
		   
		   <!-- Payment settings -->
		   <h3><?php _e("Payment Settings", 'watupro')?></h3>
		   
		   <div id="WatuPROPaymentWarning" style="display:<?php echo (empty($dquiz->ID) or !$dquiz->require_login)?'block':'none'; ?>">
		   	<p><?php _e("This feature becomes active only when <strong>Require user log-in</strong> option in the <strong>Exam Settings</strong> section is selected.", 'watupro')?></p>
		   </div>
		   
		   <div id="WatuPROPaymentDiv" style="display:<?php echo (empty($dquiz->ID) or !$dquiz->require_login)?'none':'block'; ?>">
					<p><?php _e("Charge", 'watupro')?> <?php echo get_option("watupro_currency")?> <input type="text" name="fee" size="6" value="<?php echo $dquiz->fee?>"> <?php _e("for accessing this exam.", 'watupro')?></p>
					<p><?php _e("If you leave zero in the box above, there will be no charge for users to access the exam. To manage your payments settings to to ", 'watupro')?> <a href="admin.php?page=watupro_options"><?php _e("WatuPRO Settings", 'watupro')?></a></p>	   
		   </div>
    </div>
</div>  

<script type="text/javascript" >
jQuery(function(){
	<?php if(sizeof($other_exams)):?>
		// store the clean dependency row in a var from the beginnign
		var dependencySpan = jQuery('#dependencySpan').html();
		var rowNum = 0;
		WatuPRODep.depsToDel = [];
	
		jQuery('#requieLoginChk').bind('click', function(){			
			if(jQuery(this).attr('checked')) {
				jQuery('#dependencyWarning').hide();
				jQuery('#WatuPROPaymentWarning').hide();
				jQuery('#dependencyDiv').show();
				jQuery('#WatuPROPaymentDiv').show();
			}
			else {
				jQuery('#dependencyWarning').show();
				jQuery('#WatuPROPaymentWarning').show();
				jQuery('#dependencyDiv').hide();
				jQuery('#WatuPROPaymentDiv').hide();
			}	
		});
		
		jQuery('#addDependencyButton').bind('click', function(){
			if(jQuery('#dependExam').val()=='') {
				alert("<?php _e('Please select exam', 'watupro')?>");
				return false;
			}		
			
			// check for duplicate
			var hasDuplicate = false;
			jQuery('.watupro-depend-exam option:selected').each(function(){
				if(this.value == jQuery('#dependExam').val())
				{
					hasDuplicate = true;
				}
			});	
			
			if(hasDuplicate)
			{
				alert("<?php _e('You already have this dependency!', 'watupro')?>");
				return false;
			}
			
			// add new row
			rowNum++;
			
			// replace the IDs
			html = dependencySpan.replace('dependExam', 'dependExam'+rowNum);			
			html = html.replace('dependPoints', 'dependPoints'+rowNum);
			
			// button
			var but = "<span class='submit'><input type='button' value='<?php _e("Delete", 'watupro')?>' onclick=\"jQuery('#dependencyRow"+rowNum+"').remove();\"></span>";
			
			jQuery('#dependencyDiv').append("<div id='dependencyRow" + rowNum + "'>" + html + but + "</div>");
			
			// set values
			jQuery('#dependExam'+rowNum).val(jQuery('#dependExam').val());
			jQuery('#dependPoints'+rowNum).val(jQuery('#dependPoints').val());
			
			// add class used for duplicate dependency check
			jQuery('#dependExam'+rowNum).addClass("watupro-depend-exam");
			
			jQuery('#dependExam').val('');
			jQuery('#dependPoints').val('0');
		});
		
		// mark or restore dependency for deletion
		WatuPRODep.del = function(mode, id)
		{
			if(mode) {
				// to delete
				WatuPRODep.depsToDel.push(id);
				jQuery('#delDependencies').val(WatuPRODep.depsToDel.join(","));
				jQuery('#oldDependencyRow'+id).addClass('watupro-for-removal');
				jQuery('#delDepBtn'+id).hide();
				jQuery('#restoreDepBtn'+id).show();
			}
			else {
				// to restore
				WatuPRODep.depsToDel = jQuery.grep(WatuPRODep.depsToDel, function(value){
						return value != id;
					});
				jQuery('#delDependencies').val(WatuPRODep.depsToDel.join(","));
				jQuery('#oldDependencyRow'+id).removeClass('watupro-for-removal');				
				jQuery('#delDepBtn'+id).show();
				jQuery('#restoreDepBtn'+id).hide();
			}
		}
	<?php endif;?>
});
</script>