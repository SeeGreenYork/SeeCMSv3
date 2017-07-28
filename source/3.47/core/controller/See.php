<?php
/**
 * SeePHP is a PHP micro framework
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seephp.net/seephp-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeController {

  var $version;
  
  var $HTMLCaching;
  var $HTMLCachingTime;
  
  var $AESKey;
  
  var $rootURL;
  
  var $routingType;
  var $routeManager;
  
  var $redirectionManager;
  
  var $routes;
  var $currentRoute;
  var $currentRouteParts;
  var $lastRouteAdded;
  var $configRouteDefaults;
  
  var $viewParts;
  var $lateViewParts = array();
  var $outputManager;
  var $templateManager;
  
  var $html;
  var $cache;
  var $security;
  
  var $publicFolder;
  
  var $plugins;
  var $theme;
  var $siteID;
  var $siteTitle;
  
  var $multisite;
  var $multisiteHome;

  var $site;
  
  public function __construct() {
  
    $this->HTMLCaching = true;
    $this->HTMLCachingTime = 900;
    
    $this->html = new SeeHTMLController( $this );
    $this->format = new SeeFormatController();
    $this->cache = new SeeCacheController();
    $this->security = new SeeSecurityController();
    
    $this->getCurrentRoute();
    $this->staticRouting();
    
    $this->publicFolder = 'public';
    
    $this->version = 0.2;
  }
  
  private function getCurrentRoute() {
  
    $this->currentRoute = str_replace( $this->rootURL, '', SeeRouteController::getCurrentRoute() );
    $this->currentRouteParts = explode('/', $this->currentRoute);
  }
  
  public function getLabelFromRoute( $level = null, $route = '' ) {
  
    if( !$route ) {
      $route = $this->currentRoute;
    }
    
    if( $level !== null ) {
      $routeBits = explode('/',$route);
      $route = '';
      for($a=0;$a<=$level;$a++) {
        $route .= $routeBits[$a].'/';
      }
    }
    
    return( $this->routes[ $route ][ 'label' ] );
  }
  
  /* DATABASE CONNECTION */
  public function dbConnect( $mysqlhost, $mysqldbname, $mysqlusername, $mysqlpassword ) {
  
    SeeDB::setup("mysql:host={$mysqlhost};dbname={$mysqldbname}",$mysqlusername,$mysqlpassword);
    SeeDB::freeze( true );
  }
  
  /* REDIRECT */
  public function redirect( $url, $rc = 0 ) {
    
    if( isset( $this->redirectionManager['plugin'] ) ) {
      $RMPlugin = $this->plugins[$this->redirectionManager['plugin']];
      $RMMethod = $this->redirectionManager['method'];
      $RMPlugin->$RMMethod( $url );
    }
  
    $responseCode[301] = "HTTP/1.1 301 Moved Permanently"; 
    
    if( $responseCode[$rc] ) {
      header( $responseCode[$rc] );
    }
  
    header("Location: ".$url);
    die();
  }
  
  /* SITE ROUTING */
  public function staticRouting() {
  
    $this->routingType = 'Static';
  }
  
  public function dynamicRouting() {
  
    $this->routingType = 'Dynamic';
  }
  
  public function mixedRouting() {
  
    $this->routingType = 'Mixed';
  }
  
  public function routeManager( $routeManager ) {
  
    $this->routeManager = $routeManager;
  }
  
  public function redirectionManager( $redirectionManager ) {
  
    $this->redirectionManager = $redirectionManager;
  }
  
  public function addRoute( $route, $label, $config = '' ) {
  
    $route = $this->prepareRoute( $route );
    $this->lastRouteAdded = $route;
    
    if( $this->routingType == 'Static' || $this->routingType == 'Mixed' ) {
      $this->routes[ $route ][ 'label' ] = $label;
      $this->routes[ $route ][ 'level' ] = SeeHelperController::countPathLevels( $route );
    } else {
      $this::siteError( "Can't define static route without enabling static routing." );
    }
    
    if( is_array( $config ) ) {
      foreach( $config as $k => $v ) {
        if( $this->version <= 0.2 ) {
          $this->configureRoute( $route, $k, $v );
        } else {
          $this->configureRoute( $k, $v );
        }
      }
    }

    if( is_array( $this->configRouteDefaults ) ) {
      foreach( $this->configRouteDefaults as $k => $v ) {
        if( $this->version <= 0.2 ) {
          $this->configureRoute( $route, $k, $v );
        } else {
          $this->configureRoute( $k, $v );
        }
      }
    }
  }
  
  /* If calling using the lazy method $route might be $config, $config might be $setting */
  public function configureRoute( $op1, $op2, $op3 = '', $op4 = '' ) {

    if( $this->version <= 0.2 ) {
      if( !$op3 ) {
        $config = $op1;
        $setting = $op2;
        $route = $this->lastRouteAdded;
      } else {
        $route = $op1;
        $config = $op2;
        $setting = $op3;
      }
    } else {
      $config = $op1;
      $setting = $op2;
      $override = $op3;
      $route = $this->lastRouteAdded;
    }
    
    $route = $this->prepareRoute( $route );
    
    if( $config == 'custom' ) {
      foreach( $setting as $k => $v ) {
        $this->routes[ $route ][ $config ][ $k ] = $v;
      }
    } else {
      if( $override ) {
        $this->routes[ $route ][ $config ] = array( $setting );
      } else {
        $this->routes[ $route ][ $config ][] = $setting;
      }
    }
  }
  
  public function configureRouteDefault( $config, $setting ) {
  
    if( is_array( $this->routes ) ) {
      foreach( $this->routes as $r => $rv ) {
        if( $config == 'custom' ) {
          foreach( $setting as $k => $v ) {
            if( !$this->routes[ $r ][ $config ][ $k ] ) {
              $this->routes[ $r ][ $config ][ $k ] = $v;
            }
          }
        } else {
          if( !$this->routes[ $r ][ $config ] ) {
            $this->routes[ $r ][ $config ][0] = $setting;
          }
        }
      }
    }
    
    $this->configRouteDefaults[$config] = $setting;
  }
  
  public function prepareRoute( $route ) {

    // REMOVE SLASH FROM START IF IT HAS ONE
    if( $route[0] == '/' && $route != '/' ) {
      $route = ltrim( $route, '/' );
    }
    
    /* ADD SLASH TO END OF ROUTE IF IT'S NOT ALREADY GOT ONE */
    if( substr($route, -1) != '/' ) {
      $route .= '/';
    }
    
    return( $route );
  }
  
  /* ERRORS */
  static function error( $error ) {
  
    die( $error );
  }
  
  /* View parts */
  function addViewPart( $name, $prepend = false, $late = false ) {

    if( $prepend ) {
      $this->viewParts = array_merge( array( $name => array( 'caching' => false, 'globalCache' => false ) ), $this->viewParts ); 
    } else if( $late ) {
      $this->lateViewParts[ $name ] = array( 'caching' => false, 'globalCache' => false );
    } else {
      $this->viewParts[ $name ] = array( 'caching' => false, 'globalCache' => false );
    }
  }
  
  function configureViewPart( $name, $config, $setting = '' ) {
  
    $type = (( isset( $this->viewParts[ $name ] ) ) ? 'viewParts' : 'lateViewParts' );
  
    if( is_array( $config ) ) {
      foreach( $config as $c => $s ) {
        $this->{$type}[ $name ][ $c ] = $s;
      }
    } else {
      $this->{$type}[ $name ][ $config ] = $setting;
    }
  }
  
  function setRootURL( $url ) {
  
    if( $url ) {
      $this->rootURL = $this->html->rootURL = $this->prepareRoute( $url );
      $this->getCurrentRoute();
    }
  }
  
  function setVersion( $version ) {
  
    $this->version = $version;
  }
  
  function loadPlugin( $pluginName, $configuration = '' ) {
  
    include "../plugin/{$pluginName}/config.php";
    include "../plugin/{$pluginName}/viewparts.php";
    include "../plugin/{$pluginName}/routes.php";
    
    unset( $configuration );
    
    if( !property_exists( $this, $pluginName ) ) {
      $this->$pluginName = $plugin;
    }
    
    $this->plugins[$pluginName] = $plugin;
    return( $plugin );
  }
  
  static function siteError( $error ) {

    die( $error );
  }  
  
  public function outputManager( $outputManager ) {
  
    $this->outputManager[] = $outputManager;
  }
  
  public function templateManager( $templateManager ) {
  
    $this->templateManager[] = $templateManager;
  }
}