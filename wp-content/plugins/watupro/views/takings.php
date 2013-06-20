<div class="wrap">
	<h1><?php _e('Users who took exam', 'watupro')?> "<?php echo $exam->name;?>"</h1>
	
	<?php if(!empty($_GET['msg'])):?>
		<p class="watupro-success"><?php echo $_GET['msg']?></p>
	<?php endif;?>

	<p><a href="admin.php?page=watupro_exams"><?php _e('Back to exams list', 'watupro')?></a> 
&nbsp;
<a href="edit.php?page=watupro_exam&quiz=<?php echo $exam->ID?>&action=edit"><?php _e('Edit this exam', 'watupro')?></a></p>
	
	<p><a href="#" onclick="jQuery('#filterForm').toggle('slow');return false;"><?php _e('Filter/search these records', 'watupro')?></a> | <a href="admin.php?page=watupro_takings&exam_id=<?php echo $exam->ID?>&ob=<?php echo $ob?>&dir=<?php echo $dir;?>&<?php echo $filters_url;?>&export=1&noheader=1"><?php _e('Export this page', 'watupro')?><?php if($display_filters):?> <?php _e('(Filters apply)', 'watupro')?><?php endif;?></a>
	| <a href="admin.php?page=watupro_takings&exam_id=<?php echo $exam->ID?>&ob=<?php echo $ob?>&dir=<?php echo $dir;?>&<?php echo $filters_url;?>&export=1&details=1&noheader=1"><?php _e('Export with details', 'watupro')?><?php if($display_filters):?> <?php _e('(Filters apply)', 'watupro')?><?php endif;?></a> <?php if(watupro_module('reports')):?>| <a href="admin.php?page=watupro_question_stats&exam_id=<?php echo $exam->ID?>"><?php _e('Stats per question', 'watupro')?></a><?php endif;?></p>
	<div id="filterForm" style="display:<?php echo $display_filters?'block':'none';?>;margin-bottom:10px;padding:5px;" class="widefat">
	<form method="get" class="watupro" action="admin.php">
	<input type="hidden" name="page" value="watupro_takings">
	<input type="hidden" name="exam_id" value="<?php echo $exam->ID?>">
		<div><label><?php _e('Username', 'watupro')?></label> <select name="dnf">
			<option value="equals" <?php if(empty($_GET['dnf']) or $_GET['dnf']=='equals') echo "selected"?>><?php _e('Equals', 'watupro')?></option>
			<option value="starts" <?php if(!empty($_GET['dnf']) and $_GET['dnf']=='starts') echo "selected"?>><?php _e('Starts with', 'watupro')?></option>
			<option value="ends" <?php if(!empty($_GET['dnf']) and $_GET['dnf']=='ends') echo "selected"?>><?php _e('Ends with', 'watupro')?></option>
			<option value="contains" <?php if(!empty($_GET['dnf']) and $_GET['dnf']=='contains') echo "selected"?>><?php _e('Contains', 'watupro')?></option>
		</select> <input type="text" name="dn" value="<?php echo @$_GET['dn']?>"></div>
		<div><label><?php _e('Email', 'watupro')?></label> <select name="emailf">
			<option value="equals" <?php if(empty($_GET['emailf']) or $_GET['emailf']=='equals') echo "selected"?>><?php _e('Equals', 'watupro')?></option>
			<option value="starts" <?php if(!empty($_GET['emailf']) and $_GET['emailf']=='starts') echo "selected"?>><?php _e('Starts with', 'watupro')?></option>
			<option value="ends" <?php if(!empty($_GET['emailf']) and $_GET['emailf']=='ends') echo "selected"?>><?php _e('Ends with', 'watupro')?></option>
			<option value="contains" <?php if(!empty($_GET['emailf']) and $_GET['emailf']=='contains') echo "selected"?>><?php _e('Contains', 'watupro')?></option>
		</select> <input type="text" name="email" value="<?php echo @$_GET['email']?>"></div>
		<div><label><?php _e('IP Address', 'watupro')?></label> <select name="ipf">
			<option value="equals" <?php if(empty($_GET['ipf']) or $_GET['ipf']=='equals') echo "selected"?>><?php _e('Equals', 'watupro')?></option>
			<option value="starts" <?php if(!empty($_GET['ipf']) and $_GET['ipf']=='starts') echo "selected"?>><?php _e('Starts with', 'watupro')?></option>
			<option value="ends" <?php if(!empty($_GET['ipf']) and $_GET['ipf']=='ends') echo "selected"?>><?php _e('Ends with', 'watupro')?></option>
			<option value="contains" <?php if(!empty($_GET['ipf']) and $_GET['ipf']=='contains') echo "selected"?>><?php _e('Contains', 'watupro')?></option>
		</select> <input type="text" name="ip" value="<?php echo @$_GET['ip']?>"></div>
		<div><label><?php _e('Date Taken', 'watupro')?></label> <select name="datef">
			<option value="equals" <?php if(empty($_GET['datef']) or $_GET['datef']=='equals') echo "selected"?>><?php _e('Equals', 'watupro')?></option>
			<option value="before" <?php if(!empty($_GET['datef']) and $_GET['datef']=='before') echo "selected"?>><?php _e('Is before', 'watupro')?></option>
			<option value="after" <?php if(!empty($_GET['datef']) and $_GET['datef']=='after') echo "selected"?>><?php _e('Is after', 'watupro')?></option>			
		</select> <input type="text" name="date" value="<?php echo @$_GET['date']?>"> <i>YYYY-MM-DD</i></div>
		<div><label><?php _e('Points received', 'watupro')?></label> <select name="pointsf">
			<option value="equals" <?php if(empty($_GET['pointsf']) or $_GET['pointsf']=='equals') echo "selected"?>><?php _e('Equal', 'watupro')?></option>
			<option value="less" <?php if(!empty($_GET['pointsf']) and $_GET['pointsf']=='less') echo "selected"?>><?php _e('Are less than', 'watupro')?></option>
			<option value="more" <?php if(!empty($_GET['pointsf']) and $_GET['pointsf']=='more') echo "selected"?>><?php _e('Are more than', 'watupro')?></option>			
		</select> <input type="text" name="points" value="<?php echo @$_GET['points']?>"></div>
		<div><label><?php _e('Grade equals', 'watupro')?></label> <select name="grade">
		<option value="" <?php if(empty($_GET['grade'])) echo "selected"?>>------</option>
		<?php foreach($grades as $grade):?>
			<option value="<?php echo $grade->gtitle?>" <?php if(!empty($_GET['grade']) and $_GET['grade']==$grade->gtitle) echo "selected"?>><?php echo $grade->gtitle;?></option>
		<?php endforeach;?>
		</select></div>
		<div><label><?php _e('User role is', 'watupro')?></label> <select name="role">
		<option value=""><?php _e('Any role', 'watupro')?></option>
		<?php foreach($roles as $key => $role):?>
			<option value="<?php echo $key?>" <?php if(!empty($_GET['role']) and $_GET['role']==$key) echo 'selected'?>><?php echo ucfirst($key)?></option>
		<?php endforeach;?>		
		</select></div>
		<div><input type="submit" value="<?php _e('Search/Filter', 'watupro')?>">
		<input type="button" value="<?php _e('Clear Filters', 'watupro')?>" onclick="window.location='admin.php?page=watupro_takings&exam_id=<?php echo $exam->ID;?>';"></div>
	</form>
	</div>
	
	<?php if(!sizeof($takings)):?>
		<p><?php _e('There are no records that match your search criteria', 'watupro')?></p>
	<?php else:?>
		<table class="widefat">
		<tr><th><a href="?page=watupro_takings&exam_id=<?php echo $exam->ID?>&ob=display_name&dir=<?php echo $odir;?>&<?php echo $filters_url;?>"><?php _e('Username', 'watupro')?></a></th><th><a href="?page=watupro_takings&exam_id=<?php echo $exam->ID?>&ob=user_email&dir=<?php echo $odir;?>&<?php echo $filters_url;?>"><?php _e('Email', 'watupro')?></a></th><th><a href="?page=watupro/takings.php&exam_id=<?php echo $exam->ID?>&ob=ip&dir=<?php echo $odir;?>&<?php echo $filters_url;?>"><?php _e("IP", 'watupro')?></a></th><th><a href="?page=watupro/takings.php&exam_id=<?php echo $exam->ID?>&ob=date&dir=<?php echo $odir;?>&<?php echo $filters_url;?>"><?php _e('Date', 'watupro')?></a></th>
		<th><a href="?page=watupro_takings&exam_id=<?php echo $exam->ID?>&ob=points&dir=<?php echo $odir;?>&<?php echo $filters_url;?>"><?php _e('Points', 'watupro')?></a></th><th><a href="?page=watupro_takings&exam_id=<?php echo $exam->ID?>&ob=result&dir=<?php echo $odir;?>&<?php echo $filters_url;?>"><?php _e('Grade', 'watupro')?></a></th>
		<th><?php _e('Details', 'watupro')?></th><th><?php _e('Delete', 'watupro')?></th></tr>
		<?php foreach($takings as $taking):
			$taking_email = ($taking->user_id)?$taking->user_email:$taking->email;?>
			<tr id="taking<?php echo $taking->ID?>"><td><?php echo $taking->user_id?"<a href='user-edit.php?user_id=".$taking->user_id."&wp_http_referer=".urlencode("admin.php?page=watupro_takings&exam_id=".$exam->ID)."' target='_blank'>".$taking->display_name."</a>":"N/A"?></td>
			<td><?php echo !empty($taking_email)?"<a href='mailto:".$taking_email."'>".$taking_email."</a>":"N/A"?></td>
			<td><?php echo $taking->ip;?></td>
			<td><?php echo date(get_option('date_format'), strtotime($taking->date)) ?></td>
			<td><?php echo $taking->points;?></td>
			<td><?php echo preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $taking->result);?></td>
			<td><a href="#" onclick="WatuPRO.takingDetails('<?php echo $taking->ID?>');return false;"><?php _e('view', 'watupro')?></a>
			<?php if(watupro_intel()):?>
			/ <a href="admin.php?page=watupro_edit_taking&id=<?php echo $taking->ID?>"><?php _e('edit', 'watupro')?></a>
			<?php endif;?>		
			</td>
			<td><a href="#" onclick="deleteTaking(<?php echo $taking->ID?>);return false;"><?php _e('delete', 'watupro')?></a></tr>
		<?php endforeach;?>
		</table>
		
		<p><?php _e('Showing', 'watupro')?> <?php echo ($offset+1)?> - <?php echo ($offset+10)>$count?$count:($offset+10)?> <?php _e('from', 'watupro')?> <?php echo $count;?> <?php _e('records', 'watupro')?></p>
		
		<p align="center">
		<?php if($offset>0):?>
			<a href="admin.php?page=watupro_takings&exam_id=<?php echo $exam->ID?>&offset=<?php echo $offset-10;?>&ob=<?php echo $ob?>&dir=<?php echo $dir?>&<?php echo $filters_url;?>"><?php _e('previous page', 'watupro')?></a>
		<?php endif;?>
		&nbsp;
		<?php if($count>($offset+10)):?>
			<a href="admin.php?page=watupro_takings&exam_id=<?php echo $exam->ID?>&offset=<?php echo $offset+10;?>&ob=<?php echo $ob?>&dir=<?php echo $dir?>&<?php echo $filters_url;?>"><?php _e('next page', 'watupro')?></a>
		<?php endif;?>
		</p>
	<?php endif; // end if there are takings ?>	
</div>

<div id="takingDiv"></div>

<script type="text/javascript">
function deleteTaking(id)
{
	// delete taking data by ajax and remove the row with jquery
	if(!confirm("Are you sure?")) return false;
	
	data={"action":'watupro_delete_taking', "id": id};
	jQuery.get(ajaxurl, data, function(msg){
		if(msg!='')
		{
			alert(msg);
			return false;
		}
			
		// empty msg means success, remove the row
		jQuery('#taking'+id).remove();
	});	
}
</script>