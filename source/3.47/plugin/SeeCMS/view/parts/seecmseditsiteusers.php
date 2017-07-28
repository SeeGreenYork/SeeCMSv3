<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

$u = $data['user'];
$groups = $data['groups'];

$formSettings['controller']['name'] = 'SeeCMSWebsiteUser';
$formSettings['controller']['method'] = 'update';

$formSettings['disableSpamProtection'] = true;

$formSettings['validate']['seecmswebsiteuserforename']['validate'] = 'required';
$formSettings['validate']['seecmswebsiteuserforename']['error'] = 'Please enter a forename.';

$formSettings['validate']['seecmswebsiteusersurname']['validate'] = 'required';
$formSettings['validate']['seecmswebsiteusersurname']['error'] = 'Please enter a surname.';

$formSettings['validate']['seecmswebsiteuseremail']['validate'] = 'required';
$formSettings['validate']['seecmswebsiteuseremail']['error'] = 'Please enter an email address.';

$f = $see->html->form( $formSettings );

?>
<div class="col1">
<div class="sectiontitle"><h2>Edit site user</h2></div>

<div class="columns">
<div class="column">
<div class="section">

<div class="sg_input">
<p>Title</p><p><?php $f->text( array( 'name' => 'seecmswebsiteusertitle', 'value' => $u->title ) ); ?></p>
</div><div class="sg_input">
<p>First name</p><p><?php $f->text( array( 'name' => 'seecmswebsiteuserforename', 'value' => $u->forename ) ); ?></p>
</div>
<div class="sg_input">
<p>Surname</p><p><?php $f->text( array( 'name' => 'seecmswebsiteusersurname', 'value' => $u->surname ) ); ?></p>
</div>
<div class="sg_input">
<p>Email</p><p><?php $f->text( array( 'name' => 'seecmswebsiteuseremail', 'value' => $u->email ) ); ?></p>
</div>
<div class="sg_input">
<p>Telephone</p><p><?php $f->text( array( 'name' => 'seecmswebsiteusertelephone', 'value' => $u->telephone ) ); ?></p>
</div>
<div class="sg_input">
<p>Organisation</p><p><?php $f->text( array( 'name' => 'seecmswebsiteuserorganisation', 'value' => $u->organisation ) ); ?></p>
</div>
<div class="sg_input">
<p>Role/Job title</p><p><?php $f->text( array( 'name' => 'seecmswebsiteuserjobtitle', 'value' => $u->jobtitle ) ); ?></p>
</div>
<div class="sg_input">
<p>Address 1</p><p><?php $f->text( array( 'name' => 'seecmswebsiteuseraddress1', 'value' => $u->address1 ) ); ?></p>
</div>
<div class="sg_input">
<p>Address 2</p><p><?php $f->text( array( 'name' => 'seecmswebsiteuseraddress2', 'value' => $u->address2 ) ); ?></p>
</div>
<div class="sg_input">
<p>Address 3</p><p><?php $f->text( array( 'name' => 'seecmswebsiteuseraddress3', 'value' => $u->address3 ) ); ?></p>
</div>
<div class="sg_input">
<p>City</p><p><?php $f->text( array( 'name' => 'seecmswebsiteusercity', 'value' => $u->city ) ); ?></p>
</div>
<div class="sg_input">
<p>Region</p><p><?php $f->text( array( 'name' => 'seecmswebsiteuserregion', 'value' => $u->region ) ); ?></p>
</div>
<div class="sg_input">
<p>Post Code</p><p><?php $f->text( array( 'name' => 'seecmswebsiteuserpostcode', 'value' => $u->postcode ) ); ?></p>
</div>
<div class="sg_input">
<p>Country</p><p><?php $f->text( array( 'name' => 'seecmswebsiteusercountry', 'value' => $u->country." " ) ); ?></p>
</div>

<div class="sg_input">
<p>Password</p><p><?php $f->password( array( 'name' => 'seecmswebsiteuserpassword' )); ?><br/><sub>Leave blank unless you wish to reset the user's password</sub></p>
</div>

<?php
          
// Custom user
if( isset( $see->SeeCMS->customSiteUserController['plugin'] ) ) {
  $customSiteUserController = $see->{$see->SeeCMS->customSiteUserController['plugin']};
  echo $customSiteUserController->editFields( $f, $u );
}

?>
          
</div>
</div>
<div class="column">
<?php
$adfs = SeeDB::find( 'adf', ' objecttype = ? ', array( 'websiteuser' ) );
$cc = new SeeCMSContentController( $see, $see->SeeCMS->language );
if( is_array( $adfs ) ) {
  echo "<div style=\"margin-bottom: 20px;\">";
  foreach( $adfs as $adf ) {

    $cc->objectType = $r->objecttype;
    $cc->objectID = $r->objectid;
    
    $content = SeeDB::findOne( 'adfcontent', ' objecttype = ? && objectid = ? && adf_id = ? && language = ? ', array( 'websiteuser', $u->id, $adf->id, $see->SeeCMS->language ) );

    echo $cc->ADF( $content->content, 1, $adf->id, 1, $adf->contenttype->fields, $adf->contenttype->settings, 5, true, true, true )."\r\n";
  }
  echo "</div>";
}
?>
<div class="section">
<h2>Access details</h2>
<table class="order">
<tr><th>Created</th><td><?php echo $see->format->date( $u->createddate, "d F Y H:i" ); ?></td></tr>
<tr><th>Modified</th><td><?php echo $see->format->date( $u->modifieddate, "d F Y H:i" ); ?></td></tr>
<tr><th>Last login</th><td><?php echo $see->format->date( $u->logindate, "d F Y H:i" ); ?></td></tr>
</table>
</div>
<div class="section">
<h2>User groups</h2>
<table class="order">
<thead>
  <tr><th>Group name</th></tr>
</thead>
<tbody>
<?php

foreach( $groups as $g ) {

  $member = $u->sharedWebsiteusergroup[$g->id]->id;
  echo "<tr id=\"wg{$g->id}\"><td>{$g->name}</td><td class=\"delete\">";
  echo "<input class=\"toggleusergroup\" ".(($member)?'checked="checked"':'')." data-userid=\"{$u->id}\" data-groupid=\"{$g->id}\" type=\"checkbox\">";
  echo "</td><td class=\"notification\"></td></tr>";
}

?>
</tbody>
</table>
</div>
</div>
</div>

<div class="clear"></div>
</div>

<div class="col2">
<div class="createpage">
<div class="editpage"><?php $f->submit( array( 'name' => 'Save', 'class' => 'save', 'value' => 'Save changes' ) ); ?><?php $f->hidden( array( 'name' => 'id', 'value' => $u->id ) ); ?><span><i class="fa fa-floppy-o" aria-hidden="true"></i></span></div>
</div>
<div class="support">
<h2>Support <span><i class="fa fa-question-circle" aria-hidden="true"></i></span></h2> 
<div class="supportinfo">
<?php echo $see->SeeCMS->supportMessage; ?>
</div>
</div>
</div>

<?php $f->close(); ?>