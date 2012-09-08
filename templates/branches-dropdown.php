<?php _e( 'Branch:', 'fork' ); ?> <select name="branches" id="branches" class="branches">
	<option id="original" value="<?php echo esc_attr( $post->ID ); ?>" class="original"><?php _e( 'Original', 'original' ); ?></option>
<?php foreach ( $branches as $branch ) { ?>
<<<<<<< HEAD
	<option id="<?php echo $branch->post_name; ?>" value="<?php echo $branch->ID; ?>" <?php selected( $post->ID, $branch->ID ); ?>><?php the_title( $branch->ID ); ?></option>
=======
	<option id="<?php echo esc_attr( $branch->post_name ); ?>" value="<?php echo esc_attr( $branch->ID ); ?>" <?php selected( $post->ID, $branch->ID); ?>><?php the_title( $branch->ID ); ?></option>
>>>>>>> 0cb8c6fbff6e3598642c7c0f99d3edfa458b7681
<?php } ?>
</select>