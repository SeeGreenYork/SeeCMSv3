<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeCMSSettingController {

  var $see;
  
  public function __construct( $see ) {
  
    $this->see = $see;
  }
  
  public static function load( $name ) {
  
    $s = SeeDB::findOne( 'setting', ' name = ? ', array( $name ) );
    return( $s->value );
  }
}