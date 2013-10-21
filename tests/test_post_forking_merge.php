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

	function merge_test_with_authors($fork_author, $merger, $post_author = false, $merge_should_happen = true , $msg = false ) {

		$instance = $this->get_instance();

		if ($post_author === false)
			$post_author = $fork_author;


		$fork = get_post( $this->create_fork( false, true, $post_author, $fork_author ) );
		
		$fork_arr = array( 'ID' => $fork->ID, 'post_content' => $this->fork , 'post_author' => $fork_author  );
		wp_update_post( $fork_arr );
		
		//load admin classes and add caps
		$instance->action_init();
		$instance->capabilities->add_caps();
		
		//merge does a current user check
		wp_set_current_user( $merger );
		
		$instance->merge->merge( $fork->ID );
		$post = get_post( $fork->post_parent );
		
		if ($merge_should_happen)
			$this->assertEquals( $post->post_content, $this->fork );
		else if ($msg)
			$this->assertDied(null, $msg);	
		else
			$this->assertFalse( $post->post_content == $this->fork );
		
		return array(
			'fork' => $fork,
			'post' => $post,
		);

	}

	/**
	 * @group merge
	 */
	
	function test_merge() {
		$admin = $this->create_user();  
		$this->merge_test_with_authors( $admin , $admin);
	}
	/**
	 * @group merge
	 */
	
	function test_admin_merging_author_post () {
		$admin = $this->create_user();  
		$author = $this->create_user('author');
		$this->merge_test_with_authors( $author , $admin);
	}
	/**
	 * @group merge
	 */

	function test_author_merging_admin_post () {
		$this->die_handler = new Post_Forking_Die_Handler();
		$admin = $this->create_user();  
		$author = $this->create_user('author');
		$this->merge_test_with_authors(  $admin , $author , false, 'You are not authorized to merge forks' );
	}
	/**
	 * @group merge
	 */

	function test_one_author_merging_another_authors_post() {
		$this->die_handler = new Post_Forking_Die_Handler();
		$author1 = $this->create_user('author');
		$author2 = $this->create_user('author');
		$this->merge_test_with_authors(  $author1 , $author2 , false, 'You are not authorized to merge forks' );
	}
	/**
	 * @group merge
	 */

	function test_editor_merging_authors_post() {
		$author = $this->create_user('author');
		$editor = $this->create_user('editor');
		$this->merge_test_with_authors( $author , $editor);
	}

	/**
	 * @group merge
	 */

	function test_author_merging_fork_by_another_author_of_original_authors_post() {
		$author = $this->create_user('author');
		$author2 = $this->create_user('author');
		$this->merge_test_with_authors( $author2, $author, $author );
	}

	/**
	 * @group merge
	 */
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

	function test_merge_draft_does_not_publish_post() {
		$admin = $this->create_user();  
		$testPosts = $this->merge_test_with_authors( $admin , $admin);
		$this->assertEquals( get_post($testPosts['post'])->post_status, 'draft');
	}
	
}
