<?php

echo "<div class=\"col1\"><div class=\"sectiontitle\"><h2>Admin Groups</h2></div><div class=\"columns\"><div class=\"column snav\">";

$settings['level'] = 2;
$settings['baseRoute'] = $see->SeeCMS->cmsRoot.'/admin/';
$settings['nesting'] = 0;

$see->html->makeMenuFromRoutes( $settings );

echo "</div><div class=\"column columnwide\">";

if( count( $data ) ) {
  
  echo "<table class=\"stripey adminusergroups\"><tr><th>Name</th></tr>";
  
  foreach( $data as $ag ) {

    echo "<tr><td>{$ag->name}</td><td><a href=\"edit/?id={$ag->id}\">Edit group</a></td><td class=\"delete\"><a class=\"delete\" data-admingroupid=\"{$ag->id}\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i></a></td></tr>";
  }

  echo "</table>";
} else {
  
  echo "<p><strong>There are no admin groups.</strong></p>";
}

?>

</div>
</div>
<div class="clear"></div>
</div>

<div class="col2">
  <div class="createpage">
    <a class="createuser" href="edit/">Create new admin group <span><i class="fa fa-plus-circle" aria-hidden="true"></i></span></a>
    <A class="cancel">Cancel</a>
  </div>
  <div class="support">
    <h2>Support <span><i class="fa fa-question-circle" aria-hidden="true"></i></span></h2>
    <div class="supportinfo"><?php echo $see->SeeCMS->supportMessage; ?></div>
  </div>
</div>
<div class="clear"></div>
<div id="deleteadmingrouppopup" title="Delete admin group?"></div>