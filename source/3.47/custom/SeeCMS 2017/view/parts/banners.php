<?php

if(is_array($data[1]['ordered'][0]['content'])) {

  echo '<div class="banners-wrap">';
  echo '<div class="banners">';
  echo '<div class="fade">';

  foreach( $data[1]['ordered'][0]['content'] as $adf ) {

    if( $adf['image']->id ){
      echo "<div class=\"banner hasimg\" style=\"background: url(/{$see->rootURL}/images/uploads/img-1-{$adf['image']->id}.{$adf['image']->type}) no-repeat center center; background-size: cover\">";
      echo "<div class=\"overlay\"></div>";
    } else {
      echo "<div class=\"banner\">";
    }
    echo "<div class=\"inner\">";
    echo "<div class=\"text\">";
    echo "<h2>{$adf['title']}</h2>";
    echo "<p>{$adf['text']}</p>";
    if( $adf['link']['route'] ){
     echo "<p class=\"buttons\"><a href=\"{$adf['link']['route']}\">{$adf['linktext']}</a></p>"; 
    }
    echo "</div>";
    echo "</div>";
    echo "</div>";

  }

  echo "</div>";
  echo "</div>";
  echo "</div>";

}