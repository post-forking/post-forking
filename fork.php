<?php
/*
Plugin Name: WordPress Post Forking
Description: Post forking
Author: Wired.com
Version: 0.1
Author URI: http://wired.com/
*/

/* WordPress Post Forking
 *
 * ( DESCRIPTION HERE )
 *
 * Copyright (C) 2012 Condé Nast
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright 2012
 * @license GPL v3
 * @version 0.1
 * @package post_forking
 * @author Benjamin J. Balter <ben@balter.com>
 */

require_once dirname( __FILE__ ) . '/includes/capabilities.php';
require_once dirname( __FILE__ ) . '/includes/options.php';
require_once dirname( __FILE__ ) . '/includes/admin.php';
require_once dirname( __FILE__ ) . '/includes/merge.php';
require_once dirname( __FILE__ ) . '/includes/revisions.php';

class Fork {

	public $post_type_support = 'fork'; //key to register when registerign post type support
	public $fields = array( 'post_title', 'post_content' ); //post fields to map to fork

	/**
	 * Register initial hooks with WordPress core
	 */
	function __construct() {
	
		$this->capabilities = new Fork_Capabilities( &$this );
		$this->options = new Fork_Options( &$this );

		add_action( 'init', array( &$this, 'register_cpt' ) );
		add_action( 'init', array( &$this, 'admin_init' ) );
		add_action( 'init', array( &$this, 'add_post_type_support'), 999  );
		add_action( 'init', array( &$this, 'l10n'), 5  );
				
	}
	
	/**
	 * Init i18n files
	 * Must be done early on init because they need to be in place when register_cpt is called
	 */
	function l10n() {
		load_plugin_textdomain( 'fork', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
	}
	
	/**
	 * Pseudo-lazy loading of back-end functionality
	 */
	function admin_init() { 
	
		if ( !is_admin() )
			return;

		$this->admin = new Fork_Admin( &$this );
		$this->revisions = new Fork_Revisions( &$this );
		$this->merge = new Fork_Merge( &$this );
	     
	}
	
	/**
	 * Register "fork" custom post type
	 */
	function register_cpt() {
	
	    $labels = array( 
	        'name'               => _x( 'Forks', 'fork' ),
	        'singular_name'      => _x( 'Fork', 'fork' ),
	        'add_new'            => _x( 'Add New', 'fork' ),
	        'add_new_item'       => _x( 'Add New Fork', 'fork' ),
	        'edit_item'          => _x( 'Edit Fork', 'fork' ),
	        'new_item'           => _x( 'New Fork', 'fork' ),
	        'view_item'          => _x( 'View Fork', 'fork' ),
	        'search_items'       => _x( 'Search Forks', 'fork' ),
	        'not_found'          => _x( 'No forks found', 'fork' ),
	        'not_found_in_trash' => _x( 'No forks found in Trash', 'fork' ),
	        'parent_item_colon'  => _x( 'Parent Fork:', 'fork' ),
	        'menu_name'          => _x( 'Forks', 'fork' ),
	    );
	
	    $args = array( 
	        'labels'              => $labels,
	        'hierarchical'        => true,
	        'supports'            => array( 'title', 'editor', 'author', 'revisions' ),
	        'public'              => true,
	        'show_ui'             => true,
	        'show_in_nav_menus'   => false,
	        'publicly_queryable'  => true,
	        'exclude_from_search' => true,
	        'has_archive'         => false,
	        'query_var'           => true,
	        'can_export'          => true,
	        'rewrite'             => true,
	        'capability_type'     => 'fork',
	    );
	
	    register_post_type( 'fork', $args );
	}
	
	/** 
	 * Load a template. MVC FTW!
	 * @param string $template the template to load, without extension (assumes .php). File should be in templates/ folder
	 * @param args array of args to be run through extract and passed to tempalate
	 */
	function template( $template, $args = array() ) {
		extract( $args );
		
		if ( !$template )
			return false;
			
		$path = "templates/{$template}.php";
 
		include $path;
		
	}
	
	/**
	 * Returns an array of post type => bool to indicate whether the post type(s) supports forking
	 * All post types will be included
	 * @return array an array of post types and their forkability
	 */
	function get_post_types() {
		
		$active_post_types = $this->options->post_types;
		$post_types = array();

		foreach ( $this->get_potential_post_types() as $pt )
			$post_types[ $pt->name ] = ( array_key_exists( $pt->name, (array) $active_post_types ) && $active_post_types[ $pt->name ] );

		return  $post_types;
		
	}
	
	/**
	 * Returns an array of post type objects for all registered post types other than fork
	 * @param return array array of post type objects
	 */
	function get_potential_post_types() {
		
		$post_types = get_post_types( array( 'show_ui' => true ), 'objects' );
		unset( $post_types['fork'] );
		return $post_types;
		
	}
	
	/** 
	 * Registers post_type_support for forking with all active post types on load
	 */
	function add_post_type_support() {
		
		foreach ( $this->get_post_types() as $post_type => $status )
			if ( $status == true )
				add_post_type_support( $post_type, $this->post_type_support );
			
	}
	
	/**
	 * Checks if a user has a fork for a given post, or optionally for any post
	 * @param int $parent_id the post_id of the parent post to check
	 * @param int|string the author to check
	 * @return int|bool the fork id or false if no fork exists
	 */
	function user_has_fork( $parent_id = null, $author = null ) {
		
		if ( is_int( $author ) )
			$author = get_user_by( 'ID', $author )->user_nicename;
		
		if ( $author == null )
			$author = wp_get_current_user()->nicename;
		
		$args = array( 
			'post_type' => 'fork',
			'post_author' => $author,
		);
		
		if ( $parent_id != null )
			$args[ 'post_parent' ] = (int) $parent_id;
			
		$posts = get_posts( $args );
		
		if ( empty( $posts ) )
			return false;
			
		return reset( $posts )->ID;
		
	}
	
	/**
	 * Returns an filterable list of fields to copy from original post to the fork
	 */
	function get_fork_fields() {
		
		return apply_filters( 'fork_fields', $this->fields );
		
	}
	
	/** 
	 * Main forking function
	 * @param int|object $p the post_id or post object to fork
	 * @param string the nicename of author to fork post as
	 * @return int the ID of the fork
	 */
	function fork( $p = null, $author = null ) {
		global $post;
		
		if ( $p == null )
			$p = $post;
			
		if ( !is_object( $p ) )
			$p = get_post( $p );
			
		if ( !$p )
			return false;
		
		if ( $author == null )
			$author = wp_get_current_user()->user_nicename;
			
		if ( is_int( $author ) )
			$author = get_user_by( 'ID', $author )->user_nicename;
		
		//user already has a fork, just return the existing ID
		if ( $fork = $this->user_has_fork( $p->ID, $author ) )
			return $fork;
		
		//set up base fork data array
		$fork = array( 
			'post_type' => 'fork',
			'post_author' => $author,
			'post_status' => 'draft',
			'post_parent' => $p->ID,
		);
		
		//copy necessary post fields over to fork data array
		foreach ( $this->get_fork_fields() as $field )
			$fork[ $field ] = $p->$field;

		$fork_id = wp_insert_post( $fork );
		
		//something went wrong
		if ( !$fork_id )
			return false;
		
		//store previous revision as post_meta
		update_post_meta( $fork_id, $this->previous_revision_key, $this->get_previous_post_revision( $p ) );
		
		return $fork_id;
		
	}
	
}

$fork = new Fork();

