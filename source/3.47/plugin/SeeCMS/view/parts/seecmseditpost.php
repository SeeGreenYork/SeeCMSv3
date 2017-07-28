<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

$see->html->css( 'editor.css', 'screen', '/seecms/css/' );

$post = $data['post'];
$routes = $data['postRoutes'];
$templates = $data['templates'];

if( $data['multisite'] ) {
  $siteName = $page->site->name;
    
  if( !$siteName ) {
    $siteName = 'Any';
  }
  
}

$formSettings['controller']['name'] = 'SeeCMSPost';
$formSettings['controller']['method'] = 'update';
$formSettings['attributes']['action'] = "./?id={$post->id}";
$formSettings['attributes']['enctype'] = 'multipart/form-data';
$formSettings['disableSpamProtection'] = true;
$formSettings['disableStandardErrors'] = true;
$formSettings['validate']['title']['validate'] = 'required';
$formSettings['validate']['title']['error'] = 'Please enter a title.';

$timeRange = SeeHelperController::timeRange( 0, 23, 1, 15, true );
$timeRange[] = '23:59';

$f = $see->html->form( $formSettings );

echo '<div class="col1">';
echo '<div class="sectiontitle"><h2>Post details</h2></div>';

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
  
  echo "<p>This post has unapproved content. Please select an Administrator and submit for approval.</p><p>";
  $f->select( array( "id" => "requestapproval-approver" ), array( "options" => $data["contentApprovers"] ) );
  $f->hidden( array( "id" => "requestapproval-objecttype", "value" => "post" ) );
  $f->hidden( array( "id" => "requestapproval-objectid", "value" => $post->id ) );

  echo "<i class=\"fa fa-play-circle seecmsfasubmit\" title=\"Submit for approval\" aria-hidden=\"true\"></i></p>";

  echo "</div>";
}

?>
<div class="columns">

