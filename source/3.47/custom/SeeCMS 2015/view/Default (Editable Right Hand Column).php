<htmlheader>
<pageheader>
<div class="wrap">
	<div class="container">
		<div class="jumbotron">
			<content4>
		</div>
	</div>
</div>
<div class="content container">
	<div class="col-lg-9">
		<content1>
		<content2>
<?php
			
  if( $see->SeeCMS->object->getMeta( 'type' ) == 'post' ) {
    if( $see->SeeCMS->object->posttype->name == 'Event' ) {
      echo "<a class=\"btn btn-default\" href=\"/events/\">Back to events</a>";
    } else {
      echo "<a class=\"btn btn-default\" href=\"/news/\">Back to news</a>";
    }
  }

?>
	</div>
	<div class="col-lg-3">
		<secondarynavigation>
		<content3>
	</div>
</div>
<pagefooter>