<?php

class WP_Test_Post_Forking_Branches extends Post_Forking_Test {
	
	public $core = null;
	
	function __construct() {

        $this->die_handler = new Post_Forking_Die_Handler();

		//force into admin to allow merge class to load
		if ( !defined( 'WP_ADMIN' ) )
			define( 'WP_ADMIN', true );
	
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
		$instance->action_init();
		$instance->capabilities->add_caps();
		$other_user = $this->create_user( 'author' );
		$this->assertTrue( $instance->branches->can_branch( $post->ID, $post->post_author ) );
		$this->assertFalse( $instance->branches->can_branch( $post->ID, $other_user ) );
		
	}
	
	function test_get_branches() {

		$instance = $this->get_instance();
		$branch = $this->create_branch();
		$this->assertCount( 1, $instance->branches->get_branches( get_post( $branch )->post_parent ) );
		
	}

}