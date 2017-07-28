<?php

		$formSettings['controller']['name'] = 'SeeFormProcess';
		$formSettings['controller']['method'] = 'sendByEmail';
		$formSettings['controller']['settings']['to'] = $data;
		$formSettings['controller']['settings']['from'] = $data;
		$formSettings['controller']['settings']['subject'] = "{$see->siteTitle} contact form";
		$formSettings['controller']['settings']['successredirect'] = "./?thankyou=1";

		$formSettings['validate']['name']['validate'] = 'required';
		$formSettings['validate']['name']['error'] = 'Please enter your name.';
		$formSettings['validate']['email']['validate'] = 'email';
		$formSettings['validate']['email']['error'] = 'Please enter a valid email address.';
		$formSettings['validate']['tel']['validate'] = 'required';
		$formSettings['validate']['tel']['error'] = 'Please enter a contact telephone number.';

?>

<div class="contact-wrap">
	<div class="contact">
		<div class="left">
			<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2345.9216878476013!2d-1.1016046838210267!3d53.986413980118115!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4879317d21c0437d%3A0xa9fad78d1bd9894d!2sSee+Green!5e0!3m2!1sen!2suk!4v1478081821404" width="100%" height="450" frameborder="0" style="border:0" allowfullscreen></iframe>
		</div>
		<div class="right">
			<div class="form">
		<?php $f = $see->html->form( $formSettings ); ?>
		<?php if( $_GET['thankyou'] ) { echo '<p class="thanks">Thank you, we&#39;ll be in touch.</p>'; } ?>
		<div class="input">
			<label>Name:</label>
			<?php $f->text( array( 'name' => 'name', 'value' => '') ); ?>
		</div>
		<div class="input">
			<label>Phone:</label>
			<?php $f->text( array( 'name' => 'tel', 'value' => '') ); ?>
		</div>
		<div class="input">
			<label>Email:</label>
			<?php $f->text( array( 'name' => 'email', 'value' => '') ); ?>
		</div>
		<div class="input">
			<label>Message:</label>
		<?php $f->textarea( array( 'name' => 'message', 'value' => '') ); ?>
					</div>
					<div class="input">
						<?php $f->submit( array( 'name' => 'submit', 'value' => 'Submit') ); ?>
					</div>
					<?php $f->close(); ?>
				</div>
		</div>
	</div>
</div>