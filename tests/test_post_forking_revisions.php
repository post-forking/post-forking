<?php

class WP_Test_Post_Forking_Revisions extends Post_Forking_Test {

	public $core;
	
	function __construct() {
		
		//force into admin to allow merge class to load
		if ( !defined( 'WP_ADMIN' ) )
			define( 'WP_ADMIN', true );
	
	}
		
	function test_store_previous_revision() {
	
		$instance = $this->get_instance();		
		$fork = $this->create_fork( false, false );
		
		// check the hook in revisions.php; As of 3.6, WP creates an initial revision on post save.
		$meta = get_post_meta( $fork, $instance->revisions->previous_revision_key, true );
		$revs = wp_get_post_revisions( get_post( $fork )->post_parent );
		$valid_revs = array_keys( $revs );

		$this->assertEquals( $meta, $valid_revs[0] );
		
		$fork = $this->create_fork( true );

		//just check if it's there for now (e.g., the hook's good)
		//we'll check `get_previous_post_revision()` in a momment
		$this->assertNotEquals( false, get_post_meta( $fork, $instance->revisions->previous_revision_key, true ) );
	
	}
	
	function test_get_previous_post_revision() {
		
		$instance = $this->get_instance();
		$post = $this->create_post();
	
		$this->create_revision( $post );
		$this->assertEquals( reset( wp_get_post_revisions( $post ) )->ID, $instance->revisions->get_previous_post_revision( $post ) );
	
	}
	
	function test_get_previous_revision() {

		$instance = $this->get_instance();
		
		//start out with a softball
		$fork = $this->create_fork( true );
		$revs = wp_get_post_revisions( get_post( $fork )->post_parent );
		$this->assertEquals( reset( $revs )->ID, $instance->revisions->get_previous_revision( $fork ) );
		
	}
	
	function test_get_parent_revision() {
		
		$instance = $this->get_instance();
		$fork = $this->create_fork( false );
		// WP will create a revision after it creates the post, with an ID of one higher.
		$this->assertEquals( get_post( $fork )->post_parent + 1, $instance->revisions->get_parent_revision( $fork ) );

		$fork = $this->create_fork( true );
		// WP will create a revision after it creates the post, with an ID of one higher.
		$this->assertEquals( get_post( $fork )->post_parent + 1, $instance->revisions->get_parent_revision( $fork ) );

	}

}