<div class="column">
				<div class="section">
					<h2>Post information</h2>
					<div class="sg_input">
						<p>Title</p>
						<p><?php $f->text( array( 'name' => 'title', 'value' => htmlentities( $post->title ) ) ); ?></p>
					</div>
					<div class="sg_input">
						<p>HTML title</p>
						<p><?php $f->text( array( 'name' => 'htmltitle', 'value' => htmlentities( $post->htmltitle ), 'class' => 'count' ) ); ?> <span><span id="count">0</span> chars</span></p>
					</div>
          
					<?php

					if( $post->posttype_id == 1 ){ 
					
					?> 
         
					<div class="sg_input">
						<p>Post date</p>
						<p><?php $f->text( array( 'name' => 'postdate', 'id' => 'postdate', 'class' => 'datepicker', 'value' => $see->format->date( $post->date, "d M Y" ) ) ); ?></a></p>
					</div>

					<?php

					} else if( $post->posttype_id == 2 ) {
					
					?>

					<h2>Event settings</h2>
          <?php $f->hidden( array( 'name' => 'postdate', 'id' => 'postdate', 'value' => date("Y-m-d") ) ); ?>
					<div class="sg_input">
						<p>Event start date</p>
						<p><?php $f->text( array( 'name' => 'eventstartdate', 'id' => 'eventstartdate', 'class' => 'datepicker', 'value' => $see->format->date( $post->eventstart, "d M Y" ) ) ); ?></p>
					</div>					
					<div class="sg_input">
						<p>Event start time</p>
						<p><?php $f->select( array( 'name' => 'starttimehour', 'class' => 'small', 'value' => $see->format->date( $post->eventstart, "H" ) ), array( 'options' => SeeHelperController::leadingZeroRange(0,23) ) ); ?><?php $f->select( array( 'name' => 'starttimeminute', 'class' => 'small', 'value' => $see->format->date( $post->eventstart, "i" ) ), array( 'options' => SeeHelperController::leadingZeroRange(0,59) ) ); ?></p>
					</div>
					<div class="sg_input">
						<p>Event end date</p>
						<p><?php $f->text( array( 'name' => 'eventenddate', 'id' => 'eventenddate', 'class' => 'datepicker', 'value' => $see->format->date( $post->eventend, "d M Y" ) ) ); ?></p>
					</div>
					<div class="sg_input">
						<p>Event end time</p>
						<p><?php $f->select( array( 'name' => 'endtimehour', 'class' => 'small', 'value' => $see->format->date( $post->eventend, "H" ) ), array( 'options' => SeeHelperController::leadingZeroRange(0,23) ) ); ?><?php $f->select( array( 'name' => 'endtimeminute', 'class' => 'small', 'value' => $see->format->date( $post->eventend, "i" ) ), array( 'options' => SeeHelperController::leadingZeroRange(0,59) ) ); ?></p>
					</div>
					<hr>
          
					<?php } ?>

					<div class="sg_input">
						<p>Introduction</p>
						<p><?php $f->textarea( array( 'name' => 'standfirst', 'class' => 'standfirst', 'value' => $post->standfirst ) ); ?></p>
					</div>
					<div class="sg_input">
						<p>Tags</p>
						<p><?php $f->textarea( array( 'name' => 'tags', 'class' => 'standfirst', 'value' => $post->tags ) ); ?></p>
					</div>
          
          <?php
          
          // Custom post type
          if( isset( $see->SeeCMS->customPostController[$post->posttype->name]['plugin'] ) ) {
            $customPostController = $see->{$see->SeeCMS->customPostController[$post->posttype->name]['plugin']};
            echo $customPostController->editFields( $f, $post );
          }
          
          ?>
          
				</div>
				<div class="section"></div>
				<div class="section">
					<h2>Search engine optimisation</h2>
					<div class="sg_input">
						<p>Post description</p>
						<p><?php $f->textarea( array( 'name' => 'metadescription', 'rows' => 5, 'cols' => 38, 'value' => $post->metadescription ) ); ?></p>
					</div>
					<div class="sg_input">
						<p>Post keywords</p>
						<p><?php $f->textarea( array( 'name' => 'metakeywords', 'rows' => 5, 'cols' => 38, 'value' => $post->metakeywords ) ); ?></p>
					</div>
				</div>
			</div>
      
			<div class="column">
				<div class="section">
					<h2>Settings</h2>
					<div class="template">
						<div class="thumbnail">
							<img src="/seecms/images/templates/home.gif" alt="" />
						</div>
						<div class="templateselect">
							<p>Template</p>
							<p><?php $f->select( array( 'name' => 'template', 'value' => $post->template ), array( 'options' => $templates, 'optionValueOnly' => true ) ); ?></p>
						</div>
					</div>
					<p>Add thumbnail</p>
          <?php
          echo "<div class=\"thumbnail postthumbnail\">";

          if( $post->media_id ) {
            echo "<img src=\"/images/uploads/img-139-139-{$post->media->id}.{$post->media->type}\" />";
          }

          echo "</div>";

          $f->hidden( array( 'name' => 'media_id', 'id' => 'media_id', 'value' => $post->media_id ) );
          ?>
					<div class="buttons">
						<a href="#" class="addthumb" id="seecmspostmedia">Add/change</a>
						<a href="#" class="addthumb" id="seecmspostremovemedia">Remove</a>
					</div>
					<div class="clear"></div>
					<div class="sg_input">
						<p>Commencement date</p>
						<p>
							<?php $f->text( array( 'name' => 'commencement', 'id' => 'commencement', 'class' => 'datepicker', 'value' => $see->format->date( $post->commencement, "d M Y" ) ) ); ?>
							<?php $f->select( array( 'name' => 'commencementtime', 'class' => 'time', 'value' => $see->format->date( $post->commencement, "H:i" ) ), array( 'options' => $timeRange, 'optionValueOnly' => true ) ); ?>
							<a href="#" class="cleardate"><i class="fa fa-times" aria-hidden="true"></i></a>
						</p>

					</div>
					<div class="sg_input">
						<p>Expiry date</p>
						<p><?php $f->text( array( 'name' => 'expiry', 'id' => 'expiry', 'class' => 'datepicker', 'value' => $see->format->date( $post->expiry, "d M Y" ) ) ); ?>
							<?php $f->select( array( 'name' => 'expirytime', 'class' => 'time', 'value' => $see->format->date( $post->expiry, "H:i" ) ), array( 'options' => $timeRange, 'optionValueOnly' => true ) ); ?>
						 <a href="#" class="cleardate"><i class="fa fa-times" aria-hidden="true"></i></a></p>
					</div>
        </div>

