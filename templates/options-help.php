<?php
/**
 * Content for the settings screen help
 * 
 * @package fork
 * @since   10/07/2013
 */
?>
<p><?php esc_attr_e( 'WordPress Post Forking introduces many of Git\'s well-established conventions to the WordPress world, and as a result, uses a unique vocabulary to describe what it does:', 'post-forking' ); ?></p>
<ul>
	<li><?php echo sprintf( esc_attr__( '%sPost%s - Any WordPress post that uses the post_content field, including posts, pages, and custom post types', 'post-forking' ), '<strong>', '</strong>' ); ?></li>
	<li><?php echo sprintf( esc_attr__( '%sFork%s - Clone of a post intended for editing without disturbing the parent post', 'post-forking' ), '<strong>', '</strong>' ); ?></li>
	<li><?php echo sprintf( esc_attr__( '%sBranch%s - Parallel versions of the same parent post, owned by the post author', 'post-forking' ), '<strong>', '</strong>' ); ?></li>
	<li><?php echo sprintf( esc_attr__( '%sMerge%s - To push a fork\'s changes back into its parent post', 'post-forking' ), '<strong>', '</strong>' ); ?></li>
	<li><?php echo sprintf( esc_attr__( '%sConflict%s - When a post is forked if a given line is changed on the fork, and that same line is subsequently edited on the parent post prior to the merge, the post cannot be automatically merged, and the conflict is presented to the merger to resolve', 'post-forking' ), '<strong>', '</strong>' ); ?></li>
</ul>