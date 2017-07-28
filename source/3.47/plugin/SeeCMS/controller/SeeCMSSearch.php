<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeCMSSearchController {

  var $see;
  
  public function __construct( $see ) {
  
    $this->see = $see;
  }
  
  public function search( $config = array() ) {
    
    /* Object type exclusions */
    if( is_array( $config['excludeObjects'] ) ) {
      $excludeObjects = $config['excludeObjects'];
    }
    else {
      $excludeObjects = array();
    }
    
    $excludeObjects[] = 'pagedeleted';
    
    /* Check total page or post exclusion */
    if( isset( $config['exclusion']['page'] ) && $config['exclusion']['page'] == 'all' ) {
      $cWhere .= " && objecttype != 'page'";
    }
    
    if( isset( $config['exclusion']['post'] ) && $config['exclusion']['post'] == 'all' ) {
      $cWhere .= " && objecttype != 'post'";
    }
    
    $c = SeeDB::find( 'content', ' ( content LIKE ? || content LIKE ? )'.$cWhere.' GROUP BY objecttype, objectid ', array( "%{$_GET['search']}%", "%".htmlentities($_GET['search'])."%" ) );
    
    foreach( $c as $cr ) {
      
      $id = $cr->objecttype.$cr->objectid;
      
      if( !isset( $searchresults[$id] ) && !in_array( $cr->objecttype, $excludeObjects ) ) {
        
        $x = new stdClass();
        $x->objecttype = $cr->objecttype;
        $x->objectid = $cr->objectid;
        
        if( $cr->contentcontainer->contenttype->type == 'Rich Text' ) {
          $x->content = trim( str_replace( "&nbsp;", " ", strip_tags( str_replace( ">", "> ",$cr->content ) ) ) );
        }
        
        $searchresults[$id] = $x;
      }
    }
  
    $c = SeeDB::find( 'adfcontent', ' ( content LIKE ? || content LIKE ? )'.$cWhere.' GROUP BY objecttype, objectid ', array( "%{$_GET['search']}%", "%".htmlentities($_GET['search'])."%" ) );
    
    foreach( $c as $cr ) {
      
      $id = $cr->objecttype.$cr->objectid;
      
      if( !isset( $searchresults[$id] ) && !in_array( $cr->objecttype, $excludeObjects ) && $cr->objecttype != 'postdeleted' ) {
        
        $x = new stdClass();
        $x->objecttype = $cr->objecttype;
        $x->objectid = $cr->objectid;
        
        $searchresults[$id] = $x;
      }
    }
  
    
    /* Check total page or post exclusion */
    if( isset( $config['exclusion']['page'] ) && $config['exclusion']['page'] != 'all' ) {
      
      $c = SeeDB::find( 'page', ' ( title LIKE ? || metadescription LIKE ? || metakeywords LIKE ? ) && status = ? && deleted = ? && ( commencement = ? || commencement <= ? ) && ( expiry = ? || expiry <= ? ) ', array( "%{$_GET['search']}%", "%{$_GET['search']}%", "%{$_GET['search']}%", 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', date("Y-m-d H:i:s"), '0000-00-00 00:00:00', date("Y-m-d H:i:s") ) );
      
      foreach( $c as $cr ) {
        
        $id = "page{$cr->id}";
        
        if( !isset( $searchresults[$id] ) ) {
          
          $x = new stdClass();
          $x->objecttype = 'page';
          $x->objectid = $cr->id;
          
          $searchresults[$id] = $x;
        }
      }
    }
    
    /* Check total page or post exclusion */
    if( isset( $config['exclusion']['post'] ) && $config['exclusion']['post'] != 'all' ) {
  
      $c = SeeDB::find( 'post', ' ( title LIKE ? || standfirst LIKE ? || metadescription LIKE ? || metakeywords LIKE ? ) && status = ? && deleted = ? && isfolder = ? && ( commencement = ? || commencement <= ? ) && ( expiry = ? || expiry <= ? ) ', array( "%{$_GET['search']}%", "%{$_GET['search']}%", "%{$_GET['search']}%", "%{$_GET['search']}%", 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', date("Y-m-d H:i:s"), '0000-00-00 00:00:00', date("Y-m-d H:i:s") ) );
      
      foreach( $c as $cr ) {
        
        $id = "post{$cr->id}";
        
        if( !isset( $searchresults[$id] ) ) {
          
          $x = new stdClass();
          $x->objecttype = 'post';
          $x->objectid = $cr->id;
          
          $searchresults[$id] = $x;
        }
      }
    }
  
    $c = SeeDB::find( 'download', ' ( name LIKE ? || description LIKE ? ) && status = ? && isfolder = ? ', array( "%{$_GET['search']}%", "%{$_GET['search']}%", 1, 0 ) );
    
    foreach( $c as $cr ) {
      
      $id = "download{$cr->id}";
      
      if( !isset( $searchresults[$id] ) ) {
        
        $x = new stdClass();
        $x->objecttype = 'download';
        $x->objectid = $cr->id;
        $x->content = $cr->description;
        $x->type = $cr->type;
        $x->filesize = SeeFileController::filesize( filesize( "../custom/files/download-{$cr->id}.{$cr->type}" ) );
        
        $searchresults[$id] = $x;
      }
    }
    
    if( is_array( $searchresults ) ) {
      foreach( $searchresults as $result ) {
      
        $ob = SeeDB::load( $result->objecttype, $result->objectid );
        
        if( $result->objecttype == 'page' && ( isset( $config['inclusion']['page'] ) || isset( $config['exclusion']['page'] ) ) ) {

          $ascendants = explode( ',', $ob->ascendants );
          
          if( isset( $config['inclusion']['page'] ) && is_array( $config['inclusion']['page'] ) ) {
            
            $exclude = true;
            
            foreach( $config['inclusion']['page'] as $inclusion ) {
              
              if( in_array( $inclusion, $ascendants ) ) {
                
                $exclude = false;
                break;
              }
            }
          }
          
          if( isset( $config['exclusion']['page'] ) && is_array( $config['exclusion']['page'] ) ) {
            
            foreach( $config['exclusion']['page'] as $exclusion ) {
              
              if( in_array( $exclusion, $ascendants ) ) {
                
                $exclude = true;
                break;
              }
            }
          }
        }
        
        if( $result->objecttype == 'post' && isset( $config['inclusion']['postCategory'] ) ) {
          
          if( is_array( $config['inclusion']['postCategory'] ) ) {
            
            $exclude = true;
            
            foreach( $config['inclusion']['postCategory'] as $inclusion ) {
              
              if( $ob->sharedCategory[$inclusion] ) {
                
                $exclude = false;
                break;
              }
            }
          }
        }
        
        if( $result->objecttype == 'download' && isset( $config['inclusion']['downloadFolder'] ) ) {
          
          if( is_array( $config['inclusion']['downloadFolder'] ) ) {
            
            $exclude = true;
            
            foreach( $config['inclusion']['downloadFolder']['folder'] as $folderSet ) {
            
              if( $folderSet['subfolders'] ) {

                $d = new SeeCMSDownloadController( $this->see );
                $folders = $d->downloadFolderArray( $folderSet['id'], array(), 0, '', true );
              } else {
                
                $folders = array( $folderSet['id'] );
              }
              
              foreach( $folders as $inclusion ) {
              
                if( $ob->parentid == $inclusion ) {
                  
                  $exclude = false;
                  break;
                }
              }
            }
          }
        }
        
        if( !$exclude ) {
          $access = true;
          
          // Check website user permission
          $wugp = SeeDB::find( 'websiteusergrouppermission', ' objecttype = ? && objectid = ? ', array( $result->objecttype, $result->objectid ) );
          if( count( $wugp ) ) {
            $access = false;
            if( isset( $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) ) {
              foreach( $wugp as $w ) {
                if( $w->websiteusergroup->sharedWebsiteuser[$_SESSION['seecms'][$this->see->siteID]['websiteuser']['id']]->id == $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) {
                  $access = true;
                  break;
                }
              }
            }
          }
          
          // Check it's live
          if( $result->objecttype == 'page' || $result->objecttype == 'post' ) {
            
            if( $ob->deleted != '0000-00-00 00:00:00' || ( $ob->commencement > date("Y-m-d H:i:s") && $ob->commencement != '0000-00-00 00:00:00' ) || ( $ob->expiry < date("Y-m-d H:i:s")  && $ob->expiry != '0000-00-00 00:00:00' ) ) {
              
              $access = false;
            }
          }
          
          if( !$ob->status ) {
            
            $access = false;
          }
        }
        
        
        if( $access && !$exclude ) {
        
          $r = SeeDB::findOne( 'route', ' objecttype = ? && objectid = ? && primaryroute = ? ', array( $result->objecttype, $result->objectid, 1 ) );
          if( !$r ) {
            
            if( $result->objecttype == 'download' ) {
              
              $r = new stdClass();
              $r->route = "seecmsfile/?id={$result->objectid}";
            }
          }
            
          if( $ob->metadescription ) {
            $result->content = $ob->metadescription;
          }
          
          if( !$result->content || strlen( $result->content <= 40 ) ) {
            
            $content = '';
            
            $pc = SeeDB::find( 'content', ' objecttype = ? && objectid = ? ORDER BY contentcontainer_id ASC LIMIT 3 ', array( $result->objecttype, $result->objectid ) );
            if( !$pc ) {
              
              $pc2 = SeeDB::findOne( 'adfcontent', ' objecttype = ? && objectid = ? ORDER BY adf_id ASC ', array( $result->objecttype, $result->objectid ) );
              if( $pc2 ) {
                $pcontent = json_decode( $pc2->content, true );
                if( is_array( $pcontent ) ) {
                  while( strlen( $pc->content ) < 200 ) {
                    
                    $npcs = next( $pcontent );
                    if( $npcs !== false ) {
                      
                      foreach( $npcs as $npc ) {
                        if( strpos( $npc, 'page-' ) === false && strpos( $npc, 'download-' ) === false && strpos( $npc, 'post-' ) === false ) {
                          $content .= "{$npc} ";
                        }
                      }
                      
                    } else {
                      break;
                    }
                  }
                }
              }
            } else {
              foreach( $pc as $pcp ) {
                $content .= trim( str_replace( "&nbsp;", " ", strip_tags( str_replace( ">", "> ",$pcp->content ) ) ) );
              }
            }
            
            $result->content = $content;
          }

          if( $r->route ) {
            
            if( strlen( $result->content ) > 200 ) {
              $result->content = substr( strip_tags( str_replace( ">", "> ", html_entity_decode( $result->content ) ) ), 0, 200 )." ...";
            }
            
            $data['searchresults'][] = array( 'title' => (($ob->title)?$ob->title:$ob->name), 'route' => '/'.$r->route, 'content' => $result->content, 'type' => $result->type, 'filesize' => $result->filesize );
          }
        }
      }
    }
    
    if( stristr( $_GET['search'], "script" ) || stristr( $_GET['search'], "<" ) || stristr( $_GET['search'], ">" ) ) {
      $_GET['search'] = htmlentities( $_GET['search'] );
    }
    
    return( $data );
  }
  
  public function adminSearch() {
  
    $search = trim( $_POST['search'] );
    
    $page = new SeeCMSPageController( $this->see );
    $pages = $page->adminSearch( $search );
    
    if( $pages ) {
      $data = '<div class="column pages"><h2>Pages</h2>';
      foreach( $pages as $d ) {
        $data .= "<div class=\"result\"><h3><a href=\"/{$this->see->rootURL}{$this->see->SeeCMS->cmsRoot}/page/edit/?id={$d['id']}\">{$d['title']}</a></h3><p>In section: <strong>{$d['in']}</strong></p></div>";
      }
      $data .= '</div>';
    }
    
    $post = new SeeCMSPostController( $this->see );
    $posts = $post->adminSearch( $search );
    
    if( $posts ) {
      $data .= '<div class="column posts"><h2>Posts</h2>';
      foreach( $posts as $d ) {
        $data .= "<div class=\"result\"><h3><a href=\"/{$this->see->rootURL}{$this->see->SeeCMS->cmsRoot}/post/edit/?id={$d['id']}\">{$d['title']}</a></h3><p>Post date: <strong>{$d['posted']}</strong></p></div>";
      }
      $data .= '</div>';
    }
    
    $media = new SeeCMSMediaController( $this->see );
    $medias = $media->adminSearch( $search );
    
    if( $medias ) {
      $data .= '<div class="column media"><h2>Media</h2>';
      foreach( $medias as $m ) {
        $data .= "<div class=\"result\"><img src=\"/{$this->see->rootURL}images/uploads/img-139-139-{$m['id']}.{$m['type']}\" alt=\"\" /><div class=\"title\"><h3><a href=\"/{$this->see->rootURL}{$this->see->SeeCMS->cmsRoot}/media/edit/?id={$m['id']}\">{$m['name']}</a></h3><p>In folder: {$m['in']}</p></div><div class=\"clear\"></div><!--<div class=\"info\"><p>Size: x</p><p>Author: x</p><p>Uploaded: x</p></div>--></div>";
      }
      $data .= '</div>';
    }
    
    $download = new SeeCMSDownloadController( $this->see );
    $downloads = $download->adminSearch( $search );
    
    if( $downloads ) {
      $data .= '<div class="column downloads"><h2>Downloads</h2>';
      foreach( $downloads as $d ) {
        $data .= "<div class=\"result\"><h3><a href=\"/{$this->see->rootURL}{$this->see->SeeCMS->cmsRoot}/download/edit/?id={$d['id']}\"><img src=\"/{$this->see->rootURL}seecms/images/icons/{$d['type']}.png\" alt=\"\" />{$d['name']}</a></h3><p>In folder: {$d['in']}</p></div>";
      }
      $data .= '</div>';
    }
    
    $user = new SeeCMSWebsiteUserController( $this->see );
    $users = $user->adminSearch( $search );
    
    if( $users ) {
      $data .= '<div class="column users"><h2>Website users</h2>';
      foreach( $users as $u ) {
        $data .= "<div class=\"result\"><h3><a href=\"/{$this->see->rootURL}{$this->see->SeeCMS->cmsRoot}/siteusers/viewusers/editusers/?id={$u['id']}\">{$u['name']}</a></h3><p>Email: {$u['email']}<br />Organisation: {$u['organisation']}</p></div>";
      }
      $data .= '</div>';
    }
    
    if( $this->see->SeeCMS->additionalSearchObjects ) {
			
      foreach( $this->see->SeeCMS->additionalSearchObjects as $aso ) {
      
        $searchR = $this->see->plugins[$aso['plugin']]->{$aso['pluginMethod']}( $search );
        $data .= $searchR;
      }
    }
  
    return( $data );
  }
}