<?php
          
if( $post->posttype->ownCategory ) {

  echo "<div class=\"section\">
  <h2>Categories</h2>";
  
  foreach( $post->posttype->with(" ORDER BY name ")->ownCategory as $category ) {
    echo "<p>\n";
    $f->checkbox( array( 'name' => "categories[{$category->id}]", 'id' => "categories-{$category->id}", 'value' => isset( $post->sharedCategory[$category->id] ) ) );
    echo "\n<label for=\"categories-{$category->id}\"> {$category->name}</label>
    </p>";
  }
  
  echo "</div>";
}

echo "<hr>";

echo '<div class="adf">
<h3>Post URLs</h3>
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

  if( $routeCounter == 0 ) {
    echo '<div class="pageurlsinner"><p>Primary URL</p><p>';
    $f->text( array( 'name' => "route{$routeCounter}", 'value' => $r->route, 'id' => "route{$routeCounter}" ) );
    echo "</p>";
    $route = $r;
  }
	
  echo ( $routeCounter == 1 ? '<div class="url url1"><p>Secondary URLs</p></div><div class="url url2"><p>Delete?</p></div><div class="url url3"><p>Make Primary?</p></div><div class="clear"></div>' : '' );

  if( $routeCounter >= 1 ) {
    echo "<div class=\"route\"><p>";
    $f->text( array( 'name' => "route{$routeCounter}", 'value' => $r->route, 'id' => "route{$routeCounter}" ) );
  
    echo "<span class=\"checkboxwrap\"><input type=\"checkbox\" name=\"deleteroute{$routeCounter}\" /></span><span class=\"checkboxwrap right\"><input type=\"checkbox\" name=\"primaryroute{$routeCounter}\" /></span>";
    echo "</p></div>";
		}
  
  $routeCounter++;
}

echo "<div class=\"clear\"></div>";
echo "<a href=\"#\" class=\"addnewroute\">Add route</a>";
echo "</div>";
echo "<div class=\"clear\"></div>";

echo "<script>var nextroute = {$routeCounter}; var routeHTML = '<div class=\"route\"><p><input type=\"text\" id=\"routeXXX\" value=\"\" name=\"routeXXX\"><span class=\"checkboxwrap\"><input type=\"checkbox\" name=\"deleterouteXXX\"><span class=\"checkbox\"></span></span><span class=\"checkboxwrap right\"><input type=\"checkbox\" name=\"primaryrouteXXX\" ><span class=\"checkbox\"></span></span></p></div>'; var routeHTMLHead = '<div class=\"url url1\"><p>Secondary URLs</p></div><div class=\"url url2\"><p>Delete?</p></div><div class=\"url url3\"><p>Make Primary?</p></div><div class=\"clear\"></div>';</script>";

echo "</div>
</div>"; ?>
        
