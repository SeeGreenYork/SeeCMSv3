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
<div class="sectiontitle"><h2>Import site users</h2></div>
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
<?php

if( !$data['import'] && !$data['import']['complete'] ) {
  
  //$formSettings['controller']['name'] = 'SeeCMSWebsiteUser';
  //$formSettings['controller']['method'] = 'importCSV';

  $formSettings['validate']['title']['validate'] = 'required';
  $formSettings['validate']['title']['error'] = 'Please enter a title.';

  $formSettings['attributes']['enctype'] = 'multipart/form-data';

  $f = $see->html->form( $formSettings );
  
  echo "<p>Please upload your CSV file:</p><p>";
  
  $f->file( array( 'name' => 'importfile' ));
  
  echo "</p>";
  
  echo "<p>";
  $f->submit( array( 'name' => 'Upload', 'value' => 'Upload', 'class' => 'save' ) );
  echo "</p>";
  
  $f->close();
  
} else if( !$data['import']['complete'] ) {
  
  $csv = $data['import']['csv'];
  
  $formSettings['validate']['title']['validate'] = 'required';
  $formSettings['validate']['title']['error'] = 'Please enter a title.';

  $formSettings['attributes']['enctype'] = 'multipart/form-data';

  $f = $see->html->form( $formSettings );
  
  echo "<h2>Add users to these groups</h2><table class=\"stripey importusers\"><thead><tr><th>Group name</th><th></th></tr></thead><tbody>";

  foreach( $data['import']['groups'] as $g ) {

    $member = $u->sharedWebsiteusergroup[$g->id]->id;
    echo "<tr><td>{$g->name}</td><td>";
    $f->checkbox( array( 'name' => "seecmswebsiteusergroup-{$g->id}" ));
    echo "</td></tr>";
  }

  echo "</tbody></table>";
  
	echo '<script type="text/javascript">$(document).ready(function(){$(\'#trd\').click(function(){$(\'.c1\').toggle()});});</script>';

  echo "<h2>Select the appropriate columns</h2>";
  
  echo "<p>Showing first 3 rows of data: </p>";
  
	echo "<p>Ignore row 1 data? ";
  $f->checkbox( array( 'name' => 'trd', 'id' => 'trd' ) );
  echo "</p>";
  
	echo "<table class=\"stripey importusers\"><tr><th>Select data type</th>";
  
  for( $i=1; $i<=3; $i++ ){
    echo "<th class=\"c{$i}\">Row {$i}</th>";
  }
	
  echo "</tr>";
				
  for ( $i=0; $i < count( $csv[0] ); $i++ ){
    
    echo "<tr>";
    echo "<td>";
    echo "<select class=\"rowType\" name=\"rt-{$i}\">";
    echo "<option value=\"title\">Title</option>";
    echo "<option value=\"forename\">Forename</option>";
    echo "<option value=\"surname\">Surname</option>";
    echo "<option value=\"email\">Email</option>";
    echo "<option value=\"address1\">Address 1</option>";
    echo "<option value=\"address2\">Address 2</option>";
    echo "<option value=\"address3\">Address 3</option>";
    echo "<option value=\"city\">City</option>";
    echo "<option value=\"postcode\">Postcode</option>";
    echo "<option value=\"region\">Region</option>";
    echo "<option value=\"country\">Country</option>";
    echo "<option value=\"telephone\">Telephone</option>";
    echo "<option value=\"organisation\">Organisation</option>";
    echo "<option value=\"jobtitle\">Job Title</option>";

    $adfs = SeeDB::find( 'adf', ' objecttype = ? ', array( 'websiteuser' ) );
    if( is_array( $adfs ) ) {
      foreach( $adfs as $adf ) {
        $ctf = $adf->contenttype->fields;
        $ctrs = explode( "\n", $ctf );
        foreach( $ctrs as $ctr ) {
          $ctrf = explode( ",", $ctr );
        echo "<option value=\"adf{$adf->id}-{$ctrf[0]}\">{$adf->title} - {$ctrf[1]}</option>";
        }
      }
    }

    echo "<option value=\"ignore\">Ignore</option>";

    echo "</select>";
    echo "</td>";

    for ( $ii = 1; $ii <= 3; $ii++ ) {
        echo "<td class=\"c{$ii}\">";
        echo wordwrap( trim( $csv[ $ii-1 ][ $i ] ), 20, "<br />\n", true );
        echo "</td>";
    }

    echo "</tr>";
  }
	
  echo "</table>";

	$f->submit( array( 'name' => 'Save', 'value' => 'Import site users', 'class' => 'save' ) );
  
  echo "<p><strong>Please note this may take several minutes to process.</strong></p>";
  
  $f->close();

} else if( $data['import']['complete'] ) {
  
  echo "<h2>Import successful<h2>";
  echo "<p>{$data['import']['created']} users created</p>";
}

?>
</div>
</div>

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
<div id="deletesitegrouppopup" title="Delete group?"></div>