<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */


$see->html->css( 'dropzone.css', 'screen', '/seecms/css/' );
$see->html->js( 'dropzone.js', '', '/seecms/js/' );
$see->html->js( 'dropzone-downloads.js', '', '/seecms/js/' );

?>
<div class="col1">
  <div class="imagesection">
    <div class="route">
      <div class="foldertitle">
        <h3>Folders</h3>
        <h3 class="foldername"></h3>
      </div>
    </div>
    <div class="folders downloadfolders">
      <ul>
      <?php echo $data['folderTree']; ?>
      </ul>
    </div>
    <div class="doclist">      
      <div class="doclistinner">
      <?php echo $data["downloads"]; ?>
      </div>
      <div class="dropzonecontainer">
        <?php echo $data["dropzone"]; ?>
      </div>
    </div>
  </div>
</div>
<div class="col2">
  <div class="createpage">
    <?php echo $data['createButtons']; ?>
  </div>
  <div class="support">
    <h2>Support <span><i class="fa fa-question-circle" aria-hidden="true"></i></span></h2>
    <div class="supportinfo">
      <?php echo $see->SeeCMS->supportMessage; ?>
    </div>
  </div>
</div>
<div class="clear"></div>


<div id="newdownloadfoldertitle" title="Create new folder">
  <p>Folder name:<br /><input type="text" id="foldertitle" /></p>
</div>

<div id="deletedocpopup" title="Delete file?"></div>
<div id="deletedownloadfolderpopup" title="Delete folder?"></div>

<?php 
// Edit download folder popup
echo "<div id=\"editdownloadfolderpopup\" title=\"Edit download folder\">";
$f = $see->html->form(); 
echo "<p><label for=\"foldertitle2\">Folder title:</label><br>";
$f->text( array( "name" => "title", "id" => "foldertitle2" ) );
echo "</p>
<hr>
<h3>Security</h3>";

// Websiteuser permissions
echo "<div class=\"security\">
  <p>";
  $f->checkbox( array( 'name' => "websiteusergrouppermissions-all", 'id' => "security-allUserAccess" ) );
  echo "<label for=\"security-allUserAccess\"> Everyone can access this content</label>
  </p>
  <hr>";
              
  if( count( $data["websiteuserGroups"] ) ) {
    echo "<p>Only specific groups of registered users can access this content:</p>";

    foreach( $data["websiteuserGroups"] as $ug ) {
      echo "<p>";
      $f->checkbox( array( 'name' => "websiteusergrouppermissions[]", 'id' => "security-group-{$ug->id}", 'class' => "security-group", "value" => $ug->id ) );
      echo "<label for=\"security-group-{$ug->id}\"> {$ug->name}</label></p>";
    }
    
  }

  echo "<hr>
  <p>";
  $f->checkbox( array( 'name' => "websiteusergrouppermissions-cascade", 'id' => "security-cascade" ) );
  echo "<label for=\"security-cascade\"> Update permissions on all subfolders and downloads</label>
  </p>";

echo "</div>";

// Admin permissions
if( $see->SeeCMS->config['advancedEditorPermissions'] && $_SESSION['seecms'][$this->see->siteID]['adminuser']['access']['current'] >= 5 ) {
  echo "<hr>
  <h3>Editing Permissions</h3>";
  
  if( count( $data["adminuserGroups"] ) ) {
    echo "<p>Set editing permissions, this will not override globally set access levels, such as Administrator access.</p>
    <table class=\"users stripey order permissions\"><tr><th>Group</th><th>Permission</th></tr>";
    
    foreach( $data["adminuserGroups"] as $ag ) {
      echo "<tr><td>{$ag->name}</td>";
      echo "<td>";
      $f->select( array( "class" => "admingrouppermission", "name" => "adminusergrouppermissions[{$ag->id}]", "id" => "adminusergroup-{$ag->id}" ), array( "options" => array( 0 => "None", 5 => "Full" ) ) ); // No limited for media or downloads
      echo "</td>";
      echo "</tr>";
    }
    
    echo "</table>
    <p>";
    $f->checkbox( array( "name" => "adminusergrouppermissions-cascade", "id" => "editdownloadfolder-admin-permission-cascade" ) );
    echo "
    <label for=\"editdownloadfolder-admin-permission-cascade\">Update editing permissions on all subfolders and downloads</label>
    </p>";
  }
  
}

$f->close();
echo "</div>";
