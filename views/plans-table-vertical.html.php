<table class="konnichiwa-plans-vertical">
	<tr>
	<?php foreach($plans as $plan):?>
		<td valign="top"><h3><?php echo $plan->name?></h3>
		<h5><?php echo KONN_CURRENCY." ".$plan->price;?> <?php _e('for', 'konnichiwa')?> <?php echo $plan->duration.' '.$plan->duration_unit.'s'?></h5>
		<?php if(!empty($plan->description)) echo apply_filters('the_content', stripslashes($plan->description));?>
		
			[konnichiwa-subscribe <?php echo $plan->id?>] 
		</td>
	<?php endforeach;?>
	</tr>
</table>