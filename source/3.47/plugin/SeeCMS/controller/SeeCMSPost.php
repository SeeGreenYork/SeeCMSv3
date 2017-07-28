<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */
 
class SeeCMSPostController {

  public $see;
  
  public function __construct( $see ) {
  
    $this->see = $see;
  }
  
  public function load() {
  
    if( $_GET['duplicate'] ) {
      
      $p = SeeDB::load( 'post', $_GET['duplicate'] );
      $this->duplicate( $p ); /* Redirects to edit new post */
    }

    $p = SeeDB::load( 'post', $_GET['id'] );
    
    return( $p );
  }
  
  public function loadForEdit() {
  
    $data['post'] = $this->load();
    $data['postRoutes'] = SeeDB::find( 'route', ' objecttype = ? && objectid = ? ORDER BY primaryroute DESC ', array( 'post', $data['post']->id ) );
    $data['linkSelector'] = $this->see->SeeCMS->content->loadForLinkSelector( true, true );
    $data['templates'] = json_decode( SeeCMSSettingController::load( 'pagetemplates' ) );
    $data['multisite'] = SeeCMSSettingController::load( 'multisite' );
    
    // Backwards compatibility
    if( !is_array( $data['templates'] ) ) {
      $data['templates'] = unserialize( SeeCMSSettingController::load( 'pagetemplates' ) );
    }

    // Notices
    if( $_GET['routeerror'] ) {
      $data['messages'] = "<div class=\"seecmsmessage seecmserror\"><p><i class=\"fa fa-exclamation-triangle\" aria-hidden=\"true\"></i>&nbsp;&nbsp;The URLs could not be updated because one or more of them already exist on another page/post</p></div>";
    }
    else if( $_GET['duplicated'] ) {
      $data['messages'] = "<div class=\"seecmsmessage seecmssuccess\"><p><i class=\"fa fa-check\" aria-hidden=\"true\"></i>&nbsp;&nbsp;Copy created successfully. You are now viewing the new copy of the post</p></div>";
    } 
    
    // Advanced permissions
    if( $this->see->SeeCMS->config["advancedEditorPermissions"] ) {
      $data['accessLevel'] = $this->see->SeeCMS->adminauth->checkContextAccess( 'post', $data['post']->id );
      
      if( !$data['accessLevel'] ) {
        $this->see->redirect( "../../posts/" );
      }
      
      if( $data['accessLevel'] < 5 ) {
        $data['messages'] .= "<div class=\"seecmsmessage seecmsnotice\"><p><i class=\"fa fa-exclamation-triangle\" aria-hidden=\"true\"></i>&nbsp;&nbsp;Please note: You only have limited access to this post, you're unable to make changes to settings.</p></div>";
      }
      
      if( $_SESSION['seecms'][$this->see->siteID]['adminuser']['access']['current'] >= 5 ) {
        $data["adminuserGroups"] = SeeDB::findAll( "adminusergroup", " ORDER BY name " );
        $data["adminuserGroupPermissions"] = SeeCMSAdminAuthenticationController::getPermission( $data["post"]->id, "post", $data["adminuserGroups"] );  
      }
      
      $pendingApproval = SeeDB::findOne( 'adminapproval', " complete = 0 && objecttype = 'post' && objectid = ? ", array( $data['post']->id) );
      
      // Check for content awaiting approval
      if( $pendingApproval ) {
        $globalApprovalPermission = $this->see->SeeCMS->adminauth->checkAccess( "action-content-completeApproval", null, false );
        $this->see->SeeCMS->adminauth->checkAccess( "post/edit/", null, false ); // Reset current

        if( $globalApprovalPermission >= 5 || $data["accessLevel"] >= 5 ) {
          $data['messages'] .= '<div class="seecmsmessage seecmsnotice"><p><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;&nbsp;Please note: This post has content awaiting approval</p></div>';
        }
        else {
          $data["pendingApproval"] = true;
          $data['messages'] .= '<div class="seecmsmessage seecmssuccess"><p>Approval of this post has been requested</p></div>';
        }
        
      }
      // Check for unapplied content
      else if( $data["accessLevel"] < 5 ) {
        $content = SeeDB::findOne( "content", " status = 0 && objecttype = 'post' && objectid = ? ", array( $data['post']->id ) );
        
        if( $content ) {
          $as = SeeDB::find( 'adminuser', ' adminuserrole_id = ? ', array( $this->see->SeeCMS->config['advancedEditorPermissionsAdminRole'] ) ); // TODO also full access admins
          $data['unappliedContent'] = true;
          
          foreach( $as as $au ) {
            $data['contentApprovers'][$au->id] = $au->name;
          }
          
        }
        
      }
      
      
      
    }
    
    if( $data['post']->redirect ) {
      $data['redirectDetails'] = $this->see->SeeCMS->content->loadLinkDetails( $data['post']->redirect );
    }
    
    return $data;
  }
  
