<?php
/**
 * SeePHP is a PHP micro framework
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seephp.net/seephp-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeHTMLController {

  var $see;

  var $css;
  var $js;
  var $jsLate;
  var $meta;
  var $title;
  var $link;
  var $headerHTML;
  var $rootURL;
  var $forms = 0;
  
  
  public function __construct( $see ) {
  
    $this->see = $see;
    $this->rootURL = '/';
  }

  public function output( $v, $disableNewline = 0 ) {
    
    echo $v.(($disableNewline)?"":"\n");
  }
  
  /* Add page CSS */
  public function css( $fileOrArray, $media = 'screen', $path = '/css/', $conditional = '' ) {
  
    if( is_array( $fileOrArray ) ) {
    
      $file = $fileOrArray['file'];
      $path = (( $fileOrArray['path'] ) ? $fileOrArray['path'] : $path );
      $media = $fileOrArray['media'];
      $conditional = $fileOrArray['conditional'];
    } else {
      $file = $fileOrArray;
    }
  
    $cc = $this->see->cache;
  
    $cachefile = "../core/cache/publicfiles/".str_replace('/','',$path)."-".str_replace('/','-',$file);
    $cssfile = "../{$this->see->publicFolder}/{$path}{$file}";
    
    if( $cc->exists( $cachefile ) && $cc->exists( $cssfile ) ) {
      $cacheupdatetime = $cc->lastUpdated( $cachefile );
      $version = $cc->load( $cachefile );
      if( $cacheupdatetime <  $cc->lastUpdated( $cssfile ) ) {
        $version++;
        $cc->save( $cachefile, $version );
      }
    } else {
      $version = 1;
      $cc->save( $cachefile, $version );
    }
    
    $this->css .= (($conditional)?"<!--[if {$conditional}]>\n":"");
    $this->css .= "<link rel=\"stylesheet\" href=\"{$path}{$file}?v={$version}\" type=\"text/css\" media=\"{$media}\" />\n";
    $this->css .= (($conditional)?"<![endif]-->\n":"");
  }
  
  /* Add page JS */
  public function js( $fileOrArray = '', $script = '', $path = '/js/', $name = '', $override = false ) {
  
    if( is_array( $fileOrArray ) ) {
    
      $file = $fileOrArray['file'];
      $script = $fileOrArray['script'];
      $path = (( isset( $fileOrArray['path'] ) ) ? $fileOrArray['path'] : $path );
      $name = $fileOrArray['name'];
      $override = (( $fileOrArray['override'] ) ? $fileOrArray['override'] : false );
      $snappy = $fileOrArray['snappy'];
      $late = $fileOrArray['late'];
      $attributes = (($fileOrArray['attributes'])?$fileOrArray['attributes']:array());
    } else {
      $file = $fileOrArray;
    }
  
    if( $file && $path ) {
      $cc = $this->see->cache;
    
      $cachefile = "../core/cache/publicfiles/".str_replace('/','',$path)."-".str_replace('/','-',$file);
      $cssfile = "../{$this->see->publicFolder}/{$path}{$file}";
      
      if( $cc->exists( $cachefile ) && $cc->exists( $cssfile ) ) {
        $cacheupdatetime = $cc->lastUpdated( $cachefile );
        $version = $cc->load( $cachefile );
        if( $cacheupdatetime <  $cc->lastUpdated( $cssfile ) ) {
          $version++;
          $cc->save( $cachefile, $version++ );
        }
      } else {
        $version = 1;
        $cc->save( $cachefile, $version );
      }
      $versionParameter = "?v={$version}";
    }
    
    if( $name ) {
      if( isset( $this->jsName[ $name ] ) && !$override ) {
        $skip = 1;
      }
    }
    
    if( !$skip ) {
      
      $atts = '';
      
      if( is_array( $attributes ) ) {
        if( !$attributes['type'] ) {
          $attributes = array_merge( array( "type" => "text/javascript" ), $attributes );
        }
        
        foreach( $attributes as $att => $attV ) {
          $atts .= " ".$att.(($attV&&$attV!==true)?'="'.$attV.'"':"");
        }
      }
      
      if( $snappy ) {
        $this->js = "<script{$atts}".(($file)?" src=\"{$path}{$file}{$versionParameter}\"":"").">{$script}</script>\n".$this->js;
      } else if( $late ) { 
        $this->jsLate .= "<script{$atts}".(($file)?" src=\"{$path}{$file}{$versionParameter}\"":"").">{$script}</script>\n";
      } else {
        $this->js .= "<script{$atts}".(($file)?" src=\"{$path}{$file}{$versionParameter}\"":"").">{$script}</script>\n";
      }
    }
    
    if( $name ) {
      $this->jsName[ $name ] = 1;
    }
  }
  
  /* Add page meta */
  public function meta( $nameOrArray, $content = '' ) {
  
    if( is_array( $nameOrArray ) ) {
      $this->meta[ $nameOrArray['name'] ] = $this->makeTag( 'meta', true, $nameOrArray, true, true );
    } else {
      $this->meta[$name] = "<meta name=\"{$name}\" content=\"{$content}\" />\n";
    }
  }
  
  /* Add page link */
  public function link( $attributes ) {
    ob_start();
    makeTag( 'link', true, $attributes, true );
    $this->link = ob_get_clean();
  }
  
  public function img( $image, $width, $height, $attributes, $path = 'images\\' ) {
  
    $imageName = $image;
    $image = $path.$image;
    $imageCurrentFilesize = filesize( $image );
    $imageCacheFilesize = $this->see->cache->load( "../core/cache/{$imageName}" );
    $newImage = str_replace( ".{$ext}", "-cache-{$width}-{$height}.{$ext}", $image );
    
    if( !$this->see->cache->exists( $newImage ) || ( $imageCurrentFilesize != $imageCacheFilesize ) ) {
      $imgC = new SeeImageController();
      $ext = SeeHelperController::getFileExtension( $image );
      $img = $imgC->prepare( "{$image}", $newImage, $width, $height, $ext );
      $this->see->cache->save( "../core/cache/{$imageName}", $imageCurrentFilesize );
    } else {
      $img['status'] = true;
      list($img['width'], $img['height'], $type, $attr) = getimagesize($newImage);
    }
      
    if( $img['status'] ) {
      $attributes['src'] = "/".str_replace('\\','/',$newImage);
      $attributes['width'] = $img['width'].'px';
      $attributes['height'] = $img['height'].'px';
      $this->makeTag( 'img', true, $attributes, true );
    }
  }
  
  public function start( $title = 'SeePHP', $type = 'html5', $charset = 'UTF-8', $body = true ) {
  
    $this->title = $title;
  
    $doctype['html5'] = "<!DOCTYPE html><html lang=\"en\"><head>\n<SEEPHP_META>\n<meta charset=\"{$charset}\">\n<title><SEEPHP_TITLE></title>\n<SEEPHP_CSS><SEEPHP_JS><SEEPHP_HEADERHTML>\n</head>";
    $doctype['xhtml1.1'] = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\"><html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\"><head><meta http-equiv=\"Content-Type\" content=\"application/xhtml+xml; charset={$charset}\" /><meta http-equiv=\"Content-Style-Type\" content=\"text/css\" /><SEEPHP_META><title><SEEPHP_TITLE></title>\n<SEEPHP_CSS><SEEPHP_JS><SEEPHP_HEADERHTML>\n</head>";

    $this->output( $doctype[$type] );
    
    if( $body ) {
      $this->output( "<body>" );
    }
  }
  
  public function end() {
  
    echo "</body></html>";
  }
  
  public function makeTag( $tag, $open = true, $attributes = array(), $selfClosing = false, $return = false ) {
  
    if( $tag ) {
    
      if( $return ) {
        ob_start();
      }
    
      echo "<".(($open)?'':'/').$tag;

      if( $open && is_array( $attributes ) ) {
        echo $this->makeAttributes( $attributes );
      }
      
      echo (($selfClosing)?' /':'').">";
      if( !$open ) {
        echo "\n";
      }
      
      if( $return ) {
        return( ob_get_clean() );
      }
      
    }
  }
  
  protected function makeAttributes( $attributes ) {
  
    foreach( $attributes as $a => $av ) {
      if( $av ) {
        $e .= " {$a}=\"{$av}\"";
      }
    }

    return( $e );
  }

  public function makeList( $attributes, $items ) {

    $this->makeTag( 'ul', true, $attributes );
    
    foreach( $items as $i ) {
      $this->output( "<li>{$i}</li>" );
    }
    
    $this->makeTag( 'ul', false );
  }  

  public function makeMenu( $attributes, $items ) {
  
    $route = '/'.$this->see->currentRoute;
    $this->makeTag( 'ul', true, $attributes );
    
    foreach( $items as $i => $a ) {
      $this->output( "<li".(($route==$a)?' class="selected"':'')."><a href=\"{$a}\">{$i}</a></li>" );
    }
    
    $this->makeTag( 'ul', false );
  }

  public function makeMenuFromRoutes( $settings = array() ) {
        
    $started = 0;
      
    if( $settings['level'] && !$settings['baseRoute'] ) {
      for( $a = 0; $a < $settings['level']; $a++ ) {
        $settings['baseRoute'] .= $this->see->currentRouteParts[$a].'/';
      }
    }
    
    if( !$settings['routes'] ) {
      $settings['routes'] = $this->see->routes;
    }
    
    foreach( $settings['routes'] as $i => $a ) {
      if( !$a['invisible'][0] ) {
  
        $useRoute = true;
        if( $settings['baseRoute'] ) {
          if( strpos( $i, $settings['baseRoute'] ) !== 0 ) {
            $useRoute = false;
          }
        }
        
        if( ( $a['level'] != $settings['level'] ) ) {
          $useRoute = false;
        }
        
        if( is_array( $settings['exclude'] ) ) {
          if( in_array( $i, $settings['exclude'] ) ) {
            $useRoute = false;
          }
        }
        
        if( $useRoute ) {
        
          if( !$started ) {
            $started = 1;
            $this->makeTag( 'ul', true, $settings['attributes'] );
          }

          $class = $this->see->format->lowercase($this->see->format->alpha($a['label']));
          $selected = 0;
        
          if( $i == '/' ) {
            if( $this->see->currentRoute == '/' ) {
              $class .= ' selected';
              $selected = 1;
            }
          } else {
            if( strpos( $this->see->currentRoute, $i ) === 0 ) {
              $class .= ' selected';
              $selected = 1;
            }
          }
          
          $i = (($i=='/')?'':$i);
          $nestedPart = '';
          
          if( ( $settings['nesting'] > ($settings['level']) ) && $i ) {
            $settings2 = $settings;
            $settings2['baseRoute'] = $i;
            $settings2['level']++;
            ob_start();
            $this->makeMenuFromRoutes( $settings2 );
            $nestedPart = ob_get_clean();
          }
          
          if( $nestedPart ) {
            $class .= " hasChildren";
          }
          
          $class = (($class)?' class="'.$class.'"':'');
          $this->output( "<li{$class}><a href=\"/{$i}\">{$a['label']}</a>" );
          $this->output( $nestedPart );
          $this->output( "</li>" );
        }
      }
    }
      
    if( $started ) {
      $this->makeTag( 'ul', false );
      $started = 0;
    }
  }
  
  /* Start form */
  public function form( $start = array() ) {
    $f = new SeeFormController( $this->see );
    $f->open( $start );
    $this->forms++;
    return $f;
  }

}