<div class="wrap">
	<h1><?php _e('Watu PRO Exam Categories', 'watupro')?></h1>

	<p><?php _e('Categories can be used to organize your exams by topic. The most useful part of this is that you can limit the access to categories for the different', 'watupro')?> <a href="admin.php?page=watupro_groups"><?php _e('user groups', 'watupro')?></a>.</p>

	<?php if(sizeof($cats)):?>
		<table class="widefat">
        <tr><td colspan="2"><a href="admin.php?page=watupro_cats&do=add"><?php _e('Click here to add new category', 'watupro')?></a></td></tr>
		<tr><th><?php _e('Category Name', 'watupro')?></th><th><?php _e('Shortcode for exams list', 'watupro')?></th><th><?php _e('Edit', 'watupro')?></th></tr>
		<?php foreach($cats as $cat):?>
		<tr><td><?php echo $cat->name;?></a></td>
		<td><strong>[WATUPROLIST <?php echo $cat->ID?>]</strong></td>		
		<td><a href="admin.php?page=watupro_cats&do=edit&id=<?php echo $cat->ID?>"><?php _e('Edit', 'watupro')?></a></td></tr>
		<?php endforeach;?>
		</table>
	<?php else:?>
    <p><?php _e('You have not created any categories yet.', 'watupro')?> <a href="admin.php?page=watupro_cats&do=add"><?php _e('Click here', 'watupro')?></a> <?php _e('to create one.', 'watupro')?></p>
	<?php endif;?>
	
	<h2><?php _e('Shortcodes to list published exams', 'watupro')?></h2>
	
	<p><?php _e('To list all published exams in the system you can use the shortcode', 'watupro')?> <strong>[WATUPROLIST ALL]</strong> </p>
	<p><?php _e('To list all published uncategorized exams you can use the shortcode', 'watupro')?> <strong>[WATUPROLIST 0]</strong></p>
	
</div>