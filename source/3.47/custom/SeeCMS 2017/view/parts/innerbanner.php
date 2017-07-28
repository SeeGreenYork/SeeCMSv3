<?php

if(is_array($data[3]['ordered'][0]['content'])) {

  foreach( $data[3]['ordered'][0]['content'] as $adf ) {

    echo "<div class=\"banner\" style=\"background: url(/{$see->rootURL}/images/uploads/img-1-{$adf['image']->id}.{$adf['image']->type}) no-repeat center center; background-size: cover;\">";
    echo "<div class=\"overlay\"></div>";

  }

} else {

    echo "<div class=\"banner\">";

}