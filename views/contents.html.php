<div class="wrap">
	<h1><?php _e('Manage Content Restrictions','konnichiwa')?></h1>
	
	<p><?php _e('On this page you can restrict the access to various types of content and elaborate futher by category. You can apply the same restrictions to individual posts and pages from the respective add/edit post/page screens. By default all content is accessible to everyone.', 'konnichiwa')?></p>
	
	
	<form method="post">
		<h2><?php _e('Restrict access to:', 'konnichiwa')?></h2>
		
		<?php foreach($content_types as $content_type):?>
		<div class="konnichiwa-wrap">
			 <p><input type="checkbox" name="content_types[]" value="<?php echo $content_type?>" onclick="this.checked ? jQuery('#konnContentSettings<?php echo $content_type?>').show() : jQuery('#konnContentSettings<?php echo $content_type?>').hide();" <?php if(in_array($content_type, $protected_vals)) echo 'checked'?>> <?php echo $content_type?></p>
			 <div class="wrap" id="konnContentSettings<?php echo $content_type?>" style="display:<?php echo in_array($content_type, $protected_vals)? 'block' : 'none'?>;">
			 	 <p><input type="radio" name="content_<?php echo $content_type?>_restriction" value="global" checked onclick="jQuery('#konnContentSettings<?php echo $content_type?>Global').show(); jQuery('#konnContentSettings<?php echo $content_type?>ByCat').hide();" <?php if(!empty($protected_vals[$content_type.'-0'])) echo 'checked'?>> <?php _e('Restrict in global', 'konnichiwa')?>
			 	 &nbsp; <input type="radio" name="content_<?php echo $content_type?>_restriction" value="category" onclick="jQuery('#konnContentSettings<?php echo $content_type?>Global').hide(); jQuery('#konnContentSettings<?php echo $content_type?>ByCat').show();" <?php if(in_array($content_type, $protected_vals) and empty($protected_vals[$content_type.'-0'])) echo 'checked'?>> <?php _e('Restrict by category', 'konnichiwa')?></p>
			 	 
			 	 <div class="konnichiwa-wrap" id="konnContentSettings<?php echo $content_type?>Global" style='display:<?php echo (!in_array($content_type, $protected_vals) or !empty($protected_vals[$content_type.'-0'])) ? 'block' : 'none'?>'>
			 	 	<?php $catpendix = ''; 
					$catcheck = '-0'; // used to check the saved vals
			 	 	include(KONN_PATH."/views/content-restrictions-partial.html.php");?>
			 	 </div>	
			 	 
			 	 <div class="konnichiwa-wrap" id="konnContentSettings<?php echo $content_type?>ByCat" style='display:<?php echo (in_array($content_type, $protected_vals) and empty($protected_vals[$content_type.'-0'])) ? 'block' : 'none'?>;'>
			 	 	<?php foreach($categories as $cat):?>
			 	 		<h3><?php printf(__('Category: %s', 'konnichiwa'), $cat->cat_name)?></h3>
			 	 		<div class="konnichiwa-wrap"><?php $catpendix = '_'.$cat->cat_ID; 
			 	 		$catcheck = '-'.$cat->cat_ID; // used to check the saved vals
			 	 		include(KONN_PATH."/views/content-restrictions-partial.html.php");?></div>
			 	 	<?php endforeach;?>
			 	 </div>
			 </div>		
		</div>
		<?php endforeach;?>
		
		<p><?php _e('Note: access settings configured on individual content pages will not be overwritten and always have priority over the global settings configured on this page.', 'konnichiwa')?></p>
		
		<p><input type="submit" value="<?php _e('Save Access Settings', 'konnichiwa')?>" class="button-primary"></p>
		<input type="hidden" name="ok" value="1">
		<?php wp_nonce_field('konnichiwa_contents');?>
	</form>
</div>

<script type="text/javascript" >
function konnSelectProtectionType(cType, val, catpendix) {
	if(val == 'registered') {
		jQuery('#konnContentSettings' + cType + 'ByPlan' + catpendix).hide();	
	}
	else jQuery('#konnContentSettings' + cType + 'ByPlan' + catpendix).show();
}
</script>