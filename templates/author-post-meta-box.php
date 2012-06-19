<?php if ( $fork = $this->user_has_fork( $post->ID ) ) { ?>
	BRANCHES DROP DOWN
<?php } ?>
	<a href="<?php echo admin_url( "?fork={$post->ID}" ); ?>" class="button button-primary"><?php _e( 'Create new Branch', 'fork' ); ?></a>