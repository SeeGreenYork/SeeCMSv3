<div class="newsfeed">
	
<?php

if( is_array( $data ) ) {

  foreach( $data as $post ) {

    $startmonth = $see->format->date( $post['eventStart'], "M" );
    $startdate = $see->format->date( $post['eventStart'], "d" );
    $starttime = $see->format->date( $post['eventStart'], "g:i" );
    $endtime = $see->format->date( $post['eventEnd'], "g:i" );

    echo "<div class=\"newsstory\">";
    echo "<div class=\"image\"><div class=\"date\"><div class=\"month\"><p>{$startmonth}</p></div><div class=\"day\"><p>{$startdate}</p></div></div></div>";
    echo "<div class=\"text\">";
    echo "<p class=\"date\">{$starttime} - {$endtime}</p>";
    echo "<h3>{$post['title']}</h3>";
    echo "<p>{$post['standfirst']}</p>";
    echo "<a class=\"readmore\" href=\"{$post['route']}\">Find out more</a>";
    echo "</div>";
    echo "<div class=\"clear\"></div>";
    echo "</div>";

  }
} else {

  echo "<p>There are no upcoming events.</p>";
}

?>

</div>