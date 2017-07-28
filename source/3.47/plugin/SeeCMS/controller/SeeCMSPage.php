<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeCMSPageController {

  var $see;

  public function __construct( $see ) {

    $this->see = $see;
  }

  public function load() {

    $p = SeeDB::load( 'page', (int)$_GET['id'] );
    return( $p );
  }

  public function loadForEdit() {

    $data['page'] = $this->load();
    $data['pageRoutes'] = SeeDB::find( 'route', ' objecttype = ? && objectid = ? ORDER BY primaryroute DESC ', array( 'Page', $data['page']->id ) );
    $data['linkSelector'] = $this->see->SeeCMS->content->loadForLinkSelector( true, true );
    $data['cloneLinkSelector'] = $this->see->SeeCMS->content->loadForLinkSelector( true, true, array('page'=>true) );
    $data['templates'] = json_decode( SeeCMSSettingController::load( 'pagetemplates' ) );
    $data['multisite'] = SeeCMSSettingController::load( 'multisite' );

    // Backwards compatibility
    if( !is_array( $data['templates'] ) ) {
      $data['templates'] = unserialize( SeeCMSSettingController::load( 'pagetemplates' ) );
    }

    $data['messages'] .= ( $_GET['routeerror'] ? '<div class="seecmsmessage seecmserror"><p><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;&nbsp;The URLs could not be updated because one or more of them already exist on another page/post</p></div>' : '' );

    // Advanced permissions
    if( $this->see->SeeCMS->config["advancedEditorPermissions"] ) {
      $data['accessLevel'] = $this->see->SeeCMS->adminauth->checkContextAccess( 'page', $data['page']->id );

      if( !$data['accessLevel'] ) {
        $this->see->redirect( "../../pages/" );
      }

      if( $data['accessLevel'] < 5 ) {
        $data['messages'] .= "<div class=\"seecmsmessage seecmsnotice\"><p><i class=\"fa fa-exclamation-triangle\" aria-hidden=\"true\"></i>&nbsp;&nbsp;Please note: You only have limited access to this page, you're unable to make changes to settings.</p></div>";
      }

      if( $_SESSION['seecms'][$this->see->siteID]['adminuser']['access']['current'] >= 5 ) {
        $data["adminuserGroups"] = SeeDB::findAll( "adminusergroup", " ORDER BY name " );
        $data["adminuserGroupPermissions"] = SeeCMSAdminAuthenticationController::getPermission( $data["page"]->id, "page", $data["adminuserGroups"] );
      }

      $pendingApproval = SeeDB::findOne( 'adminapproval', " complete = 0 && objecttype = 'page' && objectid = ? ", array( $data['page']->id) );

      // Check for content awaiting approval
      if( $pendingApproval ) {
        $globalApprovalPermission = $this->see->SeeCMS->adminauth->checkAccess( "action-content-completeApproval", null, false );
        $this->see->SeeCMS->adminauth->checkAccess( "page/edit/", null, false ); // Reset current

        if( $globalApprovalPermission >= 5 || $data["accessLevel"] >= 5 ) {
          $data['messages'] .= '<div class="seecmsmessage seecmsnotice"><p><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;&nbsp;Please note: This page has content awaiting approval</p></div>';
        }
        else {
          $data["pendingApproval"] = true;
          $data['messages'] .= '<div class="seecmsmessage seecmssuccess"><p>Approval of this page has been requested</p></div>';
        }

      }
      // Check for unapplied content
      else if( $data["accessLevel"] < 5 ) {
        $content = SeeDB::findOne( "content", " status = 0 && objecttype = 'page' && objectid = ? ", array( $data['page']->id ) );

        if( $content ) {
          $as = SeeDB::find( 'adminuser', ' adminuserrole_id = ? ', array( $this->see->SeeCMS->config['advancedEditorPermissionsAdminRole'] ) ); // TODO also full access admins
          $data['unappliedContent'] = true;

          foreach( $as as $au ) {
            $data['contentApprovers'][$au->id] = $au->name;
          }

        }

      }

    }

    if( $data['page']->redirect ) {
      $data['redirectDetails'] = $this->see->SeeCMS->content->loadLinkDetails( $data['page']->redirect );
    }

    if( $data['page']->clone ) {
      $data['cloneDetails'] = $this->see->SeeCMS->content->loadLinkDetails( $data['page']->clone );
    }

    $data['userGroups'] = SeeDB::findAll( 'websiteusergroup', ' ORDER BY name ' );

    foreach( $data['userGroups'] as $ug ) {

      foreach( $ug->ownWebsiteusergrouppermission as $wugp ) {

        if( $wugp->objecttype == 'page' && $wugp->objectid == $data['page']->id ) {
          $data['userGroupPermission'][$ug->id] = 1;
        }
      }
    }

    $data['page']->commencement = (( $data['page']->commencement == '0000-00-00 00:00:00' ) ? '' : $data['page']->commencement );
    $data['page']->expiry = (( $data['page']->expiry == '0000-00-00 00:00:00' ) ? '' : $data['page']->expiry );

    return( $data );
  }

  public function create() {

    $at = explode( "-", $_POST['at'] );

    // Check if parent exists
    $pp = SeeDB::load( 'page', $at[1] );

    // Advanced permissions
    $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( 'page', $pp->id );

    if( $this->see->SeeCMS->config["advancedEditorPermissions"] && $accessLevel < 5 ) {
      $ret["done"] = 0;
    }
    else if( $pp->id || $at[1] === '0' ) {

      $p = SeeDB::dispense( 'page' );

      $p->title = $_POST['title'];
      $p->parentid = $at[1];
      $p->pageorder = $at[2];
      $p->ascendants = (( isset( $pp->ascendants ) ) ? $pp->ascendants.",".$pp->id : $pp->id );

      if( $pp->id ) {
        $p->template = $pp->template;
        $p->site_id  = $pp->site_id;
      }
      else {
        $templates = json_decode( SeeCMSSettingController::load( 'pagetemplates' ) );

        // Backwards compatibility
        if( !is_array( $templates ) ) {
          $templates = unserialize( SeeCMSSettingController::load( 'pagetemplates' ) );
        }

        $p->template = $templates[0];
      }

      SeeDB::store( $p );

      SeeDB::exec( " UPDATE page SET pageorder = pageorder + 1 WHERE parentid = {$p->parentid} && pageorder >= {$p->pageorder} && id != {$p->id} " );

      // Add route
      $pr = SeeDB::findOne( 'route', ' objecttype = ? && objectid = ? && primaryroute = ? ', array( 'Page', $pp->id, 1 ) );
      SeeCMSController::makeRoute( $p->title, $p->id, 'Page', $pr->route );

      // Websiteusergroup permissions
      $wugps = SeeDB::find( 'websiteusergrouppermission', ' objecttype = ? && objectid = ? ', array( 'Page', $pp->id ) );
      if( count( $wugps ) ) {
        foreach( $wugps as $wugp ) {
          $newwugp = SeeDB::dup( $wugp );
          $newwugp->objectid = $p->id;
          SeeDB::store( $newwugp );
        }
      }

      // Adminusergroup permissions
      if( $this->see->SeeCMS->config['advancedEditorPermissions'] ) {
        $adminusergrouppermissions = SeeCMSAdminAuthenticationController::getPermission( $p->parentid, "page" );
        SeeCMSAdminAuthenticationController::setPermission( $p->id, "page", $adminusergrouppermissions );
      }

      $this->see->SeeCMS->hook->run( array( "hook" => "page-create", "data" => $p ) );

      $ret["done"] = 1;
      $ret["data"] = $this->adminTree();
      $ret['id']   = $p->id;

    }
    else {
      $ret["done"] = 0;
    }

    return( json_encode( $ret ) );
  }

  public function update( $data, $errors, $settings ) {
    
    if( !$errors ) {
      $p = SeeDB::load( 'page', (int)$data['id'] );

      if( $p->id ) {

        // Advanced permissions
        if( $this->see->SeeCMS->config["advancedEditorPermissions"] ) {
          $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( "page", $p->id );

          if( $accessLevel < 5 ) {
            
            if( !$settings["skipRedirect"] ) {
              return array( "errors" => array( "Error - you have insufficient privileges to edit this page" ) );
            }
            else {
              return;
            }

          }

        }

        if( $data['route0'] ) {

          $addToRoute = '';
          if( $p->site_id ) {
            if( $p->site->route ) {
              $addToRoute = $p->site->route;
            }
          }

          foreach( $data as $dk => $dv ) {

            if( substr( $dk, 0, 5 ) == 'route' ) {

              $routeID = str_replace( 'route', '', $dk );
              $theRoute = $this->see->prepareRoute( $addToRoute.$dv );

              // Check if the route exists somewhere else
              $r = SeeDB::findOne( 'route', ' route = ? && ( objectid != ? || objecttype != ? ) ', array( $theRoute, $p->id, 'page' ) );
              if( $r ) {
                $routesOK = false;
                break;
              } else {

                if( !$data['deleteroute'.$routeID] ) {
                  $routesOK = true;

                  $addRoute[] = array( $theRoute, (( $data['primaryroute'.$routeID] && !$primaryset ) ? 1 : 0 ) );

                  if( $data['primaryroute'.$routeID] ) {
                    $primarySet = 1;
                  }

                }
              }
              
            } 
            else if( substr( $dk, 0, 15 ) == 'security-group-' ) {

              if( !$data['security-allUserAccess'] ) {
                $groups[] = substr( $dk, 15 );
              }
              
            }
            
          }


          if( $routesOK ) {

            // If there's no primary route set use the first one
            if( !$primarySet ) {
              $addRoute[0][1] = 1;
            }

            SeeDB::exec( " DELETE FROM route WHERE objectid = {$p->id} && objecttype = 'page' " );

            foreach( $addRoute as $r ) {
              SeeCMSController::createRoute( $r[0], $p->id, 'page', $r[1] );
            }
            
          }
          
        }
        
        
        // Websiteusergroup permissions
        SeeCMSWebsiteUserController::setPermission( $p->id, "page", $groups );
        
        if( $data['security-cascade'] ) {
          SeeCMSWebsiteUserController::cascadePermission( $p->id, "page", $groups );
        }
        
        // Adminusergroup permissions
        SeeCMSAdminAuthenticationController::setPermission( $p->id, "page", $data["adminusergrouppermissions"] );
        
        if( $data["adminusergrouppermissions-cascade"] ) {
          SeeCMSAdminAuthenticationController::cascadePermission( $p->id, "page", $data["adminusergrouppermissions"] );
        }
        
        
        if( strtolower( $p->title ) != strtolower( $data['title'] ) ) {
          // Add route
          $pr = SeeDB::findOne( 'route', ' objecttype = ? && objectid = ? && primaryroute = ? ', array( 'page', $p->parentid, 1 ) );
          SeeCMSController::makeRoute( $data['title'], $p->id, 'Page', $pr->route );
        }


        $p->title = $data['title'];
        $p->htmltitle = $data['htmltitle'];

        $p->template = $data['template'];

        $p->redirect = $data['redirect'];
        $p->clone    = $data['clone'];

        $p->metadescription = $data['metadescription'];
        $p->metakeywords = $data['metakeywords'];

        $p->visibility = (( $data['hidefromnavigation'] ) ? 2 : 1 );
        $p->visibility = (( $data['hidefromsitemap'] ) ? 3 : $p->visibility );

        $commencementtime = (( $data['commencementtime'] ) ? $data['commencementtime'].":00" : '00:00:00');
        $commencement = strtotime( $data['commencement']." ".$commencementtime );
        $p->commencement = (( $commencement && $data['commencement'] ) ? date( "Y-m-d H:i:s", $commencement ) : '0000-00-00 00:00:00' );

        $expirytime = (( $data['expirytime'] ) ? $data['expirytime'].":00" : '00:00:00');
        $expiry = strtotime( $data['expiry']." ".$expirytime );
        $p->expiry = (( $expiry && $data['expiry'] ) ? date( "Y-m-d H:i:s", $expiry ) : '0000-00-00 00:00:00' );
      }

      $p->lastupdated = date( "Y-m-d H:i:s" );

      // Save
      SeeDB::store( $p );
      $this->see->SeeCMS->hook->run( array( "hook" => "page-update", "data" => $p ) );

      // Custom page data
      if( isset( $this->see->SeeCMS->customPageController['plugin'] ) ) {
        $customPageController = $this->see->{$this->see->SeeCMS->customPageController['plugin']};
        $customPageController->saveFields( $p, $data );
      }

      if( !$settings["skipRedirect"] ) {
        $this->see->redirect( "?id={$p->id}" . ( $routesOK === false  ? '&routeerror=1' : '' ) );
      }

    }

  }

  public function move( $id = 0, $at = '' ) {

    if( !$id ) {
      $id = (int)$_POST['id'];
    }

    if( !$at ) {
      $at = $_POST['at'];
    }

    $at = explode( "-", $at );

    // Check if parent exists
    $pp = SeeDB::load( 'page', $at[1] );

    if( ( $pp->id || $at[1] === '0' ) && $pp->id != $id ) {
      $p = SeeDB::load( 'page', $id );

      if( $p->id ) {
        // Advanced permissions
        if( $this->see->SeeCMS->config["advancedEditorPermissions"] ) {
          $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( "media", $m->id );

          if( $accessLevel < 5 ) {
            $ret["done"] = 0;
            return( json_encode( $ret ) );
          }

        }

        $removeRoute = 0;

        if( ( $p->site_id != $pp->site_id ) && $pp->id ) {
          if( $p->site->route || $pp->site->route ) {
            $removeRoute = 1;
          }
        }

        $oldParent = $p->parentid;
        $oldOrder = $p->pageorder;

        $p->parentid = (int)$at[1];
        $p->pageorder = (int)$at[2];
        $p->ascendants = (( isset( $pp->ascendants ) ) ? $pp->ascendants.",".$pp->id : $pp->id );

        if( $p->parentid == $oldParent && $p->pageorder > $oldOrder ) {
          $p->pageorder -= 1;
        }

        if( $oldOrder != $p->pageorder || $oldParent != $p->parentid ) {

          if( $pp->id ) {
            unset($p->site);
            $p->site_id = (int)$pp->site_id;
          }

          SeeDB::store( $p );

          // Add route
          if( $oldParent != $p->parentid ) {

            $pr = SeeDB::findOne( 'route', ' objecttype = ? && objectid = ? && primaryroute = ? ', array( 'Page', $pp->id, 1 ) );
            SeeCMSController::makeRoute( $p->title, $p->id, 'Page', $pr->route );

            $this->moveChildren( $p );
          }

          if( $removeRoute ) {

            SeeDB::exec( " DELETE FROM route WHERE objectid = {$p->id} && objecttype = 'page' && primaryroute != 1" );
          }


          SeeDB::exec( " UPDATE page SET pageorder = pageorder - 1 WHERE parentid = {$oldParent} && pageorder >= {$oldOrder} && deleted = '0000-00-00' && id != {$p->id} " );
          SeeDB::exec( " UPDATE page SET pageorder = pageorder + 1 WHERE parentid = {$p->parentid} && pageorder >= {$p->pageorder} && id != {$p->id} && deleted = '0000-00-00' " );

          $ret["done"] = 1;
          $ret["data"] = $this->adminTree();
        }
        else {
          $ret["done"] = 0;
        }
      }
      else {
        $ret["done"] = 0;
      }
    }
    else {
      $ret["done"] = 0;
    }

    return( json_encode( $ret ) );
  }

  public function moveChildren( $pp ) {

    $ps = SeeDB::find( 'page', ' parentid = ? ', array( $pp->id ) );
    if( count( $ps ) ) {

      foreach( $ps as $p ) {
        // TODO check permissions

        $pr = SeeDB::findOne( 'route', ' objecttype = ? && objectid = ? && primaryroute = ? ', array( 'Page', $pp->id, 1 ) );

        $removeRoute = 0;
        if( ( $p->site_id != $pp->site_id ) && ( $p->site->route || $pp->site->route ) ) {

          $removeRoute = 1;
        }

        $p->ascendants = (( isset( $pp->ascendants ) ) ? $pp->ascendants.",".$pp->id : $pp->id );

        unset($p->site);
        $p->site_id = (int)$pp->site_id;

        SeeDB::store( $p );

        // Add route
        SeeCMSController::makeRoute( $p->title, $p->id, 'Page', $pr->route );

        if( $removeRoute ) {
          SeeDB::exec( " DELETE FROM route WHERE objectid = {$p->id} && objecttype = 'page' && primaryroute != 1" );
        }

        $this->moveChildren( $p );
      }

    }

  }

  public function status( $id = 0 ) {

    if( !$id ) {
      $id = (int)$_POST['id'];
    }

    $p = SeeDB::load( 'page', $id );

    if( $p->id ) {

      // Advanced permissions
      if( $this->see->SeeCMS->config["advancedEditorPermissions"] ) {
        $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( "page", $p->id );

        if( $accessLevel < 5 ) {
          $ret["done"] = 0;
          $ret["data"] = $this->adminTree();
          return json_encode( $ret );
        }

      }

      $p->status = ( $p->status  ? 0 : 1 );
      SeeDB::store( $p );

      $ret["done"] = 1;
      $ret["data"] = $this->adminTree();
    }

    return json_encode( $ret );
  }

  public function delete( $id = 0 ) {

    if( !$id ) {
      $id = (int)$_POST['id'];
    }

    $this->recursiveDelete( $id );

    $ret["done"] = 1;
    $ret["data"] = $this->adminTree();

    return( json_encode( $ret ) );
  }

  private function recursiveDelete( $id ) {

    $p = SeeDB::load( 'page', $id );

    if( $p->id ) {
      $accessLevel = 5;

      // Advanced permissions
      if( $this->see->SeeCMS->config["advancedEditorPermissions"] ) {
        $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( "page", $p->id );
      }

      if( $accessLevel >= 5 ) {
        SeeDB::exec( " DELETE FROM route WHERE objectid = {$p->id} && objecttype = 'page' " );
        SeeDB::exec( " UPDATE adfcontent SET objecttype = 'pagedeleted' WHERE objectid = {$p->id} && objecttype = 'page' " );

        SeeDB::exec( " UPDATE page SET pageorder = pageorder - 1 WHERE parentid = {$p->parentid} && pageorder > {$p->pageorder} && id != {$p->id} " );

        $p->deleted = date("Y-m-d H:i:s");
        $p->pageorder = -1;
        SeeDB::store( $p );
      }

      $pages = SeeDB::find( 'page', ' parentid = ? && deleted = ? ORDER BY pageorder ASC ', array( $p->id, '0000-00-00 00:00:00' ) );

      foreach( $pages as $p ) {
        $this->recursiveDelete( $p->id );
      }

    }


  }

  public function loadForCMS() {

    $data["pages"] = $this->adminTree();
    $data["createButtons"] = $this->loadCreateButtons();

    if( strpos( $data["pages"], "approval" ) !== false ) {
      $data["pendingApproval"] = "<div class=\"seecmsmessage seecmsnotice\">
      <p><i class=\"fa fa-warning\" aria-hidden=\"true\"></i> <strong>Please note:</strong> there is page content awaiting approval</p>
      </div>";
    }

    return $data;
  }

  public function adminTree( $parentID = 0, $parentName = '', $parentAccessLevel = 0 ) {

    $parentID = (int)$parentID;

    // Insert point txt
    $subPageOf = ( ( $parentID ) ? "Sub page of <strong>{$parentName}</strong>" : '<strong>Main page</strong>' );

    $pages = SeeDB::find( 'page', ' parentid = ? && deleted = ? ORDER BY pageorder ASC ', array( $parentID, '0000-00-00 00:00:00' ) );
    $globalApprovalPermission = $this->see->SeeCMS->adminauth->checkAccess( "action-content-completeApproval", null, false );
    $this->see->SeeCMS->adminauth->checkAccess( "page/edit/", null, false ); // Reset current

    if( SeeCMSSettingController::load( 'multisite' ) ) {
      $siteName = $page->site->name;

      if( !$siteName ) {
        $siteName = 'Any';
      }

    }

    foreach( $pages as $p ) {
      $route = SeeDB::findOne( 'route', ' objecttype = ? && objectid = ? && primaryroute = ? ', array( 'page', $p->id, 1 ) );
      $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( "page", $p->id );

      if( $parentAccessLevel >= 5 || $_SESSION['seecms'][$this->see->siteID]['adminuser']['access']['pages/'] >= 5 ) {
        $content .= "<li class=\"ip\"><div id=\"insertpoint-{$parentID}-{$p->pageorder}\" class=\"page insertpoint\"><a class=\"name\" href=\"#\">{$subPageOf} - Click to <span class=\"create\">insert</span><span class=\"move\">move</span> the <span class=\"create\">new </span>page here</a></div></li>";
      }

      $ret = $this->adminTree( $p->id, $p->title, $accessLevel );

      $class = ( ( strstr( $ret, 'delete' ) || strstr( $ret, 'protected' ) ) ? 'hasChildren' : 'noChildren' );
      $class .= ( ( $_SESSION['SeeCMS'][$this->see->siteID]['page']["p{$p->id}"] ) ? ' open' : '' );
      $faclass .= ( ( $_SESSION['SeeCMS'][$this->see->siteID]['page']["p{$p->id}"] ) ? ' fa-rotate-180' : '' );

      if( $p->protected ) {
        $content .= "<li id=\"p{$p->id}\" class=\"{$class} protected\"><div class=\"page\"><a class=\"expand\" title=\"Expand/Contract\" href=\"#\"><i class=\"fa fa-chevron-down{$faclass}\" aria-hidden=\"true\"></i></a><a class=\"name namedisabled\" href=\"#\">{$p->title} &nbsp; <strong>(Protected)</strong></a>";
      }
      else {

        if( $accessLevel >= 5  ) {
          $route = SeeDB::findOne( 'route', ' objecttype = ? && objectid = ? && primaryroute = ? ', array( 'Page', $p->id, 1 ) );
          $content .= "<li id=\"p{$p->id}\" class=\"{$class}\">
          <div class=\"page\">
          <a class=\"expand\" title=\"Expand/Contract\" href=\"#\"><i class=\"fa fa-chevron-down{$faclass}\" aria-hidden=\"true\"></i></a>
          <a class=\"name\" href=\"../page/edit/?id={$p->id}\">{$p->title}</a>
          <a class=\"preview\" title=\"Preview/Edit page\" href=\"" . ( $siteName && $siteName != $_SERVER['HTTP_HOST']  ? '/'.$page->site->route : ( $route->route != '/'  ? '/' : '' ) ) . "{$route->route}?preview=1\"><i class=\"fa fa-pencil-square\" aria-hidden=\"true\"></i></a>
          <a title=\"Move page\" class=\"move\" id=\"movepage-{$p->id}\" href=\"#\"><i class=\"fa fa-arrows\" aria-hidden=\"true\"></i></a>
          <a title=\"Delete\" class=\"deletepage delete\" id=\"deletepage-{$p->id}\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i></a>";

          $wugp = SeeDB::count( 'websiteusergrouppermission', ' objecttype = ? && objectid = ? ', array( 'Page', $p->id) );

          if( $wugp ) {
            $content .= "<a class=\"secure\" title=\"Secure\" href=\"#\"><i class=\"fa fa-lock\" aria-hidden=\"true\"></i></a>";
          }

          $content .= ( $p->status  ? "<a class=\"published togglepagestatus\" title=\"Suppress\" id=\"statuspage-{$p->id}\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i></a>" : "<a class=\"notpublished togglepagestatus\" title=\"Publish\" id=\"statuspage-{$p->id}\"><i class=\"fa fa-minus\" aria-hidden=\"true\"></i></a>" );

          $content .= ( $p->visibility != 1  ? "<a class=\"hidden\" title=\"".(( $p->visibility == 2 ) ? 'Hidden from navigation' : 'Hidden from navigation and sitemap' )."\"><i class=\"fa fa-low-vision\" aria-hidden=\"true\"></i></a>" : "" );

          $content .= ( $p->commencement != '0000-00-00 00:00:00' || $p->expiry != '0000-00-00 00:00:00' ? "<a class=\"clock\" title=\"" . ( $p->commencement != '0000-00-00 00:00:00' ? "Commencement: {$this->see->format->date( $p->commencement, "d M Y H:i" )}\n" : '' ) . ( $p->expiry != '0000-00-00 00:00:00' ? "Expiry: {$this->see->format->date($p->expiry, "d M Y H:i")}\n" : '' ) . "\"><i class=\"fa fa-clock-o\" aria-hidden=\"true\"></i></a>" : "" );
        }
        else if( $accessLevel >= 2 ) {
          $route = SeeDB::findOne( 'route', ' objecttype = ? && objectid = ? && primaryroute = ? ', array( 'Page', $p->id, 1 ) );
          $content .= "<li id=\"p{$p->id}\" class=\"{$class}\">
          <div class=\"page\">
          <a class=\"expand\" title=\"Expand/Contract\" href=\"#\"><i class=\"fa fa-chevron-down{$faclass}\" aria-hidden=\"true\"></i></a>
          <a class=\"name\" href=\"../page/edit/?id={$p->id}\">{$p->title}</a>
          <a class=\"preview\" title=\"Preview/Edit page\" href=\"" . ( $siteName && $siteName != $_SERVER['HTTP_HOST']  ? '/'.$page->site->route : ( $route->route != '/'  ? '/' : '' ) ) . "{$route->route}?preview=1\"><i class=\"fa fa-pencil-square\" aria-hidden=\"true\"></i></a>
          <a class=\"move accessdisabled\" href=\"#\"><i class=\"fa fa-arrows\" aria-hidden=\"true\"></i></a>
          <a class=\"deletepage delete accessdisabled\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i></a>";

          $wugp = SeeDB::count( 'websiteusergrouppermission', ' objecttype = ? && objectid = ? ', array( 'Page', $p->id) );

          if( $wugp ) {
            $content .= "<a class=\"secure accessdisabled\" title=\"Secure\" href=\"#\"><i class=\"fa fa-lock\" aria-hidden=\"true\"></i></a>";
          }

          $content .= ( $p->status  ? "<a class=\"published togglepagestatus accessdisabled\" title=\"Published\" href=\"#\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i></a>" : "<a class=\"notpublished togglepagestatus accessdisabled\" title=\"Suppressed\" href=\"#\"><i class=\"fa fa-minus\" aria-hidden=\"true\"></i></a>" );

          $content .= ( $p->visibility != 1  ? "<a class=\"hidden accessdisabled\" title=\"" . ( $p->visibility == 2  ? 'Hidden from navigation' : 'Hidden from navigation and sitemap' )."\"><i class=\"fa fa-low-vision\" aria-hidden=\"true\"></i></a>" : "" );

          $content .= ( $p->commencement != '0000-00-00 00:00:00' || $p->expiry != '0000-00-00 00:00:00'  ? "<a class=\"clock accessdisabled\" title=\"".(( $p->commencement != '0000-00-00 00:00:00' ) ? 'Commencement: '.$this->see->format->date($p->commencement, "d M Y H:i")."\n" : '' ).(( $p->expiry != '0000-00-00 00:00:00' ) ? 'Expiry: '.$this->see->format->date($p->expiry, "d M Y H:i")."\n" : '' )."\"><i class=\"fa fa-clock-o\" aria-hidden=\"true\"></i></a>" : "" );

        }
        else {
          $content .= "<li id=\"p{$p->id}\" class=\"{$class}\">
          <div class=\"page\">
          <a class=\"expand\" title=\"Expand/Contract\" href=\"#\"><i class=\"fa fa-chevron-down{$faclass}\" aria-hidden=\"true\"></i></a>
          <a class=\"name accessdisabled\" href=\"#\">{$p->title}</a>
          <a class=\"preview accessdisabled\" href=\"#\"><i class=\"fa fa-pencil-square\" aria-hidden=\"true\"></i></a>
          <a class=\"move accessdisabled\" href=\"#\"><i class=\"fa fa-arrows\" aria-hidden=\"true\"></i></a>
          <a class=\"deletepage delete accessdisabled\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i></a>";

          $wugp = SeeDB::count( 'websiteusergrouppermission', ' objecttype = ? && objectid = ? ', array( 'Page', $p->id) );

          if( $wugp ) {
            $content .= "<a class=\"secure accessdisabled\" href=\"#\"><i class=\"fa fa-lock\" aria-hidden=\"true\"></i></a>";
          }

          $content .= ( $p->status  ? "<a class=\"published togglepagestatus accessdisabled\" title=\"Published\" href=\"#\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i></a>" : "<a class=\"notpublished togglepagestatus accessdisabled\" title=\"Suppressed\" href=\"#\"><i class=\"fa fa-minus\" aria-hidden=\"true\"></i></a>" );

          $content .= ( $p->visibility != 1  ? "<a class=\"hidden accessdisabled\" title=\"" . ( $p->visibility == 2  ? 'Hidden from navigation' : 'Hidden from navigation and sitemap' )."\"><i class=\"fa fa-low-vision\" aria-hidden=\"true\"></i></a>" : "" );

          $content .= ( $p->commencement != '0000-00-00 00:00:00' || $p->expiry != '0000-00-00 00:00:00'  ? "<a class=\"clock accessdisabled\" title=\"".(( $p->commencement != '0000-00-00 00:00:00' ) ? 'Commencement: '.$this->see->format->date($p->commencement, "d M Y H:i")."\n" : '' ).(( $p->expiry != '0000-00-00 00:00:00' ) ? 'Expiry: '.$this->see->format->date($p->expiry, "d M Y H:i")."\n" : '' )."\"><i class=\"fa fa-clock-o\" aria-hidden=\"true\"></i></a>" : "" );
        }

        if( isset( $this->see->SeeCMS->config["advancedEditorPermissions"] ) ) {

          if( $globalApprovalPermission >= 5 || $accessLevel >= 5 ) {
            $approval = SeeDB::count( "adminapproval", " complete = 0 && objecttype = 'page' && objectid = ? ", array( $p->id ) );

            if( $approval ) {
              $content .= "<a class=\"approval\"><i class=\"fa fa-warning\" aria-hidden=\"true\"></i>&nbsp&nbsp;Page pending approval</a>";
            }

          }

        }

      }


      if( $p->clone ) {
        $explode = explode( '-', $p->clone );
        $object = SeeDB::load( $explode[0], $explode[1] );
        $content .= "<a class=\"cloned\" href=\"../page/edit/?id={$object->id}\">Cloned from {$object->title}</a>";
      }

      $content .= "</div>";

      if( $ret ) {
        $content .= "<ul" . ( $_SESSION['SeeCMS'][$this->see->siteID]['page']["p{$p->id}"] ? ' class="open"':'' ) . ">".$ret."</ul>";
      }

      $content .= "</li>";

    }

    $order = ( $p->id ? $p->pageorder + 1 : 0 );

    if( $parentAccessLevel >= 5 || $_SESSION['seecms'][$this->see->siteID]['adminuser']['access']['pages/'] == 5 ) {
      $content .= "<li class=\"ip\"><div id=\"insertpoint-{$parentID}-{$order}\" class=\"page insertpoint\"><a class=\"name\" href=\"#\">{$subPageOf} - Click to <span class=\"create\">insert</span><span class=\"move\">move</span> the <span class=\"create\">new </span>page here</a></div></li>";
    }

    return $content;
  }

  public function adminTreeSimple( $parentID = 0, $parentName = '', $recurse = 0 ) {

    $parentID = (int)$parentID;

    $pages = SeeDB::find( 'page', ' parentid = ? && deleted = ? ORDER BY pageorder ASC ', array( $parentID, '0000-00-00 00:00:00' ) );
    foreach( $pages as $p ) {

      $ret = $this->adminTreeSimple( $p->id, $p->title, 1 );
      $class = ( ( $ret ) ? 'expand' : '' );

      $content .= "<li class=\"{$class}\"><a id=\"page-{$p->id}\" href=\"#\">{$p->title}</a><span class=\"arrow\"></span>";

      if( $ret ) {
        $content .= "<ul".(($_SESSION['SeeCMS'][$this->see->siteID]['page']["p{$p->id}"])?' class="open"':'').">".$ret."</ul>";
      }

      $content .= "</li>";

    }

    if( !$recurse ) {
      $content = "<ul>{$content}</ul>";
    }

    return( $content );
  }

  public function adminPageArray( $parentID = 0, $p = array(), $level = 0, $etitle = '' ) {

    $parentID = (int)$parentID;

    $pages = SeeDB::find( 'page', ' parentid = ? && deleted = ? ORDER BY pageorder ASC ', array( $parentID, '0000-00-00 00:00:00' ) );
    foreach( $pages as $page ) {

      $title = $etitle.(($level)?' > ':'').$page->title;
      $p[$page->id] = $title;
      $p = $this->adminPageArray( $page->id, $p, $level+1, $title );
    }

    return( $p );
  }

  public function adminTreeSession() {

    $id = $_POST['id'];
    $status = $_POST['status'];
    $_SESSION['SeeCMS'][$this->see->siteID]['page']["p{$id}"] = $status;
  }

  public function loadCreateButtons( $parentID = 0 ) {

    // Recursively check all pages to see if current admin has full access to any page

    if( $_SESSION['seecms'][$this->see->siteID]['adminuser']['access']['pages/'] >= 5 ) {
      return "<a class=\"createpage\" href=\"#\">Create page <span><i class=\"fa fa-plus-circle\" aria-hidden=\"true\"></i></span></a>
      <a class=\"cancel\" href=\"\">Cancel</a>\n";
    }

    $parentID = (int)$parentID;

    if( $_POST['id'] ) {
      $p = SeeDB::load( 'page', $_POST['id'] );

      if( $p->id ) {
        $parentID = $p->id;
      }

    }

    $pages = SeeDB::find( 'page', ' parentid = ? && deleted = ? ORDER BY pageorder ASC ', array( $parentID, '0000-00-00 00:00:00' ) );

    foreach( $pages as $p ) {
      $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( 'page', $p->id );

      if( $accessLevel >= 5 ) {
        return "<a class=\"createpage\" href=\"#\">Create page <span><i class=\"fa fa-plus-circle\" aria-hidden=\"true\"></i></span></a>
        <a class=\"cancel\" href=\"\">Cancel</a>\n";
      }

      $ret = $this->loadCreateButtons( $p->id );

      if( $ret ) {
        return $ret;
      }

    }

  }

  public function navigation( $settings ) {

    if( $settings['startAtLevel'] && !$settings['startAtParent'] ) {

      $settings['startAtParent'] = $this->see->SeeCMS->ascendants[ $settings['startAtLevel'] ];
      if( !$settings['startAtParent'] ) {
        $settings['startAtParent'] = -1;
      }
    }

    if( $settings['mode'] == 'sitemap' ) {

      $visibility = 2;
    } else {

      $visibility = 1;
    }

    $settings['levelsToGenerate'] -= 1;

    $now = date("Y-m-d H:i:s");
    $pages = SeeDB::find( 'page', ' parentid = ? && deleted = ? && status = ? && ( commencement = ? || commencement <= ? ) && ( expiry = ? || expiry >= ? ) && ( visibility <= ? ) ORDER BY pageorder ASC ', array( (int)$settings['startAtParent'], '0000-00-00 00:00:00', 1, '0000-00-00 00:00:00', $now, '0000-00-00 00:00:00', $now, $visibility ) );

    foreach( $pages as $p ) {

      $access = true;
      if( $settings['onlyShowIfUserHasAccess'] ) {
        // Check website user permission
        if( !$_SESSION['seecms'][$this->see->siteID]['adminuser']['id'] ) {
          $wugp = SeeDB::find( 'websiteusergrouppermission', ' objecttype = ? && objectid = ? ', array( 'page', $p->id ) );
          if( count( $wugp ) ) {
            $access = false;
            if( isset( $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) ) {
              foreach( $wugp as $w ) {
                if( $w->websiteusergroup->sharedWebsiteuser[$_SESSION['seecms'][$this->see->siteID]['websiteuser']['id']]->id == $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) {
                  $access = true;
                }
              }
            }
          }
        }
      }

      if( $access ) {

        $route = SeeDB::findOne( 'route', ' objecttype = ? && objectid = ? && primaryroute = ? ', array( 'Page', $p->id, 1 ) );

        if( $this->see->SeeCMS->ascendants ) {
          $selected = (( in_array( $p->id, $this->see->SeeCMS->ascendants ) ) ? true : false );
        }

        $route = (( $route->route == '/' ) ? $route->route : '/'.$route->route );
        $id = strtolower( preg_replace( "/[^a-zA-Z0-9]+/", "", $p->title ) );

        if( $settings['html'] ) {
          $page .= "<li ".(( $selected ) ? 'class="selected" ' : '' )."id=\"{$id}\"><a href=\"{$route}\">{$p->title}</a>";

          if( $settings['levelsToGenerate'] && ( $selected || $settings['mode'] == 'sitemap' || $settings['mode'] == 'allpages' ) ) {
            $settings['startAtParent'] = $p->id;
            $page .= $this->navigation( $settings );
          }

          $page .= "</li>";
        } else {

          $adfs = array();

          if( $settings['levelsToGenerate'] ) {
            $settings['startAtParent'] = $p->id;
            $subpages = $this->navigation( $settings );
          }

          if( $settings['loadADFs'] ) {

            if( $settings['loadADFs'] !== true ) {

              $adfstoload = true;
              $loadadfs = $settings['loadADFs'];
            }

            if( !$adfstoload ) {
              $adfstoload = SeeDB::find( 'adf', ' objecttype = ? ', array( 'page' ) );
              if( is_array( $adfstoload ) ) {
                foreach( $adfstoload as $adf ) {
                  $loadadfs[] = (int)$adf->id;
                }
              }
            }

            if( is_array( $loadadfs ) ) {
              $cc = new SeeCMSContentController( $this->see, $this->see->SeeCMS->language );
              $adfs = $cc->loadADFcontent( array( 'objectid' => $p->id, 'type' => 'page', 'adfs' => $loadadfs ) );
            }
          }

          $page[] = array( 'title' => $p->title, 'route' => $route, 'selected' => $selected, 'subpages' => $subpages, 'id' => $id, 'adfs' => $adfs, 'pageid' => $p->id );
        }
      }
    }

    if( $settings['html'] && $page ) {
      $page = "<ul>{$page}</ul>";
    }

    return( $page );
  }

  function adminSearch( $keyword ) {

    $pages = SeeDB::find( 'page', ' deleted = ? && title LIKE ? ORDER BY parentid, pageorder LIMIT 10 ', array( '0000-00-00 00:00:00', "%{$keyword}%" ) );
    foreach( $pages as $p ) {

      $pp = SeeDB::load( 'page', $p->parentid );
      $r[] = array( 'id' => $p->id, 'title' => $p->title, 'in' => (( $pp->title ) ? $pp->title : 'Root' ) );
    }

    return( $r );
  }

  public function loadParent( $level = null ) {

    if( $this->see->SeeCMS->object->getMeta('type') == 'page' ) {
      if( isset( $level ) ) {
        $pids = explode( ",", $this->see->SeeCMS->object->ascendants );
        if( isset( $pids[$level] ) ) {
          $p = SeeDB::load( 'page', $pids[$level] );
        } else if( isset( $pids[$level-1] ) ) {
          $p = $this->see->SeeCMS->object;
        }
      } else {
        $p = SeeDB::load( 'page', $this->see->SeeCMS->object->parentid );
      }
    }
    return( $p );
  }

  public function previousPage( $id = 0 ) {

    if( $id ) {
      $p = SeeDB::load( 'page', $id );
    } else {
      $p = $this->see->SeeCMS->object;
    }

    $np = SeeDB::findOne( 'page', ' parentid = ? && pageorder < ? && deleted = ? && status = ? && ( commencement = ? || commencement <= ? ) && ( expiry = ? || expiry >= ? ) && ( visibility <= ? ) ORDER BY pageorder DESC', array( $p->parentid, $p->pageorder, '0000-00-00 00:00:00', 1, '0000-00-00 00:00:00', $now, '0000-00-00 00:00:00', $now, 1 ) );

    if( !$np ) {
      $np = SeeDB::findOne( 'page', ' parentid = ? && deleted = ? && status = ? && ( commencement = ? || commencement <= ? ) && ( expiry = ? || expiry >= ? ) && ( visibility <= ? ) ORDER BY pageorder DESC ', array( $p->parentid, '0000-00-00 00:00:00', 1, '0000-00-00 00:00:00', $now, '0000-00-00 00:00:00', $now, 1 ) );
    }

    $route = SeeDB::findOne( 'route', ' objecttype = ? && objectid = ? ORDER BY primaryroute DESC ', array( 'page', $np->id ) );

    $npd = array( 'id' => $np->id, 'title' => $np->title, 'route' => '/'.$route->route );

    return( $npd );
  }

  public function nextPage( $id = 0 ) {

    if( $id ) {
      $p = SeeDB::load( 'page', $id );
    } else {
      $p = $this->see->SeeCMS->object;
    }

    $np = SeeDB::findOne( 'page', ' parentid = ? && pageorder > ? && deleted = ? && status = ? && ( commencement = ? || commencement <= ? ) && ( expiry = ? || expiry >= ? ) && ( visibility <= ? ) ORDER BY pageorder ', array( $p->parentid, $p->pageorder, '0000-00-00 00:00:00', 1, '0000-00-00 00:00:00', $now, '0000-00-00 00:00:00', $now, 1 ) );

    if( !$np ) {
      $np = SeeDB::findOne( 'page', ' parentid = ? && pageorder = ? && deleted = ? && status = ? && ( commencement = ? || commencement <= ? ) && ( expiry = ? || expiry >= ? ) && ( visibility <= ? ) ', array( $p->parentid, 0, '0000-00-00 00:00:00', 1, '0000-00-00 00:00:00', $now, '0000-00-00 00:00:00', $now, 1 ) );
    }

    $route = SeeDB::findOne( 'route', ' objecttype = ? && objectid = ? ORDER BY primaryroute DESC ', array( 'page', $np->id ) );

    $npd = array( 'id' => $np->id, 'title' => $np->title, 'route' => '/'.$route->route );

    return( $npd );
  }

}
