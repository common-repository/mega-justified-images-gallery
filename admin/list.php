<?php
$msg = '';
if(isset($_REQUEST['msg']))
	switch ($_REQUEST['msg']) {
		case '1':
			$msg = __('Saved Successfully.', 'megajig');
			break;

		case '2':
			$msg = __('Gallery deleted.', 'megajig');
			break;

		default:
			# code...
			break;
}?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php _e('MegaJig Galleries', 'megajig');?></h1>
	<?php if(!empty($msg)):?>
		<div id="message" class="updated notice notice-success is-dismissible megajig-msg">
			<p><?php echo $msg;?></p>
			<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e('Dismiss this notice.', 'megajig');?></span>
			</button>
		</div>
	<?php endif;?>
	<form id="megajig-galleries" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php $galleries->display() ?>
	</form>
	<div class="footer-actions">
		<a class="megajig-list-action megajig-add" href="<?php echo menu_page_url( 'megajig',false ) . '&action=add';?>"><span class="dashicons dashicons-plus"></span> <?php _e('Create New Gallery', 'megajig');?></a>
	</div>
</div>

<script type="text/javascript">

jQuery(document).ready(function($){
	//list page
	$('.megajig-list-action[data-action=delete]').on('click', function (e){
		e.preventDefault();
		if (confirm("<?php _e('Are you sure want to remove image this gallery?', 'megajig');?>") == true) {
			window.location.href = $(this).attr('href');
		}
	});
});
</script>
