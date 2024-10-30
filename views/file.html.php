<div class="wrap">
	<h1><?php _e('Add / Edit Protected File', 'konnichiwa')?></h1>
	
	<p><a href="admin.php?page=konnichiwa_files"><?php _e('Back to all protected files', 'konnichiwa')?></a></p>
	
	<form method="post" enctype="multipart/form-data" onsubmit="return KonnichiwaValidate(this);">
		<div class="postbox wp-admin" style="padding:10px;">
			<p><label><?php _e('Upload file:', 'konnichiwa')?></label> <input type="file" name="file"></p>
			
			<?php if(!empty($file->id)):?>
				<p><?php printf(__('Currently uploaded file: <a href="%s">%s</a> (%d KB %s)', 'konnichiwa'), site_url("?konnichiwa_file=1&id=".$file->id."&nocount=1"), $file->filename, $file->filesize, $file->filetype)?></p>
			<?php endif;?>		
		
			<p><label><?php _e('Select protection type:', 'konnichiwa')?></label> 
				<select name="select_protection_type" onchange="KonnichiwaSelectProtectionType(this.value)">
					<option value="registered" <?php if(!empty($file) and $file->protection_type == 'registered') echo 'selected'?>><?php _e('All registered users', 'konnichiwa')?></option>
					<option value="plans" <?php if(!empty($file) and $file->protection_type != 'registered') echo 'selected'?>><?php _e('Users subscribed to the selected plans', 'konnichiwa')?></option>
				</select></p>
				
			<div id="konnichiwaPlans" style='display:<?php echo (empty($file) or $file->protection_type == 'registered') ? 'none' : 'block';?>'>			
				<p><label><?php _e('Select subscription plans', 'konnichiwa')?></label>
					<?php foreach($plans as $plan):?>
						<input type="checkbox" name="plans[]" value="<?php echo $plan->id?>" <?php if(!empty($file) and strstr($file->protection_type, '|'.$plan->id.'|')) echo 'checked'?>> <?php echo stripslashes($plan->name)?> &nbsp;
					<?php endforeach;?>				
				</p>			
			</div>				
			
			<p><input type="submit" value="<?php _e('Save Protected File', 'konnichiwa')?>" class="button-primary"></p>
			<input type="hidden" name="ok" value="1">
		</div>
	</form>
</div>	

<script type="text/javascript" >
function KonnichiwaValidate(frm) {
	<?php if(empty($file->id)):?>
	if(frm.file.value == '') {
		alert("<?php _e('Please upload file', 'konnichiwa')?>");
		frm.file.focus();
		return false;
	}
	<?php endif;?>
	return true;
}

function KonnichiwaSelectProtectionType(val) {
	if(val == 'registered') jQuery('#konnichiwaPlans').hide();
	else jQuery('#konnichiwaPlans').hide();
}
</script>