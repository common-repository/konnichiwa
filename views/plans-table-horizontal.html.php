<table class="konnichiwa-plans-horizontal">
	<tr><th><?php _e('Subscription Plan', 'konnichiwa')?></th><th><?php _e('Valid for', 'konnichiwa')?></th><th><?php _e('Price', 'konnichiwa')?></th></tr>
	<?php foreach($plans as $plan):?>
		<tr><td><h3><?php echo $plan->name?></h3>
		<?php if(!empty($plan->description)) echo apply_filters('the_content', stripslashes($plan->description));?> </td>
		<td><?php echo $plan->duration.' '.$plan->duration_unit.'s'?></td>
		<td><?php echo KONN_CURRENCY." ".$plan->price;?>
		[konnichiwa-subscribe <?php echo $plan->id?>]</td></tr>
	<?php endforeach;?>
</table>