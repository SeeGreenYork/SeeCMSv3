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
$see->html->js( 'dropzone-media.js', '', '/seecms/js/' );

$see->html->js( '', 'skipInitialMediaLoad = 1;', '' );
?>
<div class="col1">
  <div class="imagesection">
    <div class="route">
      <div class="foldertitle">
        <h3>Folders</h3>
        <h3 class="foldername"></h3>
        
      </div>
      
    </div>
    <div class="folders mediafolders">
      <?php echo $data['folderTree']; ?>
    </div>
    <div class="images">
      <div class="medialistinner">
        <?php echo $data['media']; ?>
      </div>

      <div class="clear"></div>
      
      <div class="dropzonecontainer">
        <?php echo $data["dropzone"]; ?>
      </div>
    </div>

  <div class="clear"></div>
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

<div id="newmediafoldertitle" title="Create new folder">
  <p>Folder title:<br /><input type="text" id="foldertitle" /></p>
</div>

<div id="deletemediafolderpopup" title="Delete folder?"></div>
<div id="deletemediapopup" title="Delete file?"></div>

<?php 
// Edit media folder popup
echo "<div id=\"editmediafolderpopup\" title=\"Edit media folder\">";
$f = $see->html->form(); 
echo "<p><label for=\"foldertitle2\">Folder title:</label><br>";
$f->text( array( "name" => "title", "id" => "foldertitle2" ) );
echo "</p>";

if( $see->SeeCMS->config['advancedEditorPermissions'] && $_SESSION['seecms'][$this->see->siteID]['adminuser']['access']['current'] >= 5 ) {
  echo "<hr>
  <h3>Editing Permissions</h3>";
  
  if( count( $data["adminuserGroups"] ) ) {
    echo "<p>Set editing permissions, this will not override globally set access levels, such as Administrator access.</p>";
    echo "<table class=\"users stripey order permissions\"><tr><th>Group</th><th>Permission</th></tr>";
    
    foreach( $data["adminuserGroups"] as $ag ) {
      echo "<tr><td>{$ag->name}</td>";
      echo "<td>";
      $f->select( array( "class" => "admingrouppermission", "name" => "adminusergrouppermissions[{$ag->id}]", "id" => "adminusergroup-{$ag->id}" ), array( "options" => array( 0 => "None", 5 => "Full" ) ) ); // No limited for media or downloads
      echo "</td>";
      echo "</tr>";
    }
    
    echo "</table>
    <p>";
    $f->checkbox( array( "name" => "adminusergrouppermissions-cascade", "id" => "editmediafolder-admin-permission-cascade" ) );
    echo "
    <label for=\"editmediafolder-admin-permission-cascade\">Update editing permissions on all subfolders and media</label>
    </p>";
  }
  
}

$f->close();
echo "</div>";
