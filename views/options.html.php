<div class="wrap">
	<h1><?php _e("Konnichiwa Options", 'konnichiwa')?></h1>
	
	<form method="post">
		<div class="postbox wp-admin" style="padding:10px;">
		
			<h2><?php _e('Payment Settings', 'konnichiwa')?></h2>
			
			<p><label><?php _e('Payment currency:', 'konnichiwa')?></label> <select name="currency" onchange="this.value ? jQuery('#customCurrency').hide() : jQuery('#customCurrency').show(); ">
			<?php foreach($currencies as $key=>$val):
            if($key==$currency) $selected='selected';
            else $selected='';?>
        		<option <?php echo $selected?> value='<?php echo $key?>'><?php echo $val?></option>
         <?php endforeach; ?>
			<option value="" <?php if(!in_array($currency, $currency_keys)) echo 'selected'?>><?php _e('Custom', 'konnichiwa')?></option>
			</select>
			<input type="text" id="customCurrency" name="custom_currency" style='display:<?php echo in_array($currency, $currency_keys) ? 'none' : 'inline';?>' value="<?php echo $currency?>"></p>
			
			<p><?php _e('Here you can specify payment methods that you will accept to give access to content.', 'konnichiwa')?></p>
			
			<p><input type="checkbox" name="accept_paypal" value="1" <?php if($accept_paypal) echo 'checked'?> onclick="this.checked?jQuery('#paypalDiv').show():jQuery('#paypalDiv').hide()"> <?php _e('Accept PayPal', 'konnichiwa')?></p>
			
			<div id="paypalDiv" style='display:<?php echo $accept_paypal?'block':'none'?>;'>
			<p><input type="checkbox" name="paypal_sandbox" value="1" <?php if(get_option('konnichiwa_paypal_sandbox')=='1') echo 'checked'?>> <?php _e('Use Paypal in sandbox mode', 'konnichiwa')?></p>
				<p><label><?php _e('Your Paypal ID:', 'konnichiwa')?></label> <input type="text" name="paypal_id" value="<?php echo get_option('konnichiwa_paypal_id')?>"></p>
				
				<?php if(empty($use_pdt)):?>
				<p><b><?php _e('Note: Paypal IPN will not work if your site is behind a "htaccess" login box or running on localhost. Your site must be accessible from the internet for the IPN to work. In cases when IPN cannot work you need to use Paypal PDT.', 'konnichiwa')?></b></p>
				<?php endif;
				if(!konnichiwa_is_secure() and empty($use_pdt)):?>
					<p style="color:red;font-weight:bold;"><?php _e('Your site is not running on SSL so Paypal IPN will typicall not work. You MUST use the PDT option below.', 'konnichiwa');?></p>
				<?php endif;?>				
			
				<p><input type="checkbox" name="use_pdt" value="1" <?php if($use_pdt == 1) echo 'checked'?> onclick="this.checked ? jQuery('#paypalPDTToken').show() : jQuery('#paypalPDTToken').hide();"> <?php printf(__('Use Paypal PDT instead of IPN (<a href="%s" target="_blank">Why and how</a>)', 'konnichiwa'), 'http://blog.calendarscripts.info/watupro-intelligence-module-using-paypal-data-transfer-pdt-instead-of-ipn/');?></p>
				
				<div id="paypalPDTToken" style='display:<?php echo ($use_pdt == 1) ? 'block' : 'none';?>'>
					<p><label><?php _e('Paypal PDT Token:', 'konnichiwa');?></label> <input type="text" name="pdt_token" value="<?php echo get_option('konnichiwa_pdt_token');?>" size="60"></p>
				</div>
			</div>
			
			<p><input type="checkbox" name="accept_stripe" value="1" <?php if($accept_stripe) echo 'checked'?> onclick="this.checked?jQuery('#stripeDiv').show():jQuery('#stripeDiv').hide()"> <?php _e('Accept Stripe', 'konnichiwa')?></p>
			
			<div id="stripeDiv" style='display:<?php echo $accept_stripe?'block':'none'?>;'>
				<p><label><?php _e('Your Public Key:', 'konnichiwa')?></label> <input type="text" name="stripe_public" value="<?php echo get_option('konnichiwa_stripe_public')?>"></p>
				<p><label><?php _e('Your Secret Key:', 'konnichiwa')?></label> <input type="text" name="stripe_secret" value="<?php echo get_option('konnichiwa_stripe_secret')?>"></p>
			</div>
			
			<?php if(class_exists('woocommerce')):?>
				<p><input type="checkbox" name="accept_woo" value="1" <?php if($accept_woo) echo 'checked'?> onclick="this.checked?jQuery('#wooDiv').show():jQuery('#wooDiv').hide()"> <?php _e('Accept WooCommerce', 'konnichiwa')?></p>
				
				<div id="wooDiv" style='display:<?php echo $accept_woo ?'block':'none'?>;'>
					<p><?php _e('For each membership plan you will be able to select a related WooCommerce product. When the product is purchased, the buyer gets access to the associated subscription plan. Note that only products marked as <b>Downalodable</b> and <b>Virtual</b> can be used. The membership plan will be activated when the order status in WooCommerce is COMPLETED.', 'konnichiwa');?></p>
				</div>
			<?php endif;?>
			
			<p><input type="checkbox" name="accept_moolamojo" <?php if($accept_moolamojo) echo 'checked';?> value="1" onclick="this.checked ? jQuery('#konPayMoola').show() : jQuery('#konPayMoola').hide();"> <?php printf(__('Accept virtual credits from <a href="%s" target="_blank">MoolaMojo</a> (The plugin must be installed and active).', 'konnichiwa'), 'https://moolamojo.com')?></p>

			<div id="konPayMoola" style='display:<?php echo $accept_moolamojo ? 'block' : 'none';?>'>
				<p><label><?php printf(__('Cost of 1 %s in virtual credits:', 'konnichiwa'), $currency)?></label> <input type="text" name="moolamojo_price" value="<?php echo get_option('konnichiwa_moolamojo_price')?>" size="6"></p>
				<p><b><?php _e('Design of the payment button.', 'konnichiwa')?></b>
				<?php _e('You can use HTML and the following codes:', 'konnichiwa')?> {{{credits}}} <?php _e('for the price in virtual credits,', 'konnichiwa')?> {{{button}}} <?php _e('for the payment button itself and', 'konnichiwa')?> [moolamojo-balance] <?php _e('to display the currently logged user virtual credits balance.', 'konnichiwa')?></p>
				<p><textarea name="moolamojo_button" rows="7" cols="50"><?php echo stripslashes($moolamojo_button)?></textarea></p>
				<hr>	
			</div>
						
			
			<p><input type="checkbox" name="accept_other_payment_methods" value="1" <?php if($accept_other_payment_methods) echo 'checked'?> onclick="this.checked?jQuery('#otherPayments').show():jQuery('#otherPayments').hide()"> <?php _e('Accept other payment methods', 'konnichiwa')?> 
				<span class="konnichiwa_help"><?php _e('This option lets you paste your own button HTML code or other manual instructions, for example bank wire. These payments will have to be processed manually unless you can build your own script to verify them.','konnichiwa')?></span></p>
				
			<div id="otherPayments" style='display:<?php echo $accept_other_payment_methods?'block':'none'?>;'>
				<p><?php _e('Enter text or HTML code for payment button(s). You can use the following variables: {{plan-id}}, {{user-id}}, {{amount}}.', 'konnichiwa')?></p>
				<textarea name="other_payment_methods" rows="8" cols="80"><?php echo stripslashes(get_option('konnichiwa_other_payment_methods'))?></textarea>			
			</div>	
			
			<p><input type="submit" value="<?php _e('Save payment settings', 'konnichiwa')?>" class="button button-primary"></p>
		</div>
		<input type="hidden" name="konnichiwa_payment_options" value="1">
		<?php echo wp_nonce_field('konnichiwa_options');?>
	</form>
</div>	