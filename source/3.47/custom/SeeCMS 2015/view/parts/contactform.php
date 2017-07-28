<?php

		$formSettings['controller']['name'] = 'SeeFormProcess';
		$formSettings['controller']['method'] = 'sendByEmail';
		$formSettings['controller']['settings']['to'] = $data;
		$formSettings['controller']['settings']['from'] = $data;
		$formSettings['controller']['settings']['subject'] = "Website contact form";
		$formSettings['controller']['settings']['successredirect'] = "./?thankyou=1";

		$formSettings['validate']['name']['validate'] = 'required';
		$formSettings['validate']['name']['error'] = 'Please enter your name.';
		$formSettings['validate']['email']['validate'] = 'email';
		$formSettings['validate']['email']['error'] = 'Please enter a valid email address.';
		$formSettings['validate']['tel']['validate'] = 'required';
		$formSettings['validate']['tel']['error'] = 'Please enter a contact telephone number.';

		//$formSettings['disableStandardErrors'] = true;

?>
<div class="well bs-component">
<div class="seecmswebsiteuserregisteruser form form-horizontal">
	<?php $f = $see->html->form( $formSettings ); ?>
		
		<fieldset>
		<legend>Contact</legend>
		<div class="form-group"><label class="col-lg-2 control-label" for="name">Name</label><div class="col-lg-10"><?php $f->text( array( 'name' => 'name', 'value' => '', 'id' => 'inputDefault', 'class' => 'form-control') ); ?></div></div>
		<div class="form-group"><label class="col-lg-2 control-label" for="email">Email</label><div class="col-lg-10"><?php $f->text( array( 'name' => 'email', 'value' => '', 'id' => 'inputDefault', 'class' => 'form-control') ); ?></div></div>
		<div class="form-group"><label class="col-lg-2 control-label" for="tel">Phone</label><div class="col-lg-10"><?php $f->text( array( 'name' => 'tel', 'value' => '', 'id' => 'inputDefault', 'class' => 'form-control') ); ?></div></div>
		<div class="form-group"><label class="col-lg-2 control-label" for="enquiry">Comment or Enquiry</label><div class="col-lg-10"><?php $f->textarea( array( 'name' => 'enquiry', 'value' => '', 'class' => 'form-control', 'id' => 'inputDefault') ); ?></div></div>
		<div class="form-group"><label class="col-lg-2 control-label" for="submit"></label><div class="col-lg-10"><?php $f->submit( array( 'name' => 'submit', 'class' => 'btn btn-primary', 'value' => 'Submit') ); ?></div></div>
		
		<h2 id="contactform"></h2>
		
		<?php if( $_GET['thankyou'] ) { echo '<p class="thanks">Thank you, we&#39;ll be in touch.</p>'; } ?>
		<div class="clear"></div>
		<?php $f->close(); ?>
		</fieldset>

	</div>
	</div>


           