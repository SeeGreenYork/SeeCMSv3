<div class="banners">

<?php
    
if(is_array($data[1]['ordered'][0]['content'])) {

  foreach( $data[1]['ordered'][0]['content'] as $adf ) { 
    
    echo "<div class=\"banner\" style=\"background: url(/{$see->rootURL}images/uploads/img-1-{$adf['bannerimage']->id}.{$adf['bannerimage']->type}) no-repeat 0 0\">";
    echo "<div class=\"container\"><div class=\"jumbotron\"><div class=\"backbox\"><h1>{$adf['bannertitle']}</h1>{$adf['bannertext']}</div><p><a class=\"btn btn-primary btn-lg\" href=\"{$adf['bannerlink']['route']}\">Learn more</a></p></div></div>";
    echo "</div>";

  }

}

?>

</div>
