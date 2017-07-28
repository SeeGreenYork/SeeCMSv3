<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeCMSUpdateController {

  var $see;
  
  public function __construct( $see ) {
  
    $this->see = $see;
  }
  
  public function update( $name ) {
  
    $version = SeeDB::findOne( 'setting', ' name = ? ', array( 'version' ) );
    if( !$version ) {
      $version = SeeDB::dispense( 'setting' );
      $version->name = 'version';
    }
    
  $data = json_decode( file_get_contents( "http://update.seecms.net/update/?v={$version->value}&beta={$_GET['beta']}&".(($_GET['apply'])?'&loadUpdateFile=1':'') ) );
    
    if( !is_object( $data ) ) {
      
      $data = "<p>There are no updates currently available.</p>";
    }
    
    if( $_GET['apply'] ) {
      if( is_object( $data ) ) {
        foreach( $data->release as $rK => $release ) {

          foreach( $data->release->{$rK}->update as $update ) {

            $updateType = $update->type;
            $updateData = json_decode( base64_decode( $update->data ) );
            $data->release->$rK->notes .= $this->{$update->type}( $updateData );
          }
        
          $data->release->$rK->notes .= "<p><strong>Update applied</strong></p>";
          $version->value = $release->version;
          SeeDB::store( $version );
        }
      }
    }
    
    return( $data );
  }
  
  private function file( $data ) {
    if( $this->see->publicFolder != 'public' ) {
      $data->filepath = str_replace( "public/", "{$this->see->publicFolder}/", $data->filepath );
    }
    
    if( !is_dir( $data->filepath ) ) {
      mkdir( $data->filepath );
    }
    
    file_put_contents( "../{$data->filepath}{$data->filename}", base64_decode( $data->content ) );
  }
  
  private function mysql( $data ) {
    
    try {
      SeeDB::exec( base64_decode( $data->content ) );
    } catch(Exception $e) {
    }
  }
  
  private function permission( $data ) {
    
    $pd = explode( "=", base64_decode( $data->content ) );
    
    $aur = SeeDB::findAll( "adminuserrole" );
    foreach( $aur as $au ) {
      
      if( $au->config[0] == 'a' ) { // Serialized array
        $a = unserialize( $au->config );
      } 
      else { // json
        $a = json_decode( $au->config, true );
      }
      
      foreach( $a as $aK => $aV ) {
        if( $aK == $pd[0] ) {
          $a[$pd[1]] = $aV;
        }
      }
    
      $au->config = json_encode( $a );
      SeeDB::store( $au );
    }
  }
}