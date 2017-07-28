<seecmsheader>
<script>
  $('body').addClass('loginwrap');
</script>
<div class="main loginpage">
  <div class="loginbox">
    <img src="/seecms/images/login-logo.png" alt="" />
    <?php 
    
    if( $this->see->multisite ) {
        
      $sites = SeeDB::findAll( 'site' );
      foreach( $sites as $site ) {
        
        $multisite .= (($multisite)?',':'')."multisite{$site->id}:{domain:\"{$site->name}\"}";
      }
      
      $this->see->html->js( '', 'var multisite = {'.$multisite.'};', '' );
    }

    $formSettings['controller']['name'] = 'SeeCMSAdminAuthentication';
    $formSettings['controller']['method'] = 'login';

    $formSettings['disableSpamProtection'] = true;
    $formSettings['attributes']['id'] = 'SeeCMSLoginForm';

    $formSettings['validate']['email']['validate'] = 'required,email';
    $formSettings['validate']['email']['error'] = 'Please enter a valid email address.';
    $formSettings['validate']['password']['validate'] = 'required';
    $formSettings['validate']['password']['error'] = 'Please enter your password.';

    $f = $see->html->form( $formSettings );

    ?>
    <div class="input email"><span></span><?php $f->text( array( 'name' => 'email', 'id' => 'email', 'placeholder' => 'Email address' ) ); ?></div>
    <div class="input password"><span></span><?php $f->password( array( 'name' => 'password', 'id' => 'password', 'placeholder' => 'Password' ) ); ?></div>
    <p><?php $f->submit( array( 'value' => 'Log in', 'id' => 'submit' ) ); ?></p>
    <div class="clear"></div>
    <?php $f->close(); ?>
  </div>
<seecmsfooternosearch>