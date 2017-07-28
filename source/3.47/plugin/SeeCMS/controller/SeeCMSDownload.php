<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeCMSDownloadController {

  var $see;
  
  public function __construct( $see ) {
  
    $this->see = $see;
  }
  
  public function load() {
  
    $d = SeeDB::load( 'download', (int)$_GET['id'] );
    
    return( $d );
  }
  
  public function loadForEdit() {
    
    $data['download'] = $this->load();
    $data['download']->filesize = SeeFileController::filesize( filesize( "../custom/files/download-{$data['download']->id}.{$data['download']->type}" ) );
    
    // Advanced permissions
    $data['accessLevel'] = $this->see->SeeCMS->adminauth->checkContextAccess( 'download', $data['download']->id );

    if( !$data['accessLevel'] ) {
      $this->see->redirect( "../../downloads/" );
    }
    
    if( $data['accessLevel'] < 5 ) {
      $data['messages'] .= "<div class=\"seecmsmessage seecmsnotice\"><p><i class=\"fa fa-exclamation-triangle\" aria-hidden=\"true\"></i>&nbsp;&nbsp;Please note: You only have limited access to this download, you're unable to make changes to settings.</p></div>";
    }
    
    // Categories
    $data["categories"] = SeeDB::getAssoc( " SELECT id, name FROM `category` WHERE objecttype = 'download' ORDER BY NAME " );

    // Websiteusergroup permissions
    $data["websiteuserGroups"] = SeeDB::findAll( 'websiteusergroup', ' ORDER BY name ' );
    
    foreach( $data["websiteuserGroups"] as $ug ) {
    
      foreach( $ug->ownWebsiteusergrouppermission as $wugp ) {
     
        if( $wugp->objecttype == 'download' && $wugp->objectid == $data['download']->id ) {
          $data["websiteuserGroupPermissions"][$ug->id] = 1;
        }
      }
    }
 
    // Adminusergroup permissions
    if( $this->see->SeeCMS->config["advancedEditorPermissions"] ) {
      $data["adminuserGroups"] = SeeDB::findAll( "adminusergroup", " ORDER BY name " );
      $data["adminuserGroupPermissions"] = SeeCMSAdminAuthenticationController::getPermission( $data["download"]->id, "download", $data["adminuserGroups"] );
    }
    
    return $data;
  }
  
  public function create() {
    
    // Clear datacache
    $this->clearDatacache();
    
    // Check if parent exists
    $dp = SeeDB::load( 'download', $_POST['parentid'] );

    if( ( $dp->id && $dp->isfolder ) || $_POST['parentid'] === '0' ) {
      
      // Advanced permissions
      if( $this->see->SeeCMS->config["advancedEditorPermissions"] ) {
        $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( "download", $_POST['parentid'] );
        
        if( $accessLevel < 5 ) {
          header('HTTP/1.1 401 Forbidden');
          die( "Insufficient privileges" );
        }
        
      }
      
      $d = SeeDB::dispense( 'download' );
      
      $d->parentid = $_POST['parentid'];
      $d->isfolder = (int)$_POST['isfolder'];
      
      if( $d->isfolder ) {
        $d->name = $_POST['title'];
        $d->type = '';
        $d->status = 1;
        $d->description = '';
        SeeDB::store( $d );      
      }
      else {
      
        foreach( $_FILES as $fk => $fv ) {
        
          if( $fv['tmp_name'] ) {
          
            $ext = SeeFileController::getFileExtension( $fv['name'] );
            
            // Reject if an unsafe file
            $invalidformats = array( 'ashx', 'asmx', 'asp', 'aspx', 'axd', 'cer', 'config', 'htaccess', 'jsp', 'php', 'rem', 'rules', 'shtm', 'shtml', 'soap', 'stm', 'xoml' );
            
            if ( in_array( $ext, $invalidformats ) ) {
              $error = 'Invalid format';
            }
            else {
              $d->name = str_replace( ".{$ext}", "", $fv['name'] );
              $d->description = $d->name;
              $d->status = (((int)$this->see->SeeCMS->config['defaultDocumentStatus'])?1:0);
              $d->type = strtolower( $ext );
              SeeDB::store( $d );
              
              move_uploaded_file( $fv['tmp_name'], "../custom/files/download-{$d->id}.{$ext}" );
            }
          }
          
        }
        
      }
      
      if( !$error ) {
        // Websiteusergroup permissions
        $parentGroups = SeeDB::find( 'websiteusergrouppermission', ' objecttype = ? && objectid = ? ', array( 'download', $d->parentid ) );
        
        foreach( $parentGroups as $pg ) {
          $groupsToAdd[] = $pg->websiteusergroup_id;
        }
        
        SeeCMSWebsiteUserController::setPermission( $d->id, "download", $groupsToAdd );
        
        // Adminusergroup permissions
        if( $this->see->SeeCMS->config['advancedEditorPermissions'] ) {
          $adminusergrouppermissions = SeeCMSAdminAuthenticationController::getPermission( $d->parentid, "download" );
          SeeCMSAdminAuthenticationController::setPermission( $d->id, "download", $adminusergrouppermissions );
        }
        
        $this->see->SeeCMS->hook->run( array( "hook" => "download-create", "data" => $d ) );
        
        if( $_POST['doFallback'] ) {
          $this->see->redirect('../downloads/');
        }
        
        if( $_POST['return'] ) {
          $ret["done"] = 1;
          $ret['id']   = $d->id;
          $ret['type'] = $d->type;
          
          return json_encode( $ret );
        }
        else {
          die( $this->folderTree() );
        }
      }
      
    }
    else {
      header('HTTP/1.1 500 Internal Server Error');
      die( 'File could not be uploaded' );
    }
    
  }
  
  public function update( $data, $errors, $settings ) {
    
    if( !$errors ) {      
      $d = SeeDB::load( 'download', (int)$data['id'] );
      
      if( $d->id ) {
        
        // Advanced permissions
        if( $this->see->SeeCMS->config["advancedEditorPermissions"] ) {
          $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( "download", $d->id );
          
          if( $accessLevel < 5 ) {
            $this->see->redirect( "../../downloads/" );
          }
          
        }
        
        // Clear datacache
        $this->clearDatacache();
        
        $d->name = $data["name"];
        $d->description = $data["description"];
        
        // Update file
        if( $data["files"]["updatefile"]["error"] != UPLOAD_ERR_NO_FILE ) {
          
          $file = $data["files"]["updatefile"];
          
          if( $file["error"] ==  UPLOAD_ERR_INI_SIZE || $file["error"] == UPLOAD_ERR_FORM_SIZE ) {
            unlink( $data["files"]["updatefile"]["tmp_name"] );
            return array( "errors" => array( "updatefile" => "File \"{$data["files"]["updatefile"]["name"]}\" was too large" ) );
          }
          else if( $file["error"] != UPLOAD_ERR_OK ) {
            unlink( $data["files"]["updatefile"]["tmp_name"] );
            return array( "errors" => array( "updatefile" => "There was an error uploading file \"{$data["files"]["updatefile"]["name"]}\"" ) );
          }
          
          // Reject if an unsafe file
          $ext = strtolower( SeeFileController::getFileExtension( $data["files"]["updatefile"]["name"] ) );
          $invalidformats = array( "ashx", "asmx", "asp", "aspx", "axd", "cer", "config", "htaccess", "jsp", "php", "rem", "rules", "shtm", "shtml", "soap", "stm", "xoml" );
          
          if( in_array( $ext, $invalidformats ) ) {
            unlink( $data["files"]["updatefile"]["tmp_name"] );
            return array( "errors" => array( "updatefile" => "Invalid file format for \"{$data["files"]["updatefile"]["name"]}\"" ) );
          }
          
          unlink( "../custom/files/download-{$d->id}.{$d->type}" );
          $d->name = str_ireplace( ".{$ext}", "", $data["files"]["updatefile"]["name"] );
          $d->description = $d->name;
          $d->status = (bool)$this->see->SeeCMS->config['defaultDocumentStatus'];
          $d->type = $ext;
          move_uploaded_file( $data["files"]["updatefile"]["tmp_name"], "../custom/files/download-{$d->id}.{$ext}" );
          
          $d->modified = date( "Y-m-d H:i:s" );
        }
        
        // Categories
        $d->sharedCategory = array();
        
        if( count( $data["categories"] ) ){
          foreach( $data["categories"] as $cID => $onoff ) {
            $c = SeeDB::load( "category", $cID );
            
            if( $c->id ) {
              $d->sharedCategory[$c->id] = $c;
            }
            
          }
        }
        
        // Websiteusergroup permissions
        if( !$data["websiteusergrouppermissions-all"] ) {
          $websiteusergroups = array_keys( (array)$data["websiteusergrouppermissions"] );        
        }
        
        SeeCMSWebsiteUserController::setPermission( $d->id, "download", $websiteusergroups );
        
        // Adminusergroup permissions
        if( $this->see->SeeCMS->config['advancedEditorPermissions'] ) {
          SeeCMSAdminAuthenticationController::setPermission( $d->id, "download", $data["adminusergrouppermissions"] );
        }
        
        // Save
        SeeDB::store( $d );
        $this->see->SeeCMS->hook->run( array( "hook" => "download-update", "data" => $d ) );
        
        if( !$settings["skipRedirect"] ) {
          $this->see->redirect( "?id={$d->id}" );
        }

      }
      
    }
    
  }
  
  public function savefolder() {
    
    // Clear datacache
    $this->clearDatacache();
  
    // Check permission
    $d = SeeDB::load( "download", $_POST["id"] );
    
    if( $d->id && $d->isfolder ) {
      
      // Advanced permissions
      if( $this->see->SeeCMS->config["advancedEditorPermissions"] ) {
        $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( "download", $d->id );
        
        if( $accessLevel < 5 ) {
          $this->see->redirect( "../../downloads/" );
        }
        
      }
      
      $data = array();
      parse_str( $_POST["form"], $data );
      
      $d->name = $data["title"];
      SeeDB::store( $d );
      
      // Websiteusergroup permissions
      if( !$data["websiteusergrouppermissions-all"] ) {
        $websiteusergroups = (array)$data["websiteusergrouppermissions"];        
      }
      
      SeeCMSWebsiteUserController::setPermission( $d->id, "download", $websiteusergroups );
      
      if( $data["websiteusergrouppermissions-cascade"] ) {
        SeeCMSWebsiteUserController::cascadePermission( $d->id, "download", $websiteusergroups );
      }
      
      // Adminusergroup permissions
      if( $this->see->SeeCMS->config['advancedEditorPermissions'] ) {
        SeeCMSAdminAuthenticationController::setPermission( $d->id, "download", $data["adminusergrouppermissions"] );
        
        if( $data["adminusergrouppermissions-cascade"] ) {
          SeeCMSAdminAuthenticationController::cascadePermission( $d->id, "download", $data["adminusergrouppermissions"] );
        }
        
      }
      
      return $this->folderTree();
    }
    
  }
  
  public function status( $id = 0 ) {
  
    if( !$id ) {
      $id = (int)$_POST['id'];
    }
  
    $d = SeeDB::load( "download", $id );
    
    if( $d->id ) {
      
      // Advanced permissions
      if( $this->see->SeeCMS->config["advancedEditorPermissions"] ) {
        $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( "download", $d->id );
        
        if( $accessLevel < 5 ) {
          $ret["done"] = 0;
          $ret["data"] = $this->loadByFolder( $d->parentid );
          return json_encode( $ret );
        }
        
      }
      
      $d->status = ( $d->status  ? 0 : 1 );
      SeeDB::store( $d );
      
      $ret["done"] = 1;
      $ret["data"] = $this->loadByFolder( $d->parentid );
    }
        
    return json_encode( $ret );
  }
  
  public function move( $id = 0, $at = '' ) {
    
    // Clear datacache
    $this->clearDatacache();
  
    if( !$id ) {
      $id = (int)$_POST['id'];
    }
  
    if( !$at ) {
      $at = $_POST['at'];
    }
  
    // Check if parent exists
    $dp = SeeDB::load( 'download', $at );
    
    if( ( $dp->id || $at === '0' ) && $dp->id != $id ) {
  
      $d = SeeDB::load( 'download', $id );
      
      if( $d->id ) {
      
        // Advanced permissions
        if( $this->see->SeeCMS->config["advancedEditorPermissions"] ) {
          $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( "download", $d->id );
          
          if( $accessLevel < 5 ) {
            return json_encode( $this->loadForCMS() );
          }
          
        }
      
        $d->parentid = $at;
        SeeDB::store( $d );
        
        return( json_encode( $this->loadForCMS() ) );
      }
    }
  }
  
  public function delete( $id = 0, $recursive = 0 ) {
    
    // Clear datacache
    $this->clearDatacache();
  
    if( !$id ) {
      $id = (int)$_POST['id'];
      $first = 1;
    }
  
    $d = SeeDB::load( 'download', $id );
    
    
    if( $d->id ) {
    
      // Advanced permissions
      if( $this->see->SeeCMS->config["advancedEditorPermissions"] ) {
        $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( "download", $d->id );
        
        if( $accessLevel < 5 ) {
          
          if( $first ) {
            return json_encode( $this->loadForCMS() );
          }
          
          return;
        }
        
      }
      
      if( $d->isfolder && $first ) {
        $_SESSION['SeeCMS'][$this->see->siteID]['downloads']['currentFolder'] = $d->parentid;
      }
    
      @unlink( "../custom/files/download-{$d->id}.{$d->type}" );

      $this->recursiveDelete( $d->id );
      
      $this->see->SeeCMS->hook->run( array( 'hook' => 'download-delete', "data" => $d ) );
      
      SeeDB::trash( $d );
      
      $_POST['id'] = '';
    }
    
    if( $first ) {
      return json_encode( $this->loadForCMS() );
    }
    
  }
  
  private function recursiveDelete( $parentID ) {

    $downloads = SeeDB::find( 'download', ' parentid = ? ', array( $parentID ) );
    foreach( $downloads as $d ) {
      $this->delete( $d->id, 1 );
    }
  }
  
  public function loadForCMS() {
    
    if( $this->see->SeeCMS->config['advancedEditorPermissions'] ) {
      $data['adminuserGroups'] = SeeDB::findAll( 'adminusergroup', ' ORDER BY name ' );      
    }

    $data["websiteuserGroups"] = SeeDB::findAll( 'websiteusergroup', ' ORDER BY name ' );
    $data["folderTree"] = $this->folderTree( 0, $data["websiteuserGroups"], null, $data["adminuserGroups"] );
    $data["downloads"] = $this->loadByFolder( $_SESSION["SeeCMS"][$this->see->siteID]["downloads"]["currentFolder"] );
    $data["createButtons"] = $this->loadCreateButtons( $_SESSION["SeeCMS"][$this->see->siteID]["downloads"]["currentFolder"] );
    $data["dropzone"] = $this->loadDropzone( $_SESSION["SeeCMS"][$this->see->siteID]["downloads"]["currentFolder"] );

    return $data;
  }
  
  public function folderTree( $parentID = 0, $websiteuserGroups = null, $websiteuserGroupPermissions = null, $adminuserGroups = null, $adminuserGroupPermissions = null ) {
    
    $parentID = (int)$parentID;
    
    if( !$parentID ) {
      $content = "<h3" . ( !$_SESSION['SeeCMS'][$this->see->siteID]['downloads']['currentFolder'] ? ' class="selected"' : '' ) . "><a href=\"#\" class=\"downloadfolder\" id=\"folder0\">Downloads</a></h3>";
    }

    // Get websiteuser groups and permissions if not yet set
    if( !$websiteuserGroups ) {
      $websiteuserGroups = SeeDB::findAll( 'websiteusergroup', ' ORDER BY name ' );
    }

    if( !isset( $websiteuserGroupPermissions ) ) {
      $websiteuserGroupPermissions = array();
      
      foreach( $websiteuserGroups as $ug ) {      
        
        foreach( $ug->withCondition( ' objecttype = ? ', array( 'download' ) )->ownWebsiteusergrouppermission as $wugp ) {
          $websiteuserGroupPermissions[$wugp->objectid] .= "&quot;{$ug->id}&quot;:&quot;1&quot;,";
        }
        
      }
      
      foreach( $websiteuserGroupPermissions as $objectID => $wugps ) {
        $websiteuserGroupPermissions[$objectID] = "{" . rtrim( $wugps, "," ) . "}";
      }
      
    }

    // Get adminuser groups and permissions if not yet set
    if( $this->see->SeeCMS->config['advancedEditorPermissions'] ) {

      if( !$adminuserGroups ) {
        $adminuserGroups = SeeDB::findAll( 'adminusergroup', ' ORDER BY name ' );      
      }
      
      if( !isset( $adminuserGroupPermissions ) ) {
        $adminuserGroupPermissions = array();

        foreach( $adminuserGroups as $ag ) {      
          
          foreach( $ag->withCondition( " objecttype = 'download' " )->ownAdminusergrouppermission as $augp ) {
            $adminuserGroupPermissions[$augp->objectid] .= "&quot;{$ag->id}&quot;:&quot;{$augp->accesslevel}&quot;,";

          }
          
        }
        
        foreach( $adminuserGroupPermissions as $objectID => $augps ) {
          $adminuserGroupPermissions[$objectID] = "{" . rtrim( $augps, "," ) . "}";
        }
        
      }
      
    }
        
    $folders = SeeDB::find( 'download', ' parentid = ? && deleted = ? && isfolder = ? ORDER BY name ASC ', array( $parentID, '0000-00-00 00:00:00', 1 ) );
      
    foreach( $folders as $f ) {
      
      if( $this->see->SeeCMS->config['advancedEditorPermissions'] ) {
        $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( 'download', $f->id );
        $accessClass2 = ( $accessLevel < 2 ? " accessdisabled" : "" );
        $accessClass5 = ( $accessLevel < 5 ? " accessdisabled" : "" );      
      }
      
      $ret = $this->folderTree( $f->id, $websiteuserGroups, $websiteuserGroupPermissions, $adminuserGroups, $adminuserGroupPermissions );
      $class = ( $ret ? 'child' : 'nochild' );
      $class .= ( $f->id == $_SESSION['SeeCMS'][$this->see->siteID]['downloads']['currentFolder'] ? ' selected' : '' );
      $content .= "<li class=\"{$class}\">
      <a href=\"#\" class=\"downloadfolder\" id=\"folder{$f->id}\">";
            
      if( $ret ) {
        
        if( strpos( $ret, " selected" ) === false ) {
          $content .= "\n<span class=\"toggle open\"><i class=\"fa fa-chevron-down\" aria-hidden=\"true\"></i></span>";
        }
        else {
          $content .= "\n<span class=\"toggle close\"><i class=\"fa fa-chevron-up\" aria-hidden=\"true\"></i></span>";
        }
        
      }
      
      $content .= "\n<span class=\"name{$accessClass2}\">{$f->name}</span>";
      
      // Websiteusergroup permissions
      if( $websiteuserGroupPermissions[$f->id] ) {
        $content .= "\n<span title=\"" . ( $accessLevel >= 5 ? "Secure" : "" ) . "\" class=\"secure{$accessClass5}\"><i class=\"fa fa-lock\" aria-hidden=\"true\"></i></span>";
      }    
      
      $content .= "
        <span title=\"" . ( $accessLevel >= 5 ? "Move" : "" ) . "\" class=\"move{$accessClass5}\"><i class=\"fa fa-arrows\" aria-hidden=\"true\"></i></span>
        <span title=\"" . ( $accessLevel >= 5 ? "Edit folder" : "" ) . "\" class=\"viewedit{$accessClass5}\" data-title=\"{$f->name}\" data-websiteusergrouppermissions=\"{$websiteuserGroupPermissions[$f->id]}\"" . ( $this->see->SeeCMS->config['advancedEditorPermissions'] ? " data-adminusergrouppermissions=\"{$adminuserGroupPermissions[$f->id]}\"" : "" ) . "><i class=\"fa fa-pencil-square\" aria-hidden=\"true\"></i></span>
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
      
      $o .= "</li>";
      
    }
    
    return $content;
  }
  
  public function downloadTreeSimple( $parentID = 0, $recurse = 0 ) {
  
    $parentID = (int)$parentID;
    
    // Check for cache (only for full tree)
    if( !$parentID ) {
      
      $cache = SeeDB::findOne( 'datacache', ' name = ? && context = ? ', array( 'SeeCMSDownload', 'downloadTreeSimple-0' ) );
      if( $cache ) {
        $content = base64_decode( $cache->data, true );
      } else {
        $createCache = true;
      }
    }
    
    if( !$content ) {
    
      $downloads = SeeDB::find( 'download', ' parentid = ? && deleted = ? ORDER BY isfolder DESC, name ASC ', array( $parentID, '0000-00-00 00:00:00' ) );
      
      foreach( $downloads as $d ) {
        
        $ret = $this->downloadTreeSimple( $d->id, 1 );
        
        if( $d->isfolder ) {
          $content .= "<li class=\"folder\"><a href=\"#\">{$d->name}</a>";
        } else {
          $content .= "<li><a href=\"#\" id=\"download-{$d->id}\" class=\"file\"><img src=\"/seecms/images/icons/{$d->type}.png\" alt=\"\" />{$d->name}</a>";
        }
        
        if( $ret ) {
          $content .= "<ul>{$ret}</ul>";
        }
        
        $content .= "</li>";
      }
      
      if( !$recurse ) {
        $content = "<ul>{$content}</ul>";
      }
    }
    
    // Make cache
    if( !$parentID && $createCache ) {
      
      $cache = SeeDB::dispense( 'datacache' );
      $cache->name = 'SeeCMSDownload';
      $cache->context = 'downloadTreeSimple-0';
      $cache->data = base64_encode( $content );
      SeeDB::store( $cache );
    }
    
    return( $content );
  }
  
  public function downloadFolderArray( $parentID = 0, $d = array(), $level = 0, $etitle = '' ) {
    
    $parentID = (int)$parentID;
    
    $downloads = SeeDB::find( 'download', ' parentid = ? && deleted = ? && isfolder = ? ORDER BY  name ASC ', array( $parentID, '0000-00-00 00:00:00', 1 ) );
    foreach( $downloads as $download ) {
    
      $title = $etitle.(($level)?' > ':'').$download->name;
      $d[$download->id] = $title;
      $d = $this->downloadFolderArray( $download->id, $d, $level+1, $title );
    }
    
    return( $d );
  }
  
  public function loadByFolder( $parentID = 0 ) {
    
    $parentID = (int)$parentID;
    
    if( (int)$_POST['id'] ) {
      $d = SeeDB::load( 'download', (int)$_POST['id'] );
      if( $d->isfolder ) {
        $parentID = (int)$_POST['id'];
      }
    }
    
    $_SESSION['SeeCMS'][$this->see->siteID]['downloads']['currentFolder'] = $parentID;
    
    if( $this->checkLiveDownloads( $parentID ) ){
      $content .= "<div class=\"seecmsmessage seecmsnotice\"><p><strong>PLEASE NOTE</strong>: You have unpublished documents in this folder that can only be viewed if you are logged into the CMS editor. To make them viewable to the public please publish them.</p></div>";
    }
    
    $content .= "<ul>";
    $downloads = SeeDB::find( 'download', ' parentid = ? && deleted = ? && isfolder = ? ORDER BY name ', array( $parentID, '0000-00-00 00:00:00', 0 ) );
    
    foreach( $downloads as $d ) {
      $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( "download", $d->id );
      $accessClass2 = ( $accessLevel < 2 ? " accessdisabled" : "" );
      $accessClass5 = ( $accessLevel < 5 ? " accessdisabled" : "" );
      
      $content .= "<li>
      <div class=\"page\">
      <a class=\"name{$accessClass2}\" href=\"" . ( $accessLevel < 2 ? "#" : "../download/edit/?id={$d->id}" ) ."\">{$d->name}</a>
      <a class=\"date{$accessClass2}\" href=\"#\">".$this->see->format->date( $d->uploaded, "d.m.Y" )."</a>
      <a class=\"icon {$d->type}{$accessClass2}\" title=\".{$d->type}\" href=\"#\"></a>";
      $content .= ( $d->status  ? "<a class=\"published toggledownloadstatus{$accessClass5}\" target=\"Suppress\" id=\"status{$d->id}\" href=\"#\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i></a>" : "<a class=\"notpublished toggledownloadstatus{$accessClass5}\" title=\"Publish\" id=\"status{$d->id}\" href=\"#\"><i class=\"fa fa-minus\" aria-hidden=\"true\"></i></a>" );
      $content .= "<a class=\"move{$accessClass5}\" title=\"Move\" id=\"move{$d->id}\" href=\"#\"><i class=\"fa fa-arrows\" aria-hidden=\"true\"></i></a>";

      $p = SeeDB::count( 'websiteusergrouppermission', ' objectid = ? && objectType = ? ', array( $d->id, 'download' ) );
     
      if( $p ) {
        $content .= "<a class=\"secure{$accessClass5}\" title=\"Secure\" id=\"secure{$d->id}\" href=\"#\"><i class=\"fa fa-lock\" aria-hidden=\"true\"></i></a>";
      }
      
      $content .= "<a class=\"delete{$accessClass5}\" title=\"Delete\" id=\"deletedoc-{$d->id}\" href=\"#\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i></a></div></li>";
      
    }
    
    $content .= "</ul>";
    
    $content = ( $content != "<ul></ul>" ? $content : '<p><strong>There are no downloads in this folder.</strong></p>' );
    
    return( $content );
  }
  
  public function loadCreateButtons( $parentID = 0 ) {
    
    $parentID = (int)$parentID;
    
    if( $_POST['id'] ) {
      $d = SeeDB::load( 'download', $_POST['id'] );
      
      if( $d->isfolder ) {
        $parentID = $d->id;
      }
      
    }
    
    // Advanced permissions
    $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( 'download', $parentID );

    if( $accessLevel >= 5 ) {
      $content = "<a class=\"createdownloadfolder\" href=\"#\">Create folder <span><i class=\"fa fa-plus-circle\" aria-hidden=\"true\"></i></span></a>";
    }
    
    return (string)$content;
  }
  
  public function loadDropzone( $parentID = 0 ) {

    $parentID = (int)$parentID;
    
    if( $_POST['id'] ) {
      $d = SeeDB::load( 'download', $_POST['id'] );
      
      if( $d->isfolder ) {
        $parentID = $d->id;
      }
      
    }
    
    // Advanced permissions
    $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( 'download', $parentID );

    if( $accessLevel >= 5 ) {
      $content = "<div id=\"downloadsdropzone\" class=\"dropzone\">
        <div class=\"fallback\">
          <form action=\"../download/add\" encType=\"multipart/form-data\" method=\"post\">
            <div class=\"dz-fallback\">
              <p>Your browser is too old to support drag and drop uploads, please consider updating to a newer version.</p>
              <input name=\"file\" type=\"file\" undefined=\"\" />
              <input id=\"fallbackparentid\" name=\"parentid\" type=\"hidden\" value=\"{$_SESSION['SeeCMS'][$this->see->siteID]['downloads']['currentFolder']}\" />
              <input name=\"doFallback\" type=\"hidden\" value=\"1\" />
              <input type=\"submit\" value=\"Upload\" />
            </div>
          </form>
        </div>
      </div>";
    }
    
    return (string)$content;
  }
  
  public function adminSearch( $keyword ) {
  
    $downloads = SeeDB::find( 'download', ' isfolder = ? && ( name LIKE ? || description LIKE ? ) ORDER BY name LIMIT 10 ', array( 0, "%{$keyword}%", "%{$keyword}%" ) );
    foreach( $downloads as $d ) {
      
      $dp = SeeDB::load( 'download', $d->parentid );
      $r[] = array( 'id' => $d->id, 'name' => $d->name, 'type' => $d->type, 'in' => (( $dp->name ) ? $dp->name : 'Root' ) );
    }
    
    return( $r );
  }
  
  public function download() {
    
    $f = $this->load();
    
    if( !$f->status && ( !$f->id || !$_SESSION['seecms'][$this->see->siteID]['adminuser']['id'] || !$_GET['preview'] ) ) {
      
      if( $f->id && $_SESSION['seecms'][$this->see->siteID]['adminuser']['id'] && !$_GET['preview'] ) {
        SeeRouteController::http404( array( "message" => "404 - This file is currently disabled. As you're logged in you can <a href=\"./?id={$f->id}&amp;preview=1\">view the file anyway</a>, but other website visitors won't be able to access it.<br /><br /><a href=\"{$_SERVER['HTTP_REFERER']}\">Back</a>" ) );
      }
      else {
        SeeRouteController::http404( array( "message" => "404 - The file does not exist or is disabled." ) );
      }
    }
      
    // Check website user permission
    $access = true;
    if( !$_SESSION['seecms'][$this->see->siteID]['adminuser']['id'] ) {
      $wugp = SeeDB::find( 'websiteusergrouppermission', ' objecttype = ? && objectid = ? ', array( 'download', $_GET['id'] ) );
      if( count( $wugp ) ) {
        $access = false;
        if( (int)$_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) {
          foreach( $wugp as $w ) {
            if( $w->websiteusergroup->sharedWebsiteuser[$_SESSION['seecms'][$this->see->siteID]['websiteuser']['id']]->id == $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) {
              $access = true;
            }
          }
        }
      }
    }
  
    if( !$access ) {
      
      if( !$access && isset( $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) && isset( $this->see->SeeCMS->config["restrictedWebsiteUserPage"] ) ){
        // echo '<pre>'; var_dump($data); echo '</pre>';
        // die;
        
        $restrictedSettings = $this->see->SeeCMS->config["restrictedWebsiteUserPage"];
        if( $restrictedSettings["objecttype"] != "file" ){
          
          if( $route = SeeDB::findOne( "route", "objectid = ? && objecttype = ? && primaryroute = 1", array( $restrictedSettings["objectid"], $restrictedSettings["objecttype"] ) ) ){
            
            if( $route->route[0] == "/" ){
              $redirect = substr_replace( $route->route, '/'.$this->see->rootURL, 0, 1 );
            }
            else {
              $redirect = "/".$this->see->rootURL.$route->route;
            }
            
            $this->see->redirect( $redirect );
            
          }
          
        }
        else if( $restrictedSettings["objecttype"] == "file" ){
          echo file_get_contents( $restrictedSettings["filepath"] );
          die;
        }
        
      }
      
      if( $this->see->SeeCMS->config['websiteUserLoginPage'] ) {
        if( $this->see->SeeCMS->config['websiteUserLoginPage'][0] == '/' ) {
          $redirect = substr_replace( $this->see->SeeCMS->config['websiteUserLoginPage'], '/'.$this->see->rootURL, 0, 1 );
        }
        $_SESSION['restrictedRouteRequest'] = '/'.SeeRouteController::getCurrentRoute()."?id=".$_GET['id'];
        $this->see->redirect( $redirect );
      }
      die( 'Restricted' );
    }
      
    if( $_GET['id'] ) {
      SeeCMSAnalyticsController::logVisit( 'download', $f->id, $this->see->siteID );
      SeeFileController::passthrough( array( 'name' => "{$f->name}.{$f->type}", 'path' => "../custom/files/download-{$f->id}.{$f->type}" ), (($this->see->SeeCMS->config['inlineFiles'])?true:false) );
    }
  }  
  
  public function checkLiveDownloads($parentID){
    $downloads = SeeDB::find( 'download',' parentid = ? && deleted = ? && status = ? ', array( $parentID, '0000-00-00 00:00:00', 0 ) );
    return( count($downloads) );
  }  
  
  public function clearDatacache() {
  
    $r = SeeDB::exec( " DELETE FROM datacache WHERE name = ? ", array( 'SeeCMSDownload' ) );
  }
  
}