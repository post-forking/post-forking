<?php

class WP_Test_Post_Forking_Branches extends WP_UnitTestCase {
	
	public $core = null;
	
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
	
	function test_is_branch() {
		
		$instance = $this->get_instance();
		$branch = $this->create_branch();
		$fork = $this->create_fork();
		$this->assertTrue( $instance->branches->is_branch( $branch ) );
		$this->assertFalse( $instance->branches->is_branch( $fork ) );

	}

	function test_can_branch() {
		
		$post = get_post( $this->create_post() );
		$instance = $this->get_instance();
		$other_user = $this->get_core()->create_user();
		$this->markTestIncomplete();
		$this->assertTrue( $instance->branches->can_branch( $post->ID, $post->post_author ) );
		$this->assertFalse( $instance->branches->can_branch( $post->ID, $other_user ) );
		
	}
	
	function test_get_branches() {

		$instance = $this->get_instance();
		$branch = $this->create_branch();
		$this->assertCount( 1, $instance->branches->get_branches( get_post( $branch )->post_parent ) );
		
	}

}