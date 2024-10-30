<?php
// manage subscriptions
class KonnichiwaSubs {
	static function manage() {
		global $wpdb;
		
		$action = empty($_GET['action']) ? 'list' : $_GET['action'];
		
		switch($action) {
			case 'add': self :: add(); break;
			case 'edit': self :: edit(); break;
			case 'list':
			default:
				self :: list_subscriptions();
			break;
		}
	} // end manage
	
	static function list_subscriptions() {
		global $wpdb;
		$offset = empty($_GET['offset']) ? 0 : intval($_GET['offset']);
		$page_limit = 20;
		$ob = empty($_GET['ob']) ? "id" : esc_attr($_GET['ob']);
		$dir = empty($_GET['dir']) ? "DESC" : esc_attr($_GET['dir']);
		$odir = ($dir == 'ASC') ? 'DESC' : 'ASC';
		
		// select plans
		$plans = $wpdb->get_results("SELECT * FROM ".KONN_PLANS." ORDER BY name");
		
		if(!empty($_GET['plan_id'])) {
			// select subscriptions
			$subs = $wpdb->get_results($wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS tS.*, tU.user_nicename as username
			   FROM ".KONN_SUBS." tS JOIN {$wpdb->users} tU ON tS.user_id = tU.ID
				WHERE plan_id=%d ORDER BY $ob $dir LIMIT $offset, $page_limit", intval($_GET['plan_id'])));
				
			$count = $wpdb->get_var("SELECT FOUND_ROWS()");	
		}
		$dateformat = get_option('date_format');
		include(KONN_PATH."/views/subscriptions.html.php");
	} // end list_subscriptions()
	
	// manually add a subscription for an user
	static function add() {
		global $wpdb;
		$_sub = new KonnichiwaSub();
		
		// select plans
		$plans = $wpdb->get_results("SELECT * FROM ".KONN_PLANS." ORDER BY name");
		
		if(!empty($_POST['ok'])) {
			$_GET['plan_id'] = empty($_POST['plan_id']) ? intval($_GET['plan_id']) : intval($_POST['plan_id']); // POST has priority in pre-selecting the dropdown
			$error = false;
			// try to find the user by username or email
			if(strstr($_POST['userhandle'], '@')) $user = get_user_by("email", sanitize_email($_POST['userhandle']));
			else $user = get_user_by("login", sanitize_text_field($_POST['userhandle']));
			
			if(empty($user)) $error = __('User not found.', 'konnichiwa');
			
			if(!$error) {
				$_sub->add($user->ID, $_POST['plan_id'], 1, $_POST['amt_paid']);		
				konnichiwa_redirect("admin.php?page=konnichiwa_subs&plan_id=".intval($_GET['plan_id']).'&ob='.esc_attr($_GET['ob']).'&dir='.esc_attr($_GET['dir']).'&offset='.esc_attr($_GET['offset']));							
			}
		}
		
		include(KONN_PATH."/views/add-subscription.html.php");
	} // end add()
	
	// edit existing subscription
	static function edit() {
		global $wpdb;

		// select plans
		$plans = $wpdb->get_results("SELECT * FROM ".KONN_PLANS." ORDER BY name");		
		
		if(!empty($_POST['del']) and check_admin_referer('konnichiwa_subs')) {
			$wpdb->query($wpdb->prepare("DELETE FROM ".KONN_SUBS." WHERE id=%d", intval($_GET['id'])));
			konnichiwa_redirect("admin.php?page=konnichiwa_subs&ob=".esc_attr($_GET['ob']).'&dir='.esc_attr($_GET['dir']).'&offset='.esc_attr($_GET['offset']).'&plan_id='.intval($_GET['plan_id']));
		}		
		
		if(!empty($_POST['ok']) and check_admin_referer('konnichiwa_subs')) {
			$expires = intval($_POST['expyear']).'-'.intval($_POST['expmonth']).'-'.intval($_POST['expday']);
			
			$wpdb->query($wpdb->prepare("UPDATE ".KONN_SUBS." SET
				plan_id=%d, expires=%s, status=%d, amt_paid=%s WHERE id=%d",
				intval($_POST['plan_id']), $expires, sanitize_text_field($_POST['status']), floatval($_POST['amt_paid']), intval($_GET['id'])));
			konnichiwa_redirect("admin.php?page=konnichiwa_subs&plan_id=".intval($_GET['plan_id']).'&ob='.esc_attr($_GET['ob']).'&dir='.esc_attr($_GET['dir']).'&offset='.esc_attr($_GET['offset']));				
		}
		
		// select this subscription
		$sub = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".KONN_SUBS." WHERE id=%d", intval($_GET['id'])));	
		$user = get_userdata($sub->user_id);
		$dateformat = get_option('date_format');
		
		include(KONN_PATH."/views/edit-subscription.html.php");
	}
	
	// this function gets executed when user hits the Subscribe button
	static function subscribe() {
		global $wpdb, $user_ID, $post;
		
		// create the subscription if needed and handle the payment thing
		// see if there is already active and non-expired subscription for this plan
		$sub = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".KONN_SUBS." WHERE user_id=%d AND plan_id=%d 
			AND expires > CURDATE()", $user_ID, $_POST['plan_id']));
			
		// select plan
		$plan = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".KONN_PLANS." WHERE id=%d", intval($_POST['plan_id'])));
		
		// status is 1 if the plan is free, otherwise it's 0 and will be set to 1 when paid
		$status = ($plan->price <= 0);
			
		if(empty($sub)) {
			$wpdb->query($wpdb->prepare("INSERT INTO ".KONN_SUBS." SET
				user_id=%d, plan_id=%d, date=CURDATE(), expires = CURDATE() + INTERVAL {$plan->duration} {$plan->duration_unit},
				status=%d, amt_paid=%s", $user_ID, intval($_POST['plan_id']), $status, $plan->price));
			$sub_id = $wpdb->insert_id;	
		}
		else $sub_id = $sub->id;	
		// if subscription exists we won't extend it here but in the payment page. Free subscriptions cannot be extended so we safely ignore them
		
		// select the payment infos
		if($plan->price > 0) {					
			$accept_other_payment_methods = get_option('konnichiwa_accept_other_payment_methods');
			$other_payment_methods = get_option('konnichiwa_other_payment_methods');
			$accept_paypal = get_option('konnichiwa_accept_paypal');
			$accept_stripe = get_option('konnichiwa_accept_stripe');			
			$accept_woo = get_option('konnichiwa_accept_woo');	
			$currency = get_option('konnichiwa_currency');
			$paypal_id = get_option('konnichiwa_paypal_id');
			
			if($accept_stripe) {
				require_once(KONN_PATH.'/lib/Stripe.php');
 
				$stripe = array(
				  'secret_key'      => get_option('konnichiwa_stripe_secret'),
				  'publishable_key' => get_option('konnichiwa_stripe_public')
				);
				 
				Stripe::setApiKey($stripe['secret_key']);
			}		
			
			// replace variables in other payment methods
			$other_payment_methods = str_replace('{{plan-id}}', $plan->id, $other_payment_methods);
			$other_payment_methods = str_replace('{{user-id}}', $user_ID, $other_payment_methods);
			$other_payment_methods = str_replace('{{amount}}', number_format($plan->price,2,".",""), $other_payment_methods);
			
			$accept_moolamojo = get_option('konnichiwa_accept_moolamojo');
			
			if(!empty($accept_moolamojo) and class_exists('MoolaMojo')) {
				$moola_price = get_option('konnichiwa_moolamojo_price');
				$moola_button = get_option('konnichiwa_moolamojo_button');
				$fee = $plan->price;
				$cost_in_moola = round($fee * $moola_price);
				
				// get balance
				$moola_balance = get_user_meta($user_ID, 'moolamojo_balance', true);
				
				if($moola_balance < $cost_in_moola) $paybutton = sprintf(__('Not enough %s.', 'konnichiwa'), MOOLA_CURRENCY);
				else {
					$url = admin_url("admin-ajax.php?action=konnichiwa_ajax&type=pay_with_moolamojo");
					$paybutton = "<input type='button' value='".sprintf(__('Pay %d %s', 'konnichiwa'), $cost_in_moola, MOOLA_CURRENCY)."' onclick='KonnichiwaPay.payWithMoolaMojo({$sub_id}, \"$url\");'>";
				}
				
				// replace the codes in the design
				$moola_button = str_replace('{{{credits}}}', $cost_in_moola, $moola_button);
				$moola_button = str_replace('{{{item}}}', __('plan', 'konnichiwa'), $moola_button);
				$moola_button = str_replace('{{{button}}}', $paybutton, $moola_button);
				$moola_button = stripslashes($moola_button);
			}
			
			$woo_content = '';	
				
			if(!empty($accept_woo) and !empty($plan->woo_product_id)) {			
				// find the product
				$woo_link = get_permalink($plan->woo_product_id);
				if(!empty($woo_link)) {
					$woo_content = '<p align="center"><a href="'.$woo_link.'">'.__('Purchase From Our Store', 'konnichiwa').'</p>';
				}
			} // end Woo
		}		
		
		// now depending on the payment method(s) we have to display the payment / subscription success page
		include(KONN_PATH."/views/subscribe-pay.html.php");
	}
		
	// handles $_POST['konnichiwa_subscribe']
	static function template_redirect() {	
		global $post;
		if(!empty($_POST['konnichiwa_stripe_pay'])) {
			KonnichiwaPayment :: Stripe();
		}		
		
		if(!empty($_POST['konnichiwa_subscribe'])) {			
			// if user is not logged in we need to send them to login first
			if(!is_user_logged_in()) {
				konnichiwa_redirect(site_url("wp-login.php?redirect_to=".urlencode(get_permalink( $post->ID ))));
			}	
			
			ob_start();
			self :: subscribe();
			$content = ob_get_clean();
			$content = str_replace("\n", "", $content); // remove empty lines because it adds br tags
			$content = str_replace("\r", "", $content); // remove empty lines because it adds br tags
			$content = stripslashes($content);
			$post->post_content = $content;
		}
	} // end template_redirect()

	// activate and extend existing subscription	
	static function activate($sub, $plan) {
		global $wpdb;
		
		// beware of free plans. User shouldn't be able to sign up more than once for a free plan
		if($plan->price > 0) {
			$extend_from = $sub->status ? "expires" : "CURDATE()";
			
			// but anyway if expires is less than curdate, set to curdate
			if(strtotime($sub->expires) < time()) $extend_from = "CURDATE()";			
			
			$wpdb->query($wpdb->prepare("UPDATE ".KONN_SUBS." SET
				expires = $extend_from + INTERVAL {$plan->duration} {$plan->duration_unit}, status=1
				WHERE id=%d", $sub->id));
		}
	}
}