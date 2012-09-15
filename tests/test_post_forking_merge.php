<?php

class WP_Test_Post_Forking_Merge extends WP_UnitTestCase {

	public $orig = "1\n2\n3\n4";
	public $fork = "1\nfoo\n3\n4";
	public $latest = "1\nbar\n3\n4";
	public $core = null;
	
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
	
	function create_fork() {
		return $this->get_core()->create_fork();
	}

	function create_post() {
		return $this->get_core()->create_post();
	}
	
	function create_conflict( $fork ) {
		
		//assumes a standard post of 1,2,3,4 and an unedited fork
		
		if ( !is_object( $fork ) )
			$fork = get_post( $fork );
		
		//edit fork 
		$fork_arr = array( 'ID' => $fork->ID, 'post_content' => $this->fork );
		wp_update_post( $fork_arr );
		
		//edit parent to put in conflict
		$parent_arr = array( 'ID' => $fork->post_parent, 'post_content' => $this->latest );
		wp_update_post( $parent_arr );
		
		//flush object cache to ensure we have the right versions of everything
		wp_cache_flush();

	}
	
	function test_merge() {
	
		$instance = $this->get_instance();
		$fork = get_post( $this->create_fork() );
		
		$fork_arr = array( 'ID' => $fork->ID, 'post_content' => $this->fork );
		wp_update_post( $fork_arr );
		
		//load admin classes and add caps
		$instance->action_init();
		$instance->capabilities->add_caps();
		
		//merge does a current user check, so set us as the author of the post
		wp_set_current_user( $fork->post_author );
		
		$instance->merge->merge( $fork->ID );
		$post = get_post( $fork->post_parent );
		
		$this->assertEquals( $post->post_content, $this->fork );
		
	}
	
	function test_is_conflicted() {
	
		//create fork
		$instance = $this->get_instance();
		$instance->action_init();
		$fork = get_post( $this->create_fork() );

		//sanity check
		$this->assertFalse( $instance->merge->is_conflicted( $fork->ID ) );

		$this->create_conflict( $fork );
		
		//note: passing the post_id here to force `is_conflicted` to get the updated post for us
		$this->assertTrue( $instance->merge->is_conflicted( $fork->ID ) );
	
	}
	
	function test_has_conflict_markup() {
		
		//create fork
		$instance = $this->get_instance();
		$instance->action_init();
		$fork = get_post( $this->create_fork() );
		
		//sanity check
		$this->assertFalse( $instance->merge->has_conflict_markup( $fork->ID ) );
		
		//add conflict markup to fork
		$this->create_conflict( $fork );
		wp_update_post( array( 'ID' => $fork->ID, 'post_content' => $instance->merge->get_merged( $fork->ID ) ) );
		wp_cache_flush();
				
		$this->assertTrue( $instance->merge->has_conflict_markup( $fork->ID ) );	
		
	}
	
}