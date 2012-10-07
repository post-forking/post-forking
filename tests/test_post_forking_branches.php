<?php

require_once dirname( __FILE__ ) . '/wp_die_handler.php';

class WP_Test_Post_Forking_Branches extends WP_UnitTestCase {
	
	public $core = null;
	
	function __construct() {

        $this->die_handler = new Post_Forking_Die_Handler();

		//force into admin to allow merge class to load
		if ( !defined( 'WP_ADMIN' ) )
			define( 'WP_ADMIN', true );
	
	}
	
	function assertDied( $null, $msg =  null ) {
    	
    	if ( $msg == null )
    	   $msg = 'Did not properly trip `wp_die()`';
    	   
    	$this->assertTrue( $this->die_handler->died(), $msg );
    	
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
		$other_user = $this->get_core()->create_user( 'author' );
		$this->assertTrue( $instance->branches->can_branch( $post->ID, $post->post_author ) );
		$this->assertFalse( $instance->branches->can_branch( $post->ID, $other_user ) );
		
	}
	
	function test_get_branches() {

		$instance = $this->get_instance();
		$branch = $this->create_branch();
		$this->assertCount( 1, $instance->branches->get_branches( get_post( $branch )->post_parent ) );
		
	}

}