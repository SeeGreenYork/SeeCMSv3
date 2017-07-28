<?php
/**
 * SeePHP is a PHP micro framework
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seephp.net/seephp-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeImageController {

  public function prepare( $image, $newImage, $width, $height, $ext, $constrain = false, $stretch = false, $settings = '', $resample = false ) {
    
    if( trim( ini_get( 'memory_limit' ), "M" ) < 256 ) { // Try increase memory to 256MB minimum
      ini_set( 'memory_limit', "256M" );
    }
    
    if( !file_exists( $image ) ) {
      
      $newImg['status'] = false;
      $newImg['errorMessage'] = 'File could not be uploaded';
      return( $newImg );
    }
    
    $imageInfo = getimagesize( $image );
    $sizeMB = round( ( $imageInfo[0] * $imageInfo[1] * 6 ) / 1048576 ); // Approx
    
    if( $sizeMB > trim( ini_get( 'memory_limit' ), "M" ) / 2 ) { // Too big, don't try create
      
      $newImg['status'] = false;
      $newImg['errorMessage'] = 'File was too large to be processed (Pixel size)';
      return( $newImg );
    }
  
    $img = $this->load( $image, $ext );
    
    if( $settings ) {
      
      $settings = json_decode( $settings );
    }
    
    if( is_array( $resample ) ) {

      $newImg = $this->resample( $img, $resample, $width, $height );
      
    } else {
    
      $imgWidth = imagesx( $img );
      $imgHeight = imagesy( $img );
    
      $ow = $width/$imgWidth;
      $oh = $height/$imgHeight;
    
      if( $imgWidth == $width && $imgHeight == $height && !isset( $settings->filters ) ) {
        
        copy( $image, $newImage );
        $newImg = array( 'img' => $newImg, 'width' => $width, 'height' => $height, 'status' => true );
        
      } else {
      
        if( $width && $height && !$constrain ) {
          $newImg = $this->imageResizeCrop( $img, $width, $height, $ext );
        } else if( $ow <= $oh ) {
          $newImg = $this->imageSetWidth( $img, (( $width > $imgWidth && !$stretch ) ? $imgWidth : $width ), $ext );
        } else {
          $newImg = $this->imageSetHeight( $img, (( $height > $imgHeight && !$stretch ) ? $imgHeight : $height ), $ext );
        }
      }
    }
    
    if( $newImg ) {
      
      if( !$newImg['status'] ) {
        
        $newImg['status'] = $this->save( $newImg['img'], $newImage, $settings );
      }
      
      return( $newImg );
      
    } else {
      $newImg['status'] = false;
      return( $newImg );
    }
  }
  
  private function resample( $img, $resample, $width, $height ) {
    
    // Create new image 
    $newImg = imageCreateTrueColor( $width, $height );
    
    if( $ext == "png" ) {
      imagealphablending($newImg, false);
      imagesavealpha($newImg, true);  
    } 
    
    imageCopyResampled( $newImg, $img, 0, 0, $resample['sx'], $resample['sy'], $width, $height, $resample['sw'], $resample['sh'] );
    
    // Return new image
    return( array( 'img' => $newImg, 'width' => $width, 'height' => $height ) );
  }
  
  private function imageSetWidth( $img, $width, $ext ) {
  
    // Get current image dimensions
    $ow = imagesx( $img );
    $oh = imagesy( $img );
    
    // Work out new height
    $height = $oh * ( $width / $ow );
    
    // Create new image 
    $newImg = imageCreateTrueColor( $width, $height );
    
    if( $ext == "png" ) {
      imagealphablending($newImg, false);
      imagesavealpha($newImg, true);  
    } 
    
    // Resample old image onto new image
    imageCopyResampled( $newImg, $img, 0, 0, 0, 0, $width, $height, $ow, $oh );
    
    // Return new image
    return( array( 'img' => $newImg, 'width' => $width, 'height' => $height ) );
  }
  
  private function imageSetHeight( $img, $height, $ext ) {
  
    // Get current image dimensions
    $ow = imagesx( $img );
    $oh = imagesy( $img );
    
    // Work out new width
    $width = $ow * ( $height / $oh );
    
    // Create new image 
    $newImg = imageCreateTrueColor( $width, $height );
    
    if ($ext == "png") {
      imagealphablending($newImg, false);
      imagesavealpha($newImg, true);  
    } 
    
    // Resample old image onto new image
    imageCopyResampled( $newImg, $img, 0, 0, 0, 0, $width, $height, $ow, $oh );
    
    // Return new image
    return( array( 'img' => $newImg, 'width' => $width, 'height' => $height ) );
  }
  
  private function imageResizeCrop( $img, $width, $height, $ext ) {
 
    // Get size of source image
    $sw = imagesx( $img );
    $sh = imagesy( $img );

    // Use source dimensions if target width/height is 0  
    $width = ( $width ? $width : $sw );
    $height = ( $height ? $height : $sh );

    // Create destination image at target size
    $newImg = imageCreateTrueColor( $width, $height );
      
    // Work out size ratios
    $rw = $width / $sw;
    $rh = $height / $sh;
      
    // Use lowest ratio as 'full' side and work out displacement for other axis
    if ( $rw < $rh ) {
      $src_w = $width / $rh;
      $src_h = $height / $rh;
      $src_x = ( $sw - $src_w ) / 2;
      $src_y = 0;
    } else {
      $src_w = $width / $rw;
      $src_h = $height / $rw;
      $src_x = 0;
      $src_y = ( $sh - $src_h ) / 2;
    }
      
    if(  $ext == "png"  ) {
      imagealphablending($newImg, false);
      imagesavealpha($newImg, true);  
    }  
    
    // Resize the image
    imageCopyResampled( $newImg, $img, 0, 0, $src_x, $src_y, $width, $height, $src_w, $src_h );

    // Return the resized image
    return( array( 'img' => $newImg, 'width' => $width, 'height' => $height ) );
  }
  
  private function load( $filename, $ext = '' ) {
  
    $ext = strtolower( $ext );
    
    switch( $ext ) {
      case 'jpg':
      case 'jpeg':
        $img = imageCreateFromJPEG( $filename );
        break;
    
      case 'gif':
        $img = imageCreateFromGIF( $filename );
        break;
      
      case 'png':
        $img = imageCreateFromPNG( $filename );
        break;
      
      default:
        return false;
        break;
    }
    
    if( $img ) {
      
      if( function_exists( 'exif_read_data' ) ) {
        
        if( exif_read_data( $filename, 'IFD0') ) {
          
          $exif = exif_read_data( $filename, 0, true );
          
          switch( $exif['IFD0']['Orientation'] ) {
            case 3:
              $img = imagerotate( $img, 180, 0 );
              break;
            case 6:
              $img = imagerotate( $img, -90, 0 );
              break;
            case 8:
              $img = imagerotate( $img, 90, 0 );
              break;
          }
        }
      }
    }
    
    return $img;
  }
  
  private function save( $img, $filename, $settings ) {

    $ext = SeeHelperController::getFileExtension( $filename );

    if( isset( $settings->filters ) ) {
      foreach( $settings->filters as $f ) {
        
        if( $f[0] == 'SEECMS_COLOR_OVERLAY' ) {
          
          $ow = imagesx( $img );
          $oh = imagesy( $img );
          
          imagealphablending($img, true);
          imagefilledrectangle($img, 0, 0, $ow, $oh, imagecolorallocatealpha ( $img, $f[1], $f[2], $f[3], $f[4] ) );
          
        } else if( $f[0] == 'SEECMS_COLORIZE_TRANSPARENT' ) {
          
          $w = imagesx($img);
          $h = imagesy($img);

          $target = imagecreatetruecolor( $w, $h );
          $transparent = imagecolorallocatealpha( $target, 0, 0, 0, 127 );
          imagefill( $target, 0, 0, $transparent );

          for( $y=0; $y<$h; $y++ ) {
            
            for( $x=0; $x<$w; $x++ ) {
              
              $rgb = imagecolorsforindex( $img, imagecolorat( $img, $x, $y ));

              $pixelColour = imagecolorallocatealpha( $target, $f[1], $f[2], $f[3], $rgb['alpha'] );
              imagesetpixel( $target, $x, $y, $pixelColour );
            }
          }

          imagealphablending( $target, false );
          imagesavealpha( $target, true );

          $img = $target;
          
        } else {
        
          if( isset( $f[4] ) ) {
            imagefilter( $img, constant($f[0]), $f[1], $f[2], $f[3], $f[4] );
          } else if ( isset( $f[2] ) ) {
            imagefilter( $img, constant($f[0]), $f[1], $f[2] );
          } else if ( isset( $f[1] ) ) {
            imagefilter( $img, constant($f[0]), $f[1] );
          } else {
            imagefilter( $img, constant($f[0]) );
          }
        }
      }
    }
        
    switch( $ext ) {
      case 'jpg':
      case 'jpeg':
        imageJPEG( $img, $filename, 100 );
        break;
    
      case 'gif':
        imageGIF( $img, $filename );
        break;
      
      case 'png':
        imagealphablending($img, false);
        imagesavealpha($img, true);
        imagePNG( $img, $filename );
        break;
      
      default:
        return false;
        break;
    }

    return true;
  }

}