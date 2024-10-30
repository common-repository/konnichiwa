<div class="wrap">
	<h1><?php _e('Add/Edit Subscription Plan', 'konnichiwa')?></h1>
	
	<form method="post" onsubmit="return konnichiwaValidate(this);">
		<p><label><?php _e('Plan name', 'konnichiwa')?></label> <input type="text" name="name" size="50" value="<?php echo empty($plan->name) ? '' : stripslashes($plan->name)?>"></p>
		<p><label><?php _e('Optional description', 'konnichiwa')?></label> <?php wp_editor(empty($plan->description) ? '' : stripslashes($plan->description), 'description')?></p>
		<p><label><?php _e('Price', 'konnichiwa')?></label> <?php echo KONN_CURRENCY?> <input type="text" name="price" size="6" value="<?php echo empty($plan->price) ? '' : $plan->price?>"></p>
		<p><label><?php _e('Duration', 'konnichiwa')?></label> <input type="text" name="duration" size="4" value="<?php echo @$plan->duration?>"> 
		<select name="duration_unit">
			<option value="day" <?php if(!empty($plan->id) and $plan->duration_unit == 'day') echo 'selected'?>><?php _e('days', 'konnichiwa')?></option>
			<option value="week" <?php if(!empty($plan->id) and $plan->duration_unit == 'week') echo 'selected'?>><?php _e('weeks', 'konnichiwa')?></option>
			<option value="month" <?php if(!empty($plan->id) and $plan->duration_unit == 'month') echo 'selected'?>><?php _e('months', 'konnichiwa')?></option>
		</select></p>
		
		<?php if(!empty($woo_products) and count($woo_products)):?>
			<p><?php _e('Sell as a WooCommerce product:', 'konnichiwa');?> <select name="woo_product_id">
				<option value=""><?php _e('- Do not link to a product -', 'konnichiwa');?></option>
				<?php foreach($woo_products as $product):?>
					<option value="<?php echo $product->ID?>" <?php if(!empty($plan->woo_product_id) and $plan->woo_product_id == $product->ID) echo 'selected';?>><?php echo stripslashes($product->post_title);?></option>
				<?php endforeach;?>					
			</select></p>
		<?php endif;?>			
		
		<p><input type="checkbox" name="subscribe_on_signup" value="1" <?php if(!empty($plan->subscribe_on_signup)) echo 'checked'?>> <?php _e('Automatically subscribe new users for this plan (they will not be charged for this first subscription).', 'konnichiwa');?></p>
		<p><input type="submit" value="<?php _e('Save This Plan', 'konnichiwa')?>" class="button button-primary"></p>
		<input type="hidden" name="ok" value="1">
	</form>
</div>

<script type="text/javascript" >
function konnichiwaValidate(frm) {
	if(frm.name.value == '') {
		alert("<?php _e('Please enter name.', 'konnichiwa')?>");
		frm.name.focus();
		return false;
	}
	
	if(frm.duration.value == '' || isNaN(frm.duration.value) || frm.duration.value <= 0) {
		alert("<?php _e('Please enter positive number for duration.', 'konnichiwa')?>");
		frm.duration.focus();
		return false;
	}
	
	return true;
}
</script>