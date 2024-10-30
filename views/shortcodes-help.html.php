<h2><?php _e('General Shortcodes', 'konnichiwa')?></h2>
	<p><?php _e('The plan shortcode is used to generate a subscribe button. The button will automatically handle the user subscription and redirect them to the payment page accordingly to your payment settings.', 'konnichiwa');?></p>
	
	<p><?php _e('It is recommended to design your own page that will list the plans with their features etc.<br> However there are basic shortcodes that you can use to automatically generate a table with all the available plans:', 'konnichiwa');?> <input type="text" value="[konnichiwa-plans vertical]" readonly="true" onclick="this.select();" size="25"> <?php _e('- generates list of the plans with their feautures ordered in columns, while', 'konnichiwa');?> <br> <input type="text" value="[konnichiwa-plans horizontal]" readonly="true" onclick="this.select();" size="25"> <?php _e('generates a horizontal table with plans. Both codes auto-generate the "Subscribe" buttons.', 'konnichiwa');?> </p>
	
		<h2><?php _e('User Dashboard Shortcode', 'konnichiwa')?></h2>
	
	<p><?php printf(__('The shortcode %s allows you to publish a "My subscriptions" dashboard which will show a table with user active subscriptions of the logged in user. If you pass the argument <b>%s</b> you will also enable a cancel button for each subscription.', 'konnichiwa'), '<input type="text" value="[konnichiwa-mysubs]" size="20" onclick="this.select();" readonly="readonly">', 'allow_cancel=1');?></p>

	
	<h2><?php _e('Protect Shortcodes', 'konnichiwa')?></h2>
	
	<p><?php _e('This shortcode can be used to protect a piece of content inside a post, page or custom content that is public accessible. Here is how to use it:', 'konnichiwa')?></p>
	
	<p><?php _e('Start with', 'konnichiwa')?> <b>[konnichiwa-protect plans="x"]</b> <?php _e('(where x is the required subscription plan ID which you can get from the above table), and end with', 'konnichiwa')?> <b>[/konnichiwa-protect]</b></p>
	<p><?php _e('Put your content between both shortcodes.', 'konnichiwa');?></p>
	<p><?php _e('You can also allow multiple subscription plans by separating their plan IDs with comma (NO SPACES!). Example:', 'konnichiwa')?></p>
	<p><b>[konnichiwa-protect plans="2,5,6"]</b><?php _e('Your protected content here', 'konnichiwa')?><b>[/konnichiwa-protect]</b></p>