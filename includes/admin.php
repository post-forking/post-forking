<?php
/**
 * Forking administrative functions
 * @package fork
 */

class Fork_Admin {
	
	/**
	 * Hook into WordPress API on init
	 */
	function __construct( &$parent ) {
		
		$this->parent = &$parent;
		add_action( 'add_meta_boxes', array( &$this, 'add_meta_boxes' ) );
		add_action( 'admin_init', array( &$this, 'fork_callback' ) );
		add_action( 'admin_init', array( &$this, 'merge_callback' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue' ) );
		add_filter( 'post_row_actions', array( &$this, 'row_actions' ), 10, 2 );
		
	}
	
	/**
	 * Add metaboxes to post edit pages
	 */
	function add_meta_boxes() {
		global $post; 
		
		if ( $post->post_status == 'auto-draft' )
			return;
	
		foreach ( $this->parent->get_post_types() as $post_type => $status ) 
			add_meta_box( 'fork', 'Fork', array( &$this, 'post_meta_box' ), $post_type, 'side', 'high' );
			
		add_meta_box( 'fork', 'Fork', array( &$this, 'fork_meta_box' ), 'fork', 'side', 'high' );
	
	}
	
	/**
	 * Callback to listen for the primary fork action
	 */
	function fork_callback() {
	
		//@TODO CAP CHECK
		
		if ( !isset( $_GET['fork'] ) )
			return;
			
		$fork = $this->parent->fork( (int) $_GET['fork'] );
		
		if ( !$fork )
			return;
			
		wp_redirect( admin_url( "post.php?post=$fork&action=edit" ) );
		exit();
		
	}
	
	/**
	 * Callback to listen for the primary merge action
	 */
	function merge_callback() {
		
		if ( !isset( $_GET['merge'] ) )
			return;
			
		$this->parent->merge->merge( (int) $_GET['merge'] );
	
		exit();
		
	}
	
	/**
	 * Callback to render post meta box
	 */
	function post_meta_box( $post ) {
	
		$this->parent->branches->branches_dropwdown( $post );
	
		if ( $this->parent->branches->can_branch( $post ) )
			$this->parent->template( 'author-post-meta-box', compact( 'post' ) );
			
		else
			$this->parent->template( 'post-meta-box', compact( 'post' ) );

	}
	
	/**
	 * Callback to render fork meta box
	 */
	function fork_meta_box( $post ) {
		$this->parent->template( 'fork-meta-box', compact( 'post' ) );		
	}
	
	/**
	 * Registers update messages
	 * @param array $messages messages array
	 * @returns array messages array with fork messages
	 */
	function update_messages( $messages ) {
		global $post, $post_ID;

		$messages['fork'] = array(
			1 => __( 'Fork updated.', 'fork' ),
			2 => __( 'Custom field updated.', 'fork' ),
			3 => __( 'Custom field deleted.', 'fork' ),
			4 => __( 'Fork updated.', 'fork' ),
			5 => isset($_GET['revision']) ? sprintf( __( 'Fork restored to revision from %s', 'fork' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => __( 'Fork published. <a href="%s">Download Fork</a>', 'fork' ),
			7 => __( 'Fork saved.', 'fork' ),
			8 => __( 'Fork submitted.', 'fork' ),
			9 => __( 'Fork scheduled for:', 'fork' ),
			10 => __( 'Fork draft updated.', 'fork' ),
		);

		return $messages;
	}
	
	/**
	 * Enqueue javascript files on backend
	 */
	function enqueue() {
		
		$post_types = $this->parent->get_post_types( true );
		$post_types[] = 'fork';
		
		if ( !in_array( get_current_screen()->post_type, $post_types ) )
			return;
		
		$suffix = ( WP_DEBUG ) ? '.dev' : '';
		wp_enqueue_script( 'fork', plugins_url( "/js/admin{$suffix}.js", dirname( __FILE__ ) ), 'jquery', $this->parent->version, true );			
	
	}
	
	/**
	 * Add additional actions to the post row view
	 */
	function row_actions( $actions, $post ) {
		
		$label = ( $this->parent->branches->can_branch ( $post ) ) ? __( 'Create new branch', 'fork' ) : __( 'Fork', 'fork' );
		
		$actions[] = '<a href="' . admin_url( "?fork={$post->ID}" ) . '">' . $label . '</a>';
		
		return $actions;
		
	}
	

	
}