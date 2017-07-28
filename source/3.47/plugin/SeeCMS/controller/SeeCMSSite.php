<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeCMSSiteController {

  var $see;
  
  public function __construct( $see ) {
  
    $this->see = $see;
  }
  
  public static function loadAll( $name ) {
  
    $s = SeeDB::findAll( 'site', ' ORDER BY name ' );
    return( $s );
  }
}