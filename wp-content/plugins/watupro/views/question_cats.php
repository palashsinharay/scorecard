<div class="wrap">
	<h1><?php _e('Manage Question Categories', 'watupro')?></h1>
	
	<?php if(!empty($error)):?>
	<div class="watupro-error"><?php echo $error?></div>
	<?php endif;?>

	<p><?php _e('Question categories are optional. You can use them to organize large tests, to group and paginate the questions by category etc. They can also be added on the fly at the time of adding or editing a question.', 'watupro')?></a>.</p>
	
	<p><strong><?php _e('Category description is optional and supports HTML.', 'watupro')?></strong> <?php _e('If provided it will be shown when the questions in the test are <strong>grouped and paginated</strong> by category', 'watupro')?></p>
	
	<form method="post" id="wtNewCat">
	<div style="float:left;width:100%;">
		<div style="float:left;"><label><strong><?php _e('Category name:', 'watupro')?></strong></label><br>
		 <input type="text" name="name" size="30" class="required">
		</div> 
		<div style="float:left;">
				<label><strong><?php _e('Category description:', 'watupro')?></strong></label><br>
				<textarea name="description" rows="4" cols="80"></textarea>
		</div>
		<div style="float:left;"><br>
			<input type="submit" value="<?php _e('Add category', 'watupro')?>" name="add">
		</div>	
	</div>
	</form>
	<p>&nbsp;</p>
	
	<?php foreach($cats as $cat):?>
		<form method="post" id="wtCat<?php echo $cat->ID?>">
		<div style="float:left;width:100%;">
			<hr>
			<div style="float:left;"><label><strong><?php _e('Category name:', 'watupro')?></strong></label><br>
				<input type="text" name="name" size="30" class="required" value="<?php echo $cat->name?>">
			</div>
			<div style="float:left;">
				<label><strong><?php _e('Category description:', 'watupro')?></strong></label><br>
				<textarea name="description" rows="4" cols="80"><?php echo stripslashes($cat->description)?></textarea>
			</div>
			<div style="float:left;"><br>
				<input type="submit" value="<?php _e('Save', 'watupro')?>" name="save">
				<input type="button" value="<?php _e('Delete', 'watupro')?>" onclick="watuproConfirmDelete(this.form);">
				<input type="hidden" name="id" value="<?php echo $cat->ID?>">
				<input type="hidden" name="del" value="0">
			</div>
		</div>
		</form>		
	<?php endforeach;?>
</div>

<script type="text/javascript" >
function watuproConfirmDelete(frm) {
   if(confirm("<?php _e('Are you sure? All questions that use the category will be now uncategorized.', 'watupro')?>")) {
   		frm.del.value=1;
   		frm.submit();
   }
}

jQuery(function(){
	jQuery('#wtNewCat').validate();
	<?php foreach($cats as $cat):?>
	jQuery('#wtCat<?php echo $cat->ID?>').validate();
	<?php endforeach;?>
});
</script>