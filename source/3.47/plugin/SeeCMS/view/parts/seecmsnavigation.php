<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

$routes = $see->SeeCMS->routes;

if( is_array( $_SESSION['seecms'][$this->see->siteID]['adminuser']['cmsNavigation'] ) ) {
  $routes = $_SESSION['seecms'][$this->see->siteID]['adminuser']['cmsNavigation'];
}

echo '<div class="nav"><div class="inner">';

echo '<ul>';

$faIcons = array( 'pages' => 'fa-desktop', 'posts' => 'fa-rss', 'media' => 'fa-camera', 'downloads' => 'fa-download', 'addons' => 'fa-cog', 'analytics' => 'fa-bar-chart', 'admin' => 'fa-user', 'siteusers' => 'fa-users' );

if( isset( $see->SeeCMS->config['cmsNavigationIcons'] ) ) {

	$faIcons = array_merge( $faIcons, $see->SeeCMS->config['cmsNavigationIcons'] );
}

foreach( $routes as $r ) {

  $readyname = str_replace( " ", "", strtolower( $r ) );
  $path = $see->SeeCMS->cmsRoot."/{$readyname}/";
  $selected = (( $path == $see->currentRoute ) ? ' selected' : '' );
  echo "<li class=\"{$readyname}{$selected}\"><a href=\"/{$path}\"><span class=\"icon\"><i class=\"fa {$faIcons[$readyname]}\" aria-hidden=\"true\"></i></span><span>{$r}</span></a></li>";
}

echo '</ul>';

echo '</div></div>';

echo '<div class="wrap"><div class="main">';