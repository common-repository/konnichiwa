<?php
// provides the payment processing
class KonnichiwaPayment {
	static $pdt_mode = false;	
	static $pdt_response = '';	
	
	// handle Paypal IPN request
	static function parse_request($wp) {
		// only process requests with "konnichiwa=paypal"
	   if (array_key_exists('konnichiwa', $wp->query_vars) 
	            && $wp->query_vars['konnichiwa'] == 'paypal') {
	        self::paypal_ipn($wp);
	   }	
	}
	
		// process paypal IPN
	static function paypal_ipn($wp = null) {
		global $wpdb;
		echo "<!-- KONNICHIWA paypal IPN -->";
		
		// print_r($_GET);
		// read the post from PayPal system and add 'cmd'
		$pdt_mode = get_option('konnichiwa_use_pdt');
		if(!empty($_GET['tx']) and !empty($_GET['konnichiwa_pdt']) and get_option('konnichiwa_use_pdt')==1) {
			// PDT			
			$req = 'cmd=_notify-synch';
			$tx_token = strtoupper($_GET['tx']);
			$auth_token = get_option('konnichiwa_pdt_token');
			$req .= "&tx=$tx_token&at=$auth_token";
			$pdt_mode = true;
			$success_responce = "SUCCESS";
		}
		else {	
			// IPN		
			$req = 'cmd=_notify-validate';
			foreach ($_POST as $key => $value) { 
			  $value = urlencode(stripslashes($value)); 
			  $req .= "&$key=$value";
			}
			$success_responce = "VERIFIED";
		}		
		
		self :: $pdt_mode = $pdt_mode;	
		
		$paypal_host = "ipnpb.paypal.com";
		$paypal_sandbox = get_option('konnichiwa_paypal_sandbox');
		if($paypal_sandbox == '1') $paypal_host = 'ipnpb.sandbox.paypal.com';
		
		// post back to PayPal system to validate
		$paypal_host = "https://".$paypal_host;
		
		// wp_remote_post
		$response = wp_remote_post($paypal_host, array(
			    'method'      => 'POST',
			    'timeout'     => 45,
			    'redirection' => 5,
			    'httpversion' => '1.0',
			    'blocking'    => true,
			    'headers'     => array(),
			    'body'        => $req,
			    'cookies'     => array()
		    ));
		
		if ( is_wp_error( $response ) ) {
		    $error_message = $response->get_error_message();
			 return self::log_and_exit("Can't connect to Paypal: $error_message");
		} 
		
		if (strstr ($response['body'], $success_responce) or $paypal_sandbox == '1') self :: paypal_ipn_verify($response['body']);
		else return self::log_and_exit("Paypal result is not VERIFIED: ".$response['body']);			
	
		exit;
	}
	
