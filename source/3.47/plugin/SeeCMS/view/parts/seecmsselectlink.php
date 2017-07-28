<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

$see->html->start();
$see->html->css( 'editor.css', 'screen', '/seecms/css/' );

$see->html->js( 'jquery-1.11.1.min.js', '', '/seecms/js/' );
$see->html->js( 'editorpopup.js', '', '/seecms/js/' );
$see->html->js( 'editor.js', '', '/seecms/js/' );

?>

<div class="popup">
<div class="select selectLink">
<p><select id="linktype">
	<option value="">Select link type</option>
	<option value="page">CMS page</option>
	<option value="post">CMS post</option>
	<option value="download">CMS download</option>
	<option value="email">Email</option>
	<option value="external">External link</option>
</select></p>
</div>
<div class="hidden">
	<div class="folders pages"><?php echo $data['pages']; ?></div>
	<div class="folders posts"><?php echo $data['posts']; ?></div>
	<div class="folders emails"><p>Insert email address</p><p><input type="text" id="emaillink" /></p></div>
	<div class="folders externals"><p>Insert full url</p><p><input type="text" id="weblink" value="http://" /></p></div>
	<div class="folders downloads"><?php echo $data['downloads']; ?></div>
</div>
<div class="finalstep">
	<p><input type="checkbox" id="newwindow" value="1" /> Open link in new tab/window</p>
	<a class="commit" href="#" onclick="prepareLink()">Insert link</a>
</div>
</div>