<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

$g = $data['group'];
$users = $data['users'];
$userCount = $data['userCount'];

$formSettings['controller']['name'] = 'SeeCMSWebsiteUser';
$formSettings['controller']['method'] = 'updateGroup';

$formSettings['disableSpamProtection'] = true;

$formSettings['validate']['name']['validate'] = 'required';
$formSettings['validate']['name']['error'] = 'Please enter a name.';

$f = $see->html->form( $formSettings );

?>
<div class="col1">
<div class="sectiontitle"><h2>Edit site user group</h2></div>

<div class="columns">
<div class="column">
<div class="section">
<div class="sg_input">
<p>Group name</p><p><?php $f->text( array( 'name' => 'name', 'value' => $g->name )); ?></p>
</div>

<?php

if( $see->SeeCMS->customSiteUserGroupController['plugin'] ){
  $customSiteUserGroupController = $see->{$see->SeeCMS->customSiteUserGroupController['plugin']};
  echo $customSiteUserGroupController->editFields( $f, $g );
}

$adfs = SeeDB::find( 'adf', ' objecttype = ? ', array( 'websiteusergroup' ) );
$cc = new SeeCMSContentController( $see, $see->SeeCMS->language );
if( is_array( $adfs ) ) {
  echo "<div style=\"margin-bottom: 20px;\">";
  foreach( $adfs as $adf ) {

    $cc->objectType = $r->objecttype;
    $cc->objectID = $r->objectid;
    
    $content = SeeDB::findOne( 'adfcontent', ' objecttype = ? && objectid = ? && adf_id = ? && language = ? ', array( 'websiteusergroup', $g->id, $adf->id, $see->SeeCMS->language ) );

    echo $cc->ADF( $content->content, 1, $adf->id, 1, $adf->contenttype->fields, $adf->contenttype->settings, 5, true, true, true )."\r\n";
  }
  echo "</div>";
}
?>

</div>
</div>
<div class="column">
<h2>Users</h2>
<?php

if( $g->id ) {

if( $userCount > 30 ) {
  
  echo "<p id=\"groupuserfiltertext\">Showing first 30 of ",$userCount," users</p>";
}

?>

<div class="filterbox">
<p>Filter users: <input type="text" name="groupuserfilter" id="groupuserfilter" data-groupid="<?php echo $g->id; ?>" /></p>
</div>
<p><input class="save" type="button" id="groupuserloadall" data-groupid="<?php echo $g->id; ?>" value="Show all users" /></p>

<div id="groupusertable">
<table class="order">
<thead>
<tr><th>User name</th></tr>
</thead>
<tbody>
<?php

foreach( $users as $u ) {

  $member = $g->sharedWebsiteuser[$u->id]->id;
  
  echo "<tr id=\"wu{$u->id}\"><td><strong>{$u->surname}, {$u->forename}</strong> ({$u->email})</td><td class=\"delete\">";
  echo "<input class=\"togglegroupuser\" ".(($member)?'checked="checked"':'')." name=\"seecmswebsiteuser-{$u->id}\" data-userid=\"{$u->id}\" data-groupid=\"{$g->id}\" type=\"checkbox\">";
  echo "</td><td class=\"notification\"></td></tr>";
}

?>

</tbody>
</table>
</div>
<?php 

} else {
  
  echo "<p>Please save the group before adding users.</p>";
}

?>
</div>
</div>

<div class="clear"></div>
</div>

<div class="col2">
<div class="createpage">
<div class="editpage"><?php $f->submit( array( 'name' => 'Save', 'class' => 'save', 'value' => 'Save changes' ) ); ?><?php $f->hidden( array( 'name' => 'id', 'value' => $g->id ) ); ?><span><i class="fa fa-floppy-o" aria-hidden="true"></i></span></div>
</div>
<div class="support"> 
<h2>Support <span><i class="fa fa-question-circle" aria-hidden="true"></i></span></h2>
<div class="supportinfo">
<?php echo $see->SeeCMS->supportMessage; ?>
</div>
</div>
</div>
<?php $f->close(); ?>