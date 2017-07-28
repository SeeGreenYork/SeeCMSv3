<?php

$post = $see->SeeCMS->object;
    
$date = $see->format->date( $post['date'], "d F Y" );

//if( $post['media']->id ){
//	echo "<h1 class=\"withthumb\"><span class=\"image\"><img src=\"/images/uploads/img-2-{$post['media']->id}.{$post['media']->type}\" alt=\"{$post['media']->alt}\" /></span> {$see->SeeCMS->object->title}</h1>";
//} else {
	echo "<h1>{$see->SeeCMS->object->title}</h1>";
//}
echo "<p>{$post['standfirst']}</p>";
