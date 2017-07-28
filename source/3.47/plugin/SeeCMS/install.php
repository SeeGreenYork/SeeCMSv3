<!DOCTYPE html>
<html>
<head>
<title>SeeCMS install</title>
<style type="text/css">
body {font-family: arial, sans-serif; font-size: 100%;}
h1, h2, h3, h4, p {color: #383838; font-weight: normal; padding: 0; margin: 0;}
h1 {padding: 0 0 10px 0;}
h2 {font-size: 120%; padding: 0 0 10px 0; font-weight: bold}
p {padding: 0 0 1em 0;}
hr {background: #383838; border: none; width: 100%; margin: 10px auto; display: block; height: 1px; padding: 0;}
input {-webkit-appearance:none;}
input[type="text"], input[type="password"], select, textarea {border-radius: 5px; padding: 5px 2%; width: 100%; border: 1px solid #383838; box-sizing: border-box; font-size: 100%}
input[type="submit"] {display: block; width: 292px; padding:10px 10px; border-radius: 5px; background: #c2d347; color: #383838; text-align: center; border: none; font-size: 100%; font-weight: bold; margin: 10px 0}
a {color: #8d9a26;}
.center {display: block; margin: auto; max-width: 720px; padding: 10px 3%}
.left, .right {float: left;}
.left {width: 23%; padding: 0 3% 0 0;}
.left p {font-weight: bold; line-height: 2em;}
.right {width: 74%;}
.clear {clear: both; display: block}
.error { color: #933; font-weight: bold; }
</style>
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin: 0; padding: 0; background: #e8e8e8">
<div style="padding: 0; width: 100%; margin: auto">
	<div style="background: #383838; margin: 0 auto; display: block;">
		<a href="#" style="width: 300px; display: block; margin: 0 auto">
			<img style="width: 100%" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAbgAAADFCAIAAACpY3BPAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3ZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMDE0IDc5LjE1Njc5NywgMjAxNC8wOC8yMC0wOTo1MzowMiAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDphNDIwY2VkYy1mMzNiLTNkNGQtYTFhMi1jY2ZhOGYyZWRlMDQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6Qjc3MzQ3RTJCQzE0MTFFNDk4QkZCNkQzNzQ3NzRCOEYiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6Qjc3MzQ3RTFCQzE0MTFFNDk4QkZCNkQzNzQ3NzRCOEYiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTQgKFdpbmRvd3MpIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6YTQyMGNlZGMtZjMzYi0zZDRkLWExYTItY2NmYThmMmVkZTA0IiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOmE0MjBjZWRjLWYzM2ItM2Q0ZC1hMWEyLWNjZmE4ZjJlZGUwNCIv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"/>
		</a>
	</div>
	<div class="center">
<?php 

$f = $this->see->html->form( $formSettings );

?>
<h1>Welcome to SeeCMS</h1>
<p>Please complete the information below to get started.</p>
<?php

if( $_GET['error'] == 'db' ) {
  echo '<p class="error">Unable to connect to the database</p>';
}

?>
<hr />
<h2>Required information</h2>
<p>Please provide the following information.</p>
<div class="left">
  <p>Site title</p>
</div>
<div class="right">
  <p><?php $f->text( array( 'name' => 'sitetitle', 'value' => $_POST['sitetitle'] ) ); ?></p>
</div>
<div class="clear"></div>
<div class="left">
  <p>Your name</p>
</div>
<div class="right">
  <p><?php $f->text( array( 'name' => 'name', 'value' => $_POST['name'] ) ); ?></p>
</div>
<div class="clear"></div>
<div class="left">
  <p>Email address</p>
</div>
<div class="right">
  <p><?php $f->text( array( 'name' => 'email', 'value' => $_POST['email'] ) ); ?></p>
</div>
<div class="clear"></div>
<div class="left">
  <p>Password</p>
</div>
<div class="right">
  <p><?php $f->password( array( 'name' => 'password' ) ); ?></p>
</div>
<div class="clear"></div>
<div class="left">
  <p>Confirm password</p>
</div>
<div class="right">
  <p><?php $f->password( array( 'name' => 'confirmpassword' ) ); ?></p>
</div>
<div class="clear"></div>
<hr/>
<h2>Support information</h2>
<p>Tell your site administrators who they need to contact if they need help.</p>
<div class="left">
  <p>Support info</p>
</div>
<div class="right">
  <p><?php $f->textarea( array( 'name' => 'supportmessage' ) ); ?></p>
</div>
<div class="clear"></div>
<hr/>
<h2>Database</h2>
<p>Only MySQL is supported at the moment.</p>
<div class="left">
  <p>Database host</p>
</div>
<div class="right">
  <p><?php $f->text( array( 'name' => 'databasehost', 'value' => (($_POST['databasehost'])?$_POST['databasehost']:'localhost') ) ); ?></p>
</div>
<div class="clear"></div>
<div class="left">
  <p>Database name</p>
</div>
<div class="right">
  <p><?php $f->text( array( 'name' => 'databasename', 'value' => $_POST['databasename'] ) ); ?></p>
</div>
<div class="clear"></div>
<div class="left">
  <p>Database username</p>
</div>
<div class="right">
  <p><?php $f->text( array( 'name' => 'databaseusername', 'value' => $_POST['databaseusername'] ) ); ?></p>
</div>
<div class="clear"></div>
<div class="left">
  <p>Database password</p>
</div>
<div class="right">
  <p><?php $f->password( array( 'name' => 'databasepassword' , 'value' => $_POST['databasepassword'] ) ); ?></p>
</div>
<div class="clear"></div>
<hr/>
<h2>Theme</h2>
<div class="left">
  <p>Install theme</p>
</div>
<div class="right">
  <p><?php $f->select( array( 'name' => 'theme' ), array( 'options' => array( 'SeeCMS Naked' => 'SeeCMS Naked - No junk, start from scratch', 'SeeCMS 2015' => 'SeeCMS 2015 - Good if you\'re just trying it for the first time', 'SeeCMS 2017' => 'SeeCMS 2017' ) ) ); ?></p>
</div>
<div class="clear"></div>
<div class="left">
  <p>Install samples</p>
</div>
<div class="right">
  <p><?php $f->select( array( 'name' => 'themestuff' ), array( 'options' => array( 'No' => 'No - I know what I\'m doing', 'Yes' => 'Yes - I\'d like to try a sample site' ) ) ); ?></p>
</div>
<div class="clear"></div>
<hr/>
<h2>Advanced</h2>
<p>You can leave these as the default if installing to the current location, or override if necessary. You can also change them later in your config.php file.</p>
<div class="left">
  <p>Public folder</p>
</div>
<div class="right">
  <p><?php $f->text( array( 'name' => 'publicfolder', 'value' => basename( getcwd() ) ) ); ?></p>
</div>
<div class="clear"></div>
<div class="left">
  <p>Site URI</p>
</div>
<div class="right">
  <p><?php $f->text( array( 'name' => 'siteurl', 'value' => ltrim($_SERVER['REQUEST_URI'], '/') ) ); ?></p>
</div>
<div class="clear"></div>
<div class="left">
  <p>CMS URI</p>
</div>
<div class="right">
  <p><?php $f->text( array( 'name' => 'cmsurl', 'value' => substr(md5(rand().microtime()),0,6) ) ); ?></p>
</div>
<div class="clear"></div>
<?php $f->submit( array( 'name' => 'submit', 'value' => 'Install' ) ); ?>
<?php $f->close(); ?>
</div>
</div>
</body>
</html>