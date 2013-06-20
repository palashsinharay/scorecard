<div class="wrap">
	<h1><?php _e('Watu PRO Help', 'watupro')?></h1>
	
	<p>Because most of the Watu PRO screens are self-explaining, this page is not meant to be a comprehensive user manual. Its intent is only to further clarify some of the functionality in the plugin. Also check the <a href="http://calendarscripts.info/watupro/howto.html" target="_blank">online Help &amp; How-To page</a></p>
	
	<h2>Getting Started</h2>
	<p><strong>Here is how to start really quick:</strong> (<a href="http://blog.calendarscripts.info/watupro-quick-getting-started-guide/" target="_blank">See this guide with pictures</a>)</p>
	<ol>
		<li>Go to <a href="admin.php?page=watupro_exam&action=new">Create new exam</a> page.</li>
		<li>You can skip filling almost everything - just enter name.</li>
		<li>If you want to create grades, click on Show/Hide link next to "Grading" on the same form</li>
		<li>Once the test is saved you'll be taken to a page to create questions. Please add some, a test makes no sense without any questions.</li>
		<li>Go back to the <a href="admin.php?page=watupro_exams">exams list</a> and you'll see all quizzes that you have created. Under the "Embed" column there is a code similar to <strong>[WATUPRO 1]</strong> (or it might be showing other number). Copy it and paste it in a post or page in your blog. The URL of this post or page will be the URL of your test.</li>
		<li>You can view who submitted your new exam by clicking on the hyperlinked number under the "Taken" column. You will see all the details, and you can filter through them, import, export them, etc.</li>
		<li>That's it! Feel free to create exam categories and question categories, certificates, and user groups.</li>
	</ol>	
	
	
	<h2><?php _e('Shortcodes in Watu PRO', 'watupro')?></h2>
	
	<ul>
		<li><strong>[WATUPRO X]</strong> <?php _e('is the shortcode to publish an exam in a post or page. Instead of X you need to use the test ID. The full dynamically generated shortcode can be copied from "Manage exams" page.', 'watupro')?> </li>
		<li><strong>[WATUPROLIST X]</strong> <?php _e('is the shortcode to display links to all published exams from a selected category. X should be replaced with category ID so please copy the dynamic code from Categoires page.', 'watupro')?> </li>
		<li><strong>[WATUPROLIST ALL]</strong> <?php _e('lists links to all published exams in the system', 'watupro')?> </li>
		<li><strong>[WATUPRO-MYEXAMS]</strong> <?php _e('lets you replicate the logged in user "My Exams" page outside of Wordpress admin area.', 'watupro')?> </li>
		<li><strong>[WATUPRO-MYCERTIFICATES]</strong> lets you replicate the logged in user "My Certificates" page. </li>
		<li><strong>[WATUPRO-LEADERBOARD] or [WATUPRO-LEADERBOARD X]</strong> prints out a basic leaderboard of users who collected top number of points. In the second example X is the number of users, otherwise 10 is used. More configurable leaderboards are coming soon in additional module.</li>
	</ul>
	
	<h2>Translating WatuPRO</h2>
	
	<p>WatuPRO supports translating in all the languages that Wordpress supports. We have created a short guide about translating the plugin <a href="http://blog.calendarscripts.info/how-to-translate-a-wordpress-plugin/" target="_blank">here</a>.</p>
	
	<h2><?php _e('Redesigning the user pages', 'watupro')?></h2>
	
	<p><?php _e('You can safely modify the design of My Exams and My Certificates page. Simply create folder "watupro" in your theme folder and copy "my_exams.php" and "my_certificates.php" from "views" folder directly there. Then feel free to modify the code, but of course be careful not to mess with the PHP or Javascript inside.', 'watupro')?></p>
	
	<h2>Support</h2>
	
	<p>If you have any questions or issues please send us email at <strong>admin@pimteam.net</strong> or <strong>info@calendarscripts.info</strong>. <strong>Very important:</strong> if you have encountered a problem while using the plugin please do provide the URL where we can observe it. Seeing the URL helps far more than sending long descriptions of the problem. We don't knowingly release anything buggy so you can assume we don't know about the problem you have until you send us your report.</p>
</div>	