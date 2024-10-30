<?php
// manage protected files
class KonnichiwaFiles {
	static function manage() {
		global $wpdb;
		$do = empty($_GET['do']) ? 'list' : $_GET['do'];
		$plans = $wpdb->get_results("SELECT * FROM ".KONN_PLANS);
		
		switch($do) {
			case 'add':
				if(!empty($_POST['ok'])) {
					if($_POST['select_protection_type'] == 'registered') $protection_details = 'registered';											
					else $protection_details = "|".@implode('|', array_filter($_POST['plans'], 'is_int'))."|";
					
					$filename = $_FILES['file']['name'];
					$filesize = round($_FILES['file']['size'] / 1024);
					$filetype = $_FILES['file']['type'];
					$contents = file_get_contents($_FILES['file']['tmp_name']);

					$wpdb->query($wpdb->prepare("INSERT INTO ".KONN_FILES." SET
						filename=%s, filetype=%s, filesize=%d, filecontents=%s, protection_type=%s",
						$filename, $filetype, $filesize, $contents, $protection_details));
						
					konnichiwa_redirect("admin.php?page=konnichiwa_files");	
				}			
			
				include(KONN_PATH."/views/file.html.php");
			break;		
			
			case 'edit':
				if(!empty($_POST['ok'])) {
					if($_POST['select_protection_type'] == 'registered') $protection_details = 'registered';											
					else $protection_details = "|".@implode('|', array_filter($_POST['plans'], 'is_int'))."|";
					
					if(!empty($_FILES['file']['tmp_name'])) {
						$filename = $_FILES['file']['name'];
						$filesize = round($_FILES['file']['size'] / 1024);
						$filetype = $_FILES['file']['type'];
						$contents = file_get_contents($_FILES['file']['tmp_name']);
	
						$wpdb->query($wpdb->prepare("UPDATE ".KONN_FILES." SET
							filename=%s, filetype=%s, filesize=%d, filecontents=%s, protection_type=%s
							WHERE id=%d",
							$filename, $filetype, $filesize, $contents, $protection_details, intval($_GET['id'])));
					}
					else {
						// else update only protection details
						$wpdb->query($wpdb->prepare("UPDATE ".KONN_FILES." SET protection_type=%s 
							WHERE id=%d", $protection_details, $_GET['id']));
					}		
						
					konnichiwa_redirect("admin.php?page=konnichiwa_files");	
				}			
				
				$file = $wpdb->get_row($wpdb->prepare("SELECT id, filename, filetype, filesize, downloads, protection_type 
				FROM ".KONN_FILES." WHERE id=%d", intval($_GET['id'])));
				
				$content = $wpdb->get_var($wpdb->prepare("SELECT BINARY filecontents 
						FROM ".KONN_FILES." WHERE ID=%d", intval($_GET['id'])));	
			
				include(KONN_PATH."/views/file.html.php");
			break;				
			
			case 'list':
				if(!empty($_GET['del'])) {
					$wpdb->query($wpdb->prepare("DELETE FROM ".KONN_FILES." WHERE id=%d", intval($_GET['id'])));
					konnichiwa_redirect("admin.php?page=konnichiwa_files");
				}			
			
				$files = $wpdb->get_results("SELECT id, filename, filetype, filesize, downloads, protection_type 
				FROM ".KONN_FILES." ORDER BY filename");
				
				// foreach file create the "protection" text
				foreach($files as $cnt=>$file) {
					if($file->protection_type == 'registered') $files[$cnt]->protection = __('Registered users', 'konnichiwa');
					else {
						$plan_names = array();
						foreach($plans as $plan) {
							if(strstr($file->protection_type, '|'.$plan->id.'|')) $plan_names[] = $plan->name;
						}		
						$files[$cnt]->protection = sprintf(__('Subscription plans: %s', 'konnichiwa'), implode(", ", $plan_names));				
					}
				} // end foreach file
				
				include(KONN_PATH."/views/files.html.php");
			default;
		}
	} // end manage
	
	// download a file
	static function download() {		
		global $wpdb, $user_ID;
		
		// only do this when the URL contains watupro_download_file=$file_id
		if(empty($_GET['konnichiwa_file']) or empty($_GET['id']) or !is_numeric($_GET['id'])) return true;
		
		if(!is_user_logged_in()) wp_die(__('Only logged in users can download uploaded files.', 'konnichiwa'));
		
		// select the uploaded file
		$file = $wpdb->get_row($wpdb->prepare("SELECT id, filename, filetype, filesize, protection_type 
			FROM ".KONN_FILES." WHERE id=%d", $_GET['id']));
			
		if(empty($file->id)) wp_die(__('This file has been deleted.', 'konnichiwa'));	
		
		// check access	
		if($file->protection_type != 'registered' and (!current_user_can('manage_options') or empty($_GET['nocount']))) {
			$plans = explode("|", $file->protection_type);
			$plans = array_filter($plans);
			if(empty($plans)) wp_die(__('You have no access to this file', 'konnichiwa'));
			
			// get active user plans
			$subs = $wpdb->get_results($wpdb->prepare("SELECT plan_id FROM ".KONN_SUBS."
					WHERE user_id=%d AND expires >= CURDATE() AND status=1", $user_ID));
			
			$plans_found = false;	 	
			foreach($subs as $sub) {
				// if even one is found we're all ok to return the content
				if(in_array($sub->plan_id, $plans)) $plans_found = true;
			}	
			
			// no plans found? return restricted text
			if(!$plans_found) {
				$plans = array_map('intval', $plans);
				$plans = $wpdb->get_results("SELECT name FROM ".KONN_PLANS." WHERE id IN (".implode(",", $plans).")");
				$plan_names = array();
				foreach($plans as $plan) $plan_names[] = $plan->name;
				
				$msg = sprintf(__('This file is available only for users with the following subscription plans: <b>%s</b>', 'konnichiwa'), 
					implode(", ", $plan_names));
				wp_die($msg);	
			}		 
		}
		
		// all good, let's download
		$content = $wpdb->get_var($wpdb->prepare("SELECT BINARY filecontents 
			FROM ".KONN_FILES." WHERE id=%d", $file->id));	
			
		if(empty($_GET['nocount'])) {
			$wpdb->query($wpdb->prepare("UPDATE ".KONN_FILES." SET downloads = downloads + 1 WHERE id=%d", $file->id)); 
		}	
			
		header("Content-Length: ".strlen($content)); 
		header("Content-Description: File Transfer");
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"".$file->filename."\"");
		header("Content-Transfer-Encoding: binary");
		echo $content;
		exit;
	} // end download
	
}