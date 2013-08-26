<?php
/**
 * Core functions used in tested. All tests extend this class
 * Creates objects for testing purposes
 *
 */

class Post_Forking_Test extends WP_UnitTestCase {
    
    static $instance;
    
    function __construct() {
        
        self::$instance = &$this;
        
    }
    
    function get_instance() {
		 global $fork;	 
		 if (is_null($fork) ){
			$fork = new Fork();
		 }	
		 return $fork;	 
	 }
	 
	 function create_post( $author = null, $post_type = 'post' ) {
		 
		 if ( $author == null )
		 	$author = $this->create_user();
		 	
		 $post = array(
		 	'post_title' => 'foo',
		 	'post_content' => "1\n2\n3\n4",
		 	'post_author' => $author,
		 	'post_type' => 'post',
		 	'post_date' => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
		 );
		 
		 return wp_insert_post( $post );
		 
	 }
	 
	 function create_user( $role = 'administrator', $user_login = '', $pass='', $email='' ) {
		 
		 $user = array(
		 	'role' => $role,
		 	'user_login' => ( $user_login ) ? $user_login : rand_str(),
		 	'user_pass' => ( $pass ) ? $pass: rand_str(),
		 	'user_email' => ( $email ) ? $email : rand_str() . '@example.com',
		 );
		 
		 $userID = wp_insert_user( $user );
		 
		 return $userID;
		 
	 }
	 
	 function create_fork( $branch = false, $revision = true  ) {
	 
	 	$fork = $this->get_instance();
	 	$post = $this->create_post();
	 	
	 	//make a revision to make finding parent revisions easier
	 	if ( $revision )
		 	wp_update_post( array( 'ID' => $post, 'post_name' => 'bar' ) );
			
	 	$post = get_post( $post ); 
	
	 	if ( $branch )
	 		$author = $post->post_author;
		else
			$author = $this->create_user();
					
		return $fork->fork( $post, $author );		 

	 }
	 
	 function create_branch() {
		 return $this->create_fork( true );
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
	
	function create_revision( $post = null ) {
		
		if ( $post == null )
			$post = $this->create_post();
		
		if ( !is_object( $post ) )
			$post = get_post( $post );
			
		return wp_update_post( array( 'ID' => $post->ID, 'post_content' => $post->post_content . "\n5" ) );
		
	}

	
	function assertDied( $null, $msg =  null ) {
    	
    	if ( $msg == null )
    	   $msg = 'Did not properly trip `wp_die()`';
    	   
    	$this->assertTrue( $this->die_handler->died(), $msg );
    	
	}
	
	function test_test() {
    	 
    	 $this->assertTrue( true );
    	 
	 }


}
