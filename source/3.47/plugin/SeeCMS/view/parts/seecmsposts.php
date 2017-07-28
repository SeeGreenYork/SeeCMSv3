<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */
?>
<div class="col1">
  <div class="imagesection">
    <div class="route">
      <div class="foldertitle">
        <h3>Folders</h3>
        <h3 class="foldername"></h3>
      </div>
    </div>
    <div class="folders">
      <?php echo $data['folderTree']; ?>
    </div>
    <div class="newslist">
      <div class="newslistinner">
        <?php echo $data['posts']; ?>
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
<div id="newposttitle" title="Create new post">
<p>Post title:<br><input type="text" id="posttitle" /></p>
<?php

if( is_array( $data['posttypes'] ) ) {
  echo '<p>Post type:<br><select id="posttype">';

  foreach( $data['posttypes'] as $p ) {
    echo "<option value=\"{$p->id}\">{$p->name}</option>";
  }

  echo '</select></p>';
}
else {
  echo '<input type="hidden" id="posttype" value="1" />';
}

?>
</div>

<div id="newfoldertitle" title="Create new folder">
<p>Folder title:<br><input type="text" id="foldertitle" /></p>
</div>

<div id="deletepostfolderpopup" title="Delete folder?"></div>
<div id="deletepostpopup" title="Delete post?"></div>

<?php
// Edit post folder popup
echo "<div id=\"editpostfolderpopup\" title=\"Edit post folder\">";
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
      $f->select( array( "class" => "admingrouppermission", "name" => "adminusergrouppermissions[{$ag->id}]", "id" => "adminusergroup-{$ag->id}" ), array( "options" => array( 0 => "None", 5 => "Full" ) ) );
      echo "</td>";
      echo "</tr>";
    }

    echo "</table>
    <hr>
    <p>";
    $f->checkbox( array( "name" => "adminusergrouppermissions-cascade", "id" => "editpostfolder-admin-permission-cascade" ) );
    echo "
    <label for=\"editpostfolder-admin-permission-cascade\">Update editing permissions on all subfolders and posts</label>
    </p>";
  }
  else {
    echo "<p><strong>Please add some admin groups if you want to set editing permissions</strong></p>";
  }

}

$f->close();
echo "</div>";
