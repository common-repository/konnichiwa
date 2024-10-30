<?php if(count($subs)):?>
	<table class="konnichiwa-table">
		<tr><th><?php _e('Plan name', 'konnichiwa');?></th><th><?php _e('Date subscribed', 'konnichiwa');?></th>
			<th><?php _e('Expiration date', 'konnichiwa');?></th><th><?php _e('Amount paid', 'konnichiwa');?></th><th><?php _e('Renew', 'konnichiwa');?></th>
			<?php if(!empty($atts['allow_cancel'])):?><th><?php _e('Cancel', 'konnichiwa');?></th><?php endif;?></tr>
		<?php foreach($subs as $sub):
					$class = ("alternate" == @$class) ? '' : 'alternate';?>
					<tr class="<?php echo $class?>"><td><?php echo stripslashes($sub->plan_name);?></td><td><?php echo date_i18n($dateformat, strtotime($sub->date))?></td>
					<td><?php echo date_i18n($dateformat, strtotime($sub->expires))?></td>				
					<td><?php echo KONN_CURRENCY.' '.$sub->amt_paid?></td>
					<td><?php if($sub->plan_price > 0): 
						echo do_shortcode('[konnichiwa-subscribe '.$sub->plan_id.' show_form=1]');
					else: _e('N/a (Free plan)', 'konnichiwa');
					endif;?></td>
					<?php if(!empty($atts['allow_cancel'])):?><td><form method="post">
					<input type="button" value="<?php _e('Cancel', 'konnichiwa')?>" onclick="if(confirm('<?php _e('Are you sure?', 'konnichiwa')?>')) {this.form.konnichiwa_cancel.value=1; this.form.submit();}">
					<input type="hidden" name="konnichiwa_cancel" value="0">	
					<input type="hidden" name="sub_id" value="<?php echo $sub->id?>">				
					</form></td><?php endif;?></tr>
		<?php endforeach;?>	
	</table>
<?php else:?>
	<p><?php _e('You have no active subscriptions at this time.', 'konnichiwa');?></p>
<?php endif;?>