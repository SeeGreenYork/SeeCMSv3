<div class="col1">
      <div class="sectiontitle"><h2>Admin</h2></div>
<div class="columns">
<div class="column snav">
<?php

$settings['level'] = 2;
$settings['baseRoute'] = $see->SeeCMS->cmsRoot.'/admin/';
$settings['nesting'] = 0;

$see->html->makeMenuFromRoutes( $settings );

?>
</div>
<div class="column columnwide">
<table class="stripey adminusers">
<tr><th>Name</th><th>Level</th></tr>

<?php

foreach( $data as $au ) {

  echo "<tr><td>{$au->name}</td><td>{$au->adminuserrole->name}</td><td><a href=\"edit/?id={$au->id}\">Edit user</a></td><td class=\"delete\"><a class=\"delete\" data-adminuserid=\"{$au->id}\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i></a></td></tr>";
}

?>

</table>
</div>
</div>
<div class="clear"></div>
</div>

<div class="col2">
  <div class="createpage">
    <a class="createuser" href="edit/">Create new admin <span><i class="fa fa-plus-circle" aria-hidden="true"></i></span></a>
    <A class="cancel">Cancel</a>
  </div>
  <div class="support">
    <h2>Support <span><i class="fa fa-question-circle" aria-hidden="true"></i></span></h2>
    <div class="supportinfo"><?php echo $see->SeeCMS->supportMessage; ?></div>
  </div>
</div>
<div class="clear"></div>
<div id="deleteadminuserpopup" title="Delete admin user?"></div>