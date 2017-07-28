<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

$actualRoute = SeeDB::findOne( 'route', ' objecttype = ? && objectid = ? && primaryroute = ? ', array( 'Page', $_GET['id'], 1 ) );

echo "<iframe src=\"/{$actualroute}\" width=\"100%\" height=\"800px\" frameBorder=\"0\"></iframe>";