<div class="adf">
  <h3>Redirect</h3>
  <div class="redirect">
    <a href="#" id="seecmsredirectlink">
    <?php if( $data['redirectDetails'] ) { echo "Currently redirecting to: ".(($data['redirectDetails']['name'])?$data['redirectDetails']['name']:$data['redirectDetails']['route']); } else { echo 'Select a link'; } ?>
    </a>
    <br />
    <a id="seecmsremoveredirect" href="#">
    <?php if( $data['redirectDetails'] ) { echo "Remove redirect"; } ?>
    </a>
    <?php $f->hidden( array( 'name' => 'redirect', 'id' => 'redirect', 'value' => $post->redirect ) ); ?>
  </div>
</div>
        
<div class="adf">
  <h3>Duplicate</h3>
  <div class="redirect">
    <a href="./?duplicate=<?php echo $post->id; ?>" id="seecmsredirectlink">Duplicate this <?php echo $post->posttype->name; ?></a>
  </div>
</div>
      
<?php

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
    
    echo "</table>";
  }
  else {
    echo "<p><strong>Please add some admin groups if you want to set editing permissions</strong></p>";
  }
   
  echo '</div></div>';

}



$adfs = SeeDB::find( 'adf', ' objecttype = ? && ( ( objectid = ? && `cascade` = ? ) || ( ( objectid = ? || objectid = ? ) && `cascade` = ? ) ) && ( theme = ? || theme = ? ) ', array( 'post', $post->id, 0, $post->parentid, 0, 1, '', $see->theme ) );
$cc = new SeeCMSContentController( $see, $see->SeeCMS->language );
foreach( $adfs as $adf ) {

  $cc->objectType = $r->objecttype;
  $cc->objectID = $r->objectid;
  
  $content = SeeDB::findOne( 'adfcontent', ' objecttype = ? && objectid = ? && adf_id = ? && language = ? ', array( 'post', $post->id, $adf->id, $see->SeeCMS->language ) );

  echo '<div class="adf">';
  echo "<h3 id=\"editable{$adf->id}\" class=\"editcontent editcontentADF adfpopup\">{$adf->title}</h3>";
  echo $cc->makeEditPart( $adf->id, 'ADF', $content->content, 1, true );
  
  $adfpopup .= $cc->ADF( $content->content, 1, $adf->id, 1, $adf->contenttype->fields, $adf->contenttype->settings, (($accessLevel)?$accessLevel:5), true )."\r\n";
  
  echo '</div>';
}


echo "</div></div></div>
<div class=\"col2\">
<div class=\"editpage\">";

if( $data["accessLevel"] == NULL || $data['accessLevel'] >= 5 ) {
  $f->submit( array( 'name' => 'Save', 'class' => 'save', 'value' => 'Save changes' ) );
  echo "<span><i class=\"fa fa-floppy-o\" aria-hidden=\"true\"></i></span>";
}

$f->hidden( array( 'name' => 'id', 'value' => $post->id ) );

echo "</div>";
?>

<div class="editpage">
  <a class="editpage" href="<?php echo '/'.$route->route; ?>?preview=1">Preview/edit post<span><i class="fa fa-pencil-square" aria-hidden="true"></i></span></a>
</div>
<div class="editpage">
  <a class="editpage openpage" target="_blank" href="<?php echo '/'.$route->route; ?>">Open live post<span><i class="fa fa-hand-pointer-o" aria-hidden="true"></i></span></a>
</div>
<div class="support">
	<h2>Support <span><i class="fa fa-question-circle" aria-hidden="true"></i></span></h2>
	<div class="supportinfo">
    <?php echo $see->SeeCMS->supportMessage; ?>
  </div>
</div>
</div>
<div class="clear"></div>
<div class="selectpostimage" id="selectpostimage" title="Select image" style="display: none;">
<div class="adfimages">
  <div class="select selectImage"><p><select id="imageFolder"></select></p></div>
  <div class="medialistinner folders"></div>
  <div class="clear"></div>
</div>
</div>
<?php 

$f->close();
echo $adfpopup;

?>
<div class="selectseecmsredirectlink" id="selectseecmsredirectlink" title="Select link" style="display: none;">
<?php echo $data['linkSelector']; ?>
</div>