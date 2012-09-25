 <?php

class WP_Test_Post_Forking_Core extends WP_UnitTestCase {
	static $instance;

	function __construct() {
		self::$instance = &$this;		
		$this->get_instance()->action_init();
	}

	 function get_instance() {
		 global $fork;		 
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

	 function test_plugin_activated() {
		
		$this->assertTrue( class_exists( 'Fork' ) );
		 
	 }
	 
	 function test_cpt_registration() {
		 
		 $this->assertTrue( post_type_exists( 'fork' ) );
		 
	 }
	 
	 function test_get_post_types() {
	 
		 $fork = $this->get_instance();
		 $pts = $fork->get_post_types();
 
		 //out of box, should return post => true, page => false, attachment => false
		 $this->assertCount( 3, $pts );
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