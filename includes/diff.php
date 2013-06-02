<?php
/**
 * Class to provide diff/interactive merge tool.
 * Uses mergely.js for visual diff/merge.
 * @todo the table of revisions
 */
class Fork_Diff {

	/**
	 * @var WP_Post
	 * Left post
	 */
	public $left;

	/**
	 * @var WP_Post
	 * Right post
	 */
	public $right;

	/**
	 * @var boolean
	 * Can the current user use mergely as a merge tool, or just to view diff?
	 */
	public $user_can_merge;

	/**
	 * Hook into WordPress API on init
	 */
	function __construct( &$parent ) {

		$this->parent = &$parent;
		add_action( 'admin_menu', array( $this, 'register_diff_page' ) );
		add_action( 'admin_init', array( $this, 'diff_admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_mergely' ) );
	}

	/**
	 * Add a submenu callback on revision.php (doens't appear in sidebar menu)
	 */
	function register_diff_page() {
		add_submenu_page(
			'revision.php',
			__( 'Compare a Fork to its parent', 'post-forking' ),
			'',
			'edit_forks',
			'fork-diff',
			array( $this, 'render_diff_page' )
		);
	}

	/**
	 * Add mergely.js scripts and styles
	 */
	function enqueue_mergely() {
		if ( 'fork-diff' == $_GET['page'] ) {
			wp_enqueue_script( 'codemirror', plugins_url( "/js/mergely-3.3.1/lib/codemirror.min.js", dirname( __FILE__ ) ), 'jquery', $this->parent->version, true );
			wp_enqueue_script( 'mergely', plugins_url( "/js/mergely-3.3.1/lib/mergely.min.js", dirname( __FILE__ ) ), 'codemirror', $this->parent->version, true );
			wp_enqueue_style( 'codemirror_style', plugins_url( "/js/mergely-3.3.1/lib/codemirror.css", dirname( __FILE__ ) ), Null, $this->parent->version, 'all' );
			wp_enqueue_style( 'mergely_style', plugins_url( "/js/mergely-3.3.1/lib/mergely.css", dirname( __FILE__ ) ), Null, $this->parent->version, 'all' );
		}
	}

	/**
	 * Render the template for the diff page
	 */
	function render_diff_page() {
		$this->parent->template( 'diff' );
	}

	/**
	 * Load up the left and right posts and perform some validation
	 */
	function diff_admin_init() {
		if ( 'fork-diff' != $_GET['page'] ) {
			return;
		}
		$fork_id = (int) $_GET['right'];
		$this->right = get_post( $fork_id );
		if ( !get_post_type( $this->right )  == 'fork' )
			wp_die( __( 'Invalid type for right side; must be a fork.', 'post-forking' ) );
		
		if ( !empty( $_GET['left'] ) ) {
			$this->left = get_post( intval( $_GET['left'] ) );
			if ( $this->left->ID != $this->right->post_parent && $this->left->post_parent != $this->right->post_parent ) {
				wp_die( __( 'Invalid type for left side; must be a parent or sibling of fork.', 'post-forking' ) );
			}
		} else {
			$this->left = get_post( $this->right->post_parent );
		}
		$this->user_can_merge = current_user_can( 'publish_fork', $this->left->ID );
	}

}