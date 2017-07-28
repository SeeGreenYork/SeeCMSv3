<?php
/**
 * SeePHP is a PHP micro framework
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seephp.net/seephp-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeValidationController {

  static function required( $var, $type ) {
  
    if( $var ) {
      return( true );
    }
    return( false );
  }
  
  static function email( $var, $type ) {
  
    if( preg_match( '/^("[^"]+"|[^@ ]+)@[a-z0-9._-]+\.[a-z0-9]+$/i', $var ) && $var ) {
      return( true );
    }
    return( false );
  }
  
  static function alphanumeric( $var, $type ) {
  
    if( preg_match("/^([a-zA-Z0-9])+$/", $var) && $var ) {
      return( true );
    }
    return( false );
  }
  
  static function numeric( $var, $type ) {
  
    if( preg_match("/^([0-9])+$/", $var) && $var) {
      return( true );
    }
    return( false );
  }
  
  static function minlength( $var, $type ) {
  
    $l = explode( '=', $type );
    if( strlen( $var ) >= $l[1] ) {
      return( true );
    }
    return( false );
  }
  
  static function maxlength( $var, $type ) {
  
    $l = explode( '=', $type );
    if( strlen( $var ) <= $l[1] ) {
      return( true );
    }
    return( false );
  }
  
  static function mustbe( $var, $type ) {
  
    $data = explode( '=', $type );
    $l = explode( '||', $data[1] );
    if( in_array( $var, $l ) ) {
      return( true );
    }
    return( false );
  }

}