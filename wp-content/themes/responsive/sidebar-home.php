<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/**
 * Home Widgets Template
 *
 *
 * @file           sidebar-home.php
 * @package        Responsive 
 * @author         Emil Uzelac 
 * @copyright      2003 - 2013 ThemeID
 * @license        license.txt
 * @version        Release: 1.0
 * @filesource     wp-content/themes/responsive/sidebar-home.php
 * @link           http://codex.wordpress.org/Theme_Development#Widgets_.28sidebar.php.29
 * @since          available since Release 1.0
 */
?> 
<?php 


        $args = array(
	//'posts_per_page'  => '',
	'offset'          => 0,
	'category'        => get_cat_ID('testset'),
	'orderby'         => 'post_date',
	'order'           => 'DESC',
	'include'         => '',
	'exclude'         => '',
	'meta_key'        => '',
	'meta_value'      => '',
	'post_type'       => 'post',
	'post_mime_type'  => '',
	'post_parent'     => '',
	'post_status'     => 'publish',
	'suppress_filters' => FALSE ); 

$posts = get_posts($args);
//echo "<pre>";
//print_r($posts);
//echo "</pre>";
?>

	<?php responsive_widgets_before(); // above widgets container hook ?>
<?php $i=1;?>
    <div id="widgets" class="home-widgets">
      <?php foreach ($posts as $value) : ?>
        
        <div class="grid col-300<?php if($i%3 == 0) echo ' fit'?>">
        <?php responsive_widgets(); // responsive above widgets hook 
        $i++
        ?>
            <?php if($value->post_status == 'publish'):?>
			<?php if (!dynamic_sidebar('home-widget-1')) : ?>
            <div class="widget-wrapper">
            
                <div class="widget-title-home"><h3><?php echo $value->post_title ;?></h3></div>
                <div class="textwidget"><ul><?php echo str_replace(array('p>','_blank'),array('li>','_self'),do_shortcode($value->post_content));?></ul></div>
           
			</div><!-- end of .widget-wrapper -->
			<?php endif; //end of home-widget-2 ?>
           <?php endif; ?>
        <?php responsive_widgets_end(); // after widgets hook ?>
        </div><!-- end of .col-300 -->
    <?php endforeach; ?>
    </div><!-- end of #widgets -->
	<?php responsive_widgets_after(); // after widgets container hook ?>