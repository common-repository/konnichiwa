<div class="wrap">
	<h1><?php _e('Edit Subscription', 'konnichiwa')?></h1>
	
	<?php if(!empty($error)):?>
		<p class="error"><?php echo $error;?></p>
	<?php endif;?>
	
	<form method="post">
		<div class="konnichiwa-wrap konnichiwa-form wp-admin">
			<p><label><?php _e('User:', 'konnichiwa')?></label> <b><?php echo $user->user_nicename?></b></p>
			<p><label><?php _e('Date subscribed:', 'konnichiwa')?></label> <b><?php echo date($dateformat, strtotime($sub->date));?></b></p>
			<p><label><?php _e('Expiration date:', 'konnichiwa')?></label> <?php echo KonnichiwaQuickDDDate('exp', $sub->expires, null, null, 2014);?></p>
			<p><label><?php _e('Subscription plan:', 'konnichiwa')?></label> <select name="plan_id">
				<?php foreach($plans as $plan):?>
					<option value="<?php echo $plan->id?>" <?php if($plan->id == $sub->plan_id) echo 'selected'?>><?php echo $plan->name?></option>
				<?php endforeach;?>
			</select></p>
		
			<p><label><?php _e('Amount paid:','konnichiwa')?></label> <?php echo KONN_CURRENCY?> <input type="text" name="amt_paid" size="6" value="<?php echo $sub->amt_paid?>"></p>
			
			<p><label><?php _e('Status:', 'konnichiwa')?></label> <select name="status">
				<option value="1" <?php if(!empty($sub->status)) echo 'selected'?>><?php _e('Active', 'konnichiwa')?></option>
				<option value="0" <?php if(empty($sub->status)) echo 'selected'?>><?php _e('Inactive', 'konnichiwa')?></option>
				<option value="2" <?php if(!empty($sub->status) and $sub->status==2) echo 'selected'?>><?php _e('Cancelled', 'konnichiwa')?></option>
			</select></p>
			
			<p><input type="submit" value="<?php _e('Save Subscription', 'konnichiwa')?>" class="button button-primary">
			<input type="button" value="<?php _e('Delete', 'konnichiwa');?>" onclick="KonnichiwaConfirmDelete(this.form);" class="button">
			<input type="button" value="<?php _e('Cancel', 'konnichiwa')?>" onclick="window.location = 'admin.php?page=konnichiwa_subs&plan_id=<?php echo intval($_GET['plan_id'])?>&ob=<?php echo esc_attr($_GET['ob'])?>&dir=<?php echo esc_attr($_GET['dir'])?>&offset=<?php echo esc_attr($_GET['offset'])?>'" class="button"></p>
			<input type="hidden" name="ok" value="1">
			<input type="hidden" name="del" value="0">
		</div>
		<?php wp_nonce_field('konnichiwa_subs');?>
	</form>
</div>

<script type="text/javascript" >
function KonnichiwaConfirmDelete(frm) {
	if(confirm("<?php _e('Are you sure?', 'konnichiwa')?>")) {
		frm.del.value=1;
		frm.submit();
	}
}
</script>