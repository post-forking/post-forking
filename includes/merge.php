<?php
/**
 * Main class for mering a fork back into its parent
 * @package fork
 */
 
class Fork_Merge {
	
	public $ttl = 1; //super-short TTL means we cache within page load, but don't ever hit persistant cache
	
	function __construct( &$parent ) {
		
		$this->parent = &$parent;
		
		//fire up the native WordPress diff engine
		$engine = extension_loaded( 'xdiff' ) ? 'xdiff' : 'native';
		require_once ABSPATH . WPINC . '/wp-diff.php';
        require_once ABSPATH . WPINC . '/Text/Diff/Engine/' . $engine . '.php';

		//init our three-way diff library which extends WordPress's native diff library
		if ( !class_exists( 'Text_Diff3' ) )
			require_once dirname( __FILE__ ) . '/diff3.php';	
	
	}
	
	/**
	 * Merges a fork's content back into its parent post
	 * @param int $fork_id the ID of the fork to merge
	 */
	function merge( $fork_id ) {
		
		$update = array( 
			'ID' => get_post( $fork_id )->post_parent,
			'post_content' => $this->get_merged( $fork_id ),
		);

		return wp_update_post( $update );
		
	}
	
	/**
	 * Returns the merged, possibly conflicted post_content
	 * @param int $fork_id the ID of the fork
	 * @return string the post content
	 * NOTE: may be conflicted. use `is_conflicted()` to check
	 */
	function get_merged( $fork_id ) {
		
		$diff = $this->get_diff( $fork_id );
		$merged = $diff->mergedOutput( __( 'Fork', 'fork' ), __( 'Current Version', 'fork' ) );
		return implode( "\n", $merged );
		
	}
		
	/**
	 * Determines if a given fork can merge cleanly into the current post
	 * @param int $fork_id the ID for the fork to merge
	 * @return bool true if conflicted, otherwise false
	 */
	function is_conflicted( $fork_id ) {
		
		$diff = $this->get_diff( $fork_id );
		
		foreach ( $diff->_lines as $line )
			if ( $line->isConflict() )
				return true;
		
		return false;
			
	}
	
	/**
	 * Performs a three-way merge with a fork, it's parent revision, and the current version of the post
	 * Caches so that multiple calls to get_diff within the same page load will not re-run the diff each time
	 */
	function get_diff( $fork_id ) {
		
		if ( 0 && $diff = wp_cache_get( $fork_id, 'Fork_Diff' ) )
			return $diff;
		
		//grab the three elments
		$fork = get_post( $fork_id );
		$parent = $this->parent->revisions->get_parent_revision( $fork_id );
		$current = $fork->post_parent;
				
		//normalize whitespace and convert string -> array
		foreach ( array( 'fork', 'parent', 'current' ) as $string ) {
			$$string = get_post( $$string )->post_content;
			$$string = normalize_whitespace( $$string );
			$$string = explode( "\n", $$string );
		}
		
		//diff, cache, return
		$diff = new Text_Diff3( $parent, $fork, $current );
		wp_cache_set( $fork_id, $diff, 'Fork_Diff', $this->ttl );
		return $diff;
	
	}		
}