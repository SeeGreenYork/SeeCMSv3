<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeCMSController {

  var $see;

  var $config;

  var $content;
  var $adminauth;
  var $hook;

  var $language;
  var $cmsRoot;

  var $routes;

  var $ascendants;
  var $object;
  var $supportMessage;
  var $editContent;
  var $editSettings;

  var $redirect;

  var $editable;

  public function __construct( $see, $config, $install = false ) {

    $this->see = $see;

    if( !$install ) {
      if( SeeCMSSettingController::load( 'multisite' ) ) {

        $site = SeeDB::findOne( 'site', ' name = ? ', array( $_SERVER['HTTP_HOST'] ) );
        if( $site ) {

          $this->see->site = $site->id;
          $this->see->multisite = $site->route;
          $this->see->multisiteHome = $site->homeroute;
          
          if( $this->see->currentRoute == $site->route.$site->homeroute || $this->see->currentRoute == $site->homeroute ) {
            $this->see->redirect( "http://{$site->name}" );
          }
          
        } else {

          $sites = SeeDB::findAll( 'site', ' ORDER BY name ' );
          foreach( $sites as $site ) {
            if( $this->see->currentRoute == $site->route || $this->see->currentRoute == $site->route.$site->homeroute ) {
              $this->see->redirect( "http://{$site->name}" );
            }
          }
        }
      }
    }

    $this->config = $config;

    $this->redirect = $_SESSION['seecms'][$this->see->siteID]['redirect'];

    $this->editContent = array( "Edit content", "Back to live site" );
    $this->editSettings = "Page settings";

    // Reset any redirect stuff
    unset( $_SESSION['seecms'][$this->see->siteID]['redirect'] );

    $this->routes = array( 'Pages', 'Posts', 'Media', 'Downloads', 'Site users', 'Admin', 'Analytics', 'Add ons' );

    if( $config['cmsRoot'] ) {
      $this->cmsRoot = $config['cmsRoot'];
    } else {
      $this->cmsRoot = 'cms/';
    }

    if( $config['databaseSessions'] ) {
      $sessionController = new SeeCMSSessionController( $see, $config['databaseSessionsMaxLifetime'] );

      session_set_save_handler(
        array($sessionController, 'open'),
        array($sessionController, 'close'),
        array($sessionController, 'read'),
        array($sessionController, 'write'),
        array($sessionController, 'destroy'),
        array($sessionController, 'gc')
      );

      // the following prevents unexpected effects when using objects as save handlers
      register_shutdown_function('session_write_close');
    }

    if( $config['language'] ) {
      $this->language = $config['language'];
    } else {
      $this->language = 'en';
    }

    $this->supportMessage = $config['supportMessage'];

    $this->content = new SeeCMSContentController( $see, $this->language );
    $this->adminauth = new SeeCMSAdminAuthenticationController( $this->see );
    $this->hook = new SeeCMSHooksController( $this->see );

    if( $install ) {

      $this->install();
    }
  }

  public function routeManager( $r, $type ) {

    // Logout if necessary
    if( $_GET['seecmsLogout'] ) {
      $this->adminauth->logout();
    }

    if( $type == 'Dynamic' ) {
      $route = $this->dynamicRouteManager( $r );
    } else {
      $route = $this->staticRouteManager( $r );
    }

    return( $route );
  }

  private function dynamicRouteManager( $r ) {

    if( !$r->primaryroute ) {
      $actualRoute = SeeDB::findOne( 'route', ' objecttype = ? && objectid = ? && primaryroute = ? ', array( $r->objecttype, $r->objectid, 1 ) );
      if( $actualRoute ) {
        $actualRoute->route = str_replace( $this->see->multisite, "", $actualRoute->route );
        $this->see->redirect( "/{$this->see->rootURL}{$actualRoute->route}", 301 );
      }
    }

    if( $this->see->customContentLoading[ $r->objecttype ] ) {

      $data = $this->see->plugins[ $this->see->customContentLoading[ $r->objecttype ][ 'plugin' ] ]->{$this->see->customContentLoading[ $r->objecttype ][ 'method' ]}( $r );
      $route = $data['route'];
      $this->content->objectType = $data['objectType'];
      $this->content->objectID = $data['objectID'];
      $this->editContent = $data['objectEditContent'];
      $this->editSettings = $data['objectEditSettings'];

      $this->editable = (( $this->adminauth->checkAccess( $data['editableAction'], null, false ) ) ? 1 : 0 );

    } else {

      $ob = SeeDB::load( $r->objecttype, $r->objectid );
      $this->object = $ob;
      $this->object->title = (( $this->object->title ) ? $this->object->title : $this->object->name );
      $this->object->htmltitle = (( $this->object->htmltitle ) ? $this->object->htmltitle : $this->see->siteTitle." - ".$this->object->title );

      // Check website user permission
      $access = true;
      if( !isset( $_SESSION['seecms'][$this->see->siteID]['adminuser']['id'] ) || !$_SESSION['seecms'][$this->see->siteID]['adminuser']['id'] ) {
        $wugp = SeeDB::find( 'websiteusergrouppermission', ' objecttype = ? && objectid = ? ', array( $r->objecttype, $r->objectid ) );
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

      if( !$access ) {
        
        if( !$access && isset( $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) && isset( $this->config["restrictedWebsiteUserPage"] ) ){
          
          $restrictedSettings = $this->config["restrictedWebsiteUserPage"];
          
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
        

        if( $this->config['websiteUserLoginPage'] ) {

          if( $this->config['websiteUserLoginPage'][0] == '/' ) {
            $redirect = substr_replace( $this->config['websiteUserLoginPage'], '/'.$this->see->rootURL, 0, 1 );
          }

          $_SESSION['restrictedRouteRequest'] = "/" . SeeRouteController::getCurrentRoute() . ( $_SERVER["QUERY_STRING"] ? "?{$_SERVER["QUERY_STRING"]}" : "" );
          $this->see->redirect( $redirect );
        }

        if( isset( $this->config['restricted404page'] ) ) {
          echo file_get_contents( $this->config['restricted404page'] );
          die;
        }
        else {
          die( 'Restricted' );
        }
      }
      else {

        if( $this->config['websiteUserLoginPage'][0] == '/' ) {
          $lp = substr_replace( $this->config['websiteUserLoginPage'], ( $this->see->rootURL ? $this->see->rootURL : "/" ), 0, 1 );
          $lp = explode( '?', $lp );
          $lp = $lp[0];
        }

        if( $lp != SeeRouteController::getCurrentRoute() ) {
          unset( $_SESSION['restrictedRouteRequest'] );
        }
      }

      if( $ob->getMeta( 'type' ) == 'page' ) {
        $this->ascendants = explode( ",", $ob->ascendants );
        $this->ascendants[] = $ob->id;
      }
      else if( $ob->getMeta( 'type' ) == 'post' ) {
        $category = $ob->sharedCategory;

        if( count( $category ) ) {

          if( (int)$_GET['cat'] ) {
            $firstCategory = $ob->sharedCategory[(int)$_GET['cat']];

            if( $ob->sharedCategory[(int)$_GET['cat']]->template ) {
              $ob->template = $firstCategory->template;
            }
          }
          else {
            $firstCategory = current( $category );
          }

          $cPageID = $firstCategory->page_id;

        }
        else if( $ob->posttype->page_id ) {

          $cPageID = $ob->posttype->page_id;
        }

        if( $cPageID ) {

          $categoryPage = SeeDB::load( 'page', $cPageID );
          $ob->ascendants = $categoryPage->ascendants;
          $this->ascendants = explode( ",", $categoryPage->ascendants );
          $this->ascendants[] = $categoryPage->id;
        }
      }

      $route['label'] = (( $ob->htmltitle ) ? $ob->htmltitle : $ob->title );
      $route['level'] = SeeHelperController::countPathLevels( $r->route );
      $route['template'][0] = $ob->template;

      if( is_array( $this->see->templateManager ) ) {
        foreach( $this->see->templateManager as $tm ) {
          if( $tm['plugin'] ) {
            $RMPlugin = $this->see->plugins[$tm['plugin']];
            $RMMethod = $tm['method'];
            $route['template'][0] = $RMPlugin->$RMMethod( $ob );
          }
        }
      }

      $this->content->objectType = $r->objecttype;
      $this->content->objectID = $r->objectid;
      $this->editable = (( $this->adminauth->checkAccess( 'action-content-edit', null, false ) ) ? 1 : 0 );
      $status = (( $this->editable ) ? '0,1' : '1' );

      if( $ob->status == 0 && isset( $ob->status ) && !$this->editable ) {

        SeeRouteController::http404();
      }

      if( $ob->redirect ) {

        $obRedirect = $this->content->loadLinkDetails( $ob->redirect );
        $redirect = $obRedirect['route'];

        if( $this->see->multisite ) {
          $site = SeeDB::load( 'site', $obRedirect['object']->site_id );
          $redirect = str_replace( $site->route, "", $redirect );
        }

        if( $redirect[0] == '/' ) {
          $redirect = substr_replace( $redirect, '/'.$this->see->rootURL, 0, 1 );
        }

        $this->see->redirect( $redirect );
      }

      // Get admin access level
      if( isset( $_SESSION['seecms'][$this->see->siteID]['adminuser']['id'] ) && $_SESSION['seecms'][$this->see->siteID]['adminuser']['id'] ) {
        $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( $r->objecttype, $r->objectid );
      }

      if( $_GET['preview'] && $accessLevel != 50 ) {

        // Check if in approval
        $adminapproval = SeeDB::findOne( 'adminapproval', ' objecttype = ? && objectid = ? && complete = ? ', array( $r->objecttype, $r->objectid, 0 ) );
      }

      // Collect any content we need
      if( $ob->clone ) {
        $clone = explode( '-', $ob->clone );
        $contentID = $clone[1];
        $this->editable = false;
      } else {
        $contentID = $r->objectid;
      }

      $content = SeeDB::find( 'content', ' objecttype = ? && objectid = ? && language = ? && status IN ( '.$status.' ) ORDER BY contentcontainer_id ASC, status ASC ', array( $r->objecttype, $contentID, $this->language ) );
      foreach( $content as $c ) {

        if( !isset( $route['content']['content'.$c->contentcontainer->id] ) ) {
          $method = str_replace( " ", "", $c->contentcontainer->contenttype->type );
          $method[0] = strtolower( $method[0] );
          $route['content']['content'.$c->contentcontainer->id] = $this->content->$method( $c->content, (( $this->editable && $_GET['preview'] && !$adminapproval ) ? 1 : 0 ), $c->contentcontainer->id, $c->status, $c->contentcontainer->contenttype->fields, $c->contentcontainer->contenttype->settings, $accessLevel );
        }
      }

      $contentcontainer = SeeDB::findAll( 'contentcontainer' );
      foreach( $contentcontainer as $c ) {

        $contentApp = SeeDB::find( 'contentappend', ' objecttype = ? && objectid = ? && language = ? && contentcontainer_id = ? ', array( $r->objecttype, $contentID, $this->language, $c->id ) );
        foreach( $contentApp as $ca ) {
          if( $ca->position == 0 ) {
            if(!$route['content']['content'.$c->id]) {
              $emptyContent[$c->id] = 1;
            }
            $route['content']['content'.$c->id] = $ca->content.$route['content']['content'.$c->id];
          } else if( $ca->position == 1 ) {
            $route['content']['content'.$c->id] = $ca->content;
          } else if( $ca->position == 2 ) {
            if(!$route['content']['content'.$c->id]) {
              $emptyContent[$c->id] = 1;
            }
            $route['content']['content'.$c->id] .= $ca->content;
          }
        }
      }
    }

    // Load any empty content parts and include edit bar
    if( $this->editable ) {

      $this->see->html->css( 'siteoverlay.css', '', '/seecms/css/' );
      $this->see->html->js( 'siteoverlay.js', '', '/seecms/js/' );
      $this->see->html->css( 'font-awesome.min.css', 'screen', '/seecms/css/' );

      if( $_GET['preview'] ) {
        //$contentcontainer = SeeDB::findAll( 'contentcontainer' );
        foreach( $contentcontainer as $c ) {
          if( !isset( $route['content']['content'.$c->id] ) || $emptyContent[$c->id] ) {
            $method = str_replace( " ", "", $c->contenttype->type );
            $method[0] = strtolower( $method[0] );

            $route['content']['content'.$c->id] = $this->content->$method( '', (( $this->editable && $_GET['preview'] && !$adminapproval ) ? 1 : 0 ), $c->id, 1, $c->contenttype->fields, $c->contenttype->settings, $accessLevel ).$route['content']['content'.$c->id];
          }
        }
      }
    }

    /* Add default meta */
    $this->see->html->meta( array('name' => 'generator', 'content' => 'SeeCMS - seecms.net') );
    $this->see->html->meta( array('name' => 'description', 'content' => $ob->metadescription) );
    $this->see->html->meta( array('name' => 'keywords', 'content' => $ob->metakeywords) );

    /* Log analytics */
    SeeCMSAnalyticsController::logVisit( $r->objecttype, $r->objectid, $this->see->siteID );

    return( $route );
  }

  private function staticRouteManager( $r ) {

    $currentCMSSection = $this->currentCMSSection( $this->see->currentRoute );

    if( $currentCMSSection != $this->see->currentRoute && $this->see->currentRoute != "{$this->cmsRoot}/login/" ) {

      // Check user is logged in
      if( isset( $_SESSION['seecms'][$this->see->siteID]['adminuser']['id'] ) ) {

        // Check user has access to requested bit
        $this->adminauth->checkAccess( $currentCMSSection );

      } else {
        $this->see->redirect( "/{$this->see->rootURL}{$this->cmsRoot}/login/" );
      }

    } else if( $this->see->currentRoute == "{$this->cmsRoot}/login/" && isset( $_SESSION['seecms'][$this->see->siteID]['adminuser']['id'] ) ) {
      $this->see->redirect( "../" );
    }

    return( $r );
  }

  private function currentCMSSection( $route ) {

    $route = str_replace( $this->see->prepareRoute( $this->cmsRoot ), '', $route );
    return( $route );
  }

  public static function makeRoute( $title, $objectID, $objectType, $parentRoute, $primary = 1 ) {

    $route = $parentRoute.strtolower( SeeFormatController::url( $title )."/" );

    // Check page doesn't already have this route
    $cpr = SeeDB::findOne( 'route', ' route = ? && objectID = ? && objectType = ? ', array( $route, $objectID, $objectType ) );
    if( $cpr ) {
      if( !$cpr->primary && $primary ) {
        SeeDB::exec( " UPDATE route SET primaryroute = 0 WHERE objecttype = ? && objectid = ? ", array( $objectType, $objectID ) );
        $cpr->primaryroute = 1;
        SeeDB::store( $cpr );
      }
      return( $route );
    }

    // This page route
    $a = 1;
    while( SeeDB::findOne( 'route', ' route = ? && ( objectID != ? || objectType != ? ) ', array( $route, $objectID, $objectType ) ) ) {
    //while( SeeDB::findOne( 'route', ' route = ? ', array( $route ) ) ) {
      $route = $parentRoute.strtolower( SeeFormatController::url( $title ).$a."/" );
      $a++;
    }

    // Insert route
    SeeCMSController::createRoute( $route, $objectID, $objectType, $primary );

    return( $route );
  }

  public static function getSetting( $setting ) {

    $s = SeeDB::findOne( 'setting', ' name = ? ', array( $setting ) );

    return( $s->value );
  }

  public static function createRoute( $route, $objectID, $objectType, $primary ) {

    // Check if this is a primary route, and make the others secondary
    if( $primary ) {
      SeeDB::exec( " UPDATE route SET primaryroute = 0 WHERE objecttype = ? && objectid = ? ", array( $objectType, $objectID ) );
    }

    // Insert route
    $r = SeeDB::dispense( 'route' );
    $r->route = $route;
    $r->objectid = $objectID;
    $r->objecttype = strtolower( $objectType );
    $r->primaryroute = $primary;
    SeeDB::store( $r );
  }

  public function addCMSRoute( $name ) {

    $this->routes[] = $name;
  }

  public function outputManager( $o ) {

    // Needs correct permission checking
    //$editable = (( $this->adminauth->checkAccess( 'action-content-edit', null, false ) ) ? 1 : 0 );

    $preview = (( $_GET['preview'] ) ? '' : '?preview=1' );

    if( !$_GET['preview'] && $this->editable ) {
      $editButton = "<div class=\"see-cms-toolbar\"><div class=\"button\"><div class=\"see-cms-collapse\"><i class=\"fa fa-chevron-circle-up\" aria-hidden=\"true\"></i></div><div class=\"inner\"><a class=\"see-cms-tool\"><div class=\"nav-icon-sidebar\"><span></span><span></span><span></span><span></span></div></a><a class=\"see-cms-logo\"></a></div></div>".(( is_array( $this->editContent ) ) ? "<div class=\"toolbar-content\"><div class=\"inner\"><a class=\"option o1\" href=\"/{$this->see->rootURL}{$this->cmsRoot}/\" id=\"\"><span><i class=\"fa fa-home\" aria-hidden=\"true\"></i></span>CMS Home</a><a class=\"option o2\" href=\"./{$preview}\" id=\"see-cms-edit\" ><span><i class=\"fa fa-pencil-square\" aria-hidden=\"true\"></i></span>{$this->editContent[0]}</a>":"").(( $this->editSettings )?"<a class=\"option o3\" href=\"/{$this->see->rootURL}{$this->cmsRoot}/{$this->content->objectType}/edit/?id={$this->content->objectID}\" id=\"see-cms-settings\" ><span><i class=\"fa fa-cog\" aria-hidden=\"true\"></i></span>{$this->editSettings}</a>":"")."<a class=\"option o1\" href=\"?seecmsLogout=1\" id=\"\"><span><i class=\"fa fa-power-off\" aria-hidden=\"true\"></i></span>Logout</a></div></div></div>";
    } else if( $_GET['preview'] && $this->editable ) {
      $editButton = "<div class=\"see-cms-toolbar\"><div class=\"button\"><div class=\"see-cms-collapse\"><i class=\"fa fa-chevron-circle-up\" aria-hidden=\"true\"></i></div><div class=\"inner\"><a class=\"see-cms-tool\"><div class=\"nav-icon-sidebar\"><span></span><span></span><span></span><span></span></div></a><a class=\"see-cms-logo\"></a></div></div>".(( is_array( $this->editContent ) ) ? "<div class=\"toolbar-content\"><div class=\"inner\"><a class=\"option o1\" href=\"/{$this->see->rootURL}{$this->cmsRoot}/\" id=\"\"><span><i class=\"fa fa-home\" aria-hidden=\"true\"></i></span>CMS Home</a><a class=\"option o2\" href=\"./{$preview}\" id=\"see-cms-edit\" ><span><i class=\"fa fa-desktop\" aria-hidden=\"true\"></i></span>{$this->editContent[1]}</a>":"").(( $this->editSettings )?"<a class=\"option o3\" href=\"/{$this->see->rootURL}{$this->cmsRoot}/{$this->content->objectType}/edit/?id={$this->content->objectID}\" id=\"see-cms-settings\" ><span><i class=\"fa fa-cog\" aria-hidden=\"true\"></i></span>{$this->editSettings}</a>":"")."<a class=\"option o1 hideedit\" href=\"#\" id=\"see-cms-hideedit\" ><span><i class=\"fa fa-low-vision\" aria-hidden=\"true\"></i></span>Hide editing controls</a><a class=\"option o2\" href=\"?seecmsLogout=1\" id=\"\"><span><i class=\"fa fa-power-off\" aria-hidden=\"true\"></i></span>Logout</a></div></div></div>";
    }

    $o = str_replace( '<SEECMSEDIT>', $editButton, $o );

    return( $o );
  }

  public function breadcrumb() {

  }

  public function http404() {

    $route = trim( $this->see->currentRoute, '/' );
    $route = str_replace( $this->see->multisite, '', $route );

    if ( preg_match( '/^images\/uploads\/([0-9]{1,10})\/([0-9]{1,5})\/(.*)\.(jpg|png|gif|jpeg)$/', $route, $img )) {

      $route = "images/uploads/img-{$img[1]}-{$img[2]}.{$img[4]}";
    }

    if ( preg_match( '/^images\/uploads\/img-([0-9]{1,10})-([0-9]{1,5})\.(jpg|png|gif|jpeg)$/', $route, $img )) {

      $media = new SeeCMSMediaController( $this->see );
      $is = SeeDB::findOne( 'imagesize', ' ( theme = ? || theme = ? ) && ( id = ? || identifier = ? ) ORDER BY id ', array( '', $this->see->theme, $img[1], $img[1] ) );

      if( $is && file_exists( "images/uploads/img-original-{$img[2]}.{$img[3]}" ) ) {

        $media->seeimage = new SeeImageController();
        $media->createImageSize( "images/uploads/img-original-{$img[2]}.{$img[3]}", $is, $img[2], $img[3] );
        $this->see->redirect( '/'.$this->see->rootURL.$route."?v=".rand(1,10000) );
      }
    }
    
    if( isset( $this->config['http404'] ) ) {
      
      $HTTP404Controller = $this->config['http404']['controller']."Controller";
      $HTTP404Controller = new $HTTP404Controller( $this->see );
      $HTTP404Controller->{$this->config['http404']['method']}();
    }

    if( isset( $this->config['http404page'] ) ) {

      http_response_code(404);
      echo file_get_contents( $this->config['http404page'] );
      die();
    }
  }

  private function install() {

    if( $_POST['submit'] ) {

      $this->see->dbConnect( $_POST['databasehost'], $_POST['databasename'], $_POST['databaseusername'], $_POST['databasepassword'] );

      $sql = file_get_contents( "../plugin/SeeCMS/seecms.sql" );

      try {
        SeeDB::exec( $sql );
      } catch (Exception $e) {
        $this->see->redirect( './?error=db' );
      }

      // Make an admin user
      $name = $_POST['name'];
      $email = $_POST['email'];
      $password = $_POST['password'];
      $this->adminauth->update( array( 'name' => $name, 'email' => $email, 'password' => $password, 'level' => 1 ) );

      file_put_contents( '../plugin/SeeCMS/install.txt', "Installed on: ".date("Y-m-d H:i:s") );

      // UPDATE config
      $config = file_get_contents( "../custom/config.php" );
      $replace = array( '[CMSROOT]', '[ROOTURL]', '[PUBLICFOLDER]', '[DBHOST]', '[DBNAME]', '[DBUSERNAME]', '[DBPASSWORD]', '[THEME]', '[AESKEY]', '[SITEID]', '[SITETITLE]', '[CMSSUPPORTMESSAGE]' );
      $with = array( $_POST['cmsurl'], $_POST['siteurl'], $_POST['publicfolder'], $_POST['databasehost'], $_POST['databasename'], $_POST['databaseusername'], $_POST['databasepassword'], $_POST['theme'], bin2hex( openssl_random_pseudo_bytes( 16 ) ), base64_encode( openssl_random_pseudo_bytes( 32 ) ), str_replace( "'", "\\'", $_POST['sitetitle'] ), str_replace( "'", "\\'", nl2br( $_POST['supportmessage'] ) ) );
      $config = str_replace( $replace, $with, $config );
      file_put_contents( "../custom/config.php", $config );

      // Install theme
      if( file_exists( "../custom/{$_POST['theme']}/install/{$_POST['theme']}.sql" ) ) {
        SeeDB::exec( file_get_contents( "../custom/{$_POST['theme']}/install/{$_POST['theme']}.sql" ) );
        unlink( "../custom/{$_POST['theme']}/install/{$_POST['theme']}.sql" );
      }

      if( file_exists( "../custom/{$_POST['theme']}/install/manifest.json" ) ) {
        $files = json_decode( file_get_contents( "../custom/{$_POST['theme']}/install/manifest.json" ) );

        foreach( $files as $f ) {
          rename( $f->from, $f->to );
        }
      }

      if( $_POST['themestuff'] == 'Yes' ) {
        SeeDB::exec( file_get_contents( "../custom/{$_POST['theme']}/install/{$_POST['theme']} Sample.sql" ) );
        unlink( "../custom/{$_POST['theme']}/install/{$_POST['theme']} Sample.sql" );

        if( file_exists( "../custom/{$_POST['theme']}/install/manifest Sample.json" ) ) {
          $files = json_decode( file_get_contents( "../custom/{$_POST['theme']}/install/manifest Sample.json" ) );

          foreach( $files as $f ) {
            rename( $f->from, $f->to );
          }
        }
      }

      include( "../custom/{$_POST['theme']}/install/install.php" );

      @mkdir( 'css' );
      @mkdir( 'js' );
      @mkdir( 'images' );
      @mkdir( 'images/uploads' );

      $emailTemplate = SeeDB::findOne('setting', " name = 'email' ");
      $link1 = 'http'.((( !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' )) ? 's' : '').'://'."{$_SERVER['HTTP_HOST']}/{$_POST['siteurl']}";
      $link2 = 'http'.((( !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' )) ? 's' : '').'://'."{$_SERVER['HTTP_HOST']}/{$_POST['siteurl']}{$_POST['cmsurl']}/";

      $emailContent = "<h2>SeeCMS setup complete</h2><hr /><p>Please visit the link below to view your site:<br /><a href=\"{$link1}\">{$link1}</a></p><p>Or this link to login to your CMS:<br /><a href=\"{$link2}\">{$link2}</a></p>";

      $email = str_replace( '<EMAILCONTENT>', $emailContent, $emailTemplate->value );

      $seeemail = new SeeEmailController();
      $seeemail->sendHTMLEmail( $_POST['email'], $_POST['email'], $email, 'SeeCMS setup complete' );

      include '../plugin/SeeCMS/installdone.php';
      die();

    } else {
      include '../plugin/SeeCMS/install.php';
      die();
    }
  }
}
