<?php

if(is_array($data[2]['ordered'][0]['content'])) {

  echo '<div class="content-wrap footer grey">';
  echo '<div class="content">';
  echo '<div class="col1">';
  echo '<h3>Address</h3>';

  foreach( $data[2]['ordered'][0]['content'] as $adf ) {

    echo $adf['address'];

  }

  echo "</div>";
  echo '<div class="col2">';
  echo '<h3>Follow us</h3>';

  foreach( $data[2]['ordered'][0]['content'] as $adf2 ) {

    if( $adf2['facebook']['route'] ){
      echo "<a href=\"{$adf2['facebook']['route']}\" target=\"_blank\"><i class=\"fa fa-facebook\" aria-hidden=\"true\"></i></a>";
    }
    if( $adf2['twitter']['route'] ){
      echo "<a href=\"{$adf2['twitter']['route']}\" target=\"_blank\"><i class=\"fa fa-twitter\" aria-hidden=\"true\"></i></a>";
    }
    if( $adf2['linkedin']['route'] ){
      echo "<a href=\"{$adf2['linkedin']['route']}\" target=\"_blank\"><i class=\"fa fa-linkedin\" aria-hidden=\"true\"></i></a>";
    }

  }

  echo "</div>";
  echo '<div class="col3">';
  echo '<h3>Contact us</h3>';

  foreach( $data[2]['ordered'][0]['content'] as $adf3 ) {

    echo $adf3['contact'];

  }

  echo "</div>";
  echo "</div>";
  echo "</div>";

}