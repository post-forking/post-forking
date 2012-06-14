<?php

class Fork_Admin {
	
	function __construct( &$parent ) {
		
		$this->parent = &$parent;
		add_action( 'add_meta_boxes', array( &$this, 'add_meta_boxes' ) );
	}
	
	function add_meta_boxes() {
		foreach ( $this->parent->get_post_types() as $post_type => $status ) 
			add_meta_box( 'fork', 'Fork', array( &$this, 'fork_meta_box' ), $post_type, 'side', 'high' );		
	}
	
	function fork_meta_box( $post ) {
	
	?>FORK META BOX<?php
		
	}
	
	
}