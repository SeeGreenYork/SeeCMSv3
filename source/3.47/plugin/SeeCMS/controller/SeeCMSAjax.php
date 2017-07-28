<?php

class SeeCMSAjaxController {

  var $see;
  
  public function __construct( $see ) {
  
    $this->see = $see;
  }
  
  public function request( $route ) {
    
    // Check access to action
    $access = $this->see->SeeCMS->adminauth->checkAccess( 'action-'.$_POST['action'], null, false );
        
    if( $access ) {
      $actionParts = explode( '-', $_POST['action'] );

      $controllerPart = ucwords( $actionParts[0] );
      $controllerMethod = $actionParts[1];

      $controllerName = "SeeCMS{$controllerPart}Controller";
      
      
      if( class_exists( $controllerName ) ) {
      
        $c = new $controllerName( $this->see );
        if( method_exists( $c, $controllerMethod ) ) {

          echo $c->$controllerMethod();
        }
      }
    }
    
    die();
  }
}