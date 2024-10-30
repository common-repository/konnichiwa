<?php
class KonnichiwaPlan {
	function add($vars) {
		global $wpdb;
		
		$this->prepare_vars($vars);
		
		$result = $wpdb->query($wpdb->prepare("INSERT INTO ".KONN_PLANS." SET
			name=%s, description=%s, price=%f, duration=%d, duration_unit=%s, subscribe_on_signup=%d, woo_product_id=%d", 
			$vars['name'], $vars['description'], $vars['price'], $vars['duration'], $vars['duration_unit'], 
			$vars['subscribe_on_signup'], $vars['woo_product_id']));
			
		if($result === false) throw new Exception(__('DB Error', 'konnichiwa'));
		return $wpdb->insert_id;	
	} // end add
	
	function save($vars, $id) {
		global $wpdb;
		$id = intval($id);
		
		$this->prepare_vars($vars);
		
		$result = $wpdb->query($wpdb->prepare("UPDATE ".KONN_PLANS." SET
			name=%s, description=%s, price=%f, duration=%d, duration_unit=%s, subscribe_on_signup=%d, woo_product_id=%d WHERE id=%d", 
			$vars['name'], $vars['description'], $vars['price'], $vars['duration'], $vars['duration_unit'], 
			$vars['subscribe_on_signup'], $vars['woo_product_id'], $id));
			
		if($result === false) throw new Exception(__('DB Error', 'konnichiwa'));
		return true;	
	}
	
	function prepare_vars(&$vars) {
		$vars['name'] = sanitize_text_field($vars['name']);
		$vars['description'] = wp_kses_post($vars['description']);
		$vars['price'] = floatval($vars['price']);
		$vars['duration'] = intval($vars['duration']);
		$vars['duration_unit'] = sanitize_text_field($vars['duration_unit']);
		$vars['subscribe_on_signup'] = empty($vars['subscribe_on_signup']) ? 0 : 1;
		$vars['woo_product_id'] = empty($_POST['woo_product_id']) ? 0 : intval($_POST['woo_product_id']);
	}
	
	function delete($id) {
		global $wpdb;
		
		// delete subscriptions
		$wpdb->query($wpdb->prepare("DELETE FROM ".KONN_SUBS." WHERE plan_id=%d", $id));
		
		// delete usage
		$wpdb->query($wpdb->prepare("DELETE FROM ".KONN_USAGE." WHERE plan_id=%d", $id));
		
		// delete plan
		$wpdb->query($wpdb->prepare("DELETE FROM ".KONN_PLANS." WHERE id=%d", $id));
	}
}