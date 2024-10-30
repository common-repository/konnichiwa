<div class="konnichiwa-subscribe">
	<form method="post" action="<?php echo the_permalink()?>">
		<input type="submit" value="<?php empty($already_subscribed) ? _e('Subscribe Now!', 'konnichiwa') : _e('Renew', 'konnichiwa');?>">
		<input type="hidden" name="konnichiwa_subscribe" value="1">
		<input type="hidden" name="plan_id" value="<?php echo $plan_id?>">
	</form>
</div>