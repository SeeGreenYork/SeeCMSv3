<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeCMSMediaController {

  var $see;
  var $seeimage;
  
  public function __construct( $see ) {
  
    $this->see = $see;
  }
  
  public function load() {
  
    $data['media'] = SeeDB::load( 'media', (int)$_GET['id'] );
    
    $size = @getimagesize( "images/uploads/img-original-{$data['media']->id}.{$data['media']->type}" );
    $data['mediaDimensions'] = array( 'width' => (int)$size[0], 'height' => (int)$size[1] );
    $is = $this->selectimageOptions( 0 );
    $data['imageSizes'] = $is['imagesizes'];
    
    return $data;
  }
  
  public function loadForEdit() {
    
    $data = $this->load();
    
    // Advanced permissions
    if( $this->see->SeeCMS->config["advancedEditorPermissions"] ) {
      $data['accessLevel'] = $this->see->SeeCMS->adminauth->checkContextAccess( 'media', $data['media']->id );
      
      if( !$data['accessLevel'] ) {
        $this->see->redirect( "../../media/" );
      }
      
      if( $data['accessLevel'] < 5 ) {
        $data['messages'] .= "<div class=\"seecmsmessage seecmsnotice\"><p><i class=\"fa fa-exclamation-triangle\" aria-hidden=\"true\"></i>&nbsp;&nbsp;Please note: You only have limited access to this media, you're unable to make changes to settings.</p></div>";
      }
      
      if( $_SESSION['seecms'][$this->see->siteID]['adminuser']['access']['current'] >= 5 ) {
        $data["adminuserGroups"] = SeeDB::findAll( "adminusergroup", " ORDER BY name " );
        $data["adminuserGroupPermissions"] = SeeCMSAdminAuthenticationController::getPermission( $data["media"]->id, "media", $data["adminuserGroups"] );  
      }
    }
   
    return $data;
  }
  
  public function create() {
  
    // Check if parent exists
    $mp = SeeDB::load( 'media', $_POST['parentid'] );
    
    if( ( $mp->id && $mp->isfolder ) || $_POST['parentid'] === '0' ) {
      
      // Advanced permissions
      if( $this->see->SeeCMS->config["advancedEditorPermissions"] ) {
        $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( "media", $_POST['parentid'] );
        
        if( $accessLevel < 5 ) {
          header('HTTP/1.1 401 Forbidden');
          die( "Insufficient privileges" );
        }
        
      }
      
      $m = SeeDB::dispense( 'media' );
      
      $m->parentid = $_POST['parentid'];
      $m->isfolder = (int)$_POST['isfolder'];
      
      if( $m->isfolder ) {
        $m->name = $_POST['title'];
        $m->alt = '';
        $m->status = 1;
        $m->description = '';
        $m->type = '';
        SeeDB::store( $m );
      
        // Adminusergroup permissions
        if( $this->see->SeeCMS->config['advancedEditorPermissions'] ) {
          $adminusergrouppermissions = SeeCMSAdminAuthenticationController::getPermission( $m->parentid, "media" );
          SeeCMSAdminAuthenticationController::setPermission( $m->id, "media", $adminusergrouppermissions );
        }
        
        $ret["done"] = 1;
        $ret["data"] = $this->loadByFolder( $p->parentid );
        $ret['id']   = $m->id;
        
        return( json_encode( $ret ) );
        
      }
      else {
      
        foreach( $_FILES as $fk => $fv ) {
        
          if( $fv['tmp_name'] ) {
            $extO = SeeFileController::getFileExtension( $fv['name'] );
            $ext = strtolower( $extO );
            
            // Reject if not an image or video
            $imgs = array( 'jpeg', 'jpg', 'png', 'gif' );
            $media = array( 'mp4' );
            
            if ( !in_array( $ext, $imgs ) && !in_array( $ext, $media ) ) {
              $error = 'Invalid format';
            }
            else {
              list($width, $height, $type, $attr) = getimagesize( $fv['tmp_name'] );

              $m->name = str_replace( ".{$extO}", "", $fv['name'] );
              $m->alt = $m->name;
              $m->description = $m->name;
              $m->status = 1;
              $m->type = $ext;
              SeeDB::store( $m );
              
              // Adminusergroup permissions
              if( $this->see->SeeCMS->config['advancedEditorPermissions'] ) {
                $adminusergrouppermissions = SeeCMSAdminAuthenticationController::getPermission( $m->parentid, "media" );
                SeeCMSAdminAuthenticationController::setPermission( $m->id, "media", $adminusergrouppermissions );
              }
                                
              if( in_array( $ext, $imgs ) ) { // Images
                $this->seeimage = new SeeImageController();
                $res = $this->seeimage->prepare( $fv["tmp_name"], "../{$this->see->publicFolder}/images/uploads/img-original-{$m->id}.{$ext}", 2000, 2000, $ext, true );
                $this->seeimage->prepare( $fv["tmp_name"], "../{$this->see->publicFolder}/images/uploads/img-720-720-{$m->id}.{$ext}", 720, 720, $ext, true );
                $this->seeimage->prepare( $fv["tmp_name"], "../{$this->see->publicFolder}/images/uploads/img-139-139-{$m->id}.{$ext}", 139, 139, $ext, false, true );
                
                if( !$res['status'] ) {
                  SeeDB::trash( $m );
                  
                  header('HTTP/1.1 500 Internal Server Error');
                  die( $res['errorMessage'] );
                }
                
              }
              else {
                $m->pathmodifier = md5( "{$m->id}-" . rand( 0,100000000 ) );
                SeeDB::store( $m );
                copy( $fv['tmp_name'], "../{$this->see->publicFolder}/images/uploads/vid-{$m->id}-{$m->pathmodifier}.{$ext}" );
                unlink( $fv['tmp_name'] );
              }
              
              $this->see->SeeCMS->hook->run( array( "hook" => "media-create", "data" => $m ) );
            }
          
            if( $_POST['return'] ) {
              
              $ret["done"] = 1;
              $ret['id']   = $m->id;
              return( json_encode( $ret ) );
            }
            else {
              unlink( $fv['tmp_name'] );
            }
            
          }
          
        }
        
      }
    }
    else {
      $error = 'File could not be uploaded';
    }
    
    if( $error ) {
      header('HTTP/1.1 500 Internal Server Error');
      die( $error );
    }
    else {
    
      if( $_POST['doFallback'] ) {
        $this->see->redirect('./');
      }
      else {
        die("done");
      }
      
    }
    
  }
  
  public function createImageSize( $tempName, $iss, $mediaID, $ext ) {
  
    if( $iss->mode == 'crop' ) {
      $constrain = false;
      $stretch = true;
    } else if( $iss->mode == 'resize' ) {
      $constrain = true;
      $stretch = false;
    }
    
    if( $iss->identifier ) {
      $isid = $iss->identifier;
    } else {
      $isid = $iss->id;
    }
  
    $this->seeimage->prepare( $tempName, "../{$this->see->publicFolder}/images/uploads/img-{$isid}-{$mediaID}.{$ext}", $iss->width, $iss->height, $ext, $constrain, $stretch, $iss->settings );
  }
  
  public function resampleImage() {
    
    $is    = SeeDB::load( 'imagesize', $_POST['size'] );
    $media = SeeDB::load( 'media', $_POST['id'] );
  
    if( $is->id && $media->id ) {
  
      if( $is->mode == 'crop' ) {
        $constrain = false;
        $stretch = true;
      } else if( $is->mode == 'resize' ) {
        $constrain = true;
        $stretch = false;
      }
      
      if( $is->identifier ) {
        $isid = $is->identifier;
      } else {
        $isid = $is->id;
      }
    
      $this->seeimage = new SeeImageController();
      $i = $this->seeimage->prepare( "../{$this->see->publicFolder}/images/uploads/img-original-{$media->id}.{$media->type}", "../{$this->see->publicFolder}/images/uploads/img-{$is->id}-{$media->id}.{$media->type}", $is->width, $is->height, $media->type, $constrain, $stretch, $is->settings, array( 'sx' => round( $_POST['sx'] ), 'sy' => round( $_POST['sy'] ), 'sw' => round( $_POST['sw'] ), 'sh' => round( $_POST['sh'] ) ) );
      echo $i['status'];
      die();
    }
  }
  
  public function update( $data, $errors, $settings ) {
  
    if( !$errors ) {
      $m = SeeDB::load( "media", (int)$data["id"] );
      
      if( $m->id ) {
        
        // Advanced permissions
        if( $this->see->SeeCMS->config["advancedEditorPermissions"] ) {
          $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( "media", $m->id );
          
          if( $accessLevel < 5 ) {
            $this->see->redirect( "../../media/" );
          }
          
        }
        
        $m->name = $data["name"];
        $m->alt = $data["alt"];
        
        // Update file
        if( $data["files"]["updatefile"]["error"] != UPLOAD_ERR_NO_FILE ) {
          
          if( $data["files"]["updatefile"]["error"] ==  UPLOAD_ERR_INI_SIZE || $data["files"]["updatefile"]["error"] == UPLOAD_ERR_FORM_SIZE ) {
            unlink( $data["files"]["updatefile"]["tmp_name"] );
            return array( "errors" => array( "updatefile" => "File \"{$data["files"]["updatefile"]["name"]}\" was too large" ) );
          }
          else if( $data["files"]["updatefile"]["error"] != UPLOAD_ERR_OK ) {
            unlink( $data["files"]["updatefile"]["tmp_name"] );
            return array( "errors" => array( "updatefile" => "There was an error uploading file \"{$data["files"]["updatefile"]["name"]}\"" ) );
          }
          
          // Reject if not an image or video
          $ext = strtolower( SeeFileController::getFileExtension( $data["files"]["updatefile"]["name"] ) );
          $imgs = array( "jpeg", "jpg", "png", "gif" );
          $media = array( "mp4" );
          
          if( !in_array( $ext, $imgs ) && !in_array( $ext, $media ) ) {
            unlink( $data["files"]["updatefile"]["tmp_name"] );
            return array( "errors" => array( "updatefile" => "Invalid file format for \"{$data["files"]["updatefile"]["name"]}\"" ) );
          }
  
          if( in_array( $ext, $imgs ) ) { // Images
            $this->seeimage = new SeeImageController();
            $res = $this->seeimage->prepare( $data["files"]["updatefile"]["tmp_name"], "../{$this->see->publicFolder}/images/uploads/img-original-{$m->id}.{$ext}", 2000, 2000, $ext, true );
            
            if( $res["status"] ) {
              
              // Remove existing images
              foreach( glob("../{$this->see->publicFolder}/images/uploads/img-*-{$m->id}.{$m->type}" ) as $file ) {
                
                if( !strstr( $file, "img-original-{$m->id}.{$ext}" ) ) { // Remove if not original
                  unlink( $file );
                }
                
              }
              
            }
            
            $this->seeimage->prepare( $data["files"]["updatefile"]["tmp_name"], "../{$this->see->publicFolder}/images/uploads/img-720-720-{$m->id}.{$ext}", 720, 720, $ext, true );
            $this->seeimage->prepare( $data["files"]["updatefile"]["tmp_name"], "../{$this->see->publicFolder}/images/uploads/img-139-139-{$m->id}.{$ext}", 139, 139, $ext, false, true );
            
            unlink( $data["files"]["updatefile"]["tmp_name"] );
          }
          else { // Videos
            $m->pathmodifier = md5( "{$m->id}-" . rand( 0,100000000 ) );
            move_uploaded_file( $data["files"]["updatefile"]["tmp_name"], "../{$this->see->publicFolder}/images/uploads/vid-{$m->id}-{$m->pathmodifier}.{$ext}" );
          }
          
          $m->type = $ext;
        }
       
        // Adminusergroup permissions
        if( $this->see->SeeCMS->config['advancedEditorPermissions'] ) {
          SeeCMSAdminAuthenticationController::setPermission( $m->id, "media", $data["adminusergrouppermissions"] );
        }
       
        // Save
        SeeDB::store( $m );
        $this->see->SeeCMS->hook->run( array( "hook" => "media-update", "data" => $m ) );
        
        if( !$settings["skipRedirect"] ) {
          $this->see->redirect( "?id={$m->id}" );
        }
        
      }
      
    }
  
  }
  
  public function savefolder() {

    // Check permission
    $m = SeeDB::load( "media", (int)$_POST["id"] );
    
    if( $m->id && $m->isfolder ) {
      
      // Advanced permissions
      if( $this->see->SeeCMS->config["advancedEditorPermissions"] ) {
        $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( "media", $m->id );
        
        if( $accessLevel < 5 ) {
          $this->see->redirect( "../../media/" );
        }
        
      }
      
      $data = array();
      parse_str( $_POST["form"], $data );
      
      $m->name = trim( $data["title"] );
      SeeDB::store( $m );

      // Adminusergroup permissions
      if( $this->see->SeeCMS->config['advancedEditorPermissions'] ) {
        SeeCMSAdminAuthenticationController::setPermission( $m->id, "media", $data["adminusergrouppermissions"] );
        
        if( $data["adminusergrouppermissions-cascade"] ) {
          SeeCMSAdminAuthenticationController::cascadePermission( $m->id, "media", $data["adminusergrouppermissions"] );
        }
        
      }
      
      return $this->folderTree();
    }
    
  }
  
  public function move( $id = 0, $at = '' ) {
  
    if( !$id ) {
      $id = (int)$_POST['id'];
    }
  
    if( !$at ) {
      $at = $_POST['at'];
    }
  
    // Check if parent exists
    $mp = SeeDB::load( 'media', $at );
    
    if( ( $mp->id || $at === '0' ) && $mp->id != $id ) {
      $m = SeeDB::load( 'media', $id );
      
      if( $m->id ) {
        
        // Advanced permissions
        if( $this->see->SeeCMS->config["advancedEditorPermissions"] ) {
          $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( "media", $m->id );
          
          if( $accessLevel < 5 ) {
            return( json_encode( $this->loadForCMS() ) );
          }
          
        }
      
        $m->parentid = $at;
        SeeDB::store( $m );
        
        return( json_encode( $this->loadForCMS() ) );
      }
    }
  }
  
  public function delete( $id = 0, $recursive = 0 ) {
  
    if( !$id ) {
      $id = (int)$_POST['id'];
      $first = 1;
    }
  
    $m = SeeDB::load( 'media', $id );
    
    if( $m->id ) {
    
      // Advanced permissions
      if( $this->see->SeeCMS->config["advancedEditorPermissions"] ) {
        $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( "media", $m->id );
        
        if( $accessLevel < 5 ) {
          
          if( $first ) {
            return json_encode( $this->loadForCMS() );
          }
          
          return;
        }
        
      }
    
      if( $m->isfolder && $first ) {
        $_SESSION['SeeCMS'][$this->see->siteID]['media']['currentFolder'] = $m->parentid;
      }
    
      if( $m->type == 'mp4' ) {
        unlink( "../{$this->see->publicFolder}/images/uploads/vid-{$m->id}-{$m->pathmodifier}.{$m->type}" );
      }
      else {
        
        foreach ( glob("../{$this->see->publicFolder}/images/uploads/img-*-{$m->id}.{$m->type}") as $file ) {
          unlink( $file );
        }
        
      }

      $this->recursiveDelete( $m->id );
      
      SeeDB::trash( $m );
      
      $_POST['id'] = '';
    }
    
    if( $first ) {
      return json_encode( $this->loadForCMS() );
    }
  }
  
  private function recursiveDelete( $parentID ) {

    $media = SeeDB::find( 'media', ' parentid = ? ', array( $parentID ) );
    foreach( $media as $m ) {
      $this->delete( $m->id, 1 );
    }
  }
      
  public function loadForCMS() {
    
    if( $this->see->SeeCMS->config['advancedEditorPermissions'] ) {
      $data['adminuserGroups'] = SeeDB::findAll( 'adminusergroup', ' ORDER BY name ' );      
    }

    $data['folderTree'] = $this->folderTree( 0, 0, $data["adminuserGroups"] );
    $data['media'] = $this->loadByFolder( $_SESSION["SeeCMS"][$this->see->siteID]["media"]["currentFolder"] );
    $data["createButtons"] = $this->loadCreateButtons( $_SESSION["SeeCMS"][$this->see->siteID]["media"]["currentFolder"] );
    $data["dropzone"] = $this->loadDropzone( $_SESSION["SeeCMS"][$this->see->siteID]["media"]["currentFolder"] );
    
    return $data;
  }
  
  public function folderTree( $parentID = 0, $level = 0, $adminuserGroups = null, $adminuserGroupPermissions = null ) {
    
    $parentID = (int)$parentID;
    $mode = ( $_POST['mode'] ? $_POST['mode'] : 'default' );
    
    if( !$parentID && $mode == 'default' ) {
      $content = "<h3" . ( !$_SESSION['SeeCMS'][$this->see->siteID]['media']['currentFolder'] ? ' class="selected"' : '' ) . "><a href=\"#\" class=\"mediafolder\" id=\"folder0\">Media</a></h3>";
    }
    
    // Get adminuser groups and permissions if not yet set
    if( $this->see->SeeCMS->config['advancedEditorPermissions'] ) {

      if( !$adminuserGroups ) {
        $adminuserGroups = SeeDB::findAll( 'adminusergroup', ' ORDER BY name ' );      
      }
      
      if( !isset( $adminuserGroupPermissions ) ) {
        $adminuserGroupPermissions = array();

        foreach( $adminuserGroups as $ag ) {      
          
          foreach( $ag->withCondition( " objecttype = 'media' " )->ownAdminusergrouppermission as $augp ) {
            $adminuserGroupPermissions[$augp->objectid] .= "&quot;{$ag->id}&quot;:&quot;{$augp->accesslevel}&quot;,";
          }
          
        }
        
        foreach( $adminuserGroupPermissions as $objectID => $augps ) {
          $adminuserGroupPermissions[$objectID] = "{" . rtrim( $augps, "," ) . "}";
        }
        
      }
      
    }
    
    $folders = SeeDB::find( 'media', ' parentid = ? && isfolder = ? ORDER BY name ASC ', array( $parentID, 1 ) );

    foreach( $folders as $f ) {
      
      if( $this->see->SeeCMS->config['advancedEditorPermissions'] ) {
        $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( 'media', $f->id );
        $accessClass2 = ( $accessLevel < 2 ? " accessdisabled" : "" );
        $accessClass5 = ( $accessLevel < 5 ? " accessdisabled" : "" );      
      }
      
      $ret = $this->folderTree( $f->id, $level + 1, $adminuserGroups, $adminuserGroupPermissions );
      $class = ( $ret ? 'child' : 'nochild' );
      $class .= ( $f->id == $_SESSION['SeeCMS'][$this->see->siteID]['media']['currentFolder'] ? ' selected' : '' );

      if( $mode == 'default' ) {
        $content .= "<li class=\"{$class}\">
                      <a href=\"#\" class=\"mediafolder\" id=\"folder{$f->id}\">";
      
        if( $ret ) {

          if( strpos( $ret, " selected" ) === false ) {
            $content .= "\n<span class=\"toggle open\"><i class=\"fa fa-chevron-down\" aria-hidden=\"true\"></i></span>";
          }
          else {
            $content .= "\n<span class=\"toggle close\"><i class=\"fa fa-chevron-up\" aria-hidden=\"true\"></i></span>";
          }
          
        }
                
        $content .= "<span class=\"name{$accessClass2}\">{$f->name}</span>
                        <span title=\"" . ( $accessLevel >= 5 ? "Move" : "" ) . "\" class=\"move{$accessClass5}\"><i class=\"fa fa-arrows\" aria-hidden=\"true\"></i></span>
                        <span title=\"" . ( $accessLevel >= 2 ? "Edit" : "" ) . "\" class=\"viewedit{$accessClass5}\" data-title=\"{$f->name}\"" . ( $this->see->SeeCMS->config['advancedEditorPermissions'] ? " data-adminusergrouppermissions=\"{$adminuserGroupPermissions[$f->id]}\"" : "" ) . "><i class=\"fa fa-pencil-square\" aria-hidden=\"true\"></i></span>
                        <span title=\"" . ( $accessLevel >= 5 ? "Delete" : "" ) . "\" class=\"delete{$accessClass5}\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i></span>
                        <span title=\"Move here\" class=\"target\"></span>
                      </a>";
                              
        if( $ret ) {

          if( strpos( $ret, " selected" ) === false ) {
            $content .= "\n<ul style=\"display:none;\">{$ret}</ul>";
          }
          else {
            $content .= "\n<ul>{$ret}</ul>";
          }
          
        }
        
        $content .= "</li>";
      }
      else if( $mode == 'option' ) {
        $content .= "<option id=\"folder{$f->id}\">" . str_pad( '', $level, '-', STR_PAD_LEFT ) . " {$f->name}</option>";
        
        if( $ret ) {
          $content .= $ret;
        }
        
      }
      
    }
    
    if( $mode == 'option' && !$parentID ) {
      $content = "<option id=\"folder0\">Media</option>{$content}";
    }
    
    return $content;
  }
  
  public function loadByFolder( $parentID = 0, $mode = 'admin' ) {
  
    $parentID = (int)$parentID;
    if( (int)$_POST['id'] ) {
      $m = SeeDB::load( 'media', (int)$_POST['id'] );
      if( $m->isfolder ) {
        $parentID = (int)$_POST['id'];
      }
    }
    
    if( $_POST['mode'] ) {
      $mode = $_POST['mode'];
    }
    
    $media = SeeDB::find( 'media', ' parentid = ? && isfolder = ? ORDER BY name ', array( $parentID, 0 ) );

    if( $mode != "selectimage" && $mode != "data" ) {
      $_SESSION['SeeCMS'][$this->see->siteID]['media']['currentFolder'] = $parentID;
    }

    foreach( $media as $m ) {
      
      if( $this->see->SeeCMS->config['advancedEditorPermissions'] ) {
        $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( 'media', $m->id );
        $accessClass2 = ( $accessLevel < 2 ? " accessdisabled" : "" );
        $accessClass5 = ( $accessLevel < 5 ? " accessdisabled" : "" );      
      }
      else {
        $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( 'media', $m->id );  
      }

      if( $mode == 'selectimage' ) {
        $_SESSION['SeeCMS'][$this->see->siteID]['media']['currentFolder'] = $parentID;
        
        if( $m->type == 'mp4' ) {
          $content .= "<a href=\"#\" class=\"image\" id=\"i{$m->id}\"><img src=\"/{$this->see->rootURL}seecms/images/vid.png\" alt=\"\" title=\"{$m->name}\" /></a>";
        }
        else {
          $content .= "<a href=\"#\" class=\"image\" id=\"i{$m->id}\"><img src=\"/{$this->see->rootURL}images/uploads/img-139-139-{$m->id}.{$m->type}\" alt=\"{$m->alt}\" title=\"{$m->name}\" /></a>";
        }
        
      }
      else if( $mode == "data" ) {
        $content[] = array( 'id' => $m->id, 'type' => $m->type, 'name' => $m->name );
      }
      else {

        if( $m->type == 'mp4' ) {
          $content .= "<div class=\"thumb\">
          <div class=\"overlay\">
          <p class=\"name\">{$m->name}</p>
          <p>{$m->type}</p>
          <a id=\"move{$m->id}\" class=\"move{$accessClass5}\" href=\"#\">Move</a>
          <a class=\"viewedit{$accessClass2}\" href=\"" . ( $accessLevel < 2 ? "#" : "edit/?id={$m->id}" ) . "\">View/Edit</a>
          <a id=\"deletemedia-{$m->id}\" class=\"delete deletemedia\" href=\"#\">Delete</a>
          </div>
          <img src=\"../../seecms/images/vid.png\" alt=\"\" />
          </div>";
        }
        else {
          $content .= "<div class=\"thumb\">
          <div class=\"overlay\">
          <p class=\"name\">{$m->name}</p>
          <p>{$m->type}</p>
          <a id=\"move{$m->id}\" class=\"move{$accessClass5}\" href=\"#\">Move</a>
          <a class=\"viewedit{$accessClass2}\" href=\"" . ( $accessLevel < 2 ? "#" : "edit/?id={$m->id}" ) . "\">View/Edit</a>
          <a id=\"deletemedia-{$m->id}\" class=\"delete deletemedia{$accessClass5}\" href=\"#\">Delete</a>
          </div>
          <img src=\"../../images/uploads/img-139-139-{$m->id}.{$m->type}\" alt=\"\" />
          </div>";
        }
      }
    }
    
    $content = (( $content ) ? $content : '<p><strong>There\'s no media in this folder.</strong></p>' );
    
    return( $content );
  }
 
  public function loadCreateButtons( $parentID = 0 ) {

    $parentID = (int)$parentID;
    
    if( $_POST['id'] ) {
      $m = SeeDB::load( 'media', $_POST['id'] );
      
      if( $m->isfolder ) {
        $parentID = $m->id;
      }
      
    }
    
    // Advanced permissions
    $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( 'media', $parentID );

    if( $accessLevel >= 5 ) {
      $content = "<a class=\"createmediafolder\" href=\"#\">Create folder <span><i class=\"fa fa-plus-circle\" aria-hidden=\"true\"></i></span></a>";
    }
    
    return (string)$content;
  }
 
  public function loadDropzone( $parentID = 0 ) {

    $parentID = (int)$parentID;
    
    if( $_POST['id'] ) {
      $m = SeeDB::load( 'media', $_POST['id'] );
      
      if( $m->isfolder ) {
        $parentID = $m->id;
      }
      
    }
        
    // Advanced permissions
    $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( 'media', $parentID );

    if( $accessLevel >= 5 ) {
      $content = "<div id=\"mediadropzone\" class=\"dropzone\">
        <div class=\"fallback\">
          <form action=\"../media/add\" encType=\"multipart/form-data\" method=\"post\">
            <div class=\"dz-fallback\">
              <p>Your browser is too old to support drag and drop uploads, please consider updating to a newer version.</p>
              <input name=\"file\" type=\"file\" undefined=\"\" />
              <input id=\"fallbackparentid\" name=\"parentid\" type=\"hidden\" value=\"{$_SESSION['SeeCMS'][$this->see->siteID]['media']['currentFolder']}\" />
              <input name=\"doFallback\" type=\"hidden\" value=\"1\" />
              <input type=\"submit\" value=\"Upload\" />
            </div>
          </form>
        </div>
      </div>";
    }

    return (string)$content;
  }
  
  public function loadMediaByFolder( $data = '' ) {
  
    $parentID = $data['parentID'];
    $mode = $data['mode'];
    
    return( $this->loadByFolder( $parentID, $mode ) );
  }
  
  public function adminSearch( $keyword ) {
  
    $media = SeeDB::find( 'media', ' isfolder = ? && ( name LIKE ? || alt LIKE ? ) ORDER BY name LIMIT 6 ', array( 0, "%{$keyword}%", "%{$keyword}%" ) );
    foreach( $media as $m ) {
      
      $mp = SeeDB::load( 'media', $m->parentid );
      $r[] = array( 'id' => $m->id, 'name' => $m->name, 'type' => $m->type, 'in' => (( $mp->name ) ? $mp->name : 'Root' ) );
    }
    
    return( $r );
  }
  
  public function selectimageOptions( $selectable = 1 ) {
    
    $data['friendlyImageURLs'] = (int)SeeCMSSettingController::load( 'friendlyImageURLs' );
  
    if( $selectable ) {
      $data['imagesizes'] = SeeDB::find( 'imagesize', ' ( theme = ? || theme = ? ) && selectable = ? ORDER BY name ', array( '', $this->see->theme, 1 ) );
    } else {
      $data['imagesizes'] = SeeDB::find( 'imagesize', ' ( theme = ? || theme = ? ) ORDER BY name ', array( '', $this->see->theme ) );
    }
    
    return( $data );
  }
}