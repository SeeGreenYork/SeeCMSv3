
<?php

if( $_GET['tag'] ) {

  echo "<h2>Showing posts tagged '{$_GET['tag']}'</h2><hr />";
} else if( $_GET['year'] ) {

  if( $_GET['month'] ) {
    $month = $see->format->date( "2000-{$_GET['month']}-01", "F " );
  }
  
  echo "<h2>{$month}{$_GET['year']}</h2><hr />";
}


echo "<h2>Latest news</h2>";

if( is_array( $data ) ) {


  foreach( $data as $post ) {
		
		$tags = $post['tagsHTML'];

    echo "<div class=\"newsstory\">";
    echo "<div class=\"newsthumb\"><a href=\"{$post['route']}\"><img src=\"/images/uploads/img-3-{$post['media']->id}.{$post['media']->type}\" alt=\"\" /></a></div>";
    echo "<div class=\"newstext\">";
    echo "<h2>{$post['title']}</h2>";
    echo "<p>{$post['standfirst']}</p>";
    echo "<a href=\"{$post['route']}\">Read more</a>";
    echo "</div>";
    echo "<div class=\"clear\"></div>";
    echo "</div>";
  }
} else {

  echo "<p>No posts were found.</p>";
}


?>
