<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

$see->html->start( 'See CMS | Open Source Content Management System' );

$see->html->meta( array( 'http-equiv' => "X-UA-Compatible", 'content' => "IE=edge,chrome=1" ) );

$see->html->css( 'default.css', 'screen', '/seecms/css/' );
$see->html->css( 'plugins.css', 'screen', '/seecms/css/' );
$see->html->css( 'ie.css', 'screen', '/seecms/css/', 'lt IE 7' );
$see->html->css( 'jquery-ui.min.css', 'screen', '/seecms/js/' );
$see->html->css( 'jquery-ui.theme.css', 'screen', '/seecms/js/' );
$see->html->css( 'font-awesome.min.css', 'screen', '/seecms/css/' );

$see->html->js( 'jquery-1.11.1.min.js', '', '/seecms/js/' );
$see->html->js( 'jquery-ui.min.js', '', '/seecms/js/' );
$see->html->js( 'jquery.mjs.nestedSortable.js', '', '/seecms/js/' );
$see->html->js( 'jquery.tablesorter.min.js', '', '/seecms/js/' );
$see->html->js( 'jquery.tablesorter.widgets.min.js', '', '/seecms/js/' );
$see->html->js( 'stripey.js', '', '/seecms/js/' );
$see->html->js( 'loadfunctions.js', '', '/seecms/js/' );
$see->html->js( array( 'file' => 'js.js', 'name' => 'seecmsjs', 'path' => '/seecms/js/' ) );
$see->html->js( array( 'file' => 'js2.js', 'name' => 'seecmsjs2', 'path' => '/seecms/js/' ) );

$see->html->js( '', "var cmsURL = '/{$see->rootURL}{$see->SeeCMS->cmsRoot}/';", '' );