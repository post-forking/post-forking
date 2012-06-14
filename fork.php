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

require_once  dirname( __FILE__ ) . '/includes/capabilities.php';
require_once  dirname( __FILE__ ) . '/includes/options.php';
include dirname( __FILE__ ) . '/includes/admin.php';

class Fork {

	public $post_type_support = 'fork';
	
	function __construct() {
	
		$this->capabilities = new Fork_Capabilities( &$this );
	
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
	
	function admin_init() { 
	
		if ( !is_admin() )
			return;

		$this->admin = new Fork_Admin( &$this );		
		$this->options = new Fork_Options( &$this );
	
	}
	
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
	
	function template( $template, $args = array() ) {
		extract( $args );
		
		if ( !$template )
			return false;
			
		$path = "templates/{$template}.php";
 
		include $path;
		
	}
	
	function get_post_types() {
		
		$active_post_types = $this->options->post_types;
		$post_types = array();

		foreach ( $this->get_potential_post_types() as $pt )
			$post_types[ $pt->name ] = ( array_key_exists( $pt->name, (array) $active_post_types ) && $active_post_types[ $pt->name ] );

		return  $post_types;
		
	}
	
	function get_potential_post_types() {
		
		$post_types = get_post_types( array( 'show_ui' => true ), 'objects' );
		unset( $post_types['fork'] );
		return $post_types;
		
	}
	
	function add_post_type_support() {
		
		foreach ( $this->get_post_types() as $post_type => $status )
			if ( $status == true )
				add_post_type_support( $post_type, $this->post_type_support );
			
	}
	
}

$fork = new Fork();

