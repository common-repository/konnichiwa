<?php
class KonnichiwaContents {
	static function manage() {
		global $wpdb;
		$_content = new KonnichiwaContent();
		
		// content types, for now hardcode only the default ones
		// $content_types = array("post", "page", "attachment");
		$content_types = get_post_types( array('public' => true), 'names' ); 
		
		// for select content categories regardless of content tyoe
		$args = array(
		  'orderby' => 'name',
		  'order' => 'ASC'
		  );
		$categories = get_categories($args);	
		
		if(!empty($_POST['ok']) and check_admin_referer('konnichiwa_contents')) {
			foreach($content_types as $content_type) {
				if(!empty($_POST['content_types']) and is_array($_POST['content_types']) and in_array($content_type, $_POST['content_types'])) {
					// restrict in global or by category?
					if($_POST['content_'.$content_type.'_restriction'] == 'global') {
						$_content->update_protection($content_type);
					} // end if global
					else { // by category
						foreach($categories as $cat) {
							$_content->update_protection($content_type, $cat->cat_ID);
						}
					} // end if by category 
				}
				else {
					// remove any restrictions for this content type
					$wpdb->query($wpdb->prepare("DELETE FROM ".KONN_CONTENT." WHERE content_type=%s", $content_type));
				} // end removing all restrictions
			}
		}
		
		// select subscription plans
		$plans = $wpdb->get_results("SELECT * FROM ".KONN_PLANS." ORDER BY name");

		// select all protected contents		
		$contents = $wpdb->get_results("SELECT * FROM ".KONN_CONTENT." ORDER BY id");
		
		// create a simple array of text-based composed values so we can easily check what is done in the views
		$protected_vals = array();
		foreach($contents as $content) {
			if(!in_array($content->content_type, $protected_vals)) $protected_vals[] = $content->content_type; // the basic val
			$protected_vals[$content->content_type.'-'.$content->content_category] = $content->protection_type; // now enter the details;
		}
		
		// print_r($protected_vals);
		
		include(KONN_PATH."/views/contents.html.php");
	}	// end manage()
	
	// adds the meta box with content access settings
	static function meta_box() {
		$content_types = get_post_types( array('public' => true), 'names' );
		foreach($content_types as $content_type) {
			add_meta_box("konnichiwa_access", __("Content access", 'konnichiwa'), 
							array(__CLASS__, "print_meta_box"), $content_type, 'side', 'high');		
		}											
	}
	
	// print the meta box
	static function print_meta_box($post) {
		global $wpdb;
		$_content = new KonnichiwaContent();
		
		// get current Konnichiwa access
		$inherit_access = get_post_meta($post->ID, 'konnichiwa_inherit_access', true);
		$access = $_content->get_access($post, $inherit_access);
				
		$plans = $wpdb->get_results("SELECT * FROM ".KONN_PLANS." ORDER BY name");
		include(KONN_PATH."/views/meta-box.html.php");
	}
	
	// save the meta data on any post or page
	static function save_meta($post_id) {
		global $wpdb;
		
		if(!empty($_POST['konnichiwa_inherit_access'])) {
			update_post_meta($post_id, 'konnichiwa_inherit_access', sanitize_text_field($_POST['konnichiwa_inherit_access']));
			
			if($_POST['konnichiwa_inherit_access'] == 'yes') delete_post_meta($post_id, 'konnichiwa_access');
			else {
				if($_POST['konnichiwa_access'] == 'none') delete_post_meta($post_id, 'konnichiwa_access');
				if($_POST['konnichiwa_access'] == 'registered') update_post_meta($post_id, 'konnichiwa_access', 'registered');
				if($_POST['konnichiwa_access'] == 'plans') {					 
					 update_post_meta($post_id, 'konnichiwa_access', '|'.@implode('|', $_POST['konnichiwa_plans']).'|');
				}
			} // end if not inherit
		}
	} // end save meta
	
	// check if the current user has access to this content
	static function access_filter($content) {
		global $post, $wpdb, $user_ID;
		$_content = new KonnichiwaContent();
		$access = $_content->get_access($post);
		
		if(!empty($access)) {
			// when access is restricted, we always require user registration
			if(!is_user_logged_in()) return __('This content is available only for registered users.', 'konnichiwa');
			
			// from this point further a registered user will always have access UNLESS the access is defined per subscription plans
			if($access !== 'registered') {
				$plans = explode("|", $access);
				$plans = array_filter($plans);
				if(empty($plans)) return $content;
				
				// get active user plans
				$subs = $wpdb->get_results($wpdb->prepare("SELECT plan_id FROM ".KONN_SUBS."
					WHERE user_id=%d AND expires >= CURDATE() AND status=1", $user_ID));
				 	
				foreach($subs as $sub) {
					// if even one is found we're all ok to return the content
					if(in_array($sub->plan_id, $plans)) return $content;
				}	
				
				// no plans found? return restricted text
				$plans = $wpdb->get_results("SELECT name FROM ".KONN_PLANS." WHERE id IN (".implode(",", $plans).")");
				$plan_names = array();
				foreach($plans as $plan) $plan_names[] = $plan->name;
				
				$content = sprintf(__('This content is available only for users with the following subscription plans: <b>%s</b>', 'konnichiwa'), 
					implode(", ", $plan_names)); 
			} // end if $access != 'registered'
		}		
		
		return $content;
	}
}