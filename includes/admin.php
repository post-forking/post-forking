<?php

class Fork_Admin {
	
	function __construct( &$parent ) {
		
		$this->parent = &$parent;
		add_action( 'add_meta_boxes', array( &$this, 'add_meta_boxes' ) );
		add_action( 'admin_init', array( &$this, 'fork_callback' ) );
		add_action( 'admin_init', array( &$this, 'merge_callback' ) );
		add_action( 'transition_post_status', array( &$this, 'intercept_publish' ), 10, 3 );
		add_action( 'admin_init', array( &$this, 'test' ) );

	}
	
	function test() {
		
		include 'test.php';
		//exit();
		
	}
	
	function add_meta_boxes() {
	
		foreach ( $this->parent->get_post_types() as $post_type => $status ) 
			add_meta_box( 'fork', 'Fork', array( &$this, 'fork_meta_box' ), $post_type, 'side', 'high' );		
	
	}
	
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
	
	function merge_callback() {
		
		if ( !isset( $_GET['merge'] ) )
			return;
			
		$this->parent->merge->merge( (int) $_GET['merge'] );
	
		exit();
		
	}
	
	function fork_meta_box( $post ) {
	
	if ( $fork = $this->parent->user_has_fork( $post->ID ) ) { ?>
		<a href="<?php echo admin_url( "post.php?post=$fork&action=edit" ); ?>">View Fork</a>
	<?php } else { ?>
		<a href="<?php echo admin_url( "?fork={$post->ID}" ); ?>" class="button button-primary">Fork</a>
	<?php }
	
	}
	
	function intercept_publish( $old, $new, $post ) {
		
		if ( $post->post_type != 'fork' )
			return;
			
		if ( $new != 'publish' )
			return;
		
		
		//do something here to merge or something
		
	}
	
	
}