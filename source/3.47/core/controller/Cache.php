<?php
/**
 * SeePHP is a PHP micro framework
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seephp.net/seephp-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeCacheController {

  public function load( $cachefile ) {
  
    if( $this->exists( $cachefile ) ) {
      return( file_get_contents( $cachefile ) );
    }
  }

  public function save( $cachefile, $content ) {
  
    if( !strstr( $cachefile, '?' ) ) {
      return( file_put_contents( $cachefile, $content ) );
    }
  }

  public function lastUpdated( $cachefile ) {
  
    return( filemtime( $cachefile ) );
  }

  public function exists( $cachefile ) {
  
    return( file_exists( $cachefile ) );
  }

}