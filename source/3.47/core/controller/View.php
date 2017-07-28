<?php
/**
 * SeePHP is a PHP micro framework
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seephp.net/seephp-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeViewController {

  var $see;

  public function __construct( $see ) {
  
    $this->see = $see;
  }

  public function make( $see, $route ) {
    
    // Merge late view parts
    $see->viewParts = array_merge( $see->viewParts, $see->lateViewParts ); 
  
    $template = (($route['template'][0])?$route['template'][0]:'Default');
    
    ob_start();
    if( strstr( $template, '/' ) ) {
      $templateParts = explode( '/', $template );
      include "../plugin/{$templateParts[0]}/view/{$templateParts[1]}.php";
    } else {
      if( $this->see->theme ) {
        if( file_exists( "../custom/{$this->see->theme}/view/{$template}.php" ) ) {
          include "../custom/{$this->see->theme}/view/{$template}.php";
        }
      } else if( file_exists( "../custom/view/{$template}.php" ) ) {
        include "../custom/view/{$template}.php";
      } else {
        include "../core/view/{$template}.php";
      }
    }
    $SeePHPViewContext['o'] = ob_get_clean();
    
    /* Get a clean route to use for cache filenames etc */
    $cleanRoute = str_replace(ltrim($see->rootURL, '/'), '', $see->currentRoute);
    $cleanRoute = trim( $cleanRoute, '/' );
    $cleanRoute = str_replace( '/', '-', $cleanRoute );
    $cleanRoute = preg_replace('/[^A-Za-z0-9_\-]/', '', $cleanRoute);
    
    // For each tag
    if( is_array( $see->viewParts ) ) {
      foreach( $see->viewParts as $t => $c ) {

        $SeePHPViewContext = $this->processTag( $SeePHPViewContext, $t, $c, $route );
      }
    }
    
    // Replace SeePHP tags
    $tags = array( '<SEEPHP_META>', '<SEEPHP_TITLE>', '<SEEPHP_CSS>', '<SEEPHP_JS>', '<SEEPHP_HEADERHTML>' );
    $output = array( implode("",$see->html->meta), $see->html->title, $see->html->css, $see->html->js.$see->html->jsLate, $see->html->headerHTML );
    $SeePHPViewContext['o'] = str_replace( $tags, $output, $SeePHPViewContext['o'] );
    
    if( $this->see->multisite ) {
      
      $sites = SeeDB::findAll( 'site' );
      foreach( $sites as $site ) {
        
        $with = (( $site->route == $this->see->multisite ) ? '/' : 'http'.((( !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' )) ? 's' : '').'://'.$site->name.(($this->see->rootURL)?'/'.$this->see->rootURL:'/') );
        $SeePHPViewContext['o'] = str_replace( 'href="/'.$site->route, 'href="'.$with, $SeePHPViewContext['o'] );
        $SeePHPViewContext['o'] = str_replace( 'href="/'.$site->homeroute, 'href="/', $SeePHPViewContext['o'] );
      }
    }
    
    if( $see->rootURL != '/' ) {
      $replace = array( 'src="//', 'src="/css/', 'src="/', 'href="/', 'action="/', 'src="##' );
      $with = array( 'src="##', 'src="/'.$see->rootURL.'css/', 'src="/'.$see->rootURL, 'href="/'.$see->rootURL , 'action="/'.$see->rootURL, 'src="//' );
      $SeePHPViewContext['o'] = str_replace( $replace, $with, $SeePHPViewContext['o'] );
    }
    
    if( is_array( $see->outputManager ) ) {
      foreach( $see->outputManager as $om ) {
        if( $om['plugin'] ) {
          $RMPlugin = $see->plugins[$om['plugin']];
          $RMMethod = $om['method'];
          $SeePHPViewContext['o'] = $RMPlugin->$RMMethod( $SeePHPViewContext['o'] );
        }
      }
    }
      
    echo $SeePHPViewContext['o'];
  }
  
  public function processTag( $SeePHPViewContext, $t, $c, $route, $forcePassin = "" ) {

    $superpart = '{{'.$t;
    $part = "<{$t}>";
    $cache = '';
    $cachefile = '';
    $see = $this->see;
    
    // Check if the tag is in this page
    if( $c['superpart'] &&  stristr( $SeePHPViewContext['o'], $superpart ) ) { // Superpart
      
      $start = 0;
      
      while( $start = strpos( $SeePHPViewContext['o'], '{{'.$t, $start+1 ) ) {
        
        $end = strpos( $SeePHPViewContext['o'], '}}', $start+1 );
        
        $wholeSuperPart = substr( $SeePHPViewContext['o'], $start, $end-$start+2 );
        
        // Find where the part exists
        if( $c['path'] ) {
          $path = $c['path'];
        } else {
          if( $this->see->theme ) {
            if( file_exists( "../custom/{$this->see->theme}/view/parts/{$t}.php" ) ) {
              $path = "custom/{$this->see->theme}";
            }
          } else if( file_exists( "../custom/view/parts/{$t}.php" ) ) {
            $path = 'custom';
          } else {
            $path = 'core';
          }
        }
    
        $partfile = "../{$path}/view/parts/{$t}.php";
        // No caching
        // If the parameter is set to load some data
        if( isset( $c['controller'] ) ) {
          // If the requested controller exists
          if( class_exists( $c['controller'].'Controller' ) ) {
            // If there's some data to pass in to the controller
            if( isset( $c['controllerPassin'] ) ) {
              $passin = $c['controllerPassin'];
            } else {
              $passin = '';
            }
            // Instance the controller and run the load function
            $loadFromClass = $c['controller'].'Controller';
            $loadFrom = new $loadFromClass( $see );
            
            // Call method
            if( $c['controllerMethod'] ) {
              $data = $loadFrom->$c['controllerMethod']( $passin, $wholeSuperPart );
            }
          }
        } else if( isset( $c['plugin'] ) ) {
          // If the requested plugin exists
          if( $this->see->$c['plugin'] ) {
            // If there's some data to pass in to the controller
            if( isset( $c['pluginPassin'] ) ) {
              $passin = $c['pluginPassin'];
            } else {
              $passin = '';
            }
            
            // Call method
            if( $c['pluginMethod'] ) {
              $data = $this->see->$c['plugin']->$c['pluginMethod']( $passin, $wholeSuperPart );
            }
          }
        } else if( $forcePassin ) {
          $data = $forcePassin;
        } else {
          $data = '';
        }
        
        // Buffer the output and replace the tag with it
        ob_start();
        
        if( file_exists( "../{$path}/view/parts/{$t}.php" ) ) {
          include "../{$path}/view/parts/{$t}.php";
        }
        
        $oc = ob_get_clean();
        
        // replace the part in the template with the content
        $SeePHPViewContext['o'] = str_ireplace( $wholeSuperPart, $oc, $SeePHPViewContext['o'] );
      }
      
    }
    else if( stristr( $SeePHPViewContext['o'], $part ) ) {

      // Find where the part exists
      if( $c['path'] ) {
        $path = $c['path'];
      }
      else {
        if( $this->see->theme ) {
          if( file_exists( "../custom/{$this->see->theme}/view/parts/{$t}.php" ) ) {
            $path = "custom/{$this->see->theme}";
          }
        }
        else if( file_exists( "../custom/view/parts/{$t}.php" ) ) {
          $path = 'custom';
        }
        else {
          $path = 'core';
        }
      }
    
      $partfile = "../{$path}/view/parts/{$t}.php";
      
      // See if there's a cache (unless caching is turned off for this tag/whole site)
      if( $this->see->HTMLCaching && $c['caching'] ) {
        $cc = $this->see->cache;
        
        if( $c['globalCache'] == 'withPost' ) {
          ob_start();
          var_dump( $_POST );
          $postRoute = md5( ob_get_clean() );
          $cachefile = "../core/cache/{$t}_{$postRoute}.html";
        }
        else if( $c['globalCache'] ) {
          
          if( isset( $c['globalCachePassin'] ) ) {
            ob_start();
            var_dump( $c['globalCachePassin'] );
            $dataRoute = md5( ob_get_clean() );
            $cachefile = "../core/cache/{$t}_{$dataRoute}.html";
          }
          else {
            $cachefile = "../core/cache/{$t}.html";
          }
          
        }
        else {
          $cachefile = "../core/cache/{$t}_{$cleanRoute}.html";
        }
        
        if( $cc->exists( $cachefile ) && $cc->exists( $partfile ) ) {
          $cacheupdatetime = $cc->lastUpdated( $cachefile );
          $partupdatetime = $cc->lastUpdated( $partfile );
          
          if( ( $cacheupdatetime > time() - ( $c['cachingTimeout'] ? $c['cachingTimeout'] : $this->see->HTMLCachingTime ) ) && ( $cacheupdatetime > $partupdatetime ) ) {
            $cache = $cc->load( $cachefile );
          }
          else {
            unlink( $cachefile );
          }
        }
      }
      
      if( $cache ) {
        $oc = $cache;
      }
      else {
        // If the parameter is set to load some data
        if( isset( $c['controller'] ) ) {
          // If the requested controller exists
          if( class_exists( $c['controller'].'Controller' ) ) {
            // If there's some data to pass in to the controller
            if( isset( $c['controllerPassin'] ) ) {
              $passin = $c['controllerPassin'];
            } else {
              $passin = '';
            }
            // Instance the controller and run the load function
            $loadFromClass = $c['controller'].'Controller';
            $loadFrom = new $loadFromClass( $see );
            
            // Call load method unless the tag is set to use another
            if( $c['controllerMethod'] ) {
              $data = $loadFrom->$c['controllerMethod']( $passin );
            } else {
              $data = $loadFrom->load( $passin );
            }
            
          }
        }
        else if( isset( $c['plugin'] ) ) {
          // If the requested plugin exists
          if( $this->see->$c['plugin'] ) {
            // If there's some data to pass in to the controller
            if( isset( $c['pluginPassin'] ) ) {
              $passin = $c['pluginPassin'];
            }
            else {
              $passin = '';
            }
            
            // Call method
            if( $c['pluginMethod'] ) {
              $data = $this->see->$c['plugin']->$c['pluginMethod']( $passin, $wholeSuperPart );
            }
          }
        }
        else if( $forcePassin ) {
          $data = $forcePassin;
        }
        else {
          $data = '';
        }
        
        // Buffer the output and replace the tag with it
        ob_start();
        
        if( $c['contentViewPart'] ) {
          
          if( $route['content'][$t] ) {
              echo $route['content'][$t];
          } else {
            if( !$cleanRoute ) {
              $cvpCleanRoute = 'index';
            } else {
              $cvpCleanRoute = $cleanRoute;
            }
            
            if( $this->see->theme ) {
              if( file_exists( "../custom/{$this->see->theme}/view/content/{$t}/{$cvpCleanRoute}.php" ) ) {
                include "../custom/{$this->see->theme}/view/content/{$t}/{$cvpCleanRoute}.php";
              }
            } else if( file_exists( "../custom/view/content/{$t}/{$cvpCleanRoute}.php" ) ) {
              include "../custom/view/content/{$t}/{$cvpCleanRoute}.php";
            } else if( $c['contentViewPartDefault'] ) {
              if( file_exists( "../custom/view/content/{$t}/{$c['contentViewPartDefault']}.php" ) ) {
                include "../custom/view/content/{$t}/{$c['contentViewPartDefault']}.php";
              }
            }
          }
        } else {
          if( file_exists( "../{$path}/view/parts/{$t}.php" ) ) {
            include "../{$path}/view/parts/{$t}.php";
          }
        }
        
        $oc = ob_get_clean();
        
        // Save a cache of the part
        if( $cachefile ) {
          file_put_contents( $cachefile, $oc );
        }
      }
      // replace the part in the template with the content
      $SeePHPViewContext['o'] = str_ireplace( $part, $oc, $SeePHPViewContext['o'] );
    }
    
    return( $SeePHPViewContext );
  }
}