<?php
$msg = '';
if(isset($_REQUEST['msg']))
	switch ($_REQUEST['msg']) {
		case '1':
			$msg = __('Saved Successfully.');
			break;

		default:
			# code...
			break;
}?>
<div class="megajig-clear"></div>
<h2 class="megajig-page-title"><span><?php _e('Gallery Settings', 'megajig');?></span></h2>
<?php if(!empty($msg)):?>
	<div id="message" class="updated notice notice-success is-dismissible megajig-msg">
		<p><?php echo $msg;?></p>
		<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e('Dismiss this notice.', 'megajig');?></span>
		</button>
	</div>
<?php endif;?>
<form action="<?php menu_page_url( 'megajig' );?>&action=save" method="POST" id="megajig-form">
<input type="hidden" name="id" value="<?php echo $megajig_id ;?>" />
<input type="hidden" name="source" value="media" class="megajig-param"/>
<input type="hidden" name="items" value="<?php echo $gallery->items;?>" class="megajig-param megajig-items"/>
<input type="hidden" name="options" value='<?php echo $gallery->options;?>' id="megajig-options"/>
<input type="hidden" name="settings" value='<?php echo $gallery->settings;?>' id="megajig-settings"/>
<div class="megajig-settings">
	<ul class="megajig-nav">
		<li class="active">
			<a href="#sources"><?php _e('Sources', 'megajig');?></a>
		</li>
		<!--
		<li class="megajig-action">
			<a href="#cancel" class="megajig-btn-cancel">Cancel</a>
		</li>
	-->
		<li class="megajig-action">
			<a href="#save" class="megajig-btn-save"><?php _e('Save Changes', 'megajig');?></a>
		</li>
	</ul>
	<div class="megajig-tabs">
		<div class="megajig-tab-content postbox megajig-center active" id="sources">
			<div class="megajig-field">
				<label for="megajig_gallery_title">
					<?php _e('Gallery Title', 'megajig');?>
				</label>

				<input type="text" id="megajig_gallery_title" name="gallery_title" class="megajig-form-control megajig-param" value="<?php echo $gallery->title;?>"/>

			</div>
		</div>
	</div>
	<div class="megajig-clear"></div>
	<div id="megajig-preview" class="postbox">
		<div class="megajig-box-handle">
			<h3 class="msp-metabox-title"><?php _e('Preview', 'megajig');?></h3>
			<div id="megajig-screens">
				<span class="megajig-screens-status"><?php _e('Auto size', 'megajig');?></span>
				<a href="#" title="<?php _e('Auto size', 'megajig');?>" class="megajig-screen-size dashicons dashicons-controls-repeat active" data-screen="auto"></a>
				<a href="#" title="<?php _e('Desktop screen size', 'megajig');?>" class="megajig-screen-size dashicons dashicons-desktop" data-screen="1440"></a>
				<a href="#" title="<?php _e('Laptop screen size', 'megajig');?>" class="megajig-screen-size dashicons dashicons-laptop" data-screen="1280"></a>
				<a href="#" title="<?php _e('Tablet screen size', 'megajig');?>" class="megajig-screen-size dashicons dashicons-tablet" data-screen="768"></a>
				<a href="#" title="<?php _e('Smartphone screen size', 'megajig');?>" class="megajig-screen-size dashicons dashicons-smartphone" data-screen="479"></a>
				<a href="#" title="<?php _e('Expand Viewer', 'megajig');?>" class="dashicons dashicons-editor-expand megajig-expand-viewer"></a>
			</div>
		</div>
		<div class="megajig-wrapper-viewer">
			<a href="#" class="megajig_add_images megajig-btn"><?php _e('Select Images', 'megajig');?></a>
			<div id="megajig-viewer">
				<?php _e('No images found! Please add new ones.', 'megajig');?>
			</div>
		</div>

	</div>

</div>
</form>
<div class="megajig-wp-editor">
<textarea id="hidden-editor"></textarea>
<?php
require_once ABSPATH . "wp-includes/class-wp-editor.php";
wp_editor('', 'hidden-editor', array('editor_class'=>'hidden'));
//_WP_Editors::wp_link_dialog();
?>
</div>
<svg class="mj-greyscale" version="1.1" xmlns="http://www.w3.org/2000/svg">
	<filter id="mj-greyscale">
	<feColorMatrix type="matrix" values="0.3333 0.3333 0.3333 0 0
								  0.3333 0.3333 0.3333 0 0
								  0.3333 0.3333 0.3333 0 0
								  0      0      0      1 0"/>
	</filter>
</svg>
