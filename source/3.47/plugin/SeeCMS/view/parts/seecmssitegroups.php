<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */
?>
<div class="col1">
<div class="sectiontitle"><h2>Site groups</h2></div>
<div class="columns">
<div class="column snav">
<?php

$settings['level'] = 2;
$settings['baseRoute'] = $see->SeeCMS->cmsRoot.'/siteusers/';
$settings['nesting'] = 0;

$see->html->makeMenuFromRoutes( $settings );

?>
</div>
<div class="column columnwide">
<table class="order sitegroups">
<thead>
<tr><th>Group</th></tr>
</thead>
<tbody>
<?php

foreach( $data as $g ) {

  echo "<tr><td>{$g->name}</td><td><a href=\"editgroups/?id={$g->id}\">Edit</a></td><td class=\"delete\"><a class=\"delete\" data-sitegroupid=\"{$g->id}\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i></a></td></tr>";
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
<a class="createuser" href="editgroups/">Create new group <span><i class="fa fa-plus-circle" aria-hidden="true"></i></span></a>
</div>
<div class="support">
<h2>Support <span><i class="fa fa-question-circle" aria-hidden="true"></i></span></h2>
<div class="supportinfo"> 
<?php echo $see->SeeCMS->supportMessage; ?>
</div>
</div>
</div>
<div id="deletesitegrouppopup" title="Delete group?"></div>