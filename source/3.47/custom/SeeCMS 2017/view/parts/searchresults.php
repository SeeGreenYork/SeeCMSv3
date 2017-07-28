<?php

if ( $data ) {

  echo "<h2>Search results for: {$_GET['search']}</h2><br/>";

  foreach( $data['searchresults'] as $sr ) {

    $type = (($sr['type'])?$sr['type']:'html');
    echo "<div class=\"searchresult\"><h3><img src=\"/seecms/images/icons/{$type}.png\" alt=\"\" class=\"icon\" /><a href=\"{$sr['route']}\">{$sr['title']}</a>".(($sr['filesize'])?' <span>['.$sr['filesize'].']</span>':"")."</h3><p>{$sr['content']}</p></div>";
  }

} else {

  echo "<h2>No results were found for: {$_GET['search']}</h2>";

}