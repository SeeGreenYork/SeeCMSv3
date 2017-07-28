<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

$download = $data['download'];

$formSettings['controller']['name'] = 'SeeCMSDownload';
$formSettings['controller']['method'] = 'update';
$formSettings['attributes']['action'] = "./?id={$download->id}";
$formSettings['attributes']['enctype'] = "multipart/form-data";
$formSettings['disableSpamProtection'] = true;
$formSettings['disableStandardErrors'] = true;
$formSettings['validate']['name']['validate'] = 'required';
$formSettings['validate']['name']['error'] = 'Please enter a title.';

$f = $see->html->form( $formSettings );

?>

<div class="col1"><div class="sectiontitle"><h2>Edit download</h2></div>
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
	<p>Download name</p>
	<p><?php $f->text( array( 'name' => 'name', 'value' => $download->name )); ?></p>
	<p>Download description</p>
	<p><?php $f->textarea( array( 'name' => 'description', 'value' => $download->description )); ?></p>
  <p><a class="blockbutton" href="/seecmsfile/?id=<?php echo $download->id; ?>&amp;preview=1">Download file</a></p>
  <!--
	<h2>Usage</h2>
	<p><strong>Pages:</strong><br/>Does not appear on any pages ??</p>
	<p><strong>Posts:</strong><br/>Does not appear on any posts ??</p>
	-->
	<hr>
	<div class="exif">
		<p><strong>File size -</strong> <?php echo $download->filesize; ?></p>
		<p><strong>File type -</strong> <?php echo strtoupper( $download->type ); ?></p>
		<p><strong>Uploaded -</strong> <?php echo $see->format->date( $download->uploaded, "d M y / H:i:s" ); ?></p>
    <?php
      if( $download->modified ){
        echo "<p><strong>Modified -</strong>".$see->format->date( $download->modified, "d M y / H:i:s" )."</p>";
      }
    ?>
    <p><strong>Shareable Link -</strong> <textarea><?php echo 'http'.(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 's' : '').'://'."{$_SERVER['HTTP_HOST']}/".(($see->rootURL!='/')?$see->rootURL:'')."seecmsfile/?id={$download->id}"; ?></textarea></p>
	</div>
  <?php 
  if( $data['accessLevel'] >= 5 ) {
    echo "<hr>
    <p>Select a replacement file:<br>";
    $f->file( array( 'name' => 'updatefile', 'placeholder' => 'update file') );
    echo "</p>";
  }
  ?>
</div>
  
<div class="right">
  
<?php
echo "<div class=\"adf\">";
  echo "<h3>Download security</h3>";
  echo "<div class=\"security\">";
  echo "<p>";
  $f->checkbox( array( 'name' => "websiteusergrouppermissions-all", 'id' => "security-allUserAccess", "value" => ((is_array($data["websiteuserGroupPermissions"]))?0:1) ) );
  echo "<label for=\"security-allUserAccess\"> Everyone can access this content</label>
  </p>
  <hr>";
              
  if( count( $data["websiteuserGroups"] ) ) {

    echo "<p>Only specific groups of registered users can access this content:</p>";

    foreach( $data["websiteuserGroups"] as $ug ) {
    
      echo "<p>";
      $f->checkbox( array( 'name' => "websiteusergrouppermissions[{$ug->id}]", 'id' => "security-group-{$ug->id}", 'class' => "security-group", "value" => (int)$data["websiteuserGroupPermissions"][$ug->id] ) );
      echo "<label for=\"security-group-{$ug->id}\">  {$ug->name}</label>
      </p>";
    }
  }
  else {
    echo "<p><strong>Please add some site user groups if you want to set permissions</strong></p>";
  }

  echo "</div>";
echo "</div>";

// Adminusergroup permissions
if( $see->SeeCMS->config['advancedEditorPermissions'] && $_SESSION['seecms'][$this->see->siteID]['adminuser']['access']['current'] >= 5 ) {

  echo "<div class=\"adf\">";
  echo "<h3>Editing Permissions</h3>";
  echo "<div>";
  
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

// Categories
if( count( $data['categories'] ) ) {

echo "<div class=\"adf\">";
  echo "<h3>Categories</h3>";
  echo "<div>";
              
  foreach( $data['categories'] as $cID => $cName ) {
    echo "<p>";
		$f->checkbox( array( 'name' => "categories[{$cID}]", 'id' => "categories-{$cID}", 'value' => isset( $download->sharedCategory[$cID] ) ) );
    echo "<label for=\"categories-{$cID}\"> {$cName}</label></p>";
  }

  echo "</div>";
echo "</div>";
} ?>
</div>
</div>
</div>
	<div class="col2">
		<div class="editpage">
      <?php
      if( $data['accessLevel'] >= 5 ) {
        $f->submit( array( 'name' => 'Save', 'class' => 'save', 'value' => 'Save changes' ) );
        echo "<span><i class=\"fa fa-floppy-o\" aria-hidden=\"true\"></i></span>";
      }
      $f->hidden( array( 'name' => 'id', 'value' => $media->id ) ); ?>
    </div>
		<div class="support">
			<h2>Support <span><i class="fa fa-question-circle" aria-hidden="true"></i></span></h2>
			<div class="supportinfo">
        <?php echo $see->SeeCMS->supportMessage; ?>
      </div>
		</div>
	</div>
	<div class="clear"></div>