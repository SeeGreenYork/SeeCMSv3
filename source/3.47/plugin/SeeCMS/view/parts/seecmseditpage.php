<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */
      
$see->html->css( 'editor.css', 'screen', '/seecms/css/' );

$page = $data['page'];
$routes = $data['pageRoutes'];
$userGroups = $data['userGroups'];
$userGroupPermission = $data['userGroupPermission'];
$templates = $data['templates'];
$accessLevel = $data['accessLevel'];

if( $data['multisite'] ) {
  $siteName = $page->site->name;
    
  if( !$siteName ) {
    $siteName = 'Any';
  }
  
}

$formSettings['controller']['name'] = 'SeeCMSPage';
$formSettings['controller']['method'] = 'update';
$formSettings['attributes']['action'] = "./?id={$page->id}";
$formSettings['attributes']['enctype'] = 'multipart/form-data';
$formSettings['disableSpamProtection'] = true;
$formSettings['disableStandardErrors'] = true;
$formSettings['validate']['title']['validate'] = 'required';
$formSettings['validate']['title']['error'] = 'Please enter a title.';

$timeRange = SeeHelperController::timeRange( 0, 23, 1, 15, true );
$timeRange[] = '23:59';

$f = $see->html->form( $formSettings );

echo '<div class="col1">';
echo '<div class="sectiontitle"><h2>Page details</h2></div>';

if( count( $f->errors ) ) {
  echo "<div class=\"seecmsmessage seecmserror\">";
  
  if( count( $f->errors ) == 1 ) {
    echo "<p><i class=\"fa fa-warning\" aria-hidden=\"true\"></i> <strong>Error:</strong> " . reset( $f->errors ) . "</p>";
  }
  else {
    echo "<p><i class=\"fa fa-warning\" aria-hidden=\"true\"></i> <strong>Error:</strong></p>";
    echo "<p>" . implode( "<br>", $f->errors ) . "</p>";
  }

  echo "</div>";
}

echo $data["messages"];

if( $data['unappliedContent'] ) {
  echo "<div class=\"seecmsmessage seecmsaction seecmsrequestapproval\"><h2>Request approval</h2>";
  
  echo "<p>This page has unapproved content. Please select an Administrator and submit for approval.</p><p>";
  $f->select( array( "id" => "requestapproval-approver" ), array( "options" => $data["contentApprovers"] ) );
  $f->hidden( array( "id" => "requestapproval-objecttype", "value" => "page" ) );
  $f->hidden( array( "id" => "requestapproval-objectid", "value" => $page->id ) );

  echo "<i class=\"fa fa-play-circle seecmsfasubmit\" title=\"Submit for approval\" aria-hidden=\"true\"></i></p>";

  echo "</div>";
}

echo '<div class="columns"><div class="column">';

echo '<div class="section"><h2>Page information</h2><div class="sg_input"><p>Page title</p><p>';
$f->text( array( 'name' => 'title', 'value' => htmlentities( $page->title ) ) );
echo '</p></div><div class="sg_input"><p>HTML title</p><p>';
$f->text( array( 'name' => 'htmltitle', 'value' => htmlentities( $page->htmltitle ), 'class' => 'count' ) );
echo '<span><span id="count">0</span> chars</span></p></div></div>';

echo '<div class="section"><h2>Search engine optimisation</h2><div class="sg_input"><p>Page description</p><p>';
$f->textarea( array( 'name' => 'metadescription', 'rows' => 5, 'cols' => 38, 'value' => $page->metadescription ) );
echo '</p></div><div class="sg_input"><p>Page keywords</p><p>';
$f->textarea( array( 'name' => 'metakeywords', 'rows' => 5, 'cols' => 38, 'value' => $page->metakeywords ) );
echo '</p></div></div></div>';

echo '<div class="column"><div class="section"><h2>Settings</h2><div class="template"><div class="thumbnail"><img src="/seecms/images/templates/home.gif" alt="" /></div><div class="templateselect"><p>Template</p><p>';
$f->select( array( 'name' => 'template', 'value' => $page->template ), array( 'options' => $templates, 'optionValueOnly' => true ) );
echo '</p></div></div></div>';

