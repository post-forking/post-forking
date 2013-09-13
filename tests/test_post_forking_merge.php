<?php

class WP_Test_Post_Forking_Merge extends Post_Forking_Test {

	public $orig = "1\n2\n3\n4";
	public $fork = "1\n<strong>foo</strong>\nasdf\n3\n4\n5\n6";
	public $latest = "1\nbar\n3\n<em>thing</em>\n4asdf\ntest\<strong>foo</strong>\n\n5";
	public $core = null;
	
	function __construct() {
		
		//force into admin to allow merge class to load
		if ( !defined( 'WP_ADMIN' ) )
			define( 'WP_ADMIN', true );
	
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
	function test_blank_line_merge() {
		$content =  $this->fork . "\n\nblank\n\nlines";

		$instance = $this->get_instance();
		$fork = get_post( $this->create_fork() );
		
		$fork_arr = array( 'ID' => $fork->ID, 'post_content' => $content );
		wp_update_post( $fork_arr );
		
		//load admin classes and add caps
		$instance->action_init();
		$instance->capabilities->add_caps();
		
		//merge does a current user check, so set us as the author of the post
		wp_set_current_user( $fork->post_author );
		
		$instance->merge->merge( $fork->ID );
		$post = get_post( $fork->post_parent );
		
		$this->assertEquals( $post->post_content, $content );


	}
	
	function test_is_conflicted() {
	
		//create fork
		$instance = $this->get_instance();
		$instance->action_init();
		$fork = get_post( $this->create_fork() );

		//sanity check
		// $this->assertFalse( $instance->merge->is_conflicted( $fork->ID ) );

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
