<div class="gallery">
<?php

$see->html->js( 'SeeCMS%202017/jquery.fancybox.pack.js', '', '/js/fancybox/' );
$see->html->css( 'SeeCMS%202017/jquery.fancybox.css', 'screen', '/js/fancybox/' );
$see->html->css( 'SeeCMS%202017/jquery.fancybox-buttons.css', 'screen', '/js/fancybox/helpers/' );
$see->html->js( 'SeeCMS%202017/jquery.fancybox-buttons.js', '', '/js/fancybox/helpers/' );
$see->html->js( 'SeeCMS%202017/jquery.fancybox-media.js', '', '/js/fancybox/helpers/' );
$see->html->css( 'SeeCMS%202017/jquery.fancybox-thumbs.css', 'screen', '/js/fancybox/helpers/' );
$see->html->js( 'SeeCMS%202017/jquery.fancybox-thumbs.js', '', '/js/fancybox/helpers/' );
$see->html->js( 'SeeCMS%202017/seecmsgallery.js' );

foreach( $data as $image ) {

  
  echo "<a class=\"seecmsgallery\" rel=\"gallery\" href=\"/images/uploads/img-original-{$image['id']}.{$image['type']}\"><img src=\"/images/uploads/img-2-{$image['id']}.{$image['type']}\" alt=\"\" /></a>";
}
?>
<div class="clear"></div>
</div>