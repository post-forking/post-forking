<?php _e( 'Branch:', 'fork' ); ?> <select name="branches" id="branches" class="branches">
	<option id="original" value="<?php echo esc_attr( $post->ID ); ?>" class="original"><?php _e( 'Original', 'original' ); ?></option>
<?php foreach ( $branches as $branch ) { ?>
	<option id="<?php echo esc_attr( $branch->post_name ); ?>" value="<?php echo esc_attr( $branch->ID ); ?>" <?php selected( $post->ID, $branch->ID ); ?>><?php echo $branch->post_title; ?></option>
<?php } ?>
</select>