  public function create() {
  
    // Check if parent exists
    $pp = SeeDB::load( 'post', $_POST['parentid'] );

    // Advanced permissions
    $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( 'post', $pp->id );
        
    if( $this->see->SeeCMS->config["advancedEditorPermissions"] && $accessLevel < 5 ) {
      $ret["done"] = 0;
    }
    else if( ( $pp->id && $pp->isfolder ) || $_POST['parentid'] === '0' ) {
  
      $p = SeeDB::dispense( 'post' );
      
      $p->title = $_POST['title'];
      $p->parentid = $_POST['parentid'];
      $p->posttype_id = (int)$_POST['posttype'];
      $p->isfolder = (int)$_POST['isfolder'];
      $p->postorder = 0;
      $p->tags = '';
      $p->date = date("Y-m-d");
      
      $templates = json_decode( SeeCMSSettingController::load( 'pagetemplates' ) );
    
      // Backwards compatibility
      if( !is_array( $templates ) ) {
        $templates = unserialize( SeeCMSSettingController::load( 'pagetemplates' ) );
      }
      
      $p->template = $templates[0];
      
      SeeDB::store( $p );
      
      if( !$p->isfolder ) {
        
        if( $p->posttype->page->id ) {
          $route = SeeDB::findOne( 'route', ' objecttype = ? && objectid = ? ORDER BY primaryroute DESC ', array( 'page', $p->posttype->page->id ) );
          $baseURL = $route->route;
        }
        else {
          $baseURL = SeeCMSController::getSetting( 'postsURL' );
        }
        
        SeeCMSController::makeRoute( $p->title, $p->id, 'Post', $baseURL );
      }
      
      // Adminusergroup permissions
      if( $this->see->SeeCMS->config['advancedEditorPermissions'] ) {
        $adminusergrouppermissions = SeeCMSAdminAuthenticationController::getPermission( $p->parentid, "post" );
        SeeCMSAdminAuthenticationController::setPermission( $p->id, "post", $adminusergrouppermissions );
      }
      
      $this->see->SeeCMS->hook->run( array( "hook" => "post-create", "data" => $p ) );

      $ret["done"] = 1;
      $ret["data"] = $this->loadByFolder( $p->parentid );
      $ret['id']   = $p->id;
    }
    else {
      $ret["done"] = 0;
    }
    
    return( json_encode( $ret ) );
  }
  
  public function update( $data, $errors, $settings ) {
  
    if( !$errors ) {
      $p = SeeDB::load( 'post', $data['id'] );
      
      if( $p->id ) {
    
        // Advanced permissions
        if( $this->see->SeeCMS->config["advancedEditorPermissions"] ) {
          $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( 'post', $p->id );
          
          if( $accessLevel < 5 ) {
            $this->see->redirect( "../../posts/" );
          }
          
        }
    
        if( $data['route0'] ) {
        
          foreach( $data as $dk => $dv ) {
          
            if( substr( $dk, 0, 5 ) == 'route' ) {
            
              $routeID = str_replace( 'route', '', $dk );
              $theRoute = $this->see->prepareRoute( $dv );
              
              // Check if the route exists somewhere else
              $r = SeeDB::findOne( 'route', ' route = ? && ( objectid != ? || objecttype != ? ) ', array( $theRoute, $p->id, 'post' ) );
              if( $r ) {
                $routesOK = false;
                break;
              }
              else {

                if( !$data['deleteroute'.$routeID] ) {
                  $routesOK = true;
                  
                  $addRoute[] = array( $theRoute, (( $data['primaryroute'.$routeID] && !$primaryset ) ? 1 : 0 ) );
                  
                  if( $data['primaryroute'.$routeID] ) {
                    $primarySet = 1;
                  }
                  
                }
              }
            }
          }
        
          // Adminusergroup permissions
          SeeCMSAdminAuthenticationController::setPermission( $p->id, "post", $data["adminusergrouppermissions"] );
        
          if( $routesOK ) {
          
            // If there's no primary route set use the first one
            if( !$primarySet ) {
              $addRoute[0][1] = 1;
            }
          
            SeeDB::exec( " DELETE FROM route WHERE objectid = {$p->id} && objecttype = 'post' " );
            
            foreach( $addRoute as $r ) {
              SeeCMSController::createRoute( $r[0], $p->id, 'post', $r[1] );
            }
          }
        }
        
        // Categories
        $p->sharedCategory = array();
        foreach( $data["categories"] as $cID => $onoff ) {
          $c = SeeDB::load( "category", $cID );
          
          if( $c->id ) {
            $p->sharedCategory[$c->id] = $c;
          }
          
        }
      
        $p->title = $data['title'];
        $p->standfirst = $data['standfirst'];
        $p->tags = $data['tags'];
        $p->date = $this->see->format->date( (($data['postdate'] && $data["post"] != "0000-00-00")?$data['postdate']:time()), "Y-m-d" );
        
        $p->htmltitle = $data['htmltitle'];
        $p->media_id = (int)$data['media_id'];
        $p->template = $data['template'];
        
        $p->redirect = $data['redirect'];
        
        $p->metadescription = $data['metadescription'];
        $p->metakeywords = $data['metakeywords'];
        
        $commencementtime = (( $data['commencementtime'] ) ? $data['commencementtime'].":00" : '00:00:00');
        $commencement = strtotime( $data['commencement']." ".$commencementtime );
        $p->commencement = (( $commencement && $data['commencement'] ) ? date( "Y-m-d H:i:s", $commencement ) : '0000-00-00 00:00:00' );
        
        $expirytime = (( $data['expirytime'] ) ? $data['expirytime'].":00" : '00:00:00');
        $expiry = strtotime( $data['expiry']." ".$expirytime );
        $p->expiry = (( $expiry && $data['expiry'] ) ? date( "Y-m-d H:i:s", $expiry ) : '0000-00-00 00:00:00' );
        
        if( !$data['eventstartdate'] ) {
          $p->eventstart = '0000-00-00 00:00:00';
        }
        else {
          $p->eventstart = $this->see->format->date( $data['eventstartdate']." {$data['starttimehour']}:{$data['starttimeminute']}:00" , "Y-m-d H:i:s" );
        }
        
        if( !$data['eventenddate'] ) {
          $p->eventend = '0000-00-00 00:00:00';
        }
        else {
          $p->eventend = $this->see->format->date( $data['eventenddate']." {$data['endtimehour']}:{$data['endtimeminute']}:00" , "Y-m-d H:i:s" );
        }
        
        $p->lastupdated = date( "Y-m-d H:i:s" );
        
        SeeDB::store( $p );
        $this->see->SeeCMS->hook->run( array( "hook" => "post-update", "data" => $p ) );

        // Custom post type data
        if( isset( $this->see->SeeCMS->customPostController[$p->posttype->name]['plugin'] ) ) {
          $customPostController = $this->see->{$this->see->SeeCMS->customPostController[$p->posttype->name]['plugin']};
          $customPostController->saveFields( $p, $data );
        }

        if( !$settings["skipRedirect"] ) {
          $this->see->redirect( "?id={$p->id}" . ( $routesOK === false ? '&routeerror=1' : '' ) );
        }
        
      }
    }
  }
  
