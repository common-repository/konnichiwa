<?php
class KonnichiwaSub {
	// adds a subscription
	function add($user_id, $plan_id, $status, $amt_paid = -1) {
		global $wpdb;
		$plan_id = intval($plan_id);
		
		// select the plan
		$plan = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".KONN_PLANS." WHERE id=%d", $plan_id));
		
		// expiration
		$expires = $wpdb->get_var("SELECT CURDATE() + INTERVAL {$plan->duration} {$plan->duration_unit}");
		
		// when $amt_paid is the default -1 we need to get it from the plan
		if($amt_paid == -1) $amt_paid = $plan->price;				
				
		$wpdb->query($wpdb->prepare("INSERT INTO ".KONN_SUBS." SET
			user_id=%d, plan_id=%d, date=CURDATE(), expires = %s, status=%d, amt_paid=%s",
			$user_id, $plan->id, $expires, $status, $amt_paid));
	} // end add();
}