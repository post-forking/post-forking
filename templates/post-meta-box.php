<?php if ( $fork = $this->user_has_fork( $post->ID ) ) { ?>
	<a href="<?php echo admin_url( "post.php?post=$fork&action=edit" ); ?>"><?php _e( 'View Fork', 'fork' ); ?></a>
<?php } else { ?>
	<a href="<?php echo admin_url( "?fork={$post->ID}" ); ?>" class="button button-primary"><?php _e( 'Fork', 'fork' ); ?></a>
<?php }