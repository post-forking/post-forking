<?php _e( 'Branch:', 'fork' ); ?> <select name="branches" id="branches" class="branches">
	<option id="original" value="<?php echo $post->ID; ?>" class="original"><?php _e( 'Original', 'original' ); ?></option>
<?php foreach ( $branches as $branch ) { ?>
	<option id="<?php echo $branch->post_name; ?>" value="<?php echo $branch->ID; ?>" <?php selected( $post->ID, $branch->ID ); ?>><?php the_title( $branch->ID ); ?></option>
<?php } ?>
</select>