<?php
class KonnichiwaContent {
	function update_protection($content_type, $cat_id=0) {
		global $wpdb;
		$cat_id = intval($cat_id);
		$catpendix = empty($cat_id) ? '' : '_'.$cat_id;		

		// prepare $protection_details (registered or a string of plans)
		if($_POST['select_protection_type_'.$content_type.$catpendix] == 'registered') {
			$protection_details = 'registered';
		}						
		else $protection_details = "|".@implode('|', $_POST[$content_type.$catpendix.'_plans'])."|";
		
		// record already exists?
		$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".KONN_CONTENT." WHERE content_type=%s AND content_category=%d", $content_type, $cat_id));
	
		if($exists) {
			$wpdb->query($wpdb->prepare("UPDATE ".KONN_CONTENT." SET protection_type = %s WHERE id=%d", $protection_details, $exists));
		} 
		else {
			$wpdb->query($wpdb->prepare("INSERT INTO ".KONN_CONTENT." SET content_type=%s, content_category=%d, protection_type=%s",
				$content_type, $cat_id, $protection_details));
		}		
		
		// delete unnecessary records - if record of content type is for category, this means we don't need the global record
		// if the record is global, we need no category records
		if($cat_id) {
			$wpdb->query($wpdb->prepare("DELETE FROM ".KONN_CONTENT." WHERE content_type=%s AND content_category=0", $content_type));
		}
		else {
			$wpdb->query($wpdb->prepare("DELETE FROM ".KONN_CONTENT." WHERE content_type=%s AND content_category != 0", $content_type));
		}				
	} // end update protection
	
	// retrieves the information about the access to the current content
	function get_access($post, $inherit_access = null) {
		global $wpdb;
		if(empty($post)) return true;
		
		// when not passed, get it		
		if(!$inherit_access) $inherit_access = get_post_meta($post->ID, 'konnichiwa_inherit_access', true);
		
		// when meta is available, it has priority
		if($inherit_access == 'no') {			
			// get and return individual access
			$access = get_post_meta($post->ID, 'konnichiwa_access', true);
			return $access;
		}
		
		// otherwise let's check global access settings: 
		// find how/if is this content type access restricted - none, global or by category
		$accesses = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".KONN_CONTENT." WHERE 
		   content_type=%s ORDER BY content_category", $post->post_type));
		
		// not restricted at all?
		if(empty($accesses)) return null;   
		
		// ok, let's check by first access
		if($accesses[0]->content_category) {
			// the content is restricted by categories
			$cats = wp_get_post_categories($post->ID);
			$accesscode = "";
			
			// now we'll combine a string from all the accesses to get the most relaxed access. If a category, that the post belongs to,
			// is not found in the protected info, we consider the post accessible. If "registered" is found, return registered
			// otherwise we'll return a string like |3|5||5|4|1||1| etc so we'll be able to use it for easy checks.			
			foreach($cats as $cat) {
				$cat_found = false;
				foreach($accesses as $access) {
					if($access->content_category == $cat) {
						$cat_found = true;						
						// even one "registered" means "registered" is the only rule
						if($access->protection_type == 'registered') return 'registered';
						
						// didn't return? OK, let's add to $accesscode
						$accesscode .= $access->protection_type;
					}
				} // end foreach access rule for this content type
				
				// even one not-found category means the content is not resrticted at all
				if(!$cat_found) return null;
			} // end foreach cats

			// if we didn't return the function to this point, means we've collected some access rules. Let's return them
			return $accesscode;			
			
		}   // end when post type is restricted by category
		else return $accesses[0]->protection_type; // the content is registered globally
	}
	
}