<<<<<<< HEAD
<?php 
var_dump( $parent );
printf( __( 'Forked from <a href="%1$s">%2$s</a>' ), admin_url( "post.php?post={$post->post_parent}&action=edit" ), $this->get_parent_name( $post ) ); ?> <a href="<?php echo admin_url( "revision.php?action=diff&left={$parent}&right={$post->ID}" ); ?>"><?php _e( 'Compare', 'fork' ); ?>
=======
<?php printf( __( 'Forked from <a href="%1$s">%2$s</a>' ), admin_url( "post.php?post={$post->post_parent}&action=edit" ), $this->get_parent_name( $post ) ); ?> <a href="<?php echo admin_url( "revision.php?action=diff&left={$post->post_parent}&right={$post->ID}" ); ?>"><?php _e( 'Compare', 'fork' ); ?></a>
>>>>>>> 0cb8c6fbff6e3598642c7c0f99d3edfa458b7681
