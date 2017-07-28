<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */
 
echo '<div class="col1"><div class="sectiontitle"><h2>Site users</h2></div><div class="columns"><div class="column snav">';

$settings['level'] = 2;
$settings['baseRoute'] = $see->SeeCMS->cmsRoot.'/siteusers/';
$settings['nesting'] = 0;

$see->html->makeMenuFromRoutes( $settings );

echo '</div><div class="column columnwide"><h2>Site Users</h2>';

if( $data['userCount'] > 30 ) {
  
  echo "<p id=\"websiteuserfiltertext\">Showing first 30 of ",$data['userCount']," users</p>";
}

echo '<div class="filterbox">';
echo '<p>Filter users: <input type="text" name="websiteuserfilter" id="websiteuserfilter" /></p>';
echo '</div>';
echo '<p><input class="save" type="button" id="websiteuserloadall" value="Show all users" /></p>';

echo '<div id="websiteusertable">';
echo $data['view'];
echo "</div>";

?>
</div>
</div>

<div class="clear"></div>
</div>

<div class="col2">
<div class="createpage">
<a class="createuser" href="editusers/">Create new user <span><i class="fa fa-plus-circle" aria-hidden="true"></i></span></a>
</div>
<div class="support">
<h2>Support <span><i class="fa fa-question-circle" aria-hidden="true"></i></span></h2> 
<div class="supportinfo">
<?php echo $see->SeeCMS->supportMessage; ?>
</div>
</div>
</div>
<div id="deleteuserpopup" title="Delete user?"></div>