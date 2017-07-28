<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeCMSSessionController {

  var $see;
  var $maxLifetime;
  
  public function __construct( $see, $maxLifetime = 0 ) {
  
    $this->see = $see;
    $this->maxLifetime = $maxLifetime;
  }
  
  function open( $savePath, $sessionName ) {
    
    return true;
  }

  function close() {
    
    return true;
  }

  function read( $id ) {
    
    $r = SeeDB::findOne( 'session', ' sessionid = ? ', array( $id ) );
    return( $r->data );
  }

  function write( $id, $data ) {
    
    $access = time();
 
    $r = SeeDB::exec( 'REPLACE INTO session ( sessionid, access, data ) VALUES ( ?, ?, ? )', array( $id, $access, $data ) );
    return( $r );
  }

  function destroy( $id ) {
 
    return SeeDB::exec( " DELETE FROM session WHERE sessionid = ? ", array( $id ) );
  }

  function gc( $maxLifetime ) {
    
    if( $this->maxLifetime ) {
      $maxLifetime = $this->maxLifetime;
    }
    
    SeeDB::exec( " DELETE FROM session WHERE access < ? ", array( time()-$maxLifetime ) );

    return true;
  }
}