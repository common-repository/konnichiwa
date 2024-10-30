<?php
// helps the WooCommerce bridge integration. Probably at some moment Woo bridge will be fully integrated here
class KonnichiwaWoo {
	// get WooCommerce products
	public static function get_products() {
		$woo_products = array();
		// WooCommerce integration?
		if(class_exists('woocommerce')) {
			// find all virtual and downloadable products
			$args =  array(
			    'post_type'      => array('product'),
			    'post_status'    => 'publish',
			    'posts_per_page' => -1,
			    'meta_query'     => array( 
			        array(
			            'key' => '_virtual',
			            'value' => 'yes',
			            'compare' => '=',  
			        ),
			        array(
			            'key' => '_downloadable',
			            'value' => 'yes',
			            'compare' => '=',  
			        )  
			    ),
			);
			$query = new WP_Query( $args );
			
			$woo_products = $query->posts;
		}
		
		return $woo_products;			
	} // end woo_products()

	// complete a WooCommerce order	
	public static function order_complete($order_id) {
		global $wpdb;
		
		// user exists? if not, we have to create them and let's log them in
		$user_id = get_post_meta($order_id, "_customer_user", true);
		
		if(empty($user_id)) {
			$password = wp_generate_password( 12, true );
			$user_email = get_post_meta($order_id, "_billing_email", true);
			
			// email exists?
			$user = get_user_by('email', $user_email);
			if(empty($user->ID)) {
				$user_id = wp_create_user( $user_email, $password, $user_email );
				wp_update_user( array ('ID' => $user_id ) ) ;
				
				// log them in
				wp_set_current_user($user_id);
        		wp_set_auth_cookie($user_id);
			}
			else $user_id = $user->ID;
		}
		
		// select line items
		$items = $wpdb->get_results($wpdb->prepare("SELECT tI.*, tM.meta_value as product_id 
				FROM {$wpdb->prefix}woocommerce_order_items tI JOIN {$wpdb->prefix}woocommerce_order_itemmeta tM
				ON tM.order_item_id = tI.order_item_id AND tM.meta_key='_product_id'
				WHERE tI.order_id = %d AND tI.order_item_type = 'line_item'", $order_id));
		$plan_ids = []; // plan IDs to process
		
		foreach($items as $item) {
			// is there a subscription plan with this Woo product id?
			$item_plan_ids = $wpdb->get_results($wpdb->prepare("SELECT id FROM ".KONN_PLANS." WHERE woo_product_id=%d", $item->product_id));
			foreach($item_plan_ids as $id) {
				if(!in_array($id->id, $plan_ids)) $plan_ids[] = $id->id;
			} // end foreach found plan
		}	// end foreach item	
				
		// now insert payment and activate subscription for each plan
		foreach($plan_ids as $plan_id) {
			$plan = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".KONN_PLANS." WHERE id=%d", $plan_id));	
			
			// create the subscription if needed and handle the payment thing
			// see if there is already active and non-expired subscription for this plan
			$sub = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".KONN_SUBS." WHERE user_id=%d AND plan_id=%d 
				AND expires > CURDATE()", $user_id, $plan_id));	
			
			if(empty($sub)) {
				$wpdb->query($wpdb->prepare("INSERT INTO ".KONN_SUBS." SET
					user_id=%d, plan_id=%d, date=CURDATE(), expires = CURDATE() + INTERVAL {$plan->duration} {$plan->duration_unit},
					status=%d, amt_paid=%s", $user_id, $plan_id, 1, $plan->price));
				$sub_id = $wpdb->insert_id;	
			}
			else $sub_id = $sub->id;		
			
			$wpdb->query($wpdb->prepare("INSERT INTO ".KONN_PAYMENTS." SET 
				user_id=%d, plan_id=%d, sub_id=%d, date=CURDATE(), status=%s, method=%s, payment_key=%s, amount=%s",
				$user_id, $plan->id, $sub_id, 'completed', 'woocommerce', '', $plan->price));
				
			// activate or extend subscription
			$sub = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".KONN_SUBS." WHERE id=%d", $sub_id));
			KonnichiwaSubs :: activate($sub, $plan);	
		} // end foreach plan ID
		
		// now redirect, but where?
		// NYI
	} // end order_complete
}