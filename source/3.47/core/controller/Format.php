<?php
/**
 * SeePHP is a PHP micro framework
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seephp.net/seephp-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeFormatController {

  public static function date( $input, $setting ) {
  
    if( !ctype_digit( $input ) && strlen( $input ) ) {
      $input = strtotime( $input );
    }
        
    if( ctype_digit( $input ) ) {
      return date( $setting, $input );
    }
    
  }

  public static function price( $input, $thousands = ',' ) {
  
    return( number_format( (float)$input, 2, '.', $thousands ) );
  }
  
  public static function alpha( $input ) {
  
    return( preg_replace("/[^A-Za-z]/",'',$input) ); 
  }
  
  public static function alphanumeric( $input ) {
  
    return( preg_replace("/[^A-Za-z0-9]/",'',$input) ); 
  }
  
  public static function url( $input ) {
  
    $input = preg_replace('/\s+/', '-', $input);
    return( preg_replace("/[^-A-Za-z0-9]/",'',$input) ); 
  }
  
  public static function lowercase( $input ) {
  
    return( strtolower( $input ) );
  }

}