echo '<div class="adf"><h3>Commencement / expiry</h3><div class="sg_input"><p>Commencement date</p><p>';
$f->text( array( 'name' => 'commencement', 'id' => 'commencement', 'class' => 'datepicker', 'value' => $see->format->date( $page->commencement, "d M Y" ) ) );
$f->select( array( 'name' => 'commencementtime', 'class' => 'time', 'value' => $see->format->date( $page->commencement, "H:i" ) ), array( 'options' => $timeRange, 'optionValueOnly' => true ) );
echo '<a href="#" class="cleardate"><i class="fa fa-times" aria-hidden="true"></i></a></p></div><div class="sg_input"><p>Expiry date</p><p>';
$f->text( array( 'name' => 'expiry', 'id' => 'expiry', 'class' => 'datepicker', 'value' => $see->format->date( $page->expiry, "d M Y" ) ) );
$f->select( array( 'name' => 'expirytime', 'class' => 'time', 'value' => $see->format->date( $page->expiry, "H:i" ) ), array( 'options' => $timeRange, 'optionValueOnly' => true ) );
echo '<a href="#" class="cleardate"><i class="fa fa-times" aria-hidden="true"></i></a></p></div></div>';
        
echo '<div class="adf"><h3>Visibility</h3><div class="sg_checkboxes"><p>';
$f->checkbox( array( 'name' => 'hidefromnavigation', 'id' => 'hidefromnavigation', 'value' => (( $page->visibility >= 2 ) ? 1 : 0 ) ) );
echo '</p><p>Hide page from navigation</p></div><div class="sg_checkboxes"><p>';
$f->checkbox( array( 'name' => 'hidefromsitemap', 'id' => 'hidefromsitemap', 'value' => (( $page->visibility == 3 ) ? 1 : 0 ) ) );
echo '</p><p>Hide page from sitemap</p></div></div>';
        
echo '<div class="adf">
<h3>Page URLs</h3>
<div class="pageurls">';
            
if( $siteName ) {
  echo "<p>Site: <strong>{$siteName}</strong>";
  $pr = reset( $routes );
  
  if( str_replace( $page->site->route, '', $pr->route ) == $page->site->homeroute ) {
    echo ' (Home page)';
  }
  
  echo '</p><hr>';
}

$routeCounter = 0;

foreach( $routes as $r ) {
  
  if( $page->site->route ) {
    $r->route = str_replace( $page->site->route, '', $r->route );
  }
  
  if( $routeCounter == 0 ) {
  
    echo '<div class="pageurlsinner"><p>Primary URL</p><p>';
    $f->text( array( 'name' => "route{$routeCounter}", 'value' => $r->route, 'id' => "route{$routeCounter}" ) );
    echo "</p>";
    
    $route = $r;
    
    if( str_replace( $page->site->route, '', $route->route ) == $page->site->homeroute ) {
      echo $route->route = '/';
    }
  }

  echo (( $routeCounter == 1 ) ? '<div class="url url1"><p>Secondary URLs</p></div><div class="url url2"><p>Delete?</p></div><div class="url url3"><p>Make Primary?</p></div><div class="clear"></div>' : '' );

  if( $routeCounter >= 1 ) {
    echo "<div class=\"route\"><p>";
    $f->text( array( 'name' => "route{$routeCounter}", 'value' => $r->route, 'id' => "route{$routeCounter}" ) );
  
    echo "<span class=\"checkboxwrap\"><input type=\"checkbox\" name=\"deleteroute{$routeCounter}\" /></span><span class=\"checkboxwrap right\"><input type=\"checkbox\" name=\"primaryroute{$routeCounter}\" /></span>";
    echo "</p></div>";
  }
  
  $routeCounter++;

}

