<?php
	$see->html->start( $see->SeeCMS->object->htmltitle );
	$see->html->css('SeeCMS%202015/bootstrap.css');
	$see->html->css('SeeCMS%202015/slick.css');
	$see->html->js( array( 'file' => 'SeeCMS%202015/jquery-1.11.1.min.js', 'name' => 'jquery', 'snappy' => true ) );
	$see->html->js('SeeCMS%202015/js.js');
	$see->html->js('SeeCMS%202015/fancybox/jquery.fancybox.pack.js');
	$see->html->js('SeeCMS%202015/bootstrap.js');
	$see->html->js('SeeCMS%202015/slick.min.js');
	$see->html->headerHTML = "<link rel=\"stylesheet\" href=\"/js/SeeCMS%202015/fancybox/jquery.fancybox.css\" type=\"text/css\" media=\"screen\">";
?>
<SEECMSEDIT>