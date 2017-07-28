<div class="blogfeed">
	
<?php

if( $_GET['year'] ) {

  if( $_GET['month'] ) {
    $month = $see->format->date( "2000-{$_GET['month']}-01", "F " );
  }
  
  echo "<h2>Showing events for {$month}{$_GET['year']}</h2><hr />";
}

if( is_array( $data ) ) {

  echo "<table class=\"table table-striped table-hover\"><thead><tr><th>Event name</th><th>Date / Time</th><th>Description</th><th></th></tr></thead><tbody>";

  foreach( $data as $post ) {
    
    $start = $see->format->date( $post['eventStart'], "d F Y" );
    $end   = $see->format->date( $post['eventEnd'], "d F Y" );
    
    $startTime = $see->format->date( $post['eventStart'], "H:i" );
    $endTime   = $see->format->date( $post['eventEnd'], "H:i" );
        
    if( $start == $end || !$end ) {
      $date = $start.(($startTime!='00:00')?", {$startTime}":"").(($endTime!='00:00')?" - {$endTime}":"");
    } else {
      $date = "{$start} to {$end}";
    }
		
		$tags = $post['tagsHTML'];

    echo "<tr class=\"event feature {$months}\"><td>{$post['title']}</td><td>{$date}</td><td>{$post['standfirst']}</td><td><a href=\"{$post['route']}\">Read more &gt;</a></td></tr>";

  }

  echo "</tbody></table>";
} else {

  echo "<p>No posts were found.</p>";
}

?>

</div>