echo "<a href=\"#\" class=\"addnewroute\">Add route</a>";
echo "<div class=\"clear\"></div>";
echo "</div>
<div class=\"clear\"></div>";
echo "<script>var nextroute = {$routeCounter}; var routeHTML = '<div class=\"route\"><p><input type=\"text\" id=\"routeXXX\" value=\"\" name=\"routeXXX\"><span class=\"checkboxwrap\"><input type=\"checkbox\" name=\"deleterouteXXX\"><span class=\"checkbox\"></span></span><span class=\"checkboxwrap right\"><input type=\"checkbox\" name=\"primaryrouteXXX\" ><span class=\"checkbox\"></span></span></p></div>'; var routeHTMLHead = '<div class=\"url url1\"><p>Secondary URLs</p></div><div class=\"url url2\"><p>Delete?</p></div><div class=\"url url3\"><p>Make Primary?</p></div><div class=\"clear\"></div>';</script>";

echo '</div>
</div>

<div class="adf"><h3>Security</h3><div class="security"><p>';
$f->checkbox( array( 'name' => "security-allUserAccess", 'id' => "security-allUserAccess", "value" => ((is_array($userGroupPermission))?0:1) ) );
echo ' Everyone can access this content</p><hr>';
            
if( count( $userGroups ) ) {
  echo "<p>Only specific groups of registered users can access this content:</p>";

  foreach( $userGroups as $ug ) {
    echo "<p>";
    $f->checkbox( array( 'name' => "security-group-{$ug->id}", 'id' => "security-group-{$ug->id}", 'class' => "security-group", "value" => (int)$userGroupPermission[$ug->id] ) );
    echo " {$ug->name}</p>";
  }
  
  echo '<hr><p>';
  $f->checkbox( array( 'name' => "security-cascade", 'id' => "security-cascade" ) );
  echo ' Update permissions on subpages</p>';
}
else {
  echo "<p><strong>Please add some site user groups if you want to set permissions</strong></p>";
}
 
echo "</div></div>";
        
echo '<div class="adf"><h3>Redirect</h3><div class="redirect"><a href="#" id="seecmsredirectlink">';
if( $data['redirectDetails'] ) { echo "Currently redirecting to: ".(($data['redirectDetails']['name'])?$data['redirectDetails']['name']:$data['redirectDetails']['route']); } else { echo 'Select a link'; }
echo '</a><br /><a id="seecmsremoveredirect" href="#">';

if( $data['redirectDetails'] ) { echo "Remove redirect"; } 

echo '</a>';
$f->hidden( array( 'name' => 'redirect', 'id' => 'redirect', 'value' => $page->redirect ) );
echo '</div></div><div class="adf"><h3>Clone</h3><div class="clone"><a href="#" id="seecmsclonelink">';
if( $data['cloneDetails'] ) { echo "Currently cloned from: {$data['cloneDetails']['name']}"; } else { echo 'Select a page'; }
echo '</a><br /><a id="seecmsremoveclone" href="#">';
if( $data['cloneDetails'] ) { echo "Remove cloning"; }
echo '</a>';
$f->hidden( array( 'name' => 'clone', 'id' => 'clone', 'value' => $page->clone ) );
echo '</div></div>';

/* If admin permissions are enabled and current user is super */
if( $see->SeeCMS->config['advancedEditorPermissions'] && $_SESSION['seecms'][$this->see->siteID]['adminuser']['access']['current'] >= 5 ) {
  
  echo '<div class="adf">
  <h3>Editing permissions</h3>
  <div class="editingpermissions">';
            
  if( count( $data["adminuserGroups"] ) ) {
    echo "<p>Set editing permissions, this will not override globally set access levels, such as Administrator access.</p>
    <table class=\"users stripey order permissions\">
    <tr><th>Group</th><th>Permission</th></tr>";

    foreach( $data["adminuserGroups"] as $ag ) {
      echo "<tr><td>{$ag->name}</td><td>";
      $f->select( array( "class" => "admingrouppermission", "name" => "adminusergrouppermissions[{$ag->id}]", "id" => "adminusergroup-{$ag->id}", "value" => $data["adminuserGroupPermissions"][$ag->id] ), array( 'options' => array( '0' => 'None', '2' => 'Limited', '5' => 'Full' ) ) );
      echo "</td></tr>";
    }
    
    echo "</table>
    <hr>
    <p>";
    $f->checkbox( array( 'name' => "adminusergrouppermissions-cascade", 'id' => "admin-permission-cascade" ) );
    echo "<label for=\"admin-permission-cascade\"> Update editing permissions on all subpages</label></p>";
  }
  else {
    echo "<p><strong>Please add some admin groups if you want to set editing permissions</strong></p>";
  }
   
  echo '</div></div>';

}

