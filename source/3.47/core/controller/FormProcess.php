<?php
/**
 * SeePHP is a PHP micro framework
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seephp.net/seephp-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeFormProcessController {

  public function sendByEmail( $data, $errors, $configData ) {
  
    if( !count( $errors ) ) {
      $to = $configData['settings']['to'];
      $from = $configData['settings']['from'];
      $subject = $configData['settings']['subject'];
      
      $thStyle = (( $configData['settings']['style']['th'] ) ? $configData['settings']['style']['th'] : 'text-align: left; background: #383838; color: #fff; padding: 4px;' );
      $tdStyle = (( $configData['settings']['style']['td'] ) ? $configData['settings']['style']['td'] : 'text-align: left; background: #c2d347; color: #000; padding: 4px;' );
      
      if( SeeValidationController::email( $to, 'email' ) || SeeValidationController::email( $from, 'email' ) ) {
        if( is_array( $data ) ) {
          if( count( $data ) ) {
            $replyTo = '';
            $o = "<html><body>{$configData['settings']['introHTML']}<table>";
            foreach( $data as $k => $v ) {
              if( !strstr( $k, '-SeeFormProcess-sendByEmail-' ) && !isset( $configData['settings']['excludeField'][$k] ) ) {
                $replyTo = (($k=='email')?$v:$replyTo);
                $k = ucwords( str_replace( '_', ' ', $k ) );
                $v = nl2br( htmlentities( $v ) );
                $o .= "<tr><th style=\"{$thStyle}\">{$k}</th><td style=\"{$tdStyle}\">{$v}</td></tr>";
              }
            }
            $o .= "</table></body></html>";
            
            $emailController = new SeeEmailController();
            $emailController->sendHTMLEmail( $from, $to, $o, $subject, '', $replyTo );
            if( $configData['settings']['successredirect'] ) {
              SeeController::redirect( $configData['settings']['successredirect'] );
            }
          }
        }
      } else {
        SeeController::siteError( 'Email address invalid' );
      }
    }
  }

  public function saveToDB( $data, $errors, $configData ) {
  
    SeeDB::freeze( false );
  
    if( !count( $errors ) ) {
      $table = $configData['settings']['table'];
      $row = SeeDB::dispense($table);
      if( is_array( $data ) ) {
        if( count( $data ) ) {
          foreach( $data as $k => $v ) {
            if( !strstr( $k, 'seeform-' ) && !strstr( $k, 'files' ) ) {
              $row->$k = $v;
            } else if( strstr( $k, 'seeform-' ) ) {
              $_SESSION[$k.'-done'] = 1;
            }
          }
          $row->seephp_datetime = date("Y-m-d H:i:s");
          $id = SeeDB::store($row);
          if( $configData['settings']['successredirect'] ) {
            SeeController::redirect( $configData['settings']['successredirect'] );
          }
        }
      }
    }
  }
  
  public function saveToDBAndSendByEmail( $data, $errors, $configData ) {
  
    $redirect = $configData['settings']['successredirect'];
    $configData['settings']['successredirect'] = '';
    $this->saveToDB( $data, $errors, $configData );

    $configData['settings']['successredirect'] = $redirect;
    $this->sendByEmail( $data, $errors, $configData );
  }
}