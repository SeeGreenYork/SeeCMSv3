<?php
/**
 * SeePHP is a PHP micro framework
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seephp.net/seephp-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

$startTime = microtime(true); // For simple dev profiling

$includeDirs = array( 'libs', 'controller' );

foreach( $includeDirs as $dir ) {
  foreach(glob("../core/{$dir}/*.php") as $filename) {
      require_once "{$filename}";
  }
}

$time[0] = microtime(true)-$startTime;

$see = new SeeController();

$time[1] = microtime(true)-$startTime;

foreach(glob("../custom/*.php") as $filename) {
    require_once "{$filename}";
}

session_start();

if( $see->theme ) {
  $includeCustomDirs = array( (($see->theme)?"{$see->theme}/":"").'controller' );
  foreach(glob("../custom/{$see->theme}/*.php") as $filename) {
    require_once "{$filename}";
  }
} else {
  $includeCustomDirs = array( 'controller' );
}

$time[2] = microtime(true)-$startTime;

foreach( $includeCustomDirs as $dir ) {
  foreach(glob("../custom/{$dir}/*.php") as $filename) {
      require_once "{$filename}";
  }
}

$time[3] = microtime(true)-$startTime;

if( !$seeSkipRouting ) {
  SeeRouteController::route($see);
}

$time[4] = microtime(true)-$startTime;