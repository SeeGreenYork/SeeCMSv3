<htmlheader>
<pageheader>
<div class="banners-wrap">
	<div class="banners innerbanners">
		<innerbanner>
			<div class="inner">
				<div class="text">
					<?php
						if( $see->SeeCMS->object->getMeta( 'type' ) == 'post' ) {
							echo '<blogintro>';
						} else {
							echo "<h1>{$see->SeeCMS->object->title}</h1>";
							echo '<content2>';
						}
					?>
				</div>
			</div>
		</div>
	</div>
</div>
<secondarynavigation>
<div class="content-wrap">
	<div class="content">
		<breadcrumb>
		<content1>
		<content3>
	</div>
</div>
<?php
	if( $see->SeeCMS->ascendants[1] == 6 ){
		echo '<contactform>';
	}
?>
<pagefooter>