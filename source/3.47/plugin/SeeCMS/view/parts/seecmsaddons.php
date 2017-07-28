<?php

/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

echo '<div class="col1 addonslist">';
echo '<div class="sectiontitle"><h2>Add ons</h2></div>';

$settings['level'] = 2;
$settings['baseRoute'] = $see->SeeCMS->cmsRoot.'/addons/';
$settings['nesting'] = 0;

ob_start();
$see->html->makeMenuFromRoutes( $settings );
$addons = ob_get_clean();
echo $addons;

if( !$addons ) {

  echo "<p style=\"padding-bottom: 100px;\">There are no add ons installed at the moment.</p>";
}

?>
      
    <div class="clear"></div>
</div>

<div class="col2">
  <div class="support">
    <h2>Support <span><i class="fa fa-question-circle" aria-hidden="true"></i></span></h2>
    <div class="supportinfo">
      <?php echo $see->SeeCMS->supportMessage; ?>
    </div>
  </div>
</div>
<div class="clear"></div>
<div id="newpagetitle" title="Create new page">
<p>Page title:<br /><input type="text" id="pagetitle" /></p>
</div>
<div id="deletepagepopup" title="Delete page?"></div>