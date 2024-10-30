<div class="wrap">
	<h1><?php _e('Manage Protected Files', 'konnichiwa')?></h1>
	
	<p><a href="admin.php?page=konnichiwa_files&do=add"><?php _e('Upload new protected file', 'konnichiwa')?></a></p>
	
	<?php if(count($files)):?>
		<p><?php _e('Copy the download URL for each file and create links to it inside your contents.', 'konnichiwa')?></p>
		<table class="widefat">
			<tr><th><?php _e('File name', 'konnichiwa')?></th><th><?php _e('File type', 'konnichiwa')?></th>
			<th><?php _e('File size (KB)', 'konnichiwa')?></th><th><?php _e('Restriction', 'konnichiwa')?></th>
			<th><?php _e('Downloads', 'konnichiwa')?></th><th><?php _e('Edit / delete', 'konnichiwa')?></th></tr>
			<?php foreach($files as $file):
				$class = ('alternate' == @$class) ? '' : 'alternate';?>
				<tr class="<?php echo $class?>"><td><?php echo $file->filename?></td>
				<td><?php echo $file->filetype?></td><td><?php echo $file->filesize?></td>
				<td><?php echo $file->protection?></td><td><?php echo $file->downloads?></td>
				<td><a href="admin.php?page=konnichiwa_files&do=edit&id=<?php echo $file->id?>"><?php _e('Edit', 'konnichiwa')?></a>
				| <a href="#" onclick="KonnichiwaDeleteFile(<?php echo $file->id?>);return false;"><?php _e('Delete', 'konnichiwa')?></a></td></tr>
				<tr class="<?php echo $class?>"><td colspan="6">
				<?php _e('Download URL:', 'konnichiwa')?> <input type="text" size="60" readonly="readonly" onclick="this.select()" value="<?php echo site_url('?konnichiwa_file=1&id='.$file->id)?>"></td></tr>
			<?php endforeach;?> 
		</table>
	<?php endif;?>
</div>

<script type="text/javascript" >
function KonnichiwaDeleteFile(id) {
	if(confirm("<?php _e('Are you sure?', 'konnichiwa')?>")) {
		window.location = 'admin.php?page=konnichiwa_files&del=1&id=' + id;
	}
}
</script>