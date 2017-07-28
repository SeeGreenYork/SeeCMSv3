<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

$see->html->js( 'jquery.Jcrop.min.js', '', '/seecms/js/' );
$see->html->js( 'jcropcontroller.js', '', '/seecms/js/' );
$see->html->css( 'jquery.Jcrop.min.css', 'screen', '/seecms/css/' );

$media = $data['media'];
$mediaDimensions = $data['mediaDimensions'];
$imageSizes = $data['imageSizes'];

$formSettings['controller']['name'] = 'SeeCMSMedia';
$formSettings['controller']['method'] = 'update';
$formSettings['attributes']['action'] = "./?id={$media->id}";
$formSettings['attributes']['enctype'] = 'multipart/form-data';
$formSettings['disableSpamProtection'] = true;
$formSettings['disableStandardErrors'] = true;
$formSettings['validate']['name']['validate'] = 'required';
$formSettings['validate']['name']['error'] = 'Please enter a name.';

$f = $see->html->form( $formSettings );
?>

<div class="col1">
<div class="sectiontitle"><h2>Edit media</h2></div>
<?php 
if( count( $f->errors ) ) {
  echo "<div class=\"seecmsmessage seecmserror\">";
  
  if( count( $f->errors ) == 1 ) {
    echo "<p><i class=\"fa fa-warning\" aria-hidden=\"true\"></i> <strong>Error:</strong> " . reset( $f->errors ) . "</p>";
  }
  else {
    echo "<p><i class=\"fa fa-warning\" aria-hidden=\"true\"></i> <strong>Error:</strong></p>";
    echo "<p>" . implode( "<br>", $f->errors ) . "</p>";
  }

  echo "</div>";
}

echo $data["messages"];
?>

<div class="column columnfull twocolumnfull">
<div class="left">
	<p>Name</p>
	<p><?php $f->text( array( 'name' => 'name', 'value' => $media->name )); ?></p>
	<p>Alt text</p>
	<p><?php $f->text( array( 'name' => 'alt', 'value' => $media->alt )); ?></p>
	
	<hr>
	<div class="exif">
      <?php 
      if( $media->type == 'mp4' ) {
        echo "<p><strong>Embed code -</strong><br /><textarea rows=\"6\">&lt;video width=\"720\" controls&gt;&lt;source src=\"&#47;images/uploads/vid-{$media->id}-{$media->pathmodifier}.{$media->type}\" type=\"video/mp4\"&gt;&lt;/video&gt;</textarea></p>";
      } else {
      	echo "<p><strong>Original image dimensions</strong><br />{$mediaDimensions['width']} x {$mediaDimensions['height']}</p>";
      } 
      ?>
			<p><strong>File type</strong><br /><?php echo strtoupper( $media->type ); ?></p>
		</div>
  <hr>
  <div class="seecmsuploadnewversion">
  <p>Select a replacement file:<br />
  <?php $f->file( array( 'name' => 'updatefile' )); ?>
  </p>
  </div>
  <?php

  // Adminusergroup permissions
  if( $see->SeeCMS->config['advancedEditorPermissions'] && $_SESSION['seecms'][$this->see->siteID]['adminuser']['access']['current'] >= 5 ) {

    echo "<hr>
    <div class=\"adf\">
    <h3>Editing Permissions</h3>
    <div>";
    
    if( count( $data["adminuserGroups"] ) ) {
      echo "<p>Set editing permissions, this will not override globally set access levels, such as Administrator access.</p>";
      echo "<table class=\"users stripey order permissions\"><tr><th>Group</th><th>Permission</th></tr>";

      foreach( $data["adminuserGroups"] as $ag ) {
        echo "<tr><td>{$ag->name}</td>";
        echo "<td>";
        $f->select( array( "class" => "admingrouppermission", "name" => "adminusergrouppermissions[{$ag->id}]", "id" => "adminusergroup-{$ag->id}", "value" => $data["adminuserGroupPermissions"][$ag->id] ), array( "options" => array( 0 => "None", 5 => "Full" ) ) ); // No limited for media or downloads
        echo "</td>";
        echo "</tr>";
      }
      
      echo "</table>";    
    }
    else {
      echo "<p><strong>Please add some admin groups if you want to set editing permissions</strong></p>";
    }
    
    echo "</div>";
    echo "</div>";

  }

  $adfs = SeeDB::find( 'adf', ' objecttype = ? && ( ( objectid = ? && `cascade` = ? ) || ( ( objectid = ? || objectid = ? ) && `cascade` = ? ) ) && ( theme = ? || theme = ? ) ', array( 'media', $media->id, 0, $media->parentid, 0, 1, '', $see->theme ) );
  $cc = new SeeCMSContentController( $see, $see->SeeCMS->language );

  if( !empty( $adfs ) ) {

    echo "<hr><h2>Custom data</h2>";

    foreach( $adfs as $adf ) {

      $cc->objectType = 'media';
      $cc->objectID = $media->id;
      
      $content = SeeDB::findOne( 'adfcontent', ' objecttype = ? && objectid = ? && adf_id = ? && language = ? ', array( 'media', $media->id, $adf->id, $see->SeeCMS->language ) );

      echo '<div class="adf">';
      echo "<h3 id=\"editable{$adf->id}\" class=\"editcontent editcontentADF adfpopup\">{$adf->title}</h3>";
      echo $cc->makeEditPart( $adf->id, 'ADF', $content->content, 1, true );
      $adfpopup .= $cc->ADF( $content->content, 1, $adf->id, 1, $adf->contenttype->fields, $adf->contenttype->settings, (($accessLevel)?$accessLevel:5), true )."\r\n";
      
      echo '</div>';
    }
  }

  ?>
    
