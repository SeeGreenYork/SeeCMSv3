<?php

class SeeCMSHelperController {

  var $see;
  
  public function __construct( $see ) {
  
    $this->see = $see;
  }
  
  public function breadcrumb() {
  
    return( $this->see->SeeCMS->ascendants );
  }
  
  public function breadcrumbHTML( $settings ) {
  
    $message = (( isset( $settings['message'] ) ) ? $settings['message'] : 'You are here: ' );
    $divider = (( isset( $settings['divider'] ) ) ? $settings['divider'] : ' &gt; ' );
    $home = (( isset( $settings['home'] ) ) ? $settings['home'] : 'Home' );
  
    $bc = $this->breadcrumb();
    
    $html = "<p>{$message}";
    
    if( is_array( $bc ) ) {
      foreach( $bc as $pK => $p ) {
      
        if( (int)$p && ( !$this->see->multisite || $pK > 1 ) ) {
        
          $page = SeeDB::load( 'page', $p );
          if( $page->id ) {
            $route = SeeDB::findOne( 'route', ' objecttype = ? && objectid = ? && primaryRoute = ? ', array( 'page', $page->id, 1 ) );
            
            if( $this->see->SeeCMS->object->id == $page->id && $this->see->SeeCMS->object->getMeta('type') == 'page' ) {
              $html .= "{$divider}<span class=\"selected\">{$page->title}</span>";
            } else {
              $html .= "{$divider}<a href=\"/{$route->route}\">{$page->title}</a>";
            }
          }
          
        } else if ( $p == 0 ) {
          
          $html .= "<a href=\"/\">{$home}</a>";
        }
      }
    }
    
    if( $this->see->SeeCMS->object->getMeta('type') == 'post' ) {
      
      $html .= "{$divider}<span class=\"selected\">{$this->see->SeeCMS->object->title}</span>";
    }
    
    $html .= "</p>";
    
    return( $html );
  }
  
  public function xmlsitemap() {
  
    $createCache = false;
  
    // Check cache
    $cache = SeeDB::findOne( 'datacache', ' name = ? && context >= ? ', array( 'SeeCMSXMLSitemap', time()-3600 ) );
    if( $cache ) {
      echo base64_decode( $cache->data );
      die();
    } else {
      $createCache = true;
    }
  
    // Get pages
    $pc = new SeeCMSPageController( $this->see );
    $ps = $pc->navigation( array( 'startAtParent' => 0, 'startAtLevel' => 0, 'levelsToGenerate' => 3, 'html' => 0, 'mode' => 'allpages' )  );
  
    foreach( $ps as $p ) {
    
      $o[0] .= "\n<url><loc>http".((empty( $_SERVER['HTTPS'])) ? 's' : '')."://{$_SERVER['HTTP_HOST']}/".rtrim($this->see->rootURL, '/')."{$p['route']}</loc></url>";
      
      if( is_array( $p['subpages'] ) ) {
        foreach( $p['subpages'] as $p2 ) {
      
          $o[1] .= "\n<url><loc>http".((empty( $_SERVER['HTTPS'])) ? 's' : '')."://{$_SERVER['HTTP_HOST']}/".rtrim($this->see->rootURL, '/')."{$p2['route']}</loc></url>";
          
          if( is_array( $p2['subpages'] ) ) {
            foreach( $p2['subpages'] as $p3 ) {
        
              $o[2] .= "\n<url><loc>http".((empty( $_SERVER['HTTPS'])) ? 's' : '')."://{$_SERVER['HTTP_HOST']}/".rtrim($this->see->rootURL, '/')."{$p3['route']}</loc></url>";
            }
          }
        }
      }
    }
    
    // Get posts
    $pc = new SeeCMSPostController( $this->see );
    $ps = $pc->feed( array( 'limit' => 100 ) );
    
    if( is_array( $ps ) ) {
      foreach( $ps as $p ) {
        $o[1] .= "\n<url><loc>http".((empty( $_SERVER['HTTPS'])) ? 's' : '')."://{$_SERVER['HTTP_HOST']}/".rtrim($this->see->rootURL, '/')."{$p['route']}</loc></url>";
      }
    }
    
    $e = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    $e .= $o[0];
    $e .= $o[1];
    $e .= $o[2];
    $e .= "\n</urlset>";
    
    if( $createCache ) {
    
      $cache = SeeDB::dispense( 'datacache' );
      $cache->name = 'SeeCMSXMLSitemap';
      $cache->context = time();
      $cache->data = base64_encode( $e );
      SeeDB::store( $cache );
    }
    
    die( $e );
  }
}