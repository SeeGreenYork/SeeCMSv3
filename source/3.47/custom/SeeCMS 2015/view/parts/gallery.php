<div class="gallery">
<?php

foreach( $data as $image ) {

  
  echo "<a class=\"fancybox\" rel=\"gallery\" href=\"/images/uploads/img-original-{$image['id']}.{$image['type']}\"><img src=\"/images/uploads/img-2-{$image['id']}.{$image['type']}\" alt=\"\" /></a>";
}
?>
<div class="clear"></div>
</div>