</div>
<div class="right">
  <?php 
  if( $media->type == 'mp4' ) {
  	echo "<video width=\"720\" controls><source src=\"/images/uploads/vid-{$media->id}-{$media->pathmodifier}.{$media->type}\" type=\"video/mp4\"></video>";
  }
  else {
    echo "<p>Preview:
    <select class=\"editimagesizeselect\" style=\"display: inline; float: none; width: auto;\" id=\"seecmsimagesize\">
    <option value=\"original\">Original image size</option>";

    foreach( $imageSizes as $is ) {
      echo "\n<option data-mode=\"{$is->mode}\" value=\"{$is->id}\">{$is->name}</option>";
    }

    // IMAGE RECROP
    echo "\n</select>";

    echo "\n<a style=\"display: none;\" class=\"recropimagebutton\" href=\"#\">Recrop image</a>";

    echo "\n</p>";
    echo "\n<img class=\"seecmspreviewimage\" src=\"/images/uploads/img-original-{$media->id}.{$media->type}?r=".rand(0,10000)."\" alt=\"\" />";
    
  }
  ?>
</div>
</div>
</div>
<div class="col2">
	<div class="editpage">
    <?php
    if( $data['accessLevel'] >= 5 || $data['accessLevel'] == NULL ) {
      $f->submit( array( 'name' => 'Save', 'class' => 'save', 'value' => 'Save changes' ) );
      echo "<span><i class=\"fa fa-floppy-o\" aria-hidden=\"true\"></i></span>";
    }
    $f->hidden( array( 'name' => 'id', 'value' => $media->id ) ); ?>
    <span><i class="fa fa-floppy-o" aria-hidden="true"></i></span>
  </div>
	<div class="support">
		<h2>Support <span><i class="fa fa-question-circle" aria-hidden="true"></i></span></h2>
		<div class="supportinfo">
      <?php echo $see->SeeCMS->supportMessage; ?>
    </div>
	</div>
</div>
<div class="clear"></div>
  
<?php 
$f->close();

echo $adfpopup;

echo "<div class=\"recropoverlay\" style=\"display: none\"></div>
<div class=\"recropimagewindow\" style=\"display: none\">
<div class=\"heading\">
<h3>Recrop image</h3>
<a class=\"close-window\" href=\"#\">x</a>
</div>
<div class=\"main\">
<div class=\"inner\">
<p>Please select the area you wish to crop/resize to <a class=\"doneRecrop\" href=\"#\">Done</a></p>
<div class=\"original-image-container crop-image\">
<img id=\"jcrop-target\" src =\"../../../images/uploads/img-original-{$media->id}.{$media->type}?r=".rand(0,10000)."\" alt=\"\" />
</div>
</div>
</div>
</div>";
