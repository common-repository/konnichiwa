<?php
// subscription plans
class KonnichiwaPlans {
	static function manage() {
 		$action = empty($_GET['action']) ? 'list' : $_GET['action']; 
		switch($action) {
			case 'add':
				self :: add_plan();
			break;
			case 'edit': 
				self :: edit_plan();
			break;
			case 'list':
			default:
				self :: list_plans();	 
			break;
		}
	} // end manage()
	
	static function add_plan() {
		global $wpdb;
		$_plan = new KonnichiwaPlan();
		$woo_products = KonnichiwaWoo :: get_products();
		
		if(!empty($_POST['ok'])) {
			try {
				$pid = $_plan->add($_POST);			
				konnichiwa_redirect("admin.php?page=konnichiwa_plans");
			}
			catch(Exception $e) {
				$error = $e->getMessage();
			}
		}
		
		include(KONN_PATH.'/views/plan.html.php');
	} // end add_question
	
	static function edit_plan() {
		global $wpdb;
		$_plan = new KonnichiwaPlan();
		$woo_products = KonnichiwaWoo :: get_products();
		
		if(!empty($_POST['ok'])) {
			try {
				$_plan->save($_POST, intval($_GET['id']));			
				konnichiwa_redirect("admin.php?page=konnichiwa_plans");
			}
			catch(Exception $e) {
				$error = $e->getMessage();
			}
		}
		
		// select this plan
		$plan = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".KONN_PLANS." WHERE id=%d", intval($_GET['id'])));
		include(KONN_PATH.'/views/plan.html.php');
	} // end edit_plan
	
	// list and delete questions
	static function list_plans() {
		global $wpdb;
		$_plan = new KonnichiwaPlan();
		
		if(!empty($_GET['del'])) {
			$_plan->delete($_GET['id']);			
		}
		
		$plans = $wpdb->get_results("SELECT * FROM ".KONN_PLANS." ORDER BY id");
		include(KONN_PATH."/views/plans.html.php");
	} // end list_plans	
	
	// if there are plans that the user should be automatically subscribed to, subscribe.
	// this hook is called upon user registration on user_register WP action hook
	static function auto_subscribe($user_id) {
		global $wpdb;
		$_sub = new KonnichiwaSub();
		
		// select plans		
		$plans = $wpdb->get_results("SELECT id FROM ".KONN_PLANS." WHERE subscribe_on_signup=1 ORDER BY id");
		
		foreach($plans as $plan) {
			$_sub->add($user_id, $plan->id, 1, 0);		
		}
		
	} // end auto_subscribe
}