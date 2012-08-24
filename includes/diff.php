<?php
/**
 * Class to modify rendering of text-diffs on revision.php page
 * @todo the table of revisions
 */
class Fork_Diff {

	/**
	 * Hook into WordPress API on init
	 */
	function __construct( &$parent ) {
		
		$this->parent = &$parent;
		add_action( 'load-revision.php', array( $this, 'spoof_revision' ) );
		
	}

	/**
	 * /wp-admin/revision.php checks that the thing we're comparing is a "revision"
	 * When comparing a fork to a post, prime the object cache with a modified version of the fork
	 * so that revision.php grabs the post from cache, it thinks it's a revision
	 *
	 * Note: this only fires on revision.php, per the hook
	 *
	 */
	function spoof_revision() {

		$post = (int) $_GET['right'];
		$post = get_post( $post );
		
		if ( !get_post_type( $post )  == 'fork' )
			return;
	
		$post->post_type = 'revision';
		wp_cache_set( $post->ID, $post, 'posts' );
		wp_cache_set( 'spoofed_revision', $post, 'fork' );
		
		add_action( 'shutdown', array( $this, 'unspoof_revision' ) );
		
	}
	
	/**
	 * If we spoofed a revision on the start of revision.php, unspoof the revision, in case
	 * the site has persistent cache, unspoof the post prior to shutdown, so that the cache is 
	 * acurate on subsequent page loads.
	 *
	 * Note: this only fires when spoof_revision() was fired on a page
	 *
	 */
	function unspoof_revision() {
		
		$post = wp_cache_get( 'spoofed_revision', 'fork' );
		
		if ( !$post || !is_object( $post ) )
			return;

		$post->post_type = 'fork';

		wp_cache_set( $post->ID, $post, 'posts' );
		wp_cache_delete( 'spoofed_revision', 'fork' ); //in case we have a persistent cache
		
	}
	
}

/**
 * We really don't want to get in the business of rewriting WP_Text_Diff_Renderer_table
 * It's hugely complex, well thought out, and works well.
 * Problem is however, it can only render two way diffs
 * What we're doing here is running our normaly three-way diff, then passing that to 
 * renderer as a two way diff. We get better results, without the heavy lift.
 * 
 * The process here is to replace the function wp_text_diff with our own function
 * essentially writing a filter into core that doesn't exist.
 * Our version of wp_text_diff checks to see if we're comparing a fork,
 * and if so, does the three way merge
 * otherwise, we're running wp_text_diff essentially unchanged.
 * We could do this as one function (and just look for $_GET, but passing
 * as an arg is a bit cleaner of a solution in terms of hooking into other plugins, etc.
 *
 * Note, unless we're on revision.php and action is diff, we're running core's version
 */
if ( !function_exists( 'wp_text_diff' ) ) :

	//verify page
	if ( stripos( $_SERVER['PHP_SELF'], 'revision.php' ) === false )
		return;
	
	//verify action
	if ( !isset( $_GET['action'] ) || $_GET['action'] != 'diff' )
		return;
	
	/**
	 * Replace function with check for post_type, pass an arg if so
	 */
	function wp_text_diff( $left, $right, $args = array() ) {
		
		//not a fork, transparently run a two-way post diff
		if ( !isset( $_GET['right'] ) || get_post_type( $_GET['right'] != 'fork' ) )
			return _wp_text_diff( $left, $right, $args );
			
		$args['fork'] = isset( $_GET['right'] ) ? $_GET['right'] : false;
		
		return _wp_text_Diff( $left, $right, $args );
		
	}
	
	/**
	 * Normal wp_text_diff with three lines added as indicated
	 * @access private
	 * @version 3.4
	 */
	function _wp_text_diff( $left_string, $right_string, $args = null ) { 
	
		//fork edit: added parent arg
		$defaults = array( 'title' => '', 'title_left' => '', 'title_right' => '', 'fork' => false );
		$args = wp_parse_args( $args, $defaults );

		if ( !class_exists( 'WP_Text_Diff_Renderer_Table' ) )
			require( ABSPATH . WPINC . '/wp-diff.php' );
		
		//begin fork edit
		global $fork;
		if ( $args['fork'] )
			$right_lines = $fork->merge->get_merged( $args['fork'] );
		//end edit
	
		$left_string  = normalize_whitespace($left_string);
		$right_string = normalize_whitespace($right_string);
	
		$left_lines  = explode("\n", $left_string);
		$right_lines = explode("\n", $right_string);

		$text_diff = new Text_Diff($left_lines, $right_lines);
		
		$renderer  = new WP_Text_Diff_Renderer_Table();
		$diff = $renderer->render( $text_diff );
	
		if ( !$diff )
			return '';
	
		$r  = "<table class='diff'>\n";
		$r .= "<col class='ltype' /><col class='content' /><col class='ltype' /><col class='content' />";
	
		if ( $args['title'] || $args['title_left'] || $args['title_right'] )
			$r .= "<thead>";
		if ( $args['title'] )
			$r .= "<tr class='diff-title'><th colspan='4'>$args[title]</th></tr>\n";
		if ( $args['title_left'] || $args['title_right'] ) {
			$r .= "<tr class='diff-sub-title'>\n";
			$r .= "\t<td></td><th>$args[title_left]</th>\n";
			$r .= "\t<td></td><th>$args[title_right]</th>\n";
			$r .= "</tr>\n";
		}
		if ( $args['title'] || $args['title_left'] || $args['title_right'] )
			$r .= "</thead>\n";
	
		$r .= "<tbody>\n$diff\n</tbody>\n";
		$r .= "</table>";
	
		return $r;
	}

endif;