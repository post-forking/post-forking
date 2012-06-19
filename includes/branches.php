<?php
/**
 * Branching functionality
 *
 * Any fork owned by the author of a post is considered a branch. 
 *
 * @package fork
 */ 
 
class Fork_Branches {

	function __construct( &$parent ) {
		$this->parent = &$parent;
	}
	
	/**
	 * Check whether a given fork is a branch
	 */
	function is_branch( $fork ) {
		
		if ( !is_object( $fork ) )
			$fork = get_post( $fork );
		
		if ( !$fork )
			return false;
		
		$parent = get_post( $fork->post_parent );
		
		return ( $fork->post_author == $parent->post->author );
		
	} 
	
	/**
	 * Check whether a given user can branch a given post
	 */
	function can_branch( $p = null, $user = null ) {
	
		global $post;
		
		if ( $p == null )
			$p = $post;
		
		if ( $user == null )
			$user = wp_get_current_user();
			
		if ( is_integer( $user ) )
			$user = get_user_by( 'id', $user );
			
		if ( is_string( $user ) )
			$user = get_user_by( 'string', $user );

		if ( !$p || !$user )
			return false;
			
		return ( $p->post_author == $user->ID );
		
	}
	
	/**
	 * Get an array of branch objects for a given post
	 */
	function get_branches( $p = null , $args = array() ) {
		global $post;
		
		if ( $p == null )
			$p = $post;
			
		if ( !is_object( $p ) )
			$p = get_post( $p );
			
		if ( $p->post_type == 'fork' )
			$p = get_post( $p->post_parent );
		
		$args = array( 'post_author' => $p->post_author, 'post_parent' => $p->ID );
		
		return $this->parent->get_forks( $args );
		
	}
	
	/**
	 * Render branch dropdown
	 */
	function branches_dropwdown( $post ) {
		
		$branches = $this->get_branches( $post );
		$this->parent->template( 'branches-dropdown', compact( 'post', 'branches' ) );
		
	}
	
}