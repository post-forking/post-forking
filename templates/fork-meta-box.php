<p>
<?php printf( __( 'Forked from <a href="%1$s">%2$s</a>' ), admin_url( "post.php?post={$post->post_parent}&action=edit" ), $this->get_parent_name( $post ) ); ?> <a href="<?php echo admin_url( "revision.php?action=diff&left={$post->post_parent}&right={$post->ID}" ); ?>"><?php _e( 'Compare', 'fork' ); ?></a>
</p>

<div id="major-publishing-actions">
<div id="delete-action">
<?php submit_button( __( 'Save Fork', 'fork' ), 'button button-large', 'save', false ); ?>
</div>

<div id="publishing-action">
<img src="<?php echo admin_url( '/images/wpspin_light.gif' ); ?>" class="ajax-loading" id="ajax-loading" alt="" style="visibility: hidden; ">
<input name="original_publish" type="hidden" id="original_publish" value="Publish">
<?php submit_button( __( 'Merge', 'fork' ), 'primary', 'publish', false ); ?>
</div>
<div class="clear"></div>
</div>