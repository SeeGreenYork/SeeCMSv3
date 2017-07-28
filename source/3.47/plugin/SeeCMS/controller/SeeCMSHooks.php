<?php

class SeeCMSHooksController {

  var $see;
  private $hooks;
  
  public function __construct( $see ) {
  
    $this->see = $see;
  }
  
  public function add( $settings ) {
  
    $this->hooks[$settings['hook']][] = $settings;
  }
  
  public function run( $settings ) {
  
    if( is_array( $this->hooks[$settings['hook']] ) ) {
      foreach( $this->hooks[$settings['hook']] as $hook ) {
        if( $hook['controller'] ) {
          $c = new $hook['controller']( $this->see );
          $m[] = $c->$hook['method']( @$settings['data'] );
        } else if( $hook['plugin'] ) {
          $m[] = $this->see->plugins[$hook['plugin']]->$hook['method']( @$settings['data'] );
        }
      }
      return( $m );
    }
  }
}