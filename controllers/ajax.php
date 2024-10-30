<?php
function konnichiwa_ajax() {
	global $wpdb, $user_ID;	
	
	$type = empty($_POST['type']) ? $_GET['type'] : $_POST['type'];	
	
	switch($type) {
		// pay for subscription with MoolaMojo
		case 'pay_with_moolamojo':		
			if(!is_user_logged_in()) die("ERROR: Not logged in");
			
			// payment with moolamojo accepted at all?
			$accept_moolamojo = get_option('konnichiwa_accept_moolamojo');
			if(empty($accept_moolamojo)) die("ERROR: virtual credits are not accepted as payment method.");
			
			// select subscription
			$sub = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . KONN_SUBS." WHERE id=%d", intval($_POST['id'])));
			
			// select plan
			$plan = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . KONN_PLANS." WHERE id=%d", $sub->plan_id)); 
			$fee = $plan->price;
			
			// enough points to pay?
			$moola_price = get_option('konnichiwa_moolamojo_price');
		   $cost_in_moola = $fee * $moola_price;
			
			$user_balance = get_user_meta($user_ID, 'moolamojo_balance', true);	
			if($user_balance < $cost_in_moola) die("ERROR: Not enough virtual credits");
			
			$wpdb->query($wpdb->prepare("INSERT INTO ".KONN_PAYMENTS." SET 
							user_id=%d, plan_id=%d, sub_id=%d, date=CURDATE(), status=%s, method=%s, payment_key=%s, amount=%s",
							$user_ID, $plan->id, $sub->id, 'completed', 'moolamojo', '', $plan->price));
							
			// activate or extend subscription
			KonnichiwaSubs :: activate($sub, $plan);
			
			// deduct user points
			$user_balance -= $cost_in_moola;

			update_user_meta($user_ID, 'moolamojo_balance', $user_balance);
			echo "SUCCESS";
		break;
	} // end switch
	
	exit;
}