  public function savefolder() {

    // Check permission
    $p = SeeDB::load( "post", $_POST["id"] );
    
    if( $p->id && $p->isfolder ) {
      
      // Advanced permissions
      if( $this->see->SeeCMS->config["advancedEditorPermissions"] ) {
        $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( "post", $p->id );
        
        if( $accessLevel < 5 ) {
          $this->see->redirect( "../../posts/" );
        }
        
      }
      
      $data = array();
      parse_str( $_POST["form"], $data );

      $p->title = trim( $data["title"] );
      SeeDB::store( $p );
      
      // Adminusergroup permissions
      SeeCMSAdminAuthenticationController::setPermission( $p->id, "post", $data["adminusergrouppermissions"] );
      
      if( $data["adminusergrouppermissions-cascade"] ) {
        SeeCMSAdminAuthenticationController::cascadePermission( $p->id, "post", $data["adminusergrouppermissions"] );
      }
      
      
      return $this->folderTree();
    }
    
  }
  
  public function status( $id = 0 ) {
  
    if( !$id ) {
      $id = (int)$_POST['id'];
    }
  
    $p = SeeDB::load( 'post', $id );
    
    if( $p->id ) {
      
      // Advanced permissions
      if( $this->see->SeeCMS->config["advancedEditorPermissions"] ) {
        $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( "post", $p->id );
        
        if( $accessLevel < 5 ) {
          $ret["done"] = 0;
          $ret["data"] = $this->loadByFolder( $p->parentid );
          return json_encode( $ret );
        }
        
      }
      
      $p->status = ( $p->status ? 0 : 1 );
      SeeDB::store( $p );
      
      $ret["done"] = 1;
      $ret["data"] = $this->loadByFolder( $p->parentid );
    }
        
    return json_encode( $ret );
  }
  
  public function move( $id = 0, $at = '' ) {
  
    if( !$id ) {
      $id = (int)$_POST['id'];
    }
  
    if( !$at ) {
      $at = $_POST['at'];
    }
  
    // Check if parent exists
    $pp = SeeDB::load( 'post', $at );
    
    if( ( $pp->id || $at === '0' ) && $pp->id != $id ) {
      $p = SeeDB::load( 'post', $id );
      
      if( $p->id ) {
        
        // Advanced permissions
        if( $this->see->SeeCMS->config["advancedEditorPermissions"] ) {
          $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( "post", $p->id );
          
          if( $accessLevel < 5 ) {
            return json_encode( $this->loadForCMS() );
          }
          
        }
      
        $p->parentid = $at;
        SeeDB::store( $p );       
        
        return json_encode( $this->loadForCMS() );
      }
      
    }
    
  }
  
  public function delete( $id = 0, $recursive = 0 ) {
  
    if( !$id ) {
      $id = (int)$_POST['id'];
      $first = 1;
    }

    $p = SeeDB::load( 'post', $id );
    
    if( $p->id ) {
    
      // Advanced permissions
      if( $this->see->SeeCMS->config["advancedEditorPermissions"] ) {
        $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( "post", $p->id );
        
        if( $accessLevel < 5 ) {
          
          if( $first ) {
            return json_encode( $this->loadForCMS() );
          }
          
          return;
        }
        
      }
    
      if( $p->isfolder && $first ) {
        $_SESSION['SeeCMS'][$this->see->siteID]['post']['currentFolder'] = $m->parentid;
      }
      
      SeeDB::exec( " DELETE FROM route WHERE objectid = {$p->id} && objecttype = 'post' " );
      SeeDB::exec( " UPDATE adfcontent SET objecttype = 'postdeleted' WHERE objectid = {$p->id} && objecttype = 'post' " );
    
      $p->deleted = date("Y-m-d H:i:s");
      SeeDB::store( $p );
      
      $this->recursiveDelete( $p->id );
      
      $_POST['id'] = '';
    }
    
    if( $first ) {
      return( json_encode( $this->loadForCMS() ) );
    }
  }
  
  private function recursiveDelete( $parentID ) {

    $posts = SeeDB::find( 'post', ' parentid = ? && deleted = ? ', array( $parentID, '0000-00-00 00:00:00' ) );
    foreach( $posts as $p ) {
      $this->delete( $p->id, 1 );
    }
  }
  
  public function loadForCMS() {
    
    if( $this->see->SeeCMS->config['advancedEditorPermissions'] ) {
      $data['adminuserGroups'] = SeeDB::findAll( 'adminusergroup', ' ORDER BY name ' );      
    }
    
    $data['folderTree'] = $this->folderTree( 0, $data['adminuserGroups'] );
    $data["posts"] = $this->loadByFolder( $_SESSION['SeeCMS'][$this->see->siteID]['post']['currentFolder'] );
    $data['createButtons'] = $this->loadCreateButtons( $_SESSION['SeeCMS'][$this->see->siteID]['post']['currentFolder'] );
    $data['posttypes'] = SeeDB::findAll( 'posttype', ' ORDER BY name ' );
   
    if( count( $data['posttypes'] ) <= 1 ) {
      $data['posttypes'] = '';
    }
            
    return $data;
  }

