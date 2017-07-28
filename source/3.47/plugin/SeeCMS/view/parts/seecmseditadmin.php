<?php

$user = $data['user'];
$routes = $data['pageRoutes'];
$templates = $data['templates'];

$formSettings['controller']['name'] = 'SeeCMSPage';
$formSettings['controller']['method'] = 'update';

$formSettings['disableSpamProtection'] = true;

$formSettings['validate']['name']['validate'] = 'required';
$formSettings['validate']['name']['error'] = 'Please enter a title.';

$f = $see->html->form( $formSettings );

?>
  <div class="col1">
    <div class="imagebuttons">
      <a class="import" href="#">Import from CSV</a><input type="file" accept=".csv" class="hidden" />
      <a class="export" href="#">Export to CSV</a>
      <div class="searchmedia users">
        <input type="text" value="Search users" class="searchmedia" name="searchmedia" onFocus="this.value=''" onblur="this.value='Search users'">
        <input type="submit" value="" name="searchsubmit">
      </div>
      <div class="clear"></div>
    </div>
    
    <div class="columns">
      <div class="column columnwide">
        <div class="section">
          <div class="sg_input">
            <p>First name</p><p><?php $f->text( array( 'name' => 'forename', 'value' => $user->forename )); ?></p>
          </div>
          <div class="sg_input">
            <p>Surname</p><p><?php $f->text( array( 'name' => 'surname', 'value' => $user->surname )); ?></p>
          </div>
          <div class="sg_input">
            <p>Email</p><p><?php $f->text( array( 'name' => 'email', 'value' => $user->email )); ?></p>
          </div>
          <div class="sg_input">
            <p>Password</p><p><?php $f->password( array( 'name' => 'password', 'value' => $user->password )); ?><br/><sub>Leave blank unless you wish to reset the user's password</sub></p>
          </div>
          <div class="sg_input">
            <p>User level</p><p><?php $f->select( array( 'name' => 'level', 'value' => $user->level )); ?></p>
          </div>
        </div>
      </div>
   
    </div>
      
    <div class="clear"></div>
    </div>
    
  <div class="col2">
    <div class="createpage">
      <a class="createuser" href="#">Save changes</a>
      <a class="cancel" href="#">Cancel</a>
    </div>
    <div class="support">
      <h2>Support <span><i class="fa fa-question-circle" aria-hidden="true"></i></span></h2>
      <div class="supportinfo">
        <p><span>Office hours are Mon-Fri 0900-1700</span></p>
        <div class="ticket">
          <p>Support ticket information goes here to reply to the ticket click on the link</p>
          <a href="#">View ticket &gt;</a>
        </div>
        <p><strong>Tel: 01904 500500</strong></p>
        <a href="#">Create ticket &gt;</a>
      </div>
    </div>
  </div>