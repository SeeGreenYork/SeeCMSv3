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

$formSettings['controller']['name'] = 'SeeCMSAdminAuthentication';
$formSettings['controller']['method'] = 'updateGroup';

$formSettings['disableSpamProtection'] = true;

$formSettings['validate']['name']['validate'] = 'required';
$formSettings['validate']['name']['error'] = 'Please enter a name.';

$f = $see->html->form( $formSettings );

?>
<div class="col1">
<div class="sectiontitle"><h2>Edit admin group</h2></div>

<div class="columns">
<div class="column">
<div class="section">
<div class="sg_input">
<p>Group name</p><p><?php $f->text( array( 'name' => 'name', 'value' => $g->name )); ?></p>
</div>
</div>
</div>
<div class="column">
<h2>Users</h2>
<table class="order">
<thead>
<tr><th>User name</th></tr>
</thead>
<tbody>
<?php

foreach( $users as $u ) {

  $member = $g->sharedAdminuser[$u->id]->id;
  echo "<tr><td><strong>{$u->name}</strong> ({$u->email})</td><td class=\"delete\">";
  $f->checkbox( array( 'name' => "seecmsadminuser-{$u->id}", 'value' => $member ));
  echo "</td></tr>";
}

?>

</tbody>
</table>
</div>
</div>

<div class="clear"></div>
</div>

<div class="col2">
<div class="createpage">
<div class="editpage"><?php $f->submit( array( 'name' => 'Save', 'class' => 'save', 'value' => 'Save changes' ) ); ?><?php $f->hidden( array( 'name' => 'id', 'value' => $g->id ) ); ?><span><i class="fa fa-floppy-o" aria-hidden="true"></i></span></div>
</div>
<div class="support"> 
<h2>Support</h2>
<div class="supportinfo">
<?php echo $see->SeeCMS->supportMessage; ?>
</div>
</div>
</div>
<?php $f->close(); ?>