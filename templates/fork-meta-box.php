<div id="fork-info">
	<p>
		<?php printf( __( 'Forked from <a href="%1$s">%2$s</a>', 'post-forking' ), admin_url( "post.php?post={$post->post_parent}&action=edit" ), $this->get_parent_name( $post ) ); ?> <a href="<?php echo admin_url( "revision.php?page=fork-diff&right={$post->ID}" ); ?>"><span class="fork-compare button"><?php _e( 'Compare', 'post-forking' ); ?></span></a>
	</p>
	<div class="clear"></div>
</div>
<div id="major-publishing-actions">

	<?php

	// Check if fork is approved
	$approved = get_post_meta( $post->ID, '_post_fork_approved', true );

	// Check if approvals are required
	$options = get_option( 'fork' );

	if( $options['require_approval'] = 1 ) {

		if( $approved == 1 ) {
			$checked = 'checked="checked"';
		} else {
			$checked = '';
		}

		if ( current_user_can('approve_forks') ) {

			echo '<div><label for="_post_fork_approved"><input type="checkbox" name="_post_fork_approved" id="_post_fork_approved" value="1" '. $checked  .'> Approved</label><br><br></div>';

		}

	}
	?>
	<div id="delete-action">
		<?php submit_button( __( 'Save Fork', 'post-forking' ), 'button button-large', 'save', false ); ?>
	</div>

	<div id="publishing-action">
		<img src="<?php echo admin_url( '/images/wpspin_light.gif' ); ?>" class="ajax-loading" id="ajax-loading" alt="" style="visibility: hidden; ">
		<input name="original_publish" type="hidden" id="original_publish" value="Publish">
		<?php

		if ( current_user_can('publish_forks') ) {

			if( ( $approved == 1 ) || !isset( $options['require_approval'] ) ) {
				submit_button( __( 'Merge &amp; Publish', 'post-forking' ), 'primary', 'publish', false );
			} else {
				submit_button( __( 'Pending Approval', 'post-forking' ), 'primary', 'save', false, array( 'disabled' => 'disabled') );
			}

		} else {
			submit_button( __( 'Submit for Review', 'post-forking' ), 'primary', 'publish', false );
		}

		?>
	</div>

	<div class="clear"></div>

</div>

