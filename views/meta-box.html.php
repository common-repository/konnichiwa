<p><input type="radio" name="konnichiwa_inherit_access" value="yes" <?php if(empty($inherit_access) or $inherit_access == 'yes') echo 'checked'?> onclick="jQuery('#konnichiwaAccess').hide();"> 
	<?php printf(__('Inherit the <a href="%s" target=_blank">global settings</a>.'), "admin.php?page=konnichiwa_content")?> <br>
	<input type="radio" name="konnichiwa_inherit_access" value="no" <?php if(!empty($inherit_access) and $inherit_access == 'no') echo 'checked'?> onclick="jQuery('#konnichiwaAccess').show();"> <?php _e('Configure individual access', 'konnichiwa')?></p>

<div id="konnichiwaAccess" style='display:<?php echo (!empty($inherit_access) and $inherit_access == 'no') ? 'block' : 'none';?>'>
	<p><input type="radio" name="konnichiwa_access" value="none" <?php if(empty($access)) echo 'checked'?> onclick="jQuery('#konnichiwaPlans').hide();"> <?php _e('Accessible to everyone', 'konnichiwa')?><br>
	<input type="radio" name="konnichiwa_access" value="registered" <?php if(!empty($access) and $access == 'registered') echo 'checked'?> onclick="jQuery('#konnichiwaPlans').hide();"> <?php _e('Accessible to all registered users', 'konnichiwa')?><br>
	<input type="radio" name="konnichiwa_access" value="plans" <?php if(!empty($access) and $access != 'registered') echo 'checked'?> onclick="jQuery('#konnichiwaPlans').show();"> <?php _e('Accessible only to users with the following subscription plans:', 'konnichiwa')?><br></p>
	
	<div class="wrap" id="konnichiwaPlans" style="display:<?php echo (!empty($access) and $access != 'registered') ? 'block' : 'none';?>;">
		<?php foreach($plans as $plan):?>
	 		<input type="checkbox" name="konnichiwa_plans[]" value="<?php echo $plan->id?>" <?php if(!empty($access) and strstr($access, "|".$plan->id."|")) echo 'checked'?>> <?php echo $plan->name?><br> 
	 	<?php endforeach;?>
	</div>
</div>	