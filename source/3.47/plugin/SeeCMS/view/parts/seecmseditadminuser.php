<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

$user = $data['user'];
$roles = $data['roles'];

$formSettings['controller']['name'] = 'SeeCMSAdminAuthentication';
$formSettings['controller']['method'] = 'update';

$formSettings['disableSpamProtection'] = true;

$f = $see->html->form( $formSettings );

?>
  <div class="col1">
    <div class="sectiontitle"><h2>Edit admin user</h2></div>
    
    <div class="columns"> 
      <div class="column columnwide">
        <div class="section">
          <input style="display:none">
          <input type="password" style="display:none">
          <div class="sg_input">
            <p>Name</p><p><?php $f->text( array( 'name' => 'name', 'value' => $user->name )); ?></p>
          </div>
          <div class="sg_input">
            <p>Email</p><p><?php $f->text( array( 'name' => 'email', 'value' => $user->email )); ?></p>
          </div>
          <div class="sg_input">
            <p>Password</p><p><?php $f->password( array( 'name' => 'password', 'value' => '', 'autocomplete' => 'off' )); ?><br/><sub>Leave blank unless you wish to reset the user's password</sub></p>
          </div>
          <div class="sg_input">
            <p>User level</p><p><?php $f->select( array( 'name' => 'level', 'value' => $user->adminuserrole_id ), array( 'options' => $roles ) ); ?></p>
          </div>
        </div>
      </div>
   
    </div>
      
    <div class="clear"></div>
    </div>
    
  <div class="col2">
    <div class="createpage">
      <div class="editpage"><?php $f->submit( array( 'name' => 'Save', 'class' => 'save', 'value' => 'Save changes' ) ); ?><?php $f->hidden( array( 'name' => 'id', 'value' => $user->id ) ); ?><span><i class="fa fa-floppy-o" aria-hidden="true"></i></span></div>
    </div>
    <div class="support">
      <h2>Support <span><i class="fa fa-question-circle" aria-hidden="true"></i></span></h2>
      <div class="supportinfo">
        <?php echo $see->SeeCMS->supportMessage; ?>
      </div>
    </div>
  </div>