<?php
$see->html->start( $see->SeeCMS->object->htmltitle );
// CSS
$see->html->css('SeeCMS%202017/default.css');
$see->html->css('SeeCMS%202017/font-awesome.min.css');
$see->html->css('SeeCMS%202017/slick.css');
// END OF CSS
// META
if (strstr($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
  $see->html->meta( array('name' => 'viewport', 'content' => 'initial-scale=1,user-scalable=yes') );
} else {
  $see->html->meta( array('name' => 'viewport', 'content' => 'initial-scale=1,user-scalable=yes,maximum-scale=1') );
}
$see->html->meta( array('name' => 'description', 'content' => ''.$see->SeeCMS->object->metadescription.'') );
$see->html->meta( array('name' => 'keywords', 'content' => ''.$see->SeeCMS->object->metakeywords.'') );
$see->html->meta( array('name' => 'generator', 'content' => 'SeeCMS - seecms.net') );
$see->html->meta( array('name' => 'web_author', 'content' => 'See Green, seegreen.uk') );
$see->html->meta( array('name' => 'apple-mobile-web-app-capable', 'content' => 'yes') );
$see->html->meta( array('name' => 'apple-mobile-web-app-status-bar-style', 'content' => 'default') );
$see->html->meta( array('name' => 'format-detection', 'content' => 'telephone=no') );
// END OF META
// JS
$see->html->js( array( 'file' => 'SeeCMS%202017/jquery-1.12.2.min.js', 'name' => 'jquery', 'snappy' => true ) );
$see->html->js('SeeCMS%202017/slick.min.js');
$see->html->js('SeeCMS%202017/js.js');
// END OF JS
$see->html->headerHTML .= '<link href="https://fonts.googleapis.com/css?family=Raleway:400,700,900|Roboto:100,300,400,500,700" rel="stylesheet">';
?>
<SEECMSEDIT>
