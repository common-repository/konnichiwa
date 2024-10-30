<div class="wrap">
	<h1><?php _e('Manage Subscription Plans', 'konnichiwa')?></h1>
	
	<p><a href="admin.php?page=konnichiwa_plans&action=add"><?php _e('Click here to create a new plan', 'konnichiwa')?></a></p>
	
	<?php if(sizeof($plans)):?>
		<table class="widefat">
			<tr><th><?php _e('Plan name', 'konnichiwa')?></th><th><?php _e('Subscribe Shortcode', 'konnichiwa')?></th>
			<th><?php _e('Protect Shortcode', 'konnichiwa')?></th><th><?php _e('Price', 'konnichiwa')?></th><th><?php _e('Duration', 'konnichiwa')?></th>
			<th><?php _e('Edit / delete', 'konnichiwa')?></th></tr>
			<?php foreach($plans as $plan):
				$class = ("alternate" == @$class) ? '' : 'alternate';?>
				<tr class="<?php echo $class?>"><td><?php echo $plan->name?></td>
				<td><input type="text" value="[konnichiwa-subscribe <?php echo $plan->id?>]" readonly="true" onclick="this.select()"></td>
				<td><input type="text" value="[konnichiwa-protect plans='<?php echo $plan->id?>']" readonly="true" onclick="this.select()" size="25"></td>
				<td><?php echo KONN_CURRENCY.' '.$plan->price?></td>
				<td><?php echo $plan->duration.' '.$plan->duration_unit?></td>
				<td><a href="admin.php?page=konnichiwa_plans&action=edit&id=<?php echo $plan->id?>"><?php _e('Edit', 'konnichiwa')?></a>
				| <a href="#" onclick="konnichiwaConfirmDelete(<?php echo $plan->id?>);return false;"><?php _e('Delete', 'konnichiwa')?></a></td></tr>
			<?php endforeach;?>
		</table>
	<?php endif;?>
	
	<?php include(KONN_PATH . '/views/shortcodes-help.html.php');?>
	
	
</div>

<script type="text/javascript" >
function konnichiwaConfirmDelete(id) {
	if(confirm("<?php _e('Are you sure?', 'konnichiwa')?>")) {
		window.location = 'admin.php?page=konnichiwa_plans&del=1&id=' + id;
	}
}
</script>