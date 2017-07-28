<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeCMSWebsiteUserController {

  var $see;

  public function __construct( $see ) {

    $this->see = $see;
  }

  public function loadAll( $settings = null ) {

    $limit = (( isset($settings["limit"]) ) ? $settings["limit"] : "LIMIT 30" );   
    $r['userCount'] = SeeDB::getCell( "SELECT count(*) AS usercount FROM websiteuser" );
    $r['au'] = SeeDB::findAll( 'websiteuser', " ORDER BY surname, forename {$limit} " );

    $r = $this->prepareWebsiteUserData( $r );

    $r['view'] = $this->websiteUserDataDefaultView( $r );

    return( $r );
  }

  public function prepareWebsiteUserData( $r ) {

    // Load settings of what fields to display on the list
    $f = SeeCMSSettingController::load( 'websiteUserListFields' );
    if( $f ) {

      $r['fields'] = json_decode( $f );

      if( strstr( $f, '"type":"adf"' ) ) {

        $adfstoload = SeeDB::find( 'adf', ' objecttype = ? ', array( 'websiteuser' ) );
        if( is_array( $adfstoload ) ) {
          foreach( $adfstoload as $adf ) {
            $loadadfs[] = (int)$adf->id;
          }
        }

        if( is_array( $loadadfs ) ) {
          foreach( $au as &$wu ) {
            $cc = new SeeCMSContentController( $this->see, $this->see->SeeCMS->language );
            $adfs = reset( $cc->loadADFcontent( array( 'objectid' => $wu->id, 'type' => 'websiteuser', 'adfs' => $loadadfs ) ) );
            $wu->adfs = (object)$adfs['indexed'][$wu->id]['content'][0];
          }
        }
      }

    } else {

      $r['fields'] = (object)array( (object)array( 'title' => 'Surname', 'field' => 'surname', 'type' => '', 'format' => '', 'formatParameters' => '' ), (object)array( 'title' => 'Forename', 'field' => 'forename', 'type' => '', 'format' => '', 'formatParameters' => '' ), (object)array( 'title' => 'Email', 'field' => 'email', 'type' => '', 'format' => '', 'formatParameters' => '' ) );
    }

    // Load settings of what order to display if set
    $fo = SeeCMSSettingController::load( 'websiteUserListFieldOrder' );
    if( $fo ) {
      $r['order'] = json_decode( $fo );
    }

    return( $r );
  }

  public function websiteUserDataDefaultView( $data ) {

    $v .= '<table class="order users"><thead><tr>';

    foreach( $data['fields'] as $f ) {

      $v .= "<th>{$f->title}</th>";
    }

    $v .= '</tr></thead><tbody>';

    foreach( $data['au'] as $wu ) {

      $fod = '';

      if( isset( $data['order'] ) ) {

        $fod = (( $data['order']->type == 'adf' ) ? $wu->adfs->{$data['order']->field} : $wu->{$data['order']->field} );

        if( $data['order']->format ) {

          $fod = $see->format->{$data['order']->format}( $fod, $data['order']->formatParameters );
        }
      }

      $row[$fod] .= "<tr id=\"wu{$wu->id}\">";

      foreach( $data['fields'] as $f ) {

        $fd = (( $f->type == 'adf' ) ? $wu->adfs->{$f->field} : $wu->{$f->field} );

        if( $f->format ) {

          $fd = $see->format->{$f->format}( $fd, $f->formatParameters );
        }

        $row[$fod] .= "<td>{$fd}</td>";
      }

      $row[$fod] .= "<td><a href=\"editusers/?id={$wu->id}\">Edit</a></td><td class=\"delete\"><a class=\"delete\" data-siteuserid=\"{$wu->id}\" title=\"Delete {$wu->forename} {$wu->surname}\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i></a></td>";

      if( $wu->activation ) {
        $row[$fod] .= "<td class=\"activate\"><a class=\"activate\" data-siteuserid=\"{$wu->id}\" data-siteuseremail=\"{$wu->email}\" data-siteuseractivation=\"{$wu->activation}\" title=\"Activate {$wu->forename} {$wu->surname}\" data-name=\"{$wu->forename} {$wu->surname}\"><i class=\"fa fa-lock\" aria-hidden=\"true\"></i></a></td><td class=\"notification\"></td>";
      } else {
        $row[$fod] .= "<td class=\"deactivate\"><a class=\"deactivate\" data-siteuserid=\"{$wu->id}\" data-siteuseremail=\"{$wu->email}\" title=\"Deactivate {$wu->forename} {$wu->surname}\" data-name=\"{$wu->forename} {$wu->surname}\"><i class=\"fa fa-lock\" aria-hidden=\"true\"></i></a></td><td class=\"notification\"></td>";
      }

      $row[$fod] .= "</tr>";
    }

    if( isset( $data['order']->order ) ) {

      if( $data['order']->order == 'desc' ) {
        krsort( $row );
      } else {
        ksort( $row );
      }
    }

    $v .= implode( "", $row );

    $v .= '</tbody></table>';

    return( $v );
  }

  public function loadForEdit( $id = 0 ) {

    if( !$id ) {
      $id = $_GET['id'];
    }

    $d['user'] = SeeDB::load( 'websiteuser', $id );
    $d['groups'] = SeeDB::findAll( 'websiteusergroup', ' ORDER BY name ' );

    return( $d );
  }

  public function delete() {

    $wu = SeeDB::load( 'websiteuser', $_POST['id'] );
    SeeDB::trash( $wu );

    // Delete ADFs
    SeeDB::exec( " DELETE FROM adfcontent WHERE objectid = ? && objecttype = 'websiteuser' ", array( $_POST['id'] ) );

    echo 'Done';
  }

  public function login( $data, $errors, $settings ) {

    if( !$errors && $data['seecmswebsiteuserpassword'] ) {

      $u = SeeDB::findOne( 'websiteuser', ' email = ? ', array( $data['seecmswebsiteuseremail'] ) );

      if( $u ) {

        if( !$u->activation ) {

          $activated = true;

          if( $u->passwordformat == 'md5' ) {

            $data['seecmswebsiteuserpassword'] = md5( $data['seecmswebsiteuserpassword'] );
            $password = strtolower( $u->password );
            $passwordCheck = (($password==$data['seecmswebsiteuserpassword'])?true:false);
          } else if( $u->passwordformat == 'aes256' ) {

            $password = $this->see->security->decAES256( $u->password );
            $passwordCheck = (($password==$data['seecmswebsiteuserpassword'])?true:false);
          } else {

            $passwordCheck = password_verify($data['seecmswebsiteuserpassword'],$u->password);
          }

          if( $passwordCheck ) {
            $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] = $u->id;
            $_SESSION['seecms'][$this->see->siteID]['websiteuser']['forename'] = $u->forename;
            $_SESSION['seecms'][$this->see->siteID]['websiteuser']['surname'] = $u->surname;
            $_SESSION['seecms'][$this->see->siteID]['websiteuser']['email'] = $u->email;

            $adfs = SeeDB::find( 'adf', ' objecttype = ? ', array( 'websiteuser' ) );
            if( is_array( $adfs ) ) {
              foreach( $adfs as $adf ) {
                $loadadfs[] = (int)$adf->id;
              }
              $cc = new SeeCMSContentController( $this->see, $this->see->SeeCMS->language );
              $_SESSION['seecms'][$this->see->siteID]['websiteuser']['adf'] = $cc->loadADFcontent( array( 'objectid' => $u->id, 'type' => 'websiteuser', 'adfs' => $loadadfs ) );
            }

            $ret['result'] = 1;
            $this->see->SeeCMS->hook->run( array( 'hook' => 'websiteuser-login', 'data' => $u ) );

             if( isset( $_SESSION['restrictedRouteRequest'] ) ) {
              $redirect = $_SESSION['restrictedRouteRequest'];
            } else if( isset( $settings['settings']['successredirect'] ) ) {
              $redirect = $settings['settings']['successredirect'];
            }

            if( isset( $redirect ) ) {

              if( $redirect == '/' ) {
                $redirect = substr_replace( $redirect, '/'.$this->see->rootURL, 0, 1 );
              }
              $this->see->redirect( $redirect );
            }
          }
        } else {
          $notactivated = true;
        }
      }

      if( $notactivated ) {
        $ret['errors']['email'] = 'Your account needs to be activated before you can log in.';
      } else if( !isset( $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) || !$_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) {
        $ret['errors']['email'] = 'Login details incorrect.';
      }
    }

    return( $ret );
  }

  public function update( $data, $errors, $settings ) {

    if( !$errors ) {

      $u = SeeDB::load( 'websiteuser', $data['id'] );

      $new = (($u->id)?0:1);

      $u->title = (string)$data['seecmswebsiteusertitle'];
      $u->forename = (string)$data['seecmswebsiteuserforename'];
      $u->surname = (string)$data['seecmswebsiteusersurname'];
      $u->organisation = (string)$data['seecmswebsiteuserorganisation'];
      $u->jobtitle = (string)$data['seecmswebsiteuserjobtitle'];
      $u->address1 = (string)$data['seecmswebsiteuseraddress1'];
      $u->address2 = (string)$data['seecmswebsiteuseraddress2'];
      $u->address3 = (string)$data['seecmswebsiteuseraddress3'];
      $u->city = (string)$data['seecmswebsiteusercity'];
      $u->region = (string)$data['seecmswebsiteuserregion'];
      $u->postcode = (string)$data['seecmswebsiteuserpostcode'];
      $u->country = trim( (string)$data['seecmswebsiteusercountry'] );
      $u->email = (string)$data['seecmswebsiteuseremail'];
      $u->telephone = (string)$data['seecmswebsiteusertelephone'];

      if( $data['seecmswebsiteuserpassword'] ) {

          $u->password = password_hash( $data['seecmswebsiteuserpassword'], PASSWORD_BCRYPT );
          $u->passwordformat = 'hash';
      } else if( !$u->password ) {

        $u->password = password_hash( base64_encode( openssl_random_pseudo_bytes( 32 ) ), PASSWORD_BCRYPT );
        $u->passwordformat = 'hash';
      }

      if( $new ){
        $u->creationdate = date( "Y-m-d H:i:s" );
      }

      $u->modifieddate = date( "Y-m-d H:i:s" );

      SeeDB::store( $u );

      // Custom site user data
      if( isset( $this->see->SeeCMS->customSiteUserController['plugin'] ) ) {
        $customSiteUserController = $this->see->{$this->see->SeeCMS->customSiteUserController['plugin']};
        $customSiteUserController->saveFields( $u, $data );
      }

      // ADFs
      $adfs = SeeDB::find( 'adf', ' objecttype = ? ', array( 'websiteuser' ) );
      $cc = new SeeCMSContentController( $this->see, $this->see->SeeCMS->language );
      if( is_array( $adfs ) ) {
        foreach( $adfs as $adf ) {
          $data = array();
          foreach( $_POST as $pfK => $pfD ) {
            if( strstr( $pfK, "adf{$adf->id}-" ) ) {
              $data[$pfK] = $pfD;
            }
          }

          $cc->edit( array( 'settingsScreen' => 1, 'objectType' => 'websiteuser', 'objectID' => $u->id, 'containerID' => $adf->id, 'data' => $data ), true );
        }
      }

      $this->see->redirect( "./?id={$u->id}" );
    }

    return( $errors );
  }

  public function selfUpdate( $data, $errors, $settings ) {

    if( !$errors ) {

      $logUserIn = true;

      if( isset( $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) ) {

        $u = SeeDB::load( 'websiteuser', $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] );
      } else {

        $u = SeeDB::findOne( 'websiteuser', ' email = ? ', array( $data['seecmswebsiteuseremail'] ) );
        if( $u->id ) {

          $ret['errors']['seecmswebsiteuseremail'] = 'Error - your registration could not be made, please contact us.';
        }
      }

      if( $data['seecmswebsiteuserpasswordnew'] != $data['seecmswebsiteuserconfirmpasswordnew'] ) {

        $ret['errors']['seecmswebsiteuserconfirmpassword'] = 'Please ensure the password and confirm password fields match.';
      }

      if( !is_array( $ret['errors'] ) ) {

        if( !$u->id ) {
          $u = SeeDB::dispense( 'websiteuser' );
          $newUser = true;
          $ret["newUser"] = 1;
        }

        $u->title = (string)$data['seecmswebsiteusertitle'];
        $u->forename = (string)$data['seecmswebsiteuserforename'];
        $u->surname = (string)$data['seecmswebsiteusersurname'];
        $u->organisation = (string)$data['seecmswebsiteuserorganisation'];
        $u->jobtitle = (string)$data['seecmswebsiteuserjobtitle'];
        $u->address1 = (string)$data['seecmswebsiteuseraddress1'];
        $u->address2 = (string)$data['seecmswebsiteuseraddress2'];
        $u->address3 = (string)$data['seecmswebsiteuseraddress3'];
        $u->city = (string)$data['seecmswebsiteusercity'];
        $u->region = (string)$data['seecmswebsiteuserregion'];
        $u->postcode = (string)$data['seecmswebsiteuserpostcode'];
        $u->country = (string)$data['seecmswebsiteusercountry'];
        $u->email = (string)$data['seecmswebsiteuseremail'];
        $u->telephone = (string)$data['seecmswebsiteusertelephone'];

        if( $data['seecmswebsiteuserpassword'] ) {

          $changePassword = true;

          if( $u->id ) {

            $changePassword = false;

            if( $u->passwordformat == 'md5' ) {

              $data['seecmswebsiteuserpassword'] = md5( $data['seecmswebsiteuserpassword'] );
              $password = strtolower( $u->password );
              $passwordCheck = (($password==$data['seecmswebsiteuserpassword'])?true:false);
            } else if( $u->passwordformat == 'aes256' ) {

              $password = $this->see->security->decAES256( $u->password );
              $passwordCheck = (($password==$data['seecmswebsiteuserpassword'])?true:false);
            } else {

              $passwordCheck = password_verify($data['seecmswebsiteuserpassword'],$u->password);
            }

            if( $passwordCheck ) {
              $changePassword == true;
            }

          }

          if( $changePassword ) {
            $u->password = password_hash( $data['seecmswebsiteuserpasswordnew'], PASSWORD_BCRYPT );
            $u->passwordformat = 'hash';
          }
        }
        
        if( $newuser ){
          $u->creationdate = date( "Y-m-d H:i:s" );
        }
        
        $u->modifieddate = date( "Y-m-d H:i:s" );

        SeeDB::store( $u );

        if( $newUser ) {
          

          
          $wug = SeeDB::find( 'websiteusergroup', ' autoaddnewusers = ? ', array( 1 ) );
          foreach( $wug as $g ) {

            $u->sharedWebsiteusergroup[] = $g;
          }

          if( isset( $settings['settings']['activationRequired'] ) ) {
            $activationRequired = $settings['settings']['activationRequired'];
          } else {
            $activationRequired = SeeDB::findOne( 'setting', ' name = ? ', array( 'websiteUserActivation' ) );
            $activationRequired = $activationRequired->value;
          }

          if( isset( $activationRequired ) ) {

            $u->activation = md5( microtime().rand(0,10000000) );

            if( isset( $settings['settings']['websiteUserActivationPage'] ) ) {
              $websiteUserActivationPage = $settings['settings']['websiteUserActivationPage'];
            } else {
              $websiteUserActivationPage = SeeDB::findOne( 'setting', ' name = ? ', array( 'websiteUserActivationPage' ) );
              $websiteUserActivationPage = $websiteUserActivationPage->value;
            }

            $emailTemplate = SeeDB::findOne('setting', " name = 'email' ");

            $link = 'http'.(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 's' : '').'://'."{$_SERVER['HTTP_HOST']}{$websiteUserActivationPage}?email={$u->email}&amp;activation={$u->activation}";

            $emailContent = "<h2>{$settings['settings']['emailSubject']}</h2><hr /><p>Please visit the link below to activate your account.<br /><a href=\"{$link}\">{$link}</a></p>";

            $email = str_replace( '<EMAILCONTENT>', $emailContent, $emailTemplate->value );

            $seeemail = new SeeEmailController();
            $seeemail->sendHTMLEmail( $settings['settings']['emailFrom'], $u->email, $email, $settings['settings']['emailSubject'] );

            $logUserIn = false;
          }

          if( $settings['settings']['notifyAdmin']['to'] ) {

            $edata['title'] = $u->title;
            $edata['forename'] = $u->forename;
            $edata['surname'] = $u->surname;
            $edata['organisation'] = $u->organisation;
            $edata['jobtitle'] = $u->jobtitle;
            $edata['address 1'] = $u->address1;
            $edata['address 2'] = $u->address2;
            $edata['address 3'] = $u->address3;
            $edata['city'] = $u->city;
            $edata['region'] = $u->region;
            $edata['postcode'] = $u->postcode;
            $edata['country'] = $u->country;
            $edata['email'] = $u->email;
            $edata['telephone'] = $u->telephone;

            $configData['settings']['to'] = $settings['settings']['notifyAdmin']['to'];
            $configData['settings']['from'] = $settings['settings']['notifyAdmin']['from'];
            $configData['settings']['subject'] = $settings['settings']['notifyAdmin']['subject'];

            $fp = new SeeFormProcessController( $this->see );
            $fp->sendByEmail( $edata, array(), $configData );
          }
        }

        SeeDB::store( $u );

        if( $logUserIn ) {
          $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] = $u->id;
          $_SESSION['seecms'][$this->see->siteID]['websiteuser']['forename'] = $u->forename;
          $_SESSION['seecms'][$this->see->siteID]['websiteuser']['surname'] = $u->surname;
          $_SESSION['seecms'][$this->see->siteID]['websiteuser']['email'] = $u->email;
        }

        $ret['result'] = 1;

        if( $settings["settings"]["returnData"] ){
          $ret["user"] = $u;
        }

        if( $settings['settings']['successredirect'] ) {

          $this->see->redirect( $settings['settings']['successredirect'] );
        }
      }
    }

    return( $ret );
  }

  public function logout() {

    unset( $_SESSION['seecms'][$this->see->siteID]['websiteuser'] );
  }

  public function loginForm( $settings ) {

    if( $_GET['logout'] ) {

      $this->logout();
      $this->see->redirect( './' );
    }

    if( isset( $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) && !$_GET['preview'] && isset( $settings['redirect'] ) && !$settings['onlyRedirectOnLogin'] ) {

      $this->see->redirect( $settings['redirect'] );
    }

    $formSettings['controller']['name'] = 'SeeCMSWebsiteUser';
		$formSettings['controller']['method'] = 'login';
    $formSettings['controller']['class'] = "seecmswebsiteuserlogin";
    $formSettings['controller']['settings']['successredirect'] = @$settings['redirect'];

		$formSettings['validate']['seecmswebsiteuseremail']['validate'] = 'email';
		$formSettings['validate']['seecmswebsiteuseremail']['error'] = 'Please enter a valid email address.';
		$formSettings['validate']['seecmswebsiteuserpassword']['validate'] = 'required';
		$formSettings['validate']['seecmswebsiteuserpassword']['error'] = 'Please enter your password.';

    $formSettings['validate']['address4']['validate'] = 'mustbe=';

    ob_start();

    $f = $this->see->html->form( $formSettings );

    if( isset( $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) ) {

      ob_clean();
      echo "<p class=\"seecmsloggedinmessage\">{$settings['loggedInMessage']}</p>";

    } else {

      echo "<div class=\"input\"><label for=\"email\">Email address</label><br />";
      $f->text( array( 'name' => 'seecmswebsiteuseremail', 'value' => '') );
      echo "</div><div class=\"input\"><label for=\"password\">Password</label><br />";
      $f->password( array( 'name' => 'seecmswebsiteuserpassword', 'value' => '') );
      echo "</div><div style=\"display: none;\" class=\"input\" id=\"seecmssc\">";
      /* spam catcher */
      $f->text( array( 'name' => 'seecmssc', 'value' => '') );
      echo "</div><div class=\"clear\"></div>";
      $f->submit( array( 'name' => 'seecmswebsiteuserlogin', 'class' => 'submitbutton', 'value' => 'Login') );
      $f->close();

    }

    return( ob_get_clean() );
  }

  public function registerForm( $settings ) {

    $formSettings['controller']['name'] = 'SeeCMSWebsiteUser';
    $formSettings['controller']['method'] = 'selfUpdate';
    $formSettings['controller']['class'] = "seecmswebsiteuserregister";
    $formSettings['controller']['settings']['successredirect'] = $settings['redirect'];

    $formSettings['validate']['seecmswebsiteuserforename']['validate'] = 'required';
    $formSettings['validate']['seecmswebsiteuserforename']['error'] = 'Please enter your first name.';
    $formSettings['validate']['seecmswebsiteusersurname']['validate'] = 'required';
    $formSettings['validate']['seecmswebsiteusersurname']['error'] = 'Please enter your last name.';
    $formSettings['validate']['seecmswebsiteuseremail']['validate'] = 'email';
    $formSettings['validate']['seecmswebsiteuseremail']['error'] = 'Please enter a valid email address.';
    $formSettings['validate']['seecmswebsiteusertelephone']['validate'] = 'required';
    $formSettings['validate']['seecmswebsiteusertelephone']['error'] = 'Please enter a contact telephone number.';

    if( isset( $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) ) {

      $u = SeeDB::load( 'websiteuser', $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] );
    } else {

      $formSettings['validate']['seecmswebsiteuserpassword']['validate'] = 'required';
      $formSettings['validate']['seecmswebsiteuserpassword']['error'] = 'Please enter a password.';
      $formSettings['validate']['seecmswebsiteuserconfirmpassword']['validate'] = 'required';
      $formSettings['validate']['seecmswebsiteuserconfirmpassword']['error'] = 'Please confirm your password.';
    }

    $formSettings['validate']['seecmswebsiteuseraddress4']['validate'] = 'mustbe=';

    ob_start();

    echo '<div class="seecmswebsiteuserregisteruser">';
    $f = $this->see->html->form( $formSettings );

		echo '<div class="clear"></div>';
		echo '<h2>Your details</h2>';
		echo '<div class="input"><label for="seecmswebsiteusertitle">Title</label><br />';
    $f->text( array( 'name' => 'seecmswebsiteusertitle', 'value' => $u->title ) );
    echo '</div><div class="clear"></div>';
		echo '<div class="input"><label for="seecmswebsiteuserforename">First Name*</label><br />';
    $f->text( array( 'name' => 'seecmswebsiteuserforename', 'value' => $u->forename ) );
    echo '</div><div class="input"><label for="seecmswebsiteusersurname">Last Name*</label><br />';
    $f->text( array( 'name' => 'seecmswebsiteusersurname', 'value' => $u->surname ) );
    echo '</div><div class="input"><label for="seecmswebsiteuserorganisation" style="line-height: 100%">Company / Organisation</label><br />';
    $f->text( array( 'name' => 'seecmswebsiteuserorganisation', 'value' => $u->organisation ) );
    echo '</div><div class="clear"></div><div class="input"><label for="seecmswebsiteusertelephone">Telephone*</label><br/>';
    $f->text( array( 'name' => 'seecmswebsiteusertelephone', 'value' => $u->telephone ) );
    echo '</div><div class="input"><label for="seecmswebsiteuseremail">Email*</label><br />';
    $f->text( array( 'name' => 'seecmswebsiteuseremail', 'value' => $u->email ) );
    echo '</div><div class="clear"></div><hr /><h2>Address</h2>';
		echo '<div class="input"><label for="seecmswebsiteuseraddress1">Address 1</label><br />';
    $f->text( array( 'name' => 'seecmswebsiteuseraddress1', 'value' => $u->address1 ) );
    echo '</div><div class="input"><label for="seecmswebsiteuseraddress2">Address 2</label><br />';
    $f->text( array( 'name' => 'seecmswebsiteuseraddress2', 'value' => $u->address2 ) );
    echo '</div><div class="input"><label for="seecmswebsiteuseraddress3">Address 3</label><br />';
    $f->text( array( 'name' => 'seecmswebsiteuseraddress3', 'value' => $u->address3 ) );
    echo '</div><div style="display: none;" class="input" id="seecmswebsiteuseraddress4">';
    $f->text( array( 'name' => 'seecmswebsiteuseraddress4', 'value' => '') );
    echo '</div><div class="input"><label for="seecmswebsiteusercity">Town/City</label><br />';
    $f->text( array( 'name' => 'seecmswebsiteusercity', 'value' => $u->city ) );
    echo '</div><div class="input"><label for="seecmswebsiteuserregion">Region</label><br />';
    $f->text( array( 'name' => 'seecmswebsiteuserregion', 'value' => $u->region ) );
    echo '</div><div class="input"><label for="seecmswebsiteuserpostcode">Postcode</label><br />';
    $f->text( array( 'name' => 'seecmswebsiteuserpostcode', 'value' => $u->postcode ) );
    echo '</div><div class="input"><label for="seecmswebsiteusercountry">Country</label><br />';
    if( count( $this->see->SeeCMS->config["websiteUserRegisterCountries"] ) ){
      $f->select( array( "name" => "seecmswebsiteusercountry", "value" => $u->country ), array( "options" => $this->see->SeeCMS->config["websiteUserRegisterCountries"], 'optionValueOnly' => true ) );
      echo "</div>";
    }
    else {
      $f->text( array( 'name' => 'seecmswebsiteusercountry', 'value' => "United Kingdom",  ) );
      echo '</div>';
    }

    echo "<div class=\"clear\"></div><hr /><h2>Registration details</h2>";
    $pRequired = '*';

    if( isset( $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) ) {
      echo "<p>Please leave these fields blank unless you'd like to set a new password.</p>";
      $pAppend = 'New ';
      $pRequired = '';

      echo '<div class="input"><label for="seecmswebsiteuserpasswordcurrent">Current password'.$pRequired.'</label><br />';
      $f->password( array( 'name' => 'seecmswebsiteuserpassword', 'value' => '') );
      echo '</div><div class="clear"></div>';
    }

		echo '<div class="input"><label for="seecmswebsiteuserpassword">Password'.$pRequired.'</label><br />';
    $f->password( array( 'name' => 'seecmswebsiteuserpasswordnew', 'value' => '') );
    echo '</div><div class="input"><label for="seecmswebsiteuserconfirmpassword" style="line-height: 100%">Confirm password'.$pRequired.'</label><br />';
    $f->password( array( 'name' => 'seecmswebsiteuserconfirmpasswordnew', 'value' => '') );
    echo '</div><div class="clear"></div>';

		$f->hidden( array( 'name' => 'r', 'value' => $settings['redirect'] ) );
		$f->submit( array( 'name' => 'submit', 'class' => 'submitbutton', 'value' => ((!isset($_SESSION['seecms'][$this->see->siteID]['websiteuser']['id']))?'Register':'Update') ) );

		echo '<div class="clear"></div>';
		$f->close();
    echo '</div>';

    return( ob_get_clean() );
  }

  public function passwordRecoveryForm( $settings ) {

    if( ( $_GET['email'] && $_GET['passwordrecovery'] ) || ( $_POST['seecmswebsiteuserpasswordrecoveryemail'] && $_POST['seecmswebsiteuserpasswordrecoverycode'] ) ) {

      if( $_GET['email'] ) {
        $email = $_GET['email'];
        $passwordrecovery = $_GET['passwordrecovery'];
      } else {
        $email = $_POST['seecmswebsiteuserpasswordrecoveryemail'];
        $passwordrecovery = $_POST['seecmswebsiteuserpasswordrecoverycode'];
      }

      $u = SeeDB::findOne( 'websiteuser', ' email = ? && passwordrecovery = ? ', array( $email, $passwordrecovery ) );
      if( $u ) {

        echo $this->changePasswordForm( $settings );
      } else {

        echo "<p style=\"seecmspasswordrecoveryerror\">Sorry. Those details are incorrect. Please try again.</p>";
      }

    } else {

      $formSettings['controller']['name'] = 'SeeCMSWebsiteUser';
      $formSettings['controller']['method'] = 'passwordRecovery';
      $formSettings['controller']['class'] = "seecmswebsiteuserpasswordrecovery";
      $formSettings['controller']['settings']['emailFrom'] = $settings['emailFrom'];
      $formSettings['controller']['settings']['emailSubject'] = $settings['emailSubject'];

      $formSettings['validate']['seecmswebsiteuseremail']['validate'] = 'email';
      $formSettings['validate']['seecmswebsiteuseremail']['error'] = 'Please enter a valid email address.';

      $formSettings['validate']['seecmssc']['validate'] = 'mustbe=';

      ob_start();

      $f = $this->see->html->form( $formSettings );

      if( $f->returndata['complete'] ) {

        ob_clean();
        echo "<p style=\"seecmspasswordrecorymessage\">{$settings['recoveryEmailSentThankYouMessage']}</p>";

      } else {

        echo "<div class=\"input\"><label for=\"email\">Email address</label><br />";
        $f->text( array( 'name' => 'seecmswebsiteuseremail', 'value' => '') );
        echo "</div><div style=\"display: none;\" class=\"input\" id=\"seecmssc\">";
        /* spam catcher */
        $f->text( array( 'name' => 'seecmssc', 'value' => '') );
        echo "</div><div class=\"clear\"></div>";
        $f->submit( array( 'name' => 'seecmswebsiteuserpasswordrecovery', 'class' => 'submitbutton', 'value' => 'Recover password') );
        $f->close();

      }
    }

    return( ob_get_clean() );
  }

  public function changePasswordForm( $settings ) {

    $formSettings['controller']['name'] = 'SeeCMSWebsiteUser';
    $formSettings['controller']['method'] = 'changePassword';
    $formSettings['controller']['class'] = "seecmswebsiteuserpasswordrecovery";

    $formSettings['validate']['seecmswebsiteuserpassword']['validate'] = 'minlength=8';
    $formSettings['validate']['seecmswebsiteuserpassword']['error'] = 'Please ensure your password is a minimum of 8 characters';

    $formSettings['validate']['seecmssc']['validate'] = 'mustbe=';

    ob_start();

    $f = $this->see->html->form( $formSettings );

    if( $f->returndata['complete'] ) {

      ob_clean();
      echo "<p style=\"seecmspasswordrecorymessage\">{$settings['passwordChangedThankYouMessage']}</p>";

    } else {

      echo "<div class=\"input\"><label for=\"email\">Password</label><br />";
      $f->password( array( 'name' => 'seecmswebsiteuserpassword' ) );
      echo "</div><div class=\"input\"><label for=\"email\">Confirm password</label><br />";
      $f->password( array( 'name' => 'seecmswebsiteuserconfirmpassword' ) );
      echo "</div><div style=\"display: none;\" class=\"input\" id=\"seecmssc\">";
      /* spam catcher */
      $f->text( array( 'name' => 'seecmssc', 'value' => '') );
      echo "</div><div class=\"clear\"></div>";
      $f->hidden( array( 'name' => 'seecmswebsiteuserpasswordrecoverycode', 'value' => $_GET['passwordrecovery'] ) );
      $f->hidden( array( 'name' => 'seecmswebsiteuserpasswordrecoveryemail', 'value' => $_GET['email'] ) );
      $f->submit( array( 'name' => 'seecmswebsiteuserpasswordrecovery', 'class' => 'submitbutton', 'value' => 'Set password') );
      $f->close();

    }

    return( ob_get_clean() );
  }

  public function passwordRecovery( $data, $errors, $settings ) {

    if( !$errors ) {

      sleep(1);

      $u = SeeDB::findOne( 'websiteuser', ' email = ? ', array( $data['seecmswebsiteuseremail'] ) );
      if( $u ) {

        $u->passwordrecovery = md5( microtime().rand(0,10000000) );
        SeeDB::store( $u );

        $emailTemplate = SeeDB::findOne('setting', " name = 'email' ");

        $link = 'http'.(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 's' : '').'://'."{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}?email={$u->email}&amp;passwordrecovery={$u->passwordrecovery}";

        $emailContent = "<h2>{$settings['settings']['emailSubject']}</h2><hr /><p>Please visit the link below to reset your password.<br /><a href=\"{$link}\">{$link}</a></p>";

        $email = str_replace( '<EMAILCONTENT>', $emailContent, $emailTemplate->value );

        $seeemail = new SeeEmailController();
        $seeemail->sendHTMLEmail( $settings['settings']['emailFrom'], $u->email, $email, $settings['settings']['emailSubject'] );

      }

      $ret['complete'] = true;
    }

    return( $ret );
  }

  public function changePassword( $data, $errors, $settings ) {

    if( !$errors ) {

      $u = SeeDB::findOne( 'websiteuser', ' email = ? && passwordrecovery = ? ', array( $data['seecmswebsiteuserpasswordrecoveryemail'], $data['seecmswebsiteuserpasswordrecoverycode'] ) );

      if( !$u->id ) {
        $u = SeeDB::load( 'websiteuser', $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] );
      }

      if( $u ) {

        if( $data['seecmswebsiteuserpassword'] != $data['seecmswebsiteuserconfirmpassword'] ) {

          $ret['errors']['seecmswebsiteuserconfirmpassword'] = 'Please ensure the password and confirm password fields match.';
        } else {

          $u->password = password_hash( $data['seecmswebsiteuserpassword'], PASSWORD_BCRYPT );
          $u->passwordformat = 'hash';
          $u->passwordrecovery = '';

          SeeDB::store( $u );

          $ret['complete'] = true;
        }
      }
    }

    return( $ret );
  }

  public function loadAllGroups() {

    $ag = SeeDB::findAll( 'websiteusergroup', ' ORDER BY name ' );

    return( $ag );
  }

  public function loadGroupForEdit( $id = 0 ) {

    if( !$id ) {
      $id = $_GET['id'];
    }

    $d['group'] = SeeDB::load( 'websiteusergroup', $id );
    $d['users'] = SeeDB::findAll( 'websiteuser', ' ORDER BY surname, forename ' );

    return( $d );
  }

  public function updateGroup( $data, $errors, $settings ) {

    if( !$errors ) {

      $g = SeeDB::load( 'websiteusergroup', $data['id'] );
      $g->name = $data['name'];

      SeeDB::store( $g );

      // ADFs
      $adfs = SeeDB::find( 'adf', ' objecttype = ? ', array( 'websiteusergroup' ) );
      $cc = new SeeCMSContentController( $this->see, $this->see->SeeCMS->language );
      if( is_array( $adfs ) ) {
        foreach( $adfs as $adf ) {
          $data = array();
          foreach( $_POST as $pfK => $pfD ) {
            if( strstr( $pfK, "adf{$adf->id}-" ) ) {
              $data[$pfK] = $pfD;
            }
          }

          $cc->edit( array( 'settingsScreen' => 1, 'objectType' => 'websiteusergroup', 'objectID' => $g->id, 'containerID' => $adf->id, 'data' => $data ), true );
        }
      }

      if( $this->see->SeeCMS->customSiteUserGroupController["plugin"] ){
        $customSiteUserGroupController = $this->see->{$this->see->SeeCMS->customSiteUserGroupController["plugin"]};
        $customSiteUserGroupController->saveFields( $g, $data );
      }

      SeeDB::store( $g );
      $this->see->redirect( "./?id={$g->id}" );
    }

    return( $errors );
  }

  public function deleteGroup() {

    $wug = SeeDB::load( 'websiteusergroup', $_POST['id'] );
    SeeDB::trash( $wug );
    echo 'Done';
  }

  public static function setPermission( $objectID, $objectType, $groupIDs ) {

    // Delete old permissions
    SeeDB::exec( " DELETE FROM websiteusergrouppermission WHERE objectid = {$objectID} && objecttype = '{$objectType}' " );

    if( is_array( $groupIDs ) ) {
      foreach( $groupIDs as $g ) {
        // Insert permission
        $gp = SeeDB::dispense( 'websiteusergrouppermission' );
        $gp->objectid = $objectID;
        $gp->objecttype = strtolower( $objectType );
        $gp->websiteusergroup_id = $g;
        SeeDB::store( $gp );
      }
    }
  }

  public static function cascadePermission( $objectID, $objectType, $groupIDs ) {

    $obs = SeeDB::find( $objectType, ' parentid = ? && deleted = ? ', array( $objectID, '0000-00-00 00:00:00' ) );

    if( is_array( $obs ) ) {
      foreach( $obs as $o ) {
        // Insert permission
        SeeCMSWebsiteUserController::setPermission( $o->id, $objectType, $groupIDs );
        // Cascade again
        SeeCMSWebsiteUserController::cascadePermission( $o->id, $objectType, $groupIDs );
      }
    }
  }

  public function activate() {

    $u = SeeDB::findOne( 'websiteuser', ' email = ? && activation = ? ', array( (($_GET['email'])?$_GET['email']:$_POST['email']), (($_GET['activation'])?$_GET['activation']:$_POST['activation']) ) );

    if( $u ) {
      $u->activation = '';
      SeeDB::store( $u );
      $this->see->SeeCMS->hook->run( array( 'hook' => 'websiteuser-activate', 'data' => $u ) );
      $ret = true;

    } else {

      $ret = false;
    }

    if( $_SESSION['seecms'][$this->see->siteID]['adminuser']['id'] && $_POST['admin'] ) {

      $ret = json_encode( array( 'status' => $ret, 'uid' => $u->id ) );
    }

    return( $ret );
  }

  public function deactivate() {

    $u = SeeDB::load( 'websiteuser', $_POST['id'] );
    $u->activation = md5( microtime().rand(0,10000000) );
    SeeDB::store( $u );

    $ret = $u->activation;

    if( $_SESSION['seecms'][$this->see->siteID]['adminuser']['id'] && $_POST['admin'] ) {

      $ret = json_encode( array( 'activation' => $ret, 'uid' => $u->id ) );
    }

    return( $ret );
  }

  public function adminSearch( $keyword = '' ) {

    $limit = ( $_POST['limit'] ? (int)$_POST['limit'] : 10 );
    $keyword = ( $keyword ? $keyword : $_POST['search'] );
    $html = ( $_POST['loadHTML'] ? true : false );

    if( $html && $_POST['loadHTML'] == 'groupuserfilter' ) {

      $g = SeeDB::load( 'websiteusergroup', $_POST['groupid'] );
    }

    $users = SeeDB::find( 'websiteuser', ' ( CONCAT( forename, " ", surname ) LIKE ? || email LIKE ? || organisation LIKE ? ) ORDER BY surname LIMIT ? ', array( "%{$keyword}%", "%{$keyword}%", "%{$keyword}%", $limit ) );

    if( $html && $_POST['loadHTML'] == 'websiteuserfilter' ) {

      $r = $this->prepareWebsiteUserData( array( 'au' => $users ) );
      $htmlContent = $this->websiteUserDataDefaultView( $r );
      $r = json_encode( array( 'html' => $htmlContent ) );

    }
    else {

      foreach( $users as $u ) {

        if( $html && $_POST['loadHTML'] == 'groupuserfilter' ) {

          $member = $g->sharedWebsiteuser[$u->id]->id;
          $r .= "<tr id=\"wu{$u->id}\"><td><strong>{$u->surname}, {$u->forename}</strong> ({$u->email})</td><td class=\"delete\"><input type=\"checkbox\" class=\"togglegroupuser\" ".(($member)?'checked="checked"':'')." data-userid=\"{$u->id}\" data-groupid=\"{$g->id}\" />";
          $r .= "</td><td class=\"notification\"></td></tr>";

        }
        else {
          $r[] = array( 'id' => $u->id, 'name' => "{$u->forename} {$u->surname}", 'organisation' => $u->organisation, 'email' => $u->email );
        }

      }

      if( $html && $_POST['loadHTML'] == 'groupuserfilter' ) {

        if( $r ) {
          $htmlContent = "<table class=\"order\"><thead><tr><th>User name</th></tr></thead><tbody>{$r}</tbody></table>";
        }

        $r = json_encode( array( 'html' => $htmlContent ) );
      }
    }

    return $r;
  }

  public function toggleGroupUser() {

    $g = SeeDB::load( 'websiteusergroup', $_POST['groupid'] );
    $u = SeeDB::load( 'websiteuser', $_POST['userid'] );
    $member = $g->sharedWebsiteuser[$u->id]->id;

    if( !$member ) { // Add member

      $g->sharedWebsiteuser[] = $u;

      $r = array( 'result' => 1, 'uid' => $u->id, 'gid' => $g->id );

    } else { // Remove

      unset( $g->sharedWebsiteuser[$u->id] );
      $r = array( 'result' => 0, 'uid' => $u->id, 'gid' => $g->id );
    }

    SeeDB::store( $g );

    return( json_encode( $r ) );
  }

  public function loadCurrentUser() {

    $u = SeeDB::load( 'websiteuser', $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] );

    return( $u );
  }

  public function export() {
    
    ini_set('memory_limit', '512M');

    $access = $this->see->SeeCMS->adminauth->checkAccess( 'action-export-websiteusers', null, false );

    if( $access ) {
      $wus = $this->loadAll( array( "limit" => '' ) );

      $adfs = SeeDB::find( 'adf', ' objecttype = ? ', array( 'websiteuser' ) );

      foreach( $adfs as $adf ) {

        $fs = explode( "\n", $adf->contenttype->fields );

        foreach( $fs as $f ) {

          $p = explode( ',', $f );
          $fields[] = $p[0];
          $fieldList .= ',"'.$p[1].'"';
        }
      }

      header('Content-Type: text/csv; charset=utf-8');
      header('Content-Disposition: attachment; filename="Website users.csv"');
      $output = fopen('php://output', 'w');

      echo "title,forename,surname,organisation,jobtitle,email,telephone,address1,address2,address3,city,region,postcode,country,creationdate,logindate{$fieldList}\r\n";

      foreach( $wus['au'] as $wu ) {

        $fieldData = '';

        foreach( $adfs as $adf ) {

          $adfcs = SeeDB::find( 'adfcontent', ' objectid = ? && objecttype = ? && adf_id = ? ', array( $wu->id, 'websiteuser', $adf->id ));
          foreach( $adfcs as $adfc ) {

            $cs = json_decode( $adfc->content, true );

            foreach( $fields as $f ) {

              $fieldData .= ',"'.$cs[0][$f].'"';
            }
          }
        }

        echo "\"{$wu->title}\",\"{$wu->forename}\",\"{$wu->surname}\",\"{$wu->organisation}\",\"{$wu->jobtitle}\",\"{$wu->email}\",\"{$wu->telephone}\",\"{$wu->address1}\",\"{$wu->address2}\",\"{$wu->address3}\",\"{$wu->city}\",\"{$wu->region}\",\"{$wu->postcode}\",\"{$wu->country}\",\"".$this->see->format->date( $wu->creationdate, "H:i d/m/Y" )."\",\"".$this->see->format->date( $wu->logindate, "H:i d/m/Y" )."\"{$fieldData}\r\n";
      }
    }

    die();
  }

  public function import() {

    if( $_FILES['importfile'] ) {

      move_uploaded_file( $_FILES['importfile']["tmp_name"], "../custom/files/websiteuserimport.csv" );

      $data = str_getcsv( file_get_contents( "../custom/files/websiteuserimport.csv" ), "\n" ); //parse the rows

      foreach( $data as $row ) {
        $rows[] = str_getcsv( $row );
      }

      $data['import']['csv'] = $rows;

      $data['import']['groups'] = SeeDB::findAll( 'websiteusergroup', ' ORDER BY name ' );

      return( $data );
    } else if( $_POST['Save'] ) {

      $data['import']['created'] = 0;
      $data['import']['complete'] = 1;

      /* See if there's an email to send */
      $importUserEmail = SeeDB::findOne( 'setting', ' name = ? ', array( 'importUserEmail' ) );
      if( $importUserEmail ) {
        $importUserEmail = $importUserEmail->value;
        $importUserEmailFrom = SeeDB::findOne( 'setting', ' name = ? ', array( 'importUserEmailFrom' ) );
        $importUserEmailFrom = $importUserEmailFrom->value;
        $importUserEmailSubject = SeeDB::findOne( 'setting', ' name = ? ', array( 'importUserEmailSubject' ) );
        $importUserEmailSubject = $importUserEmailSubject->value;
      }

      /* Get fields */
      foreach( $_POST as $fK => $fV ) {

        if( substr( $fK, 0, 3 ) == 'rt-' ) {
          $col = substr( $fK, 3 );
          if( substr( $fV, 0, 3 ) == 'adf' ) {

            $adf = explode( '-', $fV );
            $adfs[ str_replace( 'adf', '', $adf[0] ) ][] = array( $col, $adf[1] );
          } else {

            $cols[ $fV ] = $col;
          }
        }
      }

      /* Load data */
      $dataCSV = str_getcsv( file_get_contents( "../custom/files/websiteuserimport.csv" ), "\n" ); //parse the rows
      $first = true;
      foreach( $dataCSV as $row ) {

        if( !$first || !$_POST['trd'] ) {

          $r = str_getcsv( $row );

          $u = SeeDB::findOne( 'websiteuser', ' email = ? ', array( $r[$cols[ 'email' ]] ) );

          if( !$u->id && SeeValidationController::email( $r[$cols[ 'email' ]], '' ) ) {

            // CHECK DUPLICATES
            $u = SeeDB::dispense( 'websiteuser' );

            $u->title = (( isset( $cols[ 'title' ] ) ) ? (string)$r[$cols[ 'title' ]] : '' );
            $u->forename = (( isset( $cols[ 'forename' ] ) ) ? (string)$r[$cols[ 'forename' ]] : '' );
            $u->surname = (( isset( $cols[ 'surname' ] ) ) ? (string)$r[$cols[ 'surname' ]] : '' );
            $u->organisation = (( isset( $cols[ 'organisation' ] ) ) ? (string)$r[$cols[ 'organisation' ]] : '' );
            $u->jobtitle = (( isset( $cols[ 'jobtitle' ] ) ) ? (string)$r[$cols[ 'jobtitle' ]] : '' );
            $u->address1 = (( isset( $cols[ 'address1' ] ) ) ? (string)$r[$cols[ 'address1' ]] : '' );
            $u->address2 = (( isset( $cols[ 'address2' ] ) ) ? (string)$r[$cols[ 'address2' ]] : '' );
            $u->address3 = (( isset( $cols[ 'address3' ] ) ) ? (string)$r[$cols[ 'address3' ]] : '' );
            $u->city = (( isset( $cols[ 'city' ] ) ) ? (string)$r[$cols[ 'city' ]] : '' );
            $u->region = (( isset( $cols[ 'region' ] ) ) ? (string)$r[$cols[ 'region' ]] : '' );
            $u->postcode = (( isset( $cols[ 'postcode' ] ) ) ? (string)$r[$cols[ 'postcode' ]] : '' );
            $u->country = (( isset( $cols[ 'country' ] ) ) ? (string)$r[$cols[ 'country' ]] : '' );
            $u->email = (( isset( $cols[ 'email' ] ) ) ? (string)$r[$cols[ 'email' ]] : '' );
            $u->telephone = (( isset( $cols[ 'telephone' ] ) ) ? (string)$r[$cols[ 'telephone' ]] : '' );

            $password = substr( md5(rand()), 0, 10);

            $u->password = password_hash( $password, PASSWORD_BCRYPT );
            $u->passwordformat = 'hash';

            SeeDB::store( $u );

            $u->sharedWebsiteusergroup = array();

            foreach( $_POST as $dK => $dV ) {

              if( substr( $dK, 0, 23 ) == 'seecmswebsiteusergroup-' ) {

                $wug = SeeDB::load( 'websiteusergroup', substr( $dK, 23 ) );
                $u->sharedWebsiteusergroup[] = $wug;
              }
            }

            SeeDB::store( $u );
            if( $importUserEmail ) {

              $replace = array( '<EMAIL>', '<PASSWORD>', '<FORENAME>', '<SURNAME>', '<ORGANISATION>', '<JOBTITLE>', '<ADDRESS1>', '<ADDRESS2>', '<ADDRESS3>', '<CITY>', '<REGION>', '<POSTCODE>', '<COUNTRY>', '<TELEPHONE>' );
              $with = array( $u->email, $password, $u->forename, $u->surname, $u->organisation, $u->jobtitle, $u->address1, $u->address2, $u->address3, $u->city, $u->region, $u->postcode, $u->country, $u->telephone );
              $email = str_replace( $replace, $with, $importUserEmail );

              $seeemail = new SeeEmailController();
              $seeemail->sendHTMLEmail( $importUserEmailFrom, $u->email, $email, $importUserEmailSubject );
            }

            $data['import']['created'] += 1;

            // SAVE USER
            foreach( $adfs as $adfK => $adf ) {

              $sadfData = array();

              foreach( $adf as $sadf ) {

                $sadfData[ $sadf[1] ] = $r[ $sadf[0] ];
              }

              $nsadfData[] = $sadfData;

              $uadf = SeeDB::dispense( 'adfcontent' );
              $uadf->objecttype = 'websiteuser';
              $uadf->objectid = $u->id;
              $uadf->adf_id = $adfK;
              $uadf->content = json_encode( $nsadfData );
              $uadf->language = 'en';

              SeeDB::store( $uadf );
            }
          } else if( $u->id ) {

            // Maybe update user?

          }
        }

        $first = false;
      }

      unlink( "../custom/files/websiteuserimport.csv" );

      return( $data );
    }
  }
}
