<?php

class WP_Test_Post_Forking_Revisions extends WP_UnitTestCase {

	public $core;
	
	function __construct() {
		
		//force into admin to allow merge class to load
		if ( !defined( 'WP_ADMIN' ) )
			define( 'WP_ADMIN', true );
	
	}
	
	function &get_core() {
	
		if ( $this->core == null )
			$this->core = &WP_Test_Post_Forking_Core::$instance;
	
		return $this->core;
		
	}

	function get_instance() {
		return $this->get_core()->get_instance();
	}
	
	function create_branch() {
		return $this->get_core()->create_branch();
	}
	
	function create_fork( $revision = null ) {
		return $this->get_core()->create_fork( null, $revision );
	}

	function create_post() {
		return $this->get_core()->create_post();
	}
	
	function create_revision( $post = null ) {
		
		if ( $post == null )
			$post = $this->create_post();
		
		if ( !is_object( $post ) )
			$post = get_post( $post );
			
		return wp_update_post( array( 'ID' => $post->ID, 'post_content' => $post->post_content . "\n5" ) );
		
	}
	
	function test_store_previous_revision() {
	
		$instance = $this->get_instance();		
		$fork = $this->create_fork( false );
		
		//shouldn't be any because there isn't
		$meta = get_post_meta( $fork, $instance->revisions->previous_revision_key, true );
		$this->assertTrue( empty( $meta ) );
		
		$fork = $this->create_fork( true );

		//just check if it's there for now (e.g., the hook's good)
		//we'll check `get_previous_post_revision()` in a momment
		$this->assertNotEquals( false, get_post_meta( $fork, $instance->revisions->previous_revision_key, true ) );
	
	}
	
	function test_get_previous_post_revision() {
		
		$instance = $this->get_instance();
		$post = $this->create_post();
		$this->assertFalse( $instance->revisions->get_previous_post_revision( $post ) );
	
		$this->create_revision( $post );
		$this->assertEquals( reset( wp_get_post_revisions( $post ) )->ID, $instance->revisions->get_previous_post_revision( $post ) );
	
	}
	
	function test_get_previous_revision() {

		$instance = $this->get_instance();
		
		//start out with a softball
		$fork = $this->create_fork( true );
		$revs = wp_get_post_revisions( get_post( $fork )->post_parent );
		$this->assertEquals( reset( $revs )->ID, $instance->revisions->get_previous_revision( $fork ) );
		
		//best guess approach, should return parent post
		$fork = $this->create_fork( false );
		$this->assertEquals( get_post( $fork )->post_parent, $instance->revisions->get_previous_revision( $fork ) );	
		
	}
	
	function test_get_parent_revision() {
		
		$instance = $this->get_instance();
		$fork = $this->create_fork( false );
		$this->assertEquals( get_post( $fork )->post_parent, $instance->revisions->get_parent_revision( $fork ) );	

		$fork = $this->create_fork( true );
		$this->assertEquals( get_post( $fork )->post_parent, $instance->revisions->get_parent_revision( $fork ) );

	}

}