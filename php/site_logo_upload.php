<?php
	if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>

<h2><strong>Site Logo</strong></h2>
<p style="margin-top: 5px; margin-bottom: 20px;">The site logo which will display on login page</p>
<div class="site_logo">
  <img style="max-height: 100px;" src="<?php echo esc_attr(get_option('opensocial_saml_site_logo'));?>">
</div>

<form method='post' action="">
  <div class='image-preview-wrapper'>
    <img id='image-preview' src='<?php echo wp_get_attachment_url( get_option( 'media_selector_attachment_id' ) ); ?>' style='max-height: 100px;'>
  </div>
	<?php wp_nonce_field('op_upload_logo', 'op_submit_logo' );?>
  <input id="upload_image_button" type="button" class="button" value="<?php _e( 'Select Logo' ); ?>" />
  <input type='hidden' name='logo_url' id='logo_url' value=''>
  <input type="submit" name="upload_site_logo" value="Save" class="button-primary">
</form><?php

add_action( 'admin_footer', 'op_media_selector_print_scripts' );

function op_media_selector_print_scripts() {
	$my_saved_attachment_post_id = get_option( 'media_selector_attachment_id', 0 );
	?><script type='text/javascript'>
		jQuery( document ).ready( function( $ ) {
			// Uploading files
			var file_frame;
			var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
			var set_to_post_id = <?php echo $my_saved_attachment_post_id; ?>; // Set this
			jQuery('#upload_image_button').on('click', function( event ){
				event.preventDefault();
				// If the media frame already exists, reopen it.
				if ( file_frame ) {
					// Set the post ID to what we want
					file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
					// Open frame
					file_frame.open();
					return;
				} else {
					// Set the wp.media post id so the uploader grabs the ID we want when initialised
					wp.media.model.settings.post.id = set_to_post_id;
				}
				// Create the media frame.
				file_frame = wp.media.frames.file_frame = wp.media({
					title: 'Select a image to upload',
					button: {
						text: 'Use this image',
					},
					multiple: false	// Set to true to allow multiple files to be selected
				});
				// When an image is selected, run a callback.
				file_frame.on( 'select', function() {
					// We set multiple to false so only get one image from the uploader
					attachment = file_frame.state().get('selection').first().toJSON();
					// Do something with attachment.id and/or attachment.url here
          $('.site_logo').html('');
					$( '#image-preview' ).attr( 'src', attachment.url ).css( 'width', 'auto' );
          $( '#logo_url' ).val( attachment.url );
					// Restore the main post ID
					wp.media.model.settings.post.id = wp_media_post_id;
				});
					// Finally, open the modal
					file_frame.open();
			});
			// Restore the main ID when the add media button is pressed
			jQuery( 'a.add_media' ).on( 'click', function() {
				wp.media.model.settings.post.id = wp_media_post_id;
			});
		});
	</script><?php
} ?>