  public function folderTree( $parentID = 0, $adminuserGroups = null, $adminuserGroupPermissions = null ) {
    
    $parentID = (int)$parentID;    
    
    if( !$parentID ) {
      $content = "<h3" . ( !$_SESSION['SeeCMS'][$this->see->siteID]['post']['currentFolder'] ? ' class="selected"' : '' ) . "><a href=\"#\" class=\"postfolder\" id=\"folder0\">Posts</a></h3>";
    }
    
    // Get adminuser groups and permissions if not yet set
    if( $this->see->SeeCMS->config['advancedEditorPermissions'] ) {
 
      if( !$adminuserGroups ) {
        $adminuserGroups = SeeDB::findAll( 'adminusergroup', ' ORDER BY name ' );      
      }
      
      if( !isset( $adminuserGroupPermissions ) ) {
        $adminuserGroupPermissions = array();
 
        foreach( $adminuserGroups as $ag ) {      
          
          foreach( $ag->withCondition( " objecttype = 'post' " )->ownAdminusergrouppermission as $augp ) {
            $adminuserGroupPermissions[$augp->objectid] .= "&quot;{$ag->id}&quot;:&quot;{$augp->accesslevel}&quot;,";
          }
          
        }
        
        foreach( $adminuserGroupPermissions as $objectID => $augps ) {
          $adminuserGroupPermissions[$objectID] = "{" . rtrim( $augps, "," ) . "}";
        }
        
      }
      
    }
    
    $folders = SeeDB::find( 'post', ' parentid = ? && deleted = ? && isfolder = ? ORDER BY title ASC ', array( $parentID, '0000-00-00 00:00:00', 1 ) );
    $globalApprovalPermission = $this->see->SeeCMS->adminauth->checkAccess( "action-content-completeApproval", null, false );
    $this->see->SeeCMS->adminauth->checkAccess( "post/edit/", null, false ); // Reset current

    foreach( $folders as $f ) {
      
      if( $this->see->SeeCMS->config['advancedEditorPermissions'] ) {
        $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( 'post', $f->id );
        $accessClass2 = ( $accessLevel < 2 ? " accessdisabled" : "" );
        $accessClass5 = ( $accessLevel < 5 ? " accessdisabled" : "" );      
      }
      
      $ret = $this->folderTree( $f->id, $adminuserGroups, $adminuserGroupPermissions );
      $class = ( $ret  ? 'child' : 'nochild' );
      $class .= ( $f->id == $_SESSION['SeeCMS'][$this->see->siteID]['post']['currentFolder'] ? ' selected' : '' );
      $content .= "<li class=\"{$class}\">
      <a href=\"#\" class=\"postfolder\" id=\"folder{$f->id}\">";
                    
      if( $ret ) {
        
        if( strpos( $ret, " selected" ) === false ) {
          $content .= "\n<span class=\"toggle open\"><i class=\"fa fa-chevron-down\" aria-hidden=\"true\"></i></span>";
        }
        else {
          $content .= "\n<span class=\"toggle close\"><i class=\"fa fa-chevron-up\" aria-hidden=\"true\"></i></span>";
        }
        
      }
           
      $content .= "<span class=\"name{$accessClass2}\">{$f->title}</span>";
      
      if( $globalApprovalPermission >= 5 || $accessLevel >= 5 ) { 
           
        $approval = SeeDB::getCell( "SELECT COUNT(*) FROM `adminapproval` INNER JOIN `post` ON adminapproval.objecttype = 'post' && adminapproval.objectid = post.id WHERE adminapproval.complete = 0 && post.parentid = ? ", array( $f->id ) );
        
        if( $approval ) {
          $content .= "<span title=\"Content pending approval\" class=\"approval\"><i class=\"fa fa-warning\" aria-hidden=\"true\"></i></span>";
        }
          
      }  
           
      $content .= "<span title=\"" . ( $accessLevel >= 5 ? "Edit folder" : "" ) . "\" class=\"viewedit{$accessClass5}\" data-title=\"{$f->title}\"" . ( $this->see->SeeCMS->config['advancedEditorPermissions'] ? " data-adminusergrouppermissions=\"{$adminuserGroupPermissions[$f->id]}\"" : "" ) . "><i class=\"fa fa-pencil-square\" aria-hidden=\"true\"></i></span>
        <span title=\"" . ( $accessLevel >= 5 ? "Move" : "" ) . "\" class=\"move{$accessClass5}\"><i class=\"fa fa-arrows\" aria-hidden=\"true\"></i></span>
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
    
    return $content ;
  }
  
  public function postTreeSimple( $parentID = 0, $recurse = 0 ) {
  
    $parentID = (int)$parentID;
    
    $posts = SeeDB::find( 'post', ' parentid = ? && deleted = ? ORDER BY isfolder DESC, title ASC ', array( $parentID, '0000-00-00 00:00:00' ) );
    foreach( $posts as $p ) {
      
      $ret = $this->postTreeSimple( $p->id, 1 );
      
      if( $p->isfolder ) {
        $content .= "<li class=\"folder\"><a href=\"#\">{$p->title}</a>";
      } else {
        $content .= "<li><a href=\"#\" id=\"post-{$p->id}\" class=\"file\">{$p->title} <em>".$this->see->format->date( $p->postdate, 'd M Y' )."</em></a>";
      }
      
      if( $ret ) {
        $content .= "<ul>{$ret}</ul>";
      }
      
      $content .= "</li>";
    }
    
    if( !$recurse ) {
      $content = "<ul>{$content}</ul>";
    }
    
    return( $content );
  }
  
  public function loadByFolder( $parentID = 0 ) {
  
    $parentID = (int)$parentID;
    
    if( $_POST['id'] ) {
      $p = SeeDB::load( 'post', (int)$_POST['id'] );
      if( $p->isfolder ) {
        $parentID = (int)$_POST['id'];
      }
    }
    
    $_SESSION['SeeCMS'][$this->see->siteID]['post']['currentFolder'] = $parentID;
    
    $content = "<ul>";
    
    $order = ( isset( $this->see->SeeCMS->config['defaultPostOrder'] ) ? $this->see->SeeCMS->config['defaultPostOrder'] : 'eventstart ASC, date DESC' );
    $posts = SeeDB::find( 'post', " parentid = ? && deleted = ? && isfolder = ? ORDER BY {$order} ", array( $parentID, '0000-00-00 00:00:00', 0 ) );
    $globalApprovalPermission = $this->see->SeeCMS->adminauth->checkAccess( "action-content-completeApproval", null, false );
    $this->see->SeeCMS->adminauth->checkAccess( "post/edit/", null, false ); // Reset current

    foreach( $posts as $p ) {
      $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( 'post', $p->id );
      $accessClass2 = ( $accessLevel < 2 ? " accessdisabled" : "" );
      $accessClass5 = ( $accessLevel < 5 ? " accessdisabled" : "" );
      // $route = SeeDB::findOne( 'route', ' objecttype = ? && objectid = ? && primaryroute = ? ', array( 'Post', $p->id, 1 ) );

      $date = $this->see->format->date( (($p->eventstart!='0000-00-00 00:00:00')?$p->eventstart:$p->date), "d.m.Y" );
      
      if( $accessLevel >= 5  ) {
        // $route = SeeDB::findOne( 'route', ' objecttype = ? && objectid = ? && primaryroute = ? ', array( 'Post', $p->id, 1 ) );
        $content .= "<li id=\"p{$p->id}\" class=\"{$class}\">
        <div class=\"page\">
        <a class=\"name\" href=\"../post/edit/?id={$p->id}\">{$p->title}</a>
        <a class=\"date\" href=\"#\">{$date}</a>
        <a class=\"move\" title=\"Move post\" id=\"move{$p->id}\" href=\"#\"><i class=\"fa fa-arrows\" aria-hidden=\"true\"></i></a>
        <a class=\"delete deletepost\" id=\"deletepost-{$p->id}\" title=\"Delete\" href=\"#\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i></a>";
        
        $content .= ( $p->status ? "<a class=\"published togglepoststatus\" title=\"Suppress\" id=\"status{$p->id}\" href=\"#\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i></a>" : "<a class=\"notpublished togglepoststatus\" title=\"Publish\" id=\"status{$p->id}\" href=\"#\"><i class=\"fa fa-minus\" aria-hidden=\"true\"></i></a>" );

        $content .= ( $p->commencement != '0000-00-00 00:00:00' || $p->expiry != '0000-00-00 00:00:00' ? "<a class=\"clock\" title=\"" . ( $p->commencement != '0000-00-00 00:00:00' ? "Commencement: {$this->see->format->date( $p->commencement, "d M Y H:i" )}\n" : '' ) . ( $p->expiry != '0000-00-00 00:00:00' ? "Expiry: {$this->see->format->date($p->expiry, "d M Y H:i")}\n" : '' ) . "\"><i class=\"fa fa-clock-o\" aria-hidden=\"true\"></i></a>" : "" );
          
      }
      else if( $accessLevel >= 2 ) {
        // $route = SeeDB::findOne( 'route', ' objecttype = ? && objectid = ? && primaryroute = ? ', array( 'Post', $p->id, 1 ) );
        $content .= "<li id=\"p{$p->id}\" class=\"{$class}\">
        <div class=\"page\">
        <a class=\"name\" href=\"../post/edit/?id={$p->id}\">{$p->title}</a>
        <a class=\"date\" href=\"#\">{$date}</a>
        <a class=\"move accessdisabled\" href=\"#\"><i class=\"fa fa-arrows\" aria-hidden=\"true\"></i></a>
        <a class=\"delete deletepost accessdisabled\" href=\"#\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i></a>";
        
        $content .= ( $p->status ? "<a class=\"published togglepoststatus accessdisabled\" title=\"Published\" href=\"#\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i></a>" : "<a class=\"notpublished togglepoststatus accessdisabled\" title=\"Suppressed\" href=\"#\"><i class=\"fa fa-minus\" aria-hidden=\"true\"></i></a>" );

        $content .= ( $p->commencement != '0000-00-00 00:00:00' || $p->expiry != '0000-00-00 00:00:00' ? "<a class=\"clock accessdisabled\" title=\"" . ( $p->commencement != '0000-00-00 00:00:00' ? "Commencement: {$this->see->format->date( $p->commencement, "d M Y H:i" )}\n" : '' ) . ( $p->expiry != '0000-00-00 00:00:00' ? "Expiry: {$this->see->format->date($p->expiry, "d M Y H:i")}\n" : '' ) . "\"><i class=\"fa fa-clock-o\" aria-hidden=\"true\"></i></a>" : "" );     
      }
      else {
        $content .= "<li id=\"p{$p->id}\" class=\"{$class}\">
        <div class=\"page\">
        <a class=\"name accessdisabled\" href=\"#\">{$p->title}</a>
        <a class=\"date accessdisabled\" href=\"#\">{$date}</a>
        <a class=\"move accessdisabled\" title=\"Move post\" href=\"#\"><i class=\"fa fa-arrows\" aria-hidden=\"true\"></i></a>
        <a class=\"delete deletepost accessdisabled\" href=\"#\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i></a>";
        
        $content .= ( $p->status ? "<a class=\"published togglepoststatus accessdisabled\" title=\"Published\" href=\"#\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i></a>" : "<a class=\"notpublished togglepoststatus accessdisabled\" title=\"Suppressed\" href=\"#\"><i class=\"fa fa-minus\" aria-hidden=\"true\"></i></a>" );

        $content .= ( $p->commencement != '0000-00-00 00:00:00' || $p->expiry != '0000-00-00 00:00:00' ? "<a class=\"clock accessdisabled\" title=\"" . ( $p->commencement != '0000-00-00 00:00:00' ? "Commencement: {$this->see->format->date( $p->commencement, "d M Y H:i" )}\n" : '' ) . ( $p->expiry != '0000-00-00 00:00:00' ? "Expiry: {$this->see->format->date($p->expiry, "d M Y H:i")}\n" : '' ) . "\"><i class=\"fa fa-clock-o\" aria-hidden=\"true\"></i></a>" : "" );     
      }

      if( isset( $this->see->SeeCMS->config["advancedEditorPermissions"] ) ) { 
     
        if( $globalApprovalPermission >= 5 || $accessLevel >= 5 ) {
          $approval = SeeDB::count( "adminapproval", " complete = 0 && objecttype = 'post' && objectid = ? ", array( $p->id ) );
          
          if( $approval ) {
            $folderPendingApproval = true;
            $content .= "<a class=\"approval\" title=\"Content pending approval\"><i class=\"fa fa-warning\" aria-hidden=\"true\"></i></a>";
          }
        }
        
      }  
        
      $content .= "</div></li>";
    }
    
    $content .= "</ul>";
    
    $content = ( $content != '<ul></ul>' ? $content : '<p><strong>There\'s no posts in this folder.</strong></p>' );
            
    if( $folderPendingApproval ) {
      $content = "<div class=\"seecmsmessage seecmsnotice\"><p><strong>Please note</strong>: There are posts in this folder that are awaiting content approval</p></div>" . $content;
    }
    // else if( $accessLevel >= 5 ) { // Check other folders for pending content
    //   $approval = SeeDB::count( "adminapproval", " complete = 0 && objecttype = 'post' " );
    //   
    //   if( $approval ) {
    //     $content = "<div class=\"seecmsmessage seecmsnotice\"><p><i class=\"fa fa-warning\" aria-hidden=\"true\"></i>&nbsp;&nbsp;<strong>Please note</strong>: There is post content awaiting approval</p></div>" . $content;
    //   }
    //       
    // }
    
    return $content ;
  }
  
  public function loadCreateButtons( $parentID = 0 ) {
    
    $parentID = (int)$parentID;
    
    if( $_POST['id'] ) {
      $p = SeeDB::load( 'post', (int)$_POST['id'] );
      
      if( $p->isfolder ) {
        $parentID = (int)$_POST['id'];
      }
      
    }
    
    // Advanced permissions
    $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( 'post', $parentID );

    if( $accessLevel >= 5 ) {
      $content = "<a class=\"createfolder\" href=\"#\">Create folder <span><i class=\"fa fa-plus-circle\" aria-hidden=\"true\"></i></span></a>
      <a class=\"createpost\" href=\"#\">Create a new post <span><i class=\"fa fa-plus-circle\" aria-hidden=\"true\"></i></span></a>";
    }
    
    return (string)$content;
  }
  
  public function adminSearch( $keyword ) {
  
    $posts = SeeDB::find( 'post', ' deleted = ? && isfolder = ? && title LIKE ? ORDER BY date DESC LIMIT 10 ', array( '0000-00-00 00:00:00', 0, "%{$keyword}%" ) );
    foreach( $posts as $p ) {
      
      $r[] = array( 'id' => $p->id, 'title' => $p->title, 'posted' => $this->see->format->date( $p->date, "d F Y" ) );
    }
    
    return( $r );
  }
  
  public function feed( $settings = array() ) {
    
    // GET posts
    $now = date( "Y-m-d H:i:s" );
    $sql = ' deleted = ? && isfolder = ? && status = ? && posttype_id = ? && ( commencement = ? || commencement <= ? ) && ( expiry = ? || expiry >= ? ) ';
    $sqlParams = array( '0000-00-00 00:00:00', 0, 1, (($settings['postType'])?$settings['postType']:1), '0000-00-00 00:00:00', $now, '0000-00-00 00:00:00', $now );
    
    if( $settings['futureEventsOnly'] ) {

      $sql .= ' && eventEnd >= ? ';
      $sqlParams[] = $now;
    }
    
    if( $settings['order'] ) {
      $sqlOrder = ' ORDER BY '.$settings['order'];
    } else {
      $sqlOrder = ' ORDER BY date DESC, id DESC ';
    }
    
    if( $settings['tags'] && $_GET['tag'] ) {
      $sql .= " && tags LIKE ? ";
      $sqlParams[] = "%{$_GET['tag']}%";
    }
    
    if( $settings['archives'] && $_GET['year'] ) {
      $sql .= " && date >= ? && date <= ? ";
      if( $_GET['month'] ) {
        $sqlParams[] = $_GET['year']."-".str_pad( $_GET['month'], 2, "0", STR_PAD_LEFT)."-01";
        $sqlParams[] = $this->see->format->date( $_GET['year']."-".str_pad( $_GET['month'], 2, "0", STR_PAD_LEFT)."-01", "Y-m-t" );
      } else {
        $sqlParams[] = $_GET['year']."-01-01";
        $sqlParams[] = $_GET['year']."-12-31";
      }
    }
    
    if( $settings['defaultDisplay'] && ( !$settings['archives'] || !$_GET['year'] ) && ( !$settings['tags'] || !$_GET['tag'] ) ) {
    
      if( $settings['defaultDisplay'] == 'currentYear' ) {
      
        $sqlAlternateParams = $sqlParams;
      
        $sql .= " && date >= ? && date <= ? ";
        $sqlParams[] = date("Y")."-01-01";
        $sqlParams[] = date("Y")."-12-31";
        
        $sqlAlternateParams[] = (date("Y")-1)."-01-01";
        $sqlAlternateParams[] = (date("Y")-1)."-12-31";
      
      } else if( $settings['defaultDisplay'] == 'currentMonth' ) {
      
        $sql .= " && date >= ? && date <= ? ";
        $sqlParams[] = date("Y-m")."-01";
        $sqlParams[] = date("Y-m-t");
      
      }
    }
    
    if( $settings['category'] ) {
      
      if( is_array( $settings['category'] ) ) {
        
        $sql = 'SELECT * FROM post WHERE '.$sql;
        foreach( $settings['category'] as $cat ) {
          $sql .= ' && id IN ( SELECT post_id FROM category_post WHERE category_id = ? )';
          $sqlParams[] = $cat;
        }
        $posts = SeeDB::getAll( $sql.$sqlOrder, $sqlParams );
        $posts = SeeDB::convertToBeans( 'post', $posts );
      } else {
        $category = SeeDB::load( 'category', $settings['category'] );
        $posts = $category->withCondition( $sql.$sqlOrder, $sqlParams )->sharedPost;
      }
    } else {
      $posts = SeeDB::find( 'post', $sql.$sqlOrder, $sqlParams );
    }
    
    if( !is_array( $posts ) && $sqlAlternateParams ) {
      if( $settings['category'] ) {
        if( is_array( $settings['category'] ) ) {
          $sql = 'SELECT * FROM post WHERE '.$sql;
          foreach( $settings['category'] as $cat ) {
            $sql .= ' && id IN (SELECT post_id FROM category_post WHERE category_id = ?)';
            $sqlAlternateParams[] = $cat;
          }
          $posts = SeeDB::getAll( $sql.$sqlOrder, $sqlAlternateParams );
          $posts = SeeDB::convertToBeans( 'post', $posts );
        } else {
          $category = SeeDB::load( 'category', $settings['category'] );
          $posts = $category->withCondition( $sql.$sqlOrder, $sqlAlternateParams )->sharedPost;
        }
      } else {
        $posts = SeeDB::find( 'post', $sql.$sqlOrder, $sqlAlternateParams );
      }
    }
    
    if( is_array( $posts ) ) {
    
      $postCount = count( $posts );
    
      if( $settings['limit'] ) {
        $settings['page'] = (((int)$settings['page'])?$settings['page']:1);
        $posts = array_slice( $posts, (($settings['page']-1)*$settings['limit'])+((int)$settings['offset']), $settings['limit'] );
      }
      
      foreach( $posts as $p ) {
      
        $route = SeeDB::findOne( 'route', ' objecttype = ? && objectid = ? && primaryroute = ? ', array( 'post', $p->id, 1 ) );
        $pscontent = '';
        $tagsHTML = '';
        $tags = array();
        $adfs = array();
        
        // Collect any content we need
        $content = SeeDB::find( 'content', ' objecttype = ? && objectid = ? && language = ? && status = ? ORDER BY status ASC ', array( 'post', $p->id, $this->see->SeeCMS->language, 1 ) );
        foreach( $content as $c ) {
        
          if( !isset( $ps['content'.$c->contentcontainer->id] ) ) {
            $method = str_replace( " ", "", $c->contentcontainer->contenttype->type );
            $method[0] = strtolower( $method[0] );
            $pscontent['content'.$c->contentcontainer->id] = $this->see->SeeCMS->content->$method( $c->content, 0, $c->contentcontainer->id, $c->status, $c->contentcontainer->contenttype->fields, $c->contentcontainer->contenttype->settings );
          }
        }
        
        // Get some ADFs
        if( $settings['loadADFs'] ) {
          
          if( $settings['loadADFs'] !== true ) {
            
            $adfstoload = true;
            $loadadfs = $settings['loadADFs'];
          }
          
          if( !$adfstoload ) {
            $adfstoload = SeeDB::find( 'adf', ' objecttype = ? ', array( 'post' ) );
            if( is_array( $adfstoload ) ) {
              foreach( $adfstoload as $adf ) {
                $loadadfs[] = (int)$adf->id;
              }
            }
          }
          
          if( is_array( $loadadfs ) ) {
            $cc = new SeeCMSContentController( $this->see, $this->see->SeeCMS->language );
            $adfs = $cc->loadADFcontent( array( 'objectid' => $p->id, 'type' => 'post', 'adfs' => $loadadfs ) );
          }
        }
        
        // Sort tags
        if( $p->tags ) {
          foreach( explode( ',', $p->tags ) as $t ) {
            $t = strtolower( trim( $t ) );
            $tagsHTML .= "<a class=\"seecmstag\" href=\"./?tag={$t}\">{$t}</a>, ";
            $tags[].= $t;
          }
          $tagsHTML = "<p class=\"seecmstags\">Tags: {$tagsHTML}</p>";
        }

        $ps[] = array( 'id' => $p->id, 'title' => $p->title, 'media' => $p->media, 'route' => "/{$route->route}", 'date' => $p->date, 'eventStart' => $p->eventstart, 'eventEnd' => $p->eventend, 'standfirst' => $p->standfirst, 'content' => $pscontent, 'tagsHTML' => $tagsHTML, 'tags' => $tags, 'categories' => $p->sharedCategory, 'post' => $p, 'adfs' => $adfs );
      }
    
      if( $settings['pages'] ) {
        $r = array( 'posts' => $ps, 'postCount' => $postCount, 'page' => $settings['page'], 'pages' => ceil( $postCount/$settings['limit'] ) );
      } else {
        $r = $ps;
      }
    }
    
    return $r;
  }
  
  public function archiveList( $settings = array() ) {
    
    if( !$settings['showMonths'] ) {
      
      $settings['showMonths'] = 'all';
    }
  
    // GET oldest post
    $sql = ' deleted = ? && isfolder = ? && status = ? && ( commencement = ? || commencement <= ? ) && ( expiry = ? || expiry >= ? ) && posttype_id = ? ';
    $sqlParams = array( '0000-00-00 00:00:00', 0, 1, '0000-00-00 00:00:00', $now, '0000-00-00 00:00:00', $now, (($settings['postType'])?$settings['postType']:1) );
    
    if( !isset( $settings['postType'] ) || $settings['postType'] == 1 ) {
      $sqlOrder = ' ORDER BY date';
    } else if( $settings['postType'] == 2 ) {
      $sqlOrder = ' ORDER BY eventstart';
    }
    
    if( $settings['category'] ) {
      
      $cat = SeeDB::load( 'category', $settings['category'] );
      $post = reset( $cat->withCondition( $sql.$sqlOrder, $sqlParams )->sharedPost );
      
    } else {
      
      $post = SeeDB::findOne( 'post', $sql.$sqlOrder, $sqlParams );
    }
    
    if( $post ) {
      list( $endYear, $endMonth, $endDay ) = explode( '-', (($settings['postType']==2)?$post->eventstart:$post->date) );
      $endMonth = (int)$endMonth;
    }
  
    // GET newest post
    $sql = ' deleted = ? && isfolder = ? && status = ? && ( commencement = ? || commencement <= ? ) && ( expiry = ? || expiry >= ? ) && posttype_id = ? ';
    $sqlParams = array( '0000-00-00 00:00:00', 0, 1, '0000-00-00 00:00:00', $now, '0000-00-00 00:00:00', $now, (($settings['postType'])?$settings['postType']:1) );
    
    if( $settings['postType'] == 1 || !$settings['postType'] ) {
      $sqlOrder = ' ORDER BY date DESC';
    } else if( $settings['postType'] == 2 ) {
      $sqlOrder = ' ORDER BY eventend DESC';
    }
    
    if( $settings['category'] ) {
      
      $cat = SeeDB::load( 'category', $settings['category'] );
      $post = reset( $cat->withCondition( $sql.$sqlOrder, $sqlParams )->sharedPost );
      
    } else {
      
      $post = SeeDB::findOne( 'post', $sql.$sqlOrder, $sqlParams );
    }
    
    if( $post ) {
      list( $startYear, $startMonth, $startDay ) = explode( '-', (($settings['postType']==2)?$post->eventend:$post->date) );
      $startMonth = (int)$startMonth;
    }
    
    $currentYear  = (( $_GET['year'] ) ? $_GET['year'] : $startYear );
    $currentMonth = (( $_GET['month'] ) ? $_GET['month'] : $startMonth );
    
    if( $startMonth && $endMonth ) {
    
      $o .= "<ul>";
      
      for( $year = $startYear; $year >= $endYear; $year-- ) {
      
        $o .= "<li".(( $year == $currentYear ) ? ' class="selected"' : '' )."><a href=\"./?year={$year}\">{$year}</a>";
        
        if( $year == $currentYear || strtolower( $settings['showMonths'] ) == 'all' ) {
          $o .= "<ul>";
          
          if( $year == $startYear ) {
            $bmonth = $startMonth;
          } else {
            $bmonth = 12;
          }
          
          if( $year == $endYear ) {
            $emonth = $endMonth;
          } else {
            $emonth = 1;
          }
          
          for( $month = $bmonth; $month >= $emonth; $month-- ) {
        
            $o .= "<li".(( $month == $currentMonth ) ? ' class="selected"' : '' )."><a href=\"./?year={$year}&amp;month={$month}\">".$this->see->format->date( "2000-{$month}-01", "F" )."</a></li>";
          }
          
          $o .= "</ul>";
        }
        
        $o .= "</li>";
      }
      
      $o .= "</ul>";
    }
    
    return( $o );
    
  }
  
  public function rss( $route = '' ) {

    $posts = $this->feed( $route['custom'] );

    $url = "http://".$_SERVER['HTTP_HOST'].(($this->see->rootURL)?"/".$this->see->rootURL:'');

    echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n<rss version=\"2.0\">\n<channel>\n";

    echo "<title>{$route['custom']['feedtitle']}</title>\n";
    echo "<link>{$url}</link>\n";
    echo "<description>{$route['custom']['feeddescription']}</description>\n";
    echo "<language>".(($route['custom']['feedlanguage'])?$route['custom']['rsssettings']['feedlanguage']:'en-gb')."</language>\n";

    $url = rtrim( $url, '/' );

    if( is_array( $posts ) ) {

      foreach( $posts as $post ) {
        
        $post['title'] = str_replace( "&", "&amp;", $post['title'] );
        $post['standfirst'] = str_replace( "&", "&amp;", $post['standfirst'] );

        echo "<item>\n";
        echo "<title>{$post['title']}</title>\n";
        echo "<link>{$url}{$post['route']}</link>\n";
        echo "<description>{$post['standfirst']}</description>\n";
        echo "</item>\n";

      }
    }

    echo "</channel>\n</rss>\n";
    die();
  }
  
  private function duplicate( $dp ) {
    
    /* Duplicate post */
    $p = SeeDB::dispense( 'post' );
      
    $p->title = $dp->title." COPY";
    $p->template = $dp->template;
    $p->date = $dp->date;
    $p->status = 0;
    $p->parentid = $dp->parentid;
    $p->postorder = 0;
    $p->commencement = $dp->commencement;
    $p->expiry = $dp->expiry;
    $p->lastupdated = date("Y-m-d H:i:s");
    $p->visibility = $dp->visibility;
    $p->htmltitle = $dp->htmltitle;
    $p->metadescription = $dp->metadescription;
    $p->metakeywords = $dp->metakeywords;
    $p->redirect = $dp->redirect;
    $p->standfirst = $dp->standfirst;
    $p->isfolder = 0;
    $p->tags = $dp->tags;
    $p->media_id = $dp->media_id;
    $p->posttype_id = $dp->posttype_id;
    $p->eventstart = $dp->eventstart;
    $p->eventend = $dp->eventend;
      
    SeeDB::store( $p );
      
    /* Create route */
    SeeCMSController::makeRoute( $p->title, $p->id, 'Post', SeeCMSController::getSetting( 'postsURL' ) );
    
    /* Add to categories */
    foreach( $dp->sharedCategory as $cat ) {
      
      $p->sharedCategory[] = $cat;
    }
    
    SeeDB::store( $p );
    
    /* Duplicate content */
    $adfs = SeeDB::find( 'adfcontent', ' objecttype = ? && objectid = ? ', array( 'post', $dp->id ) );
    foreach( $adfs as $adf ) {
      
      $newadf = SeeDB::dispense( 'adfcontent' );
      $newadf->objecttype = 'post';
      $newadf->objectid = $p->id;
      $newadf->adf_id = $adf->adf_id;
      $newadf->content = $adf->content;
      $newadf->language = $adf->language;
      
      SeeDB::store( $newadf );
    }
    
    $contents = SeeDB::find( 'content', ' objecttype = ? && objectid = ? ', array( 'post', $dp->id ) );
    foreach( $contents as $content ) {
      
      $newcontent = SeeDB::dispense( 'content' );
      $newcontent->objecttype = 'post';
      $newcontent->objectid = $p->id;
      $newcontent->contentcontainer_id = $content->contentcontainer_id;
      $newcontent->content = $content->content;
      $newcontent->language = $content->language;
      $newcontent->editable = $content->editable;
      $newcontent->status = $content->status;
      
      SeeDB::store( $newcontent );
    }
    
    // Deal with custom post type
    if( isset( $this->see->SeeCMS->customPostController[$p->posttype->name]['plugin'] ) ) {
      $customPostController = $this->see->{$this->see->SeeCMS->customPostController[$p->posttype->name]['plugin']};
      if( method_exists( $customPostController, 'duplicate' ) ) {
        $customPostController->duplicate( $dp, $p );
      }
    }
    
    $this->see->redirect( "./?id={$p->id}&duplicated=1" );
  }
  
}