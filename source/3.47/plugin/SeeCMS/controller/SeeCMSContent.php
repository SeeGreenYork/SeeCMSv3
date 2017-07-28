<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeCMSContentController {

  var $see;
  var $language;
  var $objectType;
  var $objectID;

  private $temp = array();
  private $editLoaded;

  public function __construct( $see, $language = '' ) {

    $this->see = $see;
    $this->language = (( $language ) ? $language : $_POST['language'] );
  }

  private function centralEditIncludes( $loadADFJS = false ) {

    if( !is_array( $this->editLoaded ) ) {

      $this->see->html->css( 'jquery-ui.min.css', 'screen', '/seecms/js/' );
      $this->see->html->css( 'jquery-ui.theme.css', 'screen', '/seecms/js/' );
      $this->see->html->css( 'editor.css', 'screen', '/seecms/css/' );
      $this->see->html->js( array( 'file' => 'jquery-1.11.1.min.js', 'name' => 'jquery', 'snappy' => true, 'path' => '/seecms/js/' ) );
      $this->see->html->js( 'jquery-ui.min.js', '', '/seecms/js/', 'jquery-ui' );
      $this->see->html->js( 'editcontent.js', '', '/seecms/js/' );
      $this->see->html->js( 'editor.js', '', '/seecms/js/' );
      $this->see->html->js( '', "var editObjectType = '{$this->objectType}'; var editObjectID = '{$this->objectID}'; var ajaxPath = '/{$this->see->rootURL}{$this->see->SeeCMS->cmsRoot}/ajax/'; var cmsURL = '/{$this->see->rootURL}{$this->see->SeeCMS->cmsRoot}/';  var siteURL = '/{$this->see->rootURL}'; var language = '{$this->language}';", '' );

      if( $this->see->multisite ) {

        $sites = SeeDB::findAll( 'site' );
        foreach( $sites as $site ) {

          $multisite .= (($multisite)?',':'')."multisite{$site->id}:{url:\"".'http'.((( !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' )) ? 's' : '').'://'.$site->name.(($this->see->rootURL)?'/'.$this->see->rootURL:'/')."\", route:\"{$site->route}\"}";

        }

        $this->see->html->js( '', 'var multisite = {'.$multisite.'};', '' );
      }

      $this->editLoaded['central'] = 1;
    }
    
    if( $loadADFJS && !$this->editLoaded['ADFs'] ) {
      $this->see->html->js( array( 'file' => 'js.js', 'name' => 'seecmsjs', 'path' => '/seecms/js/' ) );
      $this->see->html->js( '', "var adfsets = new Array(); var adfsetsinuse = new Array(); var adflimit = new Array(); var settingsScreen = 0;", '' );
      $this->editLoaded['ADFs'] = 1;
    }
  }

  public function edit( $data = '', $skipDie = false ) {

    if( !$data ) {
      $data = $_POST;
      $skipDie = (($_POST['skipDie'])?true:false);
    } else {
      $passData = $data;
    }

    $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( $data['objectType'], $data['objectID'] );

    if( $accessLevel >= 2 ) {

      if( $data['settingsScreen'] == 0 ) {
        $content = SeeDB::findOne( 'content', ' objecttype = ? && objectid = ? && contentcontainer_id = ? && language = ? && status = ? ', array( $data['objectType'], $data['objectID'], $data['containerID'], $this->language, 0 ) );
        if( $content ) {
          $ct = $content->contentcontainer->contenttype->type;
        } else {
          $contentType = SeeDB::findOne( 'contenttype', ' type = ? ', array( $data['contentType'] ) );
          $ct = $contentType->type;

          $content = SeeDB::dispense('content');
          $content->objecttype = $data['objectType'];
          $content->objectid = (int)$data['objectID'];
          $content->language = $this->language;
          $content->contentcontainer_id = (int)$data['containerID'];
          $content->status = 0;
        }

        if( $accessLevel >= 5 ) {
          $this->completeApproval( $_POST['objectType'], $_POST['objectID'] );
        }

      } else if( $data['settingsScreen'] == 1 ) {

        $ct = 'ADF';

        $content = SeeDB::findOne( 'adfcontent', ' objecttype = ? && objectid = ? && adf_id = ? && language = ? ', array( $data['objectType'], $data['objectID'], $data['containerID'], $this->language ) );
        if( !$content->id ) {
          $content = SeeDB::dispense('adfcontent');
        }

        $content->objecttype = $data['objectType'];
        $content->objectid = (int)$data['objectID'];
        $content->language = $this->language;
        $content->adf_id = (int)$data['containerID'];
      }


      $method = str_replace( " ", "", 'edit'.$ct );
      $newContent = $this->$method( $passData );

      if( is_array( $newContent ) ) {

        $displayContent = $newContent[1];
        $newContent = $newContent[0];
      } else {

        $displayContent = $newContent;
      }

      $content->content = $newContent;
      SeeDB::store( $content );

      if( $_POST['settingsScreen'] == 0 ) {

        // Check if in approval
        $aar = SeeDB::findOne( 'adminapproval', ' objecttype = ? && objectid = ? && complete = ? ', array( $data['objectType'], $data['objectID'], 0 ) );

        if( !$aar ) {
          $return = $this->makeEditPart( $content->contentcontainer_id, $ct, $displayContent, 0, false, $accessLevel );
        }

      } else {
        $return = 'Done';
      }
    }

    if( !$skipDie ) {
      die( $return );
    }
  }

  public function apply() {

    $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( $_POST['objectType'], $_POST['objectID'] );

    $skipDie = (($_POST['skipDie'])?true:false);

    $content = SeeDB::findOne( 'content', ' objecttype = ? && objectid = ? && contentcontainer_id = ? && language = ? && status = ? ', array( $_POST['objectType'], $_POST['objectID'], $_POST['containerID'], $this->language, 0 ) );
    if( $content && $accessLevel >= 5 ) {
      $oldContent = SeeDB::findOne( 'content', ' objecttype = ? && objectid = ? && contentcontainer_id = ? && language = ? && status = ? ', array( $_POST['objectType'], $_POST['objectID'], $_POST['containerID'], $this->language, 1 ) );
      if( $oldContent ) {
        SeeDB::trash( $oldContent );
      }

      $this->completeApproval( $_POST['objectType'], $_POST['objectID'] );

      $content->status = 1;
      SeeDB::store( $content );

      $this->see->SeeCMS->hook->run( array( 'hook' => 'content-apply', 'data' => array( 'content' => $content, 'objectype' => $_POST['objectType'], 'objectid' => $_POST['objectID'] ) ) );

      $editPart = $this->makeEditPart( $content->contentcontainer_id, $content->contentcontainer->contenttype->type, $content->content, 1 );
      echo $editPart;

      if( !$skipDie ) {
        die();
      }
    }
  }

  public function discard() {

    $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( $_POST['objectType'], $_POST['objectID'] );

    $content = SeeDB::findOne( 'content', ' objecttype = ? && objectid = ? && contentcontainer_id = ? && language = ? && status = ? ', array( $_POST['objectType'], $_POST['objectID'], $_POST['containerID'], $this->language, 0 ) );
    if( $content && $accessLevel >= 2 ) {

      if( $accessLevel >= 5 ) {
        $this->completeApproval( $_POST['objectType'], $_POST['objectID'] );
      }

      $content->content = "";
      SeeDB::store( $content );

      $content = SeeDB::findOne( 'content', ' objecttype = ? && objectid = ? && contentcontainer_id = ? && language = ? && status = ? ', array( $_POST['objectType'], $_POST['objectID'], $_POST['containerID'], $this->language, 1 ) );

      $editPart = $this->makeEditPart( (($content->contentcontainer_id)? $content->contentcontainer_id: $_POST["containerID"] ), $content->contentcontainer->contenttype->type, $content->content, 1 );
      die( $editPart );
    }

  }

  public function richText( $content, $editable, $contentContainerID, $status, $fields = '', $settings = '', $accessLevel = 5 ) {

    if( $editable ) {

      $this->centralEditIncludes();

      if( !$this->editLoaded['richTextOnly'] ) {

        $this->includeRichText();
        $o .= "<div id=\"editableRTcontent\" title=\"Edit content\" style=\"display: none;\"><div id=\"seecmsMainRTE\" class=\"tinymce\"></div></div>";
        $this->editLoaded['richTextOnly'] = 1;
      }

      $o .= $this->makeEditPart( $contentContainerID, 'richText', $content, $status, $settings, $accessLevel );

    } else {
      $o = $content;
    }

    return( $o );
  }

  public function includeRichText() {

    if( !$this->editLoaded['richText'] ) {

      $styles = "{title: 'Heading 1', block: 'h1'}, {title: 'Heading 2', block: 'h2'}, {title: 'Heading 3', block: 'h3'}, {title: 'Heading 4', block: 'h4'}, {title: 'Normal text', block: 'p'}";
      $additionalStyles = SeeDB::findOne( 'setting', ' name = ? ', array( 'additionalRTEStyles' ) );
      if( $additionalStyles ) {

        $styles .= ", ".$additionalStyles->value;
      }

      $this->see->html->js( array( 'file' => '//tinymce.cachefly.net/4.1/tinymce.min.js', 'name' => 'tinymce', 'path' => '' ) );
      $this->see->html->js( 'tinymce.seecms.plugin.js', '', '/seecms/js/' );
      $this->see->html->js( '', "function initialiseTinyMCE() { tinymce.init({content_css : '/{$this->see->rootURL}seecms/css/editorcontent.css,/{$this->see->rootURL}css/editorcontent.css',selector:'.tinymce', plugins: 'hr link lists paste table noneditable SeeCMS', menu : { edit   : {title : 'Edit'  , items : 'undo redo | cut copy paste pastetext | selectall'}, table  : {title : 'Table' , items : 'inserttable tableprops deletetable | cell row column'}, format : {title : 'Other formatting', items : 'strikethrough superscript subscript | removeformat'}, insert : {title : 'Insert', items : 'insertimage insertlink inserthtml | hr'} }, style_formats: [{$styles}], toolbar: ['undo redo | styleselect | bold italic underline superscript subscript removeformat | alignleft aligncenter alignright | bullist numlist | image | link unlink '], statusbar: false, resize: false, height: '380px', inline_styles: false, object_resizing: 'table', relative_urls: false, skin_url: '/{$this->see->rootURL}seecms/css/tiny', forced_root_block : 'p', force_p_newlines : true, setup: function(editor) { editor.on('init', function(e) { $('.mce-tinymce iframe').attr('title',''); }); } }); } initialiseTinyMCE();", '' );
      $this->see->html->js( '', "function initialiseSmallTinyMCE() { tinymce.init({content_css : '/{$this->see->rootURL}seecms/css/editorcontent.css,/{$this->see->rootURL}css/editorcontent.css',selector:'.smalltinymce', plugins: 'hr link lists paste table noneditable SeeCMS', menu : { edit   : {title : 'Edit'  , items : 'undo redo | cut copy paste pastetext | selectall'}, table  : {title : 'Table' , items : 'inserttable tableprops deletetable | cell row column'}, format : {title : 'Other formatting', items : 'strikethrough superscript subscript | removeformat'}, insert : {title : 'Insert', items : 'insertimage insertlink inserthtml | hr'} }, style_formats: [{$styles}], toolbar: ['undo redo | styleselect | bold italic underline superscript subscript removeformat | alignleft aligncenter alignright | bullist numlist | image | link unlink '], statusbar: false, resize: false, height: '200px', inline_styles: false, object_resizing: 'table', relative_urls: false, skin_url: '/{$this->see->rootURL}seecms/css/tiny', forced_root_block : 'p', force_p_newlines : true }); } initialiseSmallTinyMCE();", '' );

      $this->editLoaded['richText'] = 1;
    }
  }

  public function editRichText() {
  
    $html = '';
    $content = str_replace( "/{$this->see->rootURL}", "/", $_POST['content'] );

    if( $content ) {
      $doc = new DOMDocument();

      $loaded = $doc->loadHTML( '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'.$content, LIBXML_HTML_NODEFDTD );
      if( !$loaded ) {
        $doc->loadHTML( $content );
      }
      
      foreach( $doc->childNodes as $cN ) {
        
        $this->htmlParser( $doc, array(), $cN );
      }

      $html = str_replace( array( '<html>', '</html>', '<body>', '</body>', '<head>', '</head>', '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">', '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">' ), '',  $doc->saveHTML() );
    }
    
    return( $html );
  }

  public function ADF( $content, $editable, $contentContainerID, $status, $fields, $settings, $accessLevel = 5, $settingsScreen = false, $inline = false, $skipForm = false ) {

    if( $editable ) {

      $this->centralEditIncludes( true );

      if( !$settingsScreen ) {
        $o .= $this->makeEditPart( $contentContainerID, 'ADF', $content, $status );
      } else {
        $o2 .= "<script>var settingsScreen = 1;</script>";
      }

      $content = json_decode( $content, true );

      if( !$inline ) {
        $o .= "<div class=\"editableADFcontent\" id=\"editablecontent{$contentContainerID}\" title=\"Edit content\" style=\"display: none;\">";
      }

      $o .= "<".(($skipForm)?'div':'form')." id=\"adfcontent{$contentContainerID}\">";

      $settings = explode( ",", $settings );
      foreach( $settings as $ss ) {
        
        $s = explode( "=", $ss );
        $settingsA[$s[0]] = $s[1];
      }
      $o .= "<div class=\"editableADFcontentinner ".(( $settingsA["sortable"] )?"sortable":"")."\">";

      $o .= "<span class=\"ui-helper-hidden-accessible\"><input type=\"text\"/></span>";


      $o .= "<h2>{$settingsA['title']}</h2>";

      $sets = (($settingsA['repeatable']=='true')?count( $content ):(int)$settingsA['limit']);

      for( $set = 0; $set < $sets; $set++ ) {

        $o .= "<div class=\"adfset\" id=\"adf{$contentContainerID}-adfset{$set}\">";

        $field = preg_split( '/\r\n|\r|\n/', $fields );
        foreach( $field as $f ) {

          $fd = explode( ',', $f );
          $o .= "<p>{$fd[1]}</p>";

          if( $fd[2] == 'text' ) {

            $o .= "<input type=\"text\" name=\"adf{$contentContainerID}-field{$fd[0]}-set{$set}\" value=\"".htmlentities( $content[$set][$fd[0]] )."\" />";
          }

          if( $fd[2] == 'textarea' ) {

            $o .= "<textarea name=\"adf{$contentContainerID}-field{$fd[0]}-set{$set}\">{$content[$set][$fd[0]]}</textarea>";
          }

          if( $fd[2] == 'richText' ) {

            $this->includeRichText();
            if( $see->rootURL != '/' ) {
              $replace = array( 'src="//', 'src="/css/', 'src="/', 'href="/', 'action="/', 'src="##' );
              $with = array( 'src="##', 'src="/'.$see->rootURL.'css/', 'src="/'.$see->rootURL, 'href="/'.$see->rootURL , 'action="/'.$see->rootURL, 'src="//' );
              $content[$set][$fd[0]] = str_replace( $replace, $with, $content[$set][$fd[0]] );
            }
            $o .= "<div class=\"smalltinymce\" id=\"adf{$contentContainerID}-field{$fd[0]}-set{$set}\">{$content[$set][$fd[0]]}</div>";
          }

          if( $fd[2] == 'select' ) {

            $o .= "<select name=\"adf{$contentContainerID}-field{$fd[0]}-set{$set}\">";

            $ops = explode( ';', $fd[3] );
            foreach( $ops as $op ) {

              $o .= "<option value=\"{$op}\"".(($content[$set][$fd[0]]==$op)?' selected="selected"':'').">{$op}</option>";
            }

            $o .= "</select>";
          }

          if( $fd[2] == 'image' ) {
            $m = SeeDB::load( 'media', $content[$set][$fd[0]] );
            $size = (( $fd[3] ) ? $fd[3] : '139-139' );
            $o .= "<a class=\"adfselectimage\" data-size=\"{$fd[3]}\" id=\"adf{$contentContainerID}-field{$fd[0]}-set{$set}\">Select image</a> <a class=\"adfremoveimage\" data-size=\"{$fd[3]}\" id=\"adf{$contentContainerID}-field{$fd[0]}-set{$set}-remove\">Remove image</a><div class=\"adf{$contentContainerID}-field{$fd[0]}-set{$set}\"><img src=\"/images/uploads/img-{$size}-{$m->id}.{$m->type}\" /></div>";
            $o .= "<input type=\"hidden\" name=\"adf{$contentContainerID}-field{$fd[0]}-set{$set}\" value=\"{$content[$set][$fd[0]]}\" />";
            $hasImage = true;
          }

          if( $fd[2] == 'mediaFolder' ) {
            $m = SeeDB::load( 'media', $content[$set][$fd[0]] );
            $o .= "<a class=\"adfselectmediafolder\" id=\"adf{$contentContainerID}-field{$fd[0]}-set{$set}\">Select media folder</a> <a class=\"adfremovemediafolder\" id=\"adf{$contentContainerID}-field{$fd[0]}-set{$set}-remove\">Remove media folder</a><div class=\"adf{$contentContainerID}-field{$fd[0]}-set{$set}\">{$m->name}</div>";
            $o .= "<input type=\"hidden\" name=\"adf{$contentContainerID}-field{$fd[0]}-set{$set}\" value=\"{$content[$set][$fd[0]]}\" />";
            $hasMediaFolder = true;
          }

          if( $fd[2] == 'link' ) {
            $thisLink = $this->loadLinkDetails( $content[$set][$fd[0]] );

            if( $thisLink['object'] ) {
              $displayLink = "Link: {$thisLink['name']} ({$thisLink['route']})";
            } else if( $thisLink['name'] ) {
              $displayLink = "Link: {$thisLink['name']}";
            } else {
              $displayLink = '';
            }

            $o .= "<a class=\"adfselectlink\" id=\"adf{$contentContainerID}-field{$fd[0]}-set{$set}\">Change link</a><div class=\"adf{$contentContainerID}-field{$fd[0]}-set{$set}\">{$displayLink}</div>";
            $o .= "<input type=\"hidden\" name=\"adf{$contentContainerID}-field{$fd[0]}-set{$set}\" value=\"{$content[$set][$fd[0]]}\" />";
            $hasLink = true;
          }

          if( $fd[2] == "datepicker" ){
            $o .= "<input type=\"text\" name=\"adf{$contentContainerID}-field{$fd[0]}-set{$set}\" class=\"datepicker\" value=\"{$content[$set][$fd[0]]}\" />";
          }
        }

        if( $settingsA['repeatable'] == 'true' ) {
          $o .= "<p><a class=\"deleteadfset\" data-set-container=\"{$contentContainerID}\" id=\"delete-adf{$contentContainerID}-adfset{$set}\">Delete set</a></p>";
        }

        $o .= "</div>";
      }

      $o .= "</div>";

      $o2 .= "<script>adfsets[{$contentContainerID}] = {$sets}; adfsetsinuse[{$contentContainerID}] = {$sets}; adflimit[{$contentContainerID}] = ".(int)$settingsA['limit'].";</script>";

      if( $settingsA['repeatable'] == 'true' ) {

        $o .= "<p><a class=\"adfaddanotherset\" id=\"adfaddanotherset{$contentContainerID}\">Add another set</a></p>";

        $o2 .= "<script>var emptyadf{$contentContainerID} = '<div class=\"adfset\" id=\"adf{$contentContainerID}-adfsetSEECMSADFSET\">";

        $field = preg_split( '/\r\n|\r|\n/', $fields );
        foreach( $field as $f ) {
          $fd = explode( ',', $f );
          $o2 .= "<p>{$fd[1]}</p>";

          if( $fd[2] == 'text' ) {

            $o2 .= "<input type=\"text\" name=\"adf{$contentContainerID}-field{$fd[0]}-setSEECMSADFSET\" />";
          }

          if( $fd[2] == 'select' ) {

            $o2 .= "<select name=\"adf{$contentContainerID}-field{$fd[0]}-setSEECMSADFSET\">";

            $ops = explode( ';', $fd[3] );
            foreach( $ops as $op ) {

              $o2 .= "<option value=\"{$op}\">{$op}</option>";
            }

            $o2 .= "</select>";
          }

          if( $fd[2] == 'textarea' ) {

            $o2 .= "<textarea name=\"adf{$contentContainerID}-field{$fd[0]}-setSEECMSADFSET\"></textarea>";
          }

          if( $fd[2] == 'richText' ) {

            $this->includeRichText();
            $o2 .= "<div class=\"smalltinymce\" id=\"adf{$contentContainerID}-field{$fd[0]}-setSEECMSADFSET\"></div>";
          }

          if( $fd[2] == 'image' ) {
            $size = (( $fd[3] ) ? $fd[3] : '139-139' );
            $o2 .= "<a class=\"adfselectimage\" data-size=\"{$size}\" id=\"adf{$contentContainerID}-field{$fd[0]}-setSEECMSADFSET\">Select image</a><div class=\"adf{$contentContainerID}-field{$fd[0]}-setSEECMSADFSET\"></div>";
            $o2 .= "<input type=\"hidden\" name=\"adf{$contentContainerID}-field{$fd[0]}-setSEECMSADFSET\" />";
            $hasImage = true;
          }

          if( $fd[2] == 'mediaFolder' ) {
            $o2 .= "<a class=\"adfselectmediafolder\" id=\"adf{$contentContainerID}-field{$fd[0]}-setSEECMSADFSET\">Select media folder</a> <a class=\"adfremovemediafolder\" id=\"adf{$contentContainerID}-field{$fd[0]}-setSEECMSADFSET-remove\">Remove media folder</a><div class=\"adf{$contentContainerID}-field{$fd[0]}-setSEECMSADFSET\"></div>";
            $o2 .= "<input type=\"hidden\" name=\"adf{$contentContainerID}-field{$fd[0]}-setSEECMSADFSET\" />";
            $hasMediaFolder = true;
          }

          if( $fd[2] == 'link' ) {
            $o2 .= "<a class=\"adfselectlink\" id=\"adf{$contentContainerID}-field{$fd[0]}-setSEECMSADFSET\">Add a link</a><div class=\"adf{$contentContainerID}-field{$fd[0]}-setSEECMSADFSET\"></div>";
            $o2 .= "<input type=\"hidden\" name=\"adf{$contentContainerID}-field{$fd[0]}-setSEECMSADFSET\" />";
            $hasLink = true;
          }
        }

        $o2 .= "<p><a class=\"deleteadfset\" data-set-container=\"{$contentContainerID}\" id=\"delete-adf{$contentContainerID}-adfsetSEECMSADFSET\">Delete set</a></p>";
        $o2 .= "</div>';</script>";
      }

      if( $hasImage ) {
        if( !$this->editLoaded['adfImage'] ) {

          $this->see->html->js( 'loadfunctions.js', '', '/seecms/js/' );

          $this->editLoaded['adfImage'] = 1;
        }

        $o .= "<div class=\"adfimages\" style=\"display: none;\"><div class=\"select selectImage\"><p><select class=\"adfimageFolder\"></select></p></div><div class=\"medialistinner folders\"></div><div class=\"clear\"></div></div>";
      }

      if( $hasMediaFolder ) {
        if( !$this->editLoaded['adfMediaFolder'] ) {

          $this->see->html->js( 'loadfunctions.js', '', '/seecms/js/' );

          $this->editLoaded['adfMediaFolder'] = 1;
        }

        $o .= "<div class=\"adfmediafolder\" style=\"display: none;\"><div class=\"select selectMediaFolder\"><p><select class=\"adfimageFolder\"></select></p></div><p><a href=\"#\" id=\"selectMediaFolderButton\">Select</a> <a href=\"#\" id=\"cancelSelectMediaFolderButton\">Cancel</a></p><div class=\"clear\"></div></div>";
      }

      if( $hasLink ) {
        if( !$this->editLoaded['adfLink'] ) {

          $o .= $this->loadForLinkSelector( true );
        }
      }

      $o .= "</".(($skipForm)?'div':'form').">";

      if( !$inline ) {
        $o .= "</div>";
      }

    } 
    else {

      $content = json_decode( $content, true );

      $t = $this->see->viewParts['content'.$contentContainerID]['useDisplayViewPart'];
      $SeePHPViewContext['o'] = "<{$t}>";

      $cc = SeeDB::load( 'contentcontainer', $contentContainerID );

      $fields = $cc->contenttype->fields;
      $field = preg_split( '/\r\n|\r|\n/', $fields );
      foreach( $field as $f ) {
        $fd = explode( ',', $f );
        $thefields[$fd[0]] = $fd[2];
      }

      foreach( $content as $k => $c ) {
        foreach( $c as $cK => $cV ) {

          if( $thefields[$cK] == 'image' ) {

            $cV = SeeDB::load( 'media', $cV );
          }

          if( $thefields[$cK] == 'link' ) {

            if( $cV ) {
              $cV = $this->loadLinkDetails( $cV );
            }
          }

          $content[$k][$cK] = $cV;
        }
      }

      $o = array( 'content' => $content, 'object' => $ob, 'route' => $route->route );

      $route['content']['content'.$contentContainerID] = $o;
      $c = $this->see->viewParts[$t];
      $seeview = new SeeViewController( $this->see );
      $displayContent = $seeview->processTag( $SeePHPViewContext, $t, $c, $route, $content );
      $content = $displayContent['o'];

      $o = $content;
    }

    $o = $o2.$o;

    return( $o );
  }

  public function editADF( $adfData = '' ) {

    if( !$adfData ) {
      $adfData = $_POST;
      $data = array();
      parse_str($adfData['content'], $data);
    } else {
      $data = $adfData['data'];
    }

    $container = SeeDB::load( 'contentcontainer', $_POST['containerID'] );

    $fields = $container->contenttype->fields;
    $field = preg_split( '/\r\n|\r|\n/', $fields );
    foreach( $field as $f ) {
      $fd = explode( ',', $f );
      $thefields[$fd[0]] = $fd[2];
    }

    foreach( $data as $cK => $cV ) {

      $keys = explode( "-", $cK );
      $set   = str_replace( "set", "", $keys[2] );
      $field = str_replace( "field", "", $keys[1] );

      if( $thefields[$field] == 'richText' ) {

        $cV = str_replace( "/{$this->see->rootURL}", "/", $cV );
      }

      $d[$set][$field] = $cV;
    }

    $d = array_values( $d );

    $content = json_encode( $d );

    return( $content );
  }

  public function makeEditPart( $contentContainerID, $contentType, $content, $status, $settingsScreen = false, $accessLevel = 5 ) {

    if( $contentType == 'ADF' ) {

      $content = json_decode( $content, true );

      $t = $this->see->viewParts['content'.$contentContainerID]['useDisplayViewPart'];
      $SeePHPViewContext['o'] = "<{$t}>";

      $cc = SeeDB::load( 'contentcontainer', $contentContainerID );

      $fields = $cc->contenttype->fields;
      $field = preg_split( '/\r\n|\r|\n/', $fields );
      foreach( $field as $f ) {
        $fd = explode( ',', $f );
        $thefields[$fd[0]] = $fd[2];
      }

      if( is_array( $content ) ) {
        foreach( $content as $k => $c ) {
          foreach( $c as $cK => $cV ) {

            if( $thefields[$cK] == 'image' ) {

              $cV = SeeDB::load( 'media', $cV );
            }

            if( $thefields[$cK] == 'link' ) {

              if( $cV ) {
                $cV = $this->loadLinkDetails( $cV );
              }
            }

            $content[$k][$cK] = $cV;
          }
        }
      }

      $oA = array( 'content' => $content, 'object' => $ob, 'route' => $route->route );

      $route['content']['content'.$contentContainerID] = $oA;
      $c = $this->see->viewParts[$t];
      $seeview = new SeeViewController( $this->see );
      $displayContent = $seeview->processTag( $SeePHPViewContext, $t, $c, $route, $content );
      $content = $displayContent['o'];
    }

    $editbarVersion2 = SeeCMSSettingController::load('editBarV2');

    if( !$settingsScreen ) {
      $o .= "<div class=\"editable\"><div class=\"editable{$contentContainerID}\">{$content}</div><p class=\"editbar".(($editbarVersion2)?' emilieseditbar':'')."\">";

      $o .= (($editbarVersion2)?'':'<a class="editcontent editcontent'.ucwords(str_replace(" ", "", $contentType)).'" id="editable'.$contentContainerID.'">Edit</a> | ');

      if( $status ) {
        $o .= "<span class=\"text\">This content is live</span>";
      } else {
        $o .= "<strong>This content is draft</strong>";

        if( $accessLevel >= 5 ) {
          $o .= " | <a hred=\"#\" class=\"editableApply\" id=\"editableApply{$contentContainerID}\">Apply</a>";
        }

        if( $accessLevel >= 2 ) {
          $o .= " | <a hred=\"#\" class=\"editableDiscard\" id=\"editableDiscard{$contentContainerID}\">Discard</a>";
        }

        if( $accessLevel < 5 ) {
          $o .= " | Approval required to publish ";
        }
      }

      $o .= "&nbsp;&nbsp;";

      $o .= (($editbarVersion2)?"<a class=\"editcontent editcontent".ucwords(str_replace(" ", "", $contentType))."\" id=\"editable{$contentContainerID}\"><span class=\"icon\"><i class=\"fa fa-pencil-square\" aria-hidden=\"true\"></i></span></a>":"");

      $o .= "</p></div>";
    } else {

      $o .= "<div class=\"editable\"><div class=\"editable{$contentContainerID}\">{$content}</div><p class=\"editbar\"><a class=\"editcontent editcontent".ucwords(str_replace(" ", "", $contentType))."\" id=\"editable{$contentContainerID}\">Edit</a></p></div>";
    }

    return( $o );
  }

  public function findSelectedLink() {

    $link = $_POST['link'];

    if( $link ) {

      if( $link[0] == '/' ) {

        if( substr( $link, 0, 12 ) == '/seecmsfile/' ) {
          $id = end( explode( '?', $link ) );
          $linkData = json_encode( array( 'type' => 'download', 'id' => "download-{$id}" ) );
        } else {
          $r = SeeDB::findOne( 'route', ' route = ? ', array( substr( $link, 1 ) ) );
          $linkData = json_encode( array( 'type' => $r->objecttype, 'id' => "{$r->objecttype}-{$r->objectid}" ) );
        }

      } else if( substr( $link, 0, 7 ) == 'http://' ) {

        $linkData = json_encode( array( 'type' => 'external', 'id' => $link ) );
      } else if( substr( $link, 0, 7 ) == 'mailto:' ) {

        $linkData = json_encode( array( 'type' => 'email', 'id' => substr( $link, 7 ) ) );
      }
    }

    return( $linkData );
  }

  public function prepareSelectedLink() {

    if( $_POST['item'] ) {

      $itemParts = explode( '-', $_POST['item'] );

      $d['type'] = $itemParts[0];

      if( $itemParts[0] == 'page' ) {
        $p = SeeDB::load( 'page', $itemParts[1] );
        $d['name'] = $p->title;
        $r = SeeDB::findOne( 'route', ' objectid = ? && objectType = ? && primaryroute = ? ', array( $itemParts[1], 'page', 1 ) );
        $link = '/'.$r->route;
      } else if( $itemParts[0] == 'post' ) {
        $p = SeeDB::load( 'post', $itemParts[1] );
        $d['name'] = $p->title;
        $r = SeeDB::findOne( 'route', ' objectid = ? && objectType = ? && primaryroute = ? ', array( $itemParts[1], 'post', 1 ) );
        $link = '/'.$r->route;
      } else if( $itemParts[0] == 'download' ) {
        $dd = SeeDB::load( 'download', $itemParts[1] );
        $d['name'] = $dd->name;
        $d['type'] .= " seecms{$dd->type}";
        $link = substr( $_POST['item'], 9 );
        $link = "/seecmsfile/?id={$link}";
      } else if( $itemParts[0] == 'email' ) {
        $link = substr( $_POST['item'], 6 );
      } else if( $itemParts[0] == 'weblink' ) {
        $link = substr( $_POST['item'], 8 );
      }

      $d['link'] = "<a href=\"{$link}\"".(($_POST['newwindow'])?' target="_blank"':'').">";

      return( json_encode( $d ) );
    }
  }

  public function loadForLinkSelector( $html = 0, $visible = 0, $types = null ) {

    if( !isset( $types ) || $types['page'] ) {
      if( isset( $this->temp['loadForLinkSelector']['pages'] ) ) {

        $data['pages'] = $this->temp['loadForLinkSelector']['pages'];

      } else {

        $page = new SeeCMSPageController( $this->see );
        $data['pages'] = $page->adminTreeSimple(0);
        $this->temp['loadForLinkSelector']['pages'] = $data['pages'];
      }
    }

    if( !isset( $types ) || $types['post'] ) {
      $post = new SeeCMSPostController( $this->see );
      $data['posts'] = $post->postTreeSimple(0);
    }

    if( !isset( $types ) || $types['download'] ) {
      $download = new SeeCMSDownloadController( $this->see );
      $data['downloads'] = $download->downloadTreeSimple(0);
    }

    if( $html ) {
      $o .= "<div class=\"adflinks\"".((!$visible)?" style=\"display: none;\"":"")."><div class=\"select adfselectLink\"><p><select id=\"linktype\"><option value=\"\">Select link type</option>";

      if( !isset( $types ) || $types['page'] ) {
        $o .= "<option value=\"page\">CMS page</option>";
      }

      if( !isset( $types ) || $types['post'] ) {
        $o .= "<option value=\"post\">CMS post</option>";
      }

      if( !isset( $types ) || $types['download'] ) {
        $o .= "<option value=\"download\">CMS download</option>";
      }

      if( !isset( $types ) || $types['email'] ) {
        $o .= "<option value=\"email\">Email</option>";
      }

      if( !isset( $types ) || $types['external'] ) {
        $o .= "<option value=\"external\">External link</option>";
      }

      $o .= "</select></p></div><div class=\"hidden\" style=\"display: block;\">";
      $o .= "<div class=\"folders pages\"".(($visible)?" style=\"display: none;\"":"").">{$data['pages']}</div>";
      $o .= "<div class=\"folders posts\"".(($visible)?" style=\"display: none;\"":"").">{$data['posts']}</div>";
      $o .= "<div class=\"folders emails\"".(($visible)?" style=\"display: none;\"":"")."><p>Insert email address</p><p><input type=\"text\" id=\"emaillink\" /></p></div><div class=\"folders externals\"".(($visible)?" style=\"display: none;\"":"")."><p>Insert full url</p><p><input type=\"text\" id=\"weblink\" value=\"http://\" /></p></div>";
      $o .= "<div class=\"folders downloads\"".(($visible)?" style=\"display: none;\"":"").">{$data['downloads']}</div>";
      $o .= "</div><div class=\"clear\"></div></div>";

      $data = $o;
    }

    return( $data );
  }

  public function loadADFcontent( $data ) {

    if( $data['objectid'] ) {
      $objectid = $data['objectid'];
    } else {
      $objectid = $this->see->SeeCMS->object->id;
    }

    if( $data['type'] ) {
      $type = $data['type'];
    } else if( $data['objecttype'] ) {
      $type = $data['objecttype'];
    } else {
      $type = $this->see->SeeCMS->object->getMeta('type');
    }

    if( $data['children'] ) {

      $order = '';
      if( $type == 'page' ) {

        $order = ' ORDER BY pageorder';
      }

      $children = SeeDB::find( $type, ' parentid = ? '.$order, array( $objectid ) );
      foreach( $children as $c ) {
        $ids[] = $c->id;
      }

      if( !$ids ) {

        return;
      }
    } else if( $data['parent'] ) {
      $ids[] = $this->see->SeeCMS->object->parentid;
    } else if( $data['ascendant'] ) {
      $ids[] = $this->see->SeeCMS->ascendants[$data['ascendant']];
    } else {
      $ids[] = $objectid;
    }

    if( !is_array( $data['adfs'] ) ) {
      $adfs[] = $data['adfs'];
    } else {
      $adfs = $data['adfs'];
    }

    foreach( $adfs as $a ) {

      if( is_int( $a ) ) {
        $theadf = SeeDB::load( 'adf', $a );
      } else {
        $theadf = SeeDB::findOne( 'adf', ' identifier = ? ', array( $a ) );
      }

      $fields = $theadf->contenttype->fields;
      $field = preg_split( '/\r\n|\r|\n/', $fields );
      foreach( $field as $f ) {
        $fd = explode( ',', $f );
        $thefields[$fd[0]] = $fd[2];
      }

      foreach( $ids as $id ) {
        $adf = SeeDB::find( "adfcontent", " adf_id = ? && objecttype = ? && objectid = ? ", array( $theadf->id, $type, $id ) );
        foreach( $adf as $aa ) {

          $ob = SeeDB::load( $aa->objecttype, $aa->objectid );
          $route = SeeDB::findOne( 'route', ' objecttype = ? && objectid = ? && primaryRoute = ? ', array( $type, $ob->id, 1 ) );
          $content = json_decode( $aa->content, true );

          if( is_array( $content ) ) {
            foreach( $content as $k => $c ) {
              foreach( $c as $cK => $cV ) {

                if( $thefields[$cK] == 'image' ) {

                  $cV = SeeDB::load( 'media', $cV );
                }

                if( $thefields[$cK] == 'link' ) {
                  $cV = $this->loadLinkDetails( $cV );
                }

                if( $thefields[$cK] == 'mediaFolder' ) {

                  $mfid = $cV;
                  $cV = array();
                  $cV['media'] = SeeDB::find( 'media', ' parentid = ? && isfolder = ? ', array( $mfid, 0 ) );
                  $cV['mediaFolder'] = SeeDB::load( 'media', $mfid );
                }

                $content[$k][$cK] = $cV;
              }
            }
          }

          $o[$a]['ordered'][] = array( 'content' => $content, 'object' => $ob, 'route' => $route->route );
          $o[$a]['indexed'][$ob->id] = array( 'content' => $content, 'object' => $ob, 'route' => $route->route );
        }
      }
    }

    return( $o );
  }

  function loadLinkDetails( $link ) {

    if( $link ) {
      $link = explode( '-', $link );
      if( $link[0] == 'email' ) {
        $cV = array();
        $cV['route'] = "mailto:{$link[1]}";
        $cV['object'] = null;
        $cV['name'] = $link[1];
      } else if( $link[0] == 'link' ) {
        $cV = array();
        unset( $link[0] );
        $cV['route'] = implode( "-", $link );
        $cV['object'] = null;
        $cV['name'] = $cV['route'];
      } else if( $link[0] == 'download' ) {
        $linkob = SeeDB::load( $link[0], $link[1] );
        $cV = array();
        $cV['route'] = "/seecmsfile/?id={$link[1]}";
        $cV['object'] = $linkob;
        $cV['name'] = $linkob->name;
      } else {
        $linkob = SeeDB::load( $link[0], $link[1] );
        $linkroute = SeeDB::findOne( 'route', ' objecttype = ? && objectid = ? && primaryRoute = ? ', array( $link[0], $link[1], 1 ) );
        $cV = array();
        $cV['route'] = '/'.$linkroute->route;
        $cV['object'] = $linkob;
        $cV['name'] = (($linkob)?$linkob->title:$linkob->name);
      }
    } else {
      $cV = array( 'route' => '', 'object' => '', 'name' => '' );
    }

    return( $cV );
  }

  private function htmlParser( &$doc, $parentTags, $tag ) {

    if ( $tag->nodeType == XML_ELEMENT_NODE ) {

      if( $tag->nodeName == 'script' ) {

        $remove = true;

        foreach( $parentTags as $pT ) {

          if( $pT["name"] == 'div' && strstr( $pT["class"], 'seecmshtml' ) ) {

            $remove = false;
          }
        }

        if( $remove ) {

          $tag->parentNode->removeChild( $tag );
        }
      }

      $parentTags[] = array( 'name' => $tag->nodeName, 'class' => $tag->getAttribute('class'), 'id' => $tag->getAttribute('id') );

      foreach( $tag->childNodes as $cN ) {

        $this->htmlParser( $doc, $parentTags, $cN );
      }
    }
  }

  private function completeApproval( $objectType, $objectID ) {

    $aas = SeeDB::find( 'adminapproval', ' objecttype = ? && objectid = ? && complete = ? ', array( $objectType, $objectID, 0 ) );
    foreach( $aas as $aa ) {

      $aa->complete = 1;

      SeeDB::store( $aa );
    }
  }

  public function requestApproval( $data = null ) {

    if( !is_array( $data ) && !empty( $_POST ) ) {
      $data = $_POST;
    }

    if( is_array( $data ) ) {
      $accessLevel = $this->see->SeeCMS->adminauth->checkContextAccess( $data["objectType"], $data['objectID'] );

      if( $accessLevel < 5 ) {
        $approvalRequest = SeeDB::dispense( "adminapproval" );
        $approvalRequest->objecttype = (string)$data["objectType"];
        $approvalRequest->objectid   = (int)$data['objectID'];
        $approvalRequest->requester  = (int)$_SESSION['seecms'][$this->see->siteID]['adminuser']['id'];
        $approvalRequest->approver   = (int)$data['approverID'];
        $approvalRequest->submitted  = date("Y-m-d H:i:s");
        SeeDB::store( $approvalRequest );

        $r['done'] = 1;
      }
      else {
        $r['error'] = 'Unable to complete request';
      }

      return json_encode( $r );
    }

  }

}
