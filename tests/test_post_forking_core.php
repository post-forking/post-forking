<?php

class WP_Test_Post_Forking_Core extends Post_Forking_Test {
	static $instance;

	function __construct() {
		self::$instance = &$this;		
		$this->get_instance()->action_init();
	}
	
	 function test_plugin_activated() {
		
		$this->assertTrue( class_exists( 'Fork' ) );
		 
	 }
	 
	 function test_cpt_registration() {
		 
		 $this->assertTrue( post_type_exists( 'fork' ) );
		 
	 }
	 
	 function test_get_post_types() {
	 
		 $fork = $this->get_instance();
		 $pts = $fork->get_post_types();
 
		 //out of box, should return post => true, page => false pre 3.5
		 // and includes attachment => false from 3.5 on
		 $expected = ( get_bloginfo( 'version' ) < '3.5' ) ? 2 : 3;
		 $this->assertCount( $expected, $pts );
		 $this->assertTrue( $pts['post'] );
		 $this->assertFalse( $pts['page'] );
	     	
	 }
	 
	 function test_fork() {

		$fork = $this->get_instance(); 
		$f = $this->create_fork();
		$this->assertEquals( 'fork', get_post_type( $f ) );
		 
	 }
	 
	 function test_user_has_fork() {
	 
		$fork = $this->get_instance();
		$f = $this->create_fork();
		$f = get_post( $fork );
		
		//does this user have any fork?
		$this->assertGreaterThan( 0, $fork->user_has_fork( null, $f->post_author ) );
		
		//does this user have a fork of the parent post?
		$this->assertGreaterThan( 0, $fork->user_has_fork( $f->post_parent, $f->post_author ) );

		//does the current user have a fork?
		wp_set_current_user( $f->post_author );
		$this->assertGreaterThan( 0, $fork->user_has_fork() );

	 }
	 
	 function test_duplicate_fork() {
		
		$fork = $this->get_instance();
		$f = $this->create_fork();
		$f = get_post( $f );
		$fork_id = $fork->fork( $f->post_parent, $f->post_author );
		$this->assertEquals( $f->ID, $fork_id );
		
	 }
	
}