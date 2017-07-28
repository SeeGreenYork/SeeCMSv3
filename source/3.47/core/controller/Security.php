<?php
/**
 * SeePHP is a PHP micro framework
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seephp.net/seephp-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeSecurityController {

  private $AESKey;

  public function encAES256( $text ) {
  
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $crypttext = base64_encode( $iv . mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->AESKey, $text, MCRYPT_MODE_CBC, $iv));
    return( $crypttext );
  }
  
  public function decAES256( $cryptstring ) {
  
    if( $cryptstring ) {
      $cryptstring = base64_decode( $cryptstring );
      $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
      $iv = substr( $cryptstring, 0, $iv_size );
      $crypttext = substr( $cryptstring, $iv_size );
      $text = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->AESKey, $crypttext, MCRYPT_MODE_CBC, $iv);
      return( rtrim( $text, "\0" ) );
    }
  }
  
  public function setAESKey( $key ) {
  
    $this->AESKey = $key;
  }

}