$adfs = SeeDB::find( 'adf', ' objecttype = ? && ( ( objectid = ? && `cascade` = ? ) || ( objectid IN ( '.$page->ascendants.",{$page->id}".' ) && `cascade` = ? ) || ( objectid IN ( '.$page->ascendants.' ) && `cascade` = ? && objectid != ? ) ) && ( theme = ? || theme = ? ) ', array( 'page', $page->id, 0, 1, 2, $page->id, '', $see->theme ) );
$cc = $this->see->SeeCMS->content;
foreach( $adfs as $adf ) {

  $exclude = explode( ",", $adf->exclude );
  if( !in_array( $page->id, $exclude ) ) {

    $cc->objectType = $r->objecttype;
    $cc->objectID = $r->objectid;
    
    $content = SeeDB::findOne( 'adfcontent', ' objecttype = ? && objectid = ? && adf_id = ? && language = ? ', array( 'page', $page->id, $adf->id, $see->SeeCMS->language ) );

    echo '<div class="adf">';
    echo "<h3 id=\"editable{$adf->id}\" class=\"editcontent editcontentADF adfpopup\">{$adf->title}</h3>";
    echo $cc->makeEditPart( $adf->id, 'ADF', $content->content, 1, true );
    
    $adfpopup .= $cc->ADF( $content->content, 1, $adf->id, 1, $adf->contenttype->fields, $adf->contenttype->settings, (($accessLevel)?$accessLevel:5), true )."\r\n";
    
    echo '</div>';
  }
}
        
echo '</div></div></div>
<div class="col2">
<div class="editpage">';

if( $data["accessLevel"] == NULL || $data['accessLevel'] >= 5 ) {
  $f->submit( array( 'name' => 'Save', 'class' => 'save', 'value' => 'Save changes' ) );
}

$f->hidden( array( 'name' => 'id', 'value' => $page->id, 'id' => 'pageid' ) );



echo "<span><i class=\"fa fa-floppy-o\" aria-hidden=\"true\"></i></span>
</div>";

if( !$data["pendingApproval"] ) {
  echo "<div class=\"editpage\">
    <a class=\"editpage\" href=\"" . ( $siteName && $siteName != $_SERVER['HTTP_HOST']  ? '/'.$page->site->route : ( $route->route != '/'  ? '/' : '' ) ) . "{$route->route}?preview=1\">Preview/edit page<span><i class=\"fa fa-pencil-square\" aria-hidden=\"true\"></i></span></a>
  </div>";
}

echo "<div class=\"editpage\">
  <a class=\"editpage openpage\" target=\"_blank\" href=\"" . ( $route->route != '/' ? '/' : '' ) . "{$route->route}\">Open live page<span><i class=\"fa fa-hand-pointer-o\" aria-hidden=\"true\"></i></span></a>
</div>
<div class=\"support\">
<h2>Support <span><i class=\"fa fa-question-circle\" aria-hidden=\"true\"></i></span></h2>
<div class=\"supportinfo\">{$see->SeeCMS->supportMessage}</div>
</div>
</div>";

$f->close();

echo $adfpopup;

echo '<div class="selectseecmsredirectlink" id="selectseecmsredirectlink" title="Select link" style="display: none;">';
echo $data['linkSelector'];
echo '</div><div class="selectseecmsclonelink" id="selectseecmsclonelink" title="Select page" style="display: none;">';
echo $data['cloneLinkSelector'];
echo '</div><div class="clear"></div>';

