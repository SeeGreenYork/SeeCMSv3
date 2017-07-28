<?php
/**
 * SeePHP is a PHP micro framework
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seephp.net/seephp-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeRouteController {

  static function route( $see ) {
  
    if( !$see->currentRoute ) {
      $see->currentRoute = '/';
    }
    
    if( $see->routingType == 'Static' || $see->routingType == 'Mixed' ) {
      $route = $see->routes[$see->currentRoute];
      
      // See if the route is to a controller
      if( $route['routeToController'][0] ) {
        $RMController = $route['routeToController'][0]."Controller";
        $RMController = new $RMController( $see );
        $RMMethod = $route['routeToMethod'][0];
        $route = $RMController->$RMMethod( $route );
      } else if( $route['routeToPlugin'][0] ) { // Or a plugin
        $RMPlugin = $see->plugins[$route['routeToPlugin'][0]];
        $RMMethod = $route['routeToMethod'][0];
        $route = $RMPlugin->$RMMethod( $route );
      } else if( $route && $see->routeManager['plugin'] ) {
        $RMPlugin = $see->plugins[$see->routeManager['plugin']];
        $RMMethod = $see->routeManager['method'];
        $route = $RMPlugin->$RMMethod( $route, 'Static' );
      }
    }

    if ( ( $see->routingType == 'Mixed' && !$route ) || $see->routingType == 'Dynamic' ) {
      
      $originalRoute = $see->currentRoute;
      
      if( $see->multisiteHome && $see->currentRoute == '/' ) {
        
        $see->currentRoute = $see->multisiteHome;
      }
      
      $see->currentRoute = $see->prepareRoute( $see->multisite.$see->currentRoute );
      $r = SeeDB::findOne( 'route', ' route = ? ', array( $see->currentRoute ) );
      if( !$r && $see->multisite ) {
        $see->currentRoute = $see->prepareRoute( $originalRoute );
        $r = SeeDB::findOne( 'route', ' route = ? ', array( $see->currentRoute ) );
      }
      
      if( $r && $see->routeManager['plugin'] ) {
        $RMPlugin = $see->plugins[$see->routeManager['plugin']];
        $RMMethod = $see->routeManager['method'];
        $route = $RMPlugin->$RMMethod( $r, 'Dynamic' );
      }

      $see->route = $route;
    }

    if ( $see->routingType == 'Backup' ) {
      $see->currentRoute = $see->prepareRoute( $see->currentRoute );
      $RMPlugin = $see->plugins[$see->routeManager['plugin']];
      $RMMethod = $see->routeManager['method'];
      $RMPlugin->$RMMethod( $r, 'Backup' );
    }
    
    // Deal with redirects
    if( $route['redirect'] ) {
        if( $see->rootURL ) {
          if( $route['redirect'][0][0] == '/' ) {
            $route['redirect'][0]  = substr_replace( $route['redirect'][0][0], $see->rootURL, 1, 0 );
          }
        }
        
        $see->redirect( $route['redirect'][0] );
    }
    
    // Deal with Simple Session Authentication
    if( is_array( $route['ssauth'] ) ) {
      foreach( $route['ssauth'] as $ssa ) {
        if( !SeeHelperController::checkValueOperator( $_SESSION[$ssa[0]], $ssa[1], $ssa[2] ) ) {
          if( $see->rootURL ) {
            if( $ssa[3][0] == '/' ) {
              $ssa[3]  = substr_replace( $ssa[3], $see->rootURL, 1, 0 );
            }
          }
          
          $see->redirect( $ssa[3] );
        }
      }
    }
    
    // Deal with Commencement
    if( is_array( $route['commencement'] ) ) {
      if( $see->format->date( $route['commencement'][0][0], "U" ) > time() ) {
        if( $see->rootURL ) {
          if( $route['commencement'][0][1][0] == '/' ) {
            $route['commencement'][0][1]  = substr_replace( $route['commencement'][0][1], $see->rootURL, 1, 0 );
          }
        }
        $see->redirect( $route['commencement'][0][1] );
      }
    }
    
    // Deal with Expiry
    if( is_array( $route['expiry'] ) ) {
      if( $see->format->date( $route['expiry'][0][0], "U" ) < time() ) {
        if( $see->rootURL ) {
          if( $route['expiry'][0][1][0] == '/' ) {
            $route['expiry'][0][1]  = substr_replace( $route['expiry'][0][1], $see->rootURL, 1, 0 );
          }
        }
        $see->redirect( $route['expiry'][0][1] );
      }
    }
    
    // If route exists make the view
    if( is_array( $route ) ) {
      $seeview = new SeeViewController( $see );
      $seeview->make( $see, $route );
    } else {
      if( method_exists( $see->routeManager['plugin']."Controller", 'http404' ) ) {
        $RMPlugin = $see->plugins[$see->routeManager['plugin']];
        $RMPlugin->http404();
      }
        
      SeeRouteController::http404();
    }
  }
  
  static function getCurrentRoute() {
  
    $replace = array( 'index.php', '//' );
    $with = array( '', '/' );
    
    if( strstr( $_SERVER['REQUEST_URI'], '?' ) ) {
      
      $qPos = strpos( $_SERVER['REQUEST_URI'], "?" );
      if( $_SERVER['REQUEST_URI'][$qPos-1] != '/' ) {
      
        $ruri2 = substr_replace( $_SERVER['REQUEST_URI'], "/", $qPos, 0 );
        header("Location: {$ruri2}");
      }
    }
    
    $route = ltrim( strtok( str_replace( $replace, $with, $_SERVER['REQUEST_URI'] ), '?'), '/' );
    
    if( !strstr( $_SERVER['REQUEST_URI'], '?' ) && substr( $route, -1 ) != '/' && $route != '' ) {
      header("Location: /{$route}/");
      die();
    }
    
    if( $route == '' ) {
      $route = '/';
    }
    
    return( $route );
  }
  
  static function http404( $d = array() ) {
    
    $message = (( isset( $d['message'] ) ) ? $d['message'] : '404 - Page not found' );
  
    http_response_code( 404 );
    echo $message;
    die();
  }

}