	// process paypal IPN
	static function paypal_ipn_verify($pp_response) {
		global $wpdb, $user_ID, $post;
		echo "<!-- KONNICHIWA paypal IPN -->";

		// when we are in PDT mode let's assign all lines as POST variables
		if(self :: $pdt_mode) {
			 $lines = explode("\n", $pp_response);	
				if (strcmp ($lines[0], "SUCCESS") == 0) {
				for ($i=1; $i<count($lines);$i++){
					if(strstr($lines[$i], '=')) list($key,$val) = explode("=", $lines[$i]);
					$_POST[urldecode($key)] = urldecode($val);
				}
			 }
			 
			 $_GET['user_id'] = $user_ID;
			 self :: $pdt_response = $pp_response;
		} // end PDT mode transfer from lines to $_POST	 		
		
	   $paypal_email = get_option("konnichiwa_paypal_id");
		
		
   	// check the payment_status is Completed
      // check that txn_id has not been previously processed
      // check that receiver_email is your Primary PayPal email
      // process payment
	   $payment_completed = false;
	   $txn_id_okay = false;
	   $receiver_okay = false;
	   $payment_currency_okay = false;
	   $payment_amount_okay = false;
	   
	   if(@$_POST['payment_status']=="Completed") {
	   	$payment_completed = true;
	   } 
	   else self::log_and_exit("Payment status: $_POST[payment_status]");
	   
	   // check txn_id
	   $txn_exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".KONN_PAYMENTS."
					   WHERE method='paypal' AND payment_key=%s", sanitize_text_field($_POST['txn_id'])));
		if(empty($txn_id)) $txn_id_okay = true; 
		else {
			// in PDT mode just redirect to the post because existing txn_id isn't a problem.
			// but of course we shouldn't insert second payment
			if( self :: $pdt_mode) konnichiwa_redirect(get_permalink($_GET['post_id']));
			return self::log_and_exit("TXN ID exists: $txn_exists");
		}  
			
		// check receiver email
		if(strtolower($_POST['business']) == strtolower($paypal_email)) {
			$receiver_okay = true;
		}
		else self::log_and_exit("Business email is wrong: $_POST[business]");
		
		// check payment currency
		if($_POST['mc_currency']==get_option("konnichiwa_currency")) {
			$payment_currency_okay = true;
		}
		else self::log_and_exit("Currency is $_POST[mc_currency]"); 
		
		// select subscription
		$sub = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".KONN_SUBS." WHERE id=%d", intval($_GET['sub_id'])));
		
		// select plan
		$plan = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".KONN_PLANS." WHERE id=%d", $sub->plan_id));
		
		// check amount
		if($_POST['mc_gross'] >= $plan->price) {
				$payment_amount_okay = true;
		}
		else self::log_and_exit("Wrong amount: $_POST[mc_gross] when price is {$plan->price}"); 
		
		
		// everything OK, insert payment and activate/extend subscription
		if($payment_completed and $txn_id_okay and $receiver_okay and $payment_currency_okay 
				and $payment_amount_okay) {						
			$wpdb->query($wpdb->prepare("INSERT INTO ".KONN_PAYMENTS." SET 
				user_id=%d, plan_id=%d, sub_id=%d, date=CURDATE(), status=%s, method=%s, payment_key=%s, amount=%s",
				intval($_GET['user_id']), $plan->id, $sub->id, 'completed', 'paypal', sanitize_text_field($_POST['txn_id']), $plan->price));
				
			// activate or extend subscription
			KonnichiwaSubs :: activate($sub, $plan);
			if(!self :: $pdt_mode) exit;
			else konnichiwa_redirect(get_permalink($_GET['post_id']));
		}		
		
		exit;
	}
	
	
	// log paypal errors
	static function log_and_exit($msg) {
		// log
		$msg = "Paypal payment attempt failed at ".date(get_option('date_format').' '.get_option('time_format')).": ".$msg;
		$errorlog=get_option("konnichiwa_errorlog");
		$errorlog = $msg."\n".$errorlog;
		update_option("konnichiwa_errorlog",$errorlog);
		
		// throw exception as there's no need to contninue
		exit;
	}
	
	static function Stripe() {
		global $wpdb, $user_ID;
		require_once(KONN_PATH.'/lib/Stripe.php');
 
		$stripe = array(
		  'secret_key'      => get_option('konnichiwa_stripe_secret'),
		  'publishable_key' => get_option('konnichiwa_stripe_public')
		);
		 
		Stripe::setApiKey($stripe['secret_key']);		
		
		$token  = $_POST['stripeToken'];
		$sub = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".KONN_SUBS." WHERE id=%d", intval($_POST['sub_id'])));
		$plan = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".KONN_PLANS." WHERE id=%d", $sub->plan_id));
		$fee = $plan->price;
		$user = get_userdata($user_ID);
		$currency = get_option('konnichiwa_currency');
			 
		try {
			 $customer = Stripe_Customer::create(array(
		      'email' => $user->user_email,
		      'card'  => $token
		  ));				
			
		  $charge = Stripe_Charge::create(array(
		      'customer' => $customer->id,
		      'amount'   => $fee*100,
		      'currency' => $currency
		  ));
		} 
		catch (Exception $e) {
			wp_die($e->getMessage());
		}	  
		
		// insert payment record		
		$wpdb->query($wpdb->prepare("INSERT INTO ".KONN_PAYMENTS." SET 
							user_id=%d, plan_id=%d, sub_id=%d, date=CURDATE(), status=%s, method=%s, payment_key=%s, amount=%s",
							$user_ID, $plan->id, $sub->id, 'completed', 'stripe', $customer->ID, $plan->price));	
							
		KonnichiwaSubs :: activate($sub, $plan);					
			
		// redirect to self to avoid inserting again
		konnichiwa_redirect($_SERVER['REQUEST_URI']);	
	}	
}