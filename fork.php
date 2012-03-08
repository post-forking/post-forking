<?php
/*
Plugin Name: Forking Proof of Concept
Description: Post forking
Author: Benjamin Balter
Version: 0.1
Author URI: http://ben.balter.com/
*/

class WP_Fork {

	function __construct() {
		add_action( 'init', array( &$this, 'register_cpt' ) );
	}
	
	function register_cpt() {
		
		$args = array( 
		'public'          => 'false',
		'labels'          => array(
				'name'          => __( 'Forks' ),
				'singular_name' => __( 'Fork' ),
			),
		'capability_type' => 'post',
		'map_meta_cap'    => true,
		'hierarchical'    => false,
		'rewrite'         => false,
		'query_var'       => false,
		'can_export'      => false,
		);
		
		register_post_type( 'fork', $args );
		
		//register with *all* taxonomies
		$taxonomies = get_taxonomies();
		foreach ( $taxonomies as $taxonomy )
			register_taxonomy_for_object_type( $taxonomy, 'fork' );
		
	}
	
	function fork( $post ) {
		
		if ( is_int( $post ) )
			$post = get_post( $post, ARRAY_A );
		
		if ( is_object( $post ) )
			$post = get_object_vars( $post );
		
		if ( !is_array( $post ) )
			return false;
		
		$fork = $this->get_fork_fields( $post );
		$fork_id = wp_insert_post( $fork );
		
		
		
		return $fork;
		
	}
	
	function get_fork_fields( $post = null ) {
	
		$fork = _wp_post_revision_fields( $post );
		
		if ( is_null( $post ) )
			return $fork;
					
		$fork['post_type'] = 'fork';
		$fork['post_status'] = $post['post_status'];
		$fork['post_name'] = $post['ID'] . '-fork';
	
		return $fork;
	}
	
	function fork_meta( $from_id, $to_id ) {
	
		if ( !get_post( $from_id ) || !get_post( $to_id ) )
			return;
			
		if ( !get_post_type( $to_id ) == 'fork' )
			return;
		
		$fields = get_post_custom( $from_id );
		
		if ( !$fields )
			return false;
			
		foreach ( $fields as $meta_key => $meta_value )
			 update_metadata( 'post', $to_id, $meta_key, $meta_value );
			 
		return $fields;
	
	}
	
	function fork_taxonomies( $from_id, $to_id ) {

		if ( !get_post( $from_id ) || !get_post( $to_id ) )
			return;
			
		if ( !get_post_type( $to_id ) == 'fork' )
			return;

		$taxonomies = get_taxonomies( array( 'post_type' => get_post_type( $from_id ) ) );
		
		if ( !$taxonomies )
			return;
			
		foreach ( $taxonomies as $taxonomy ) {
			
			$terms = wp_get_post_terms( $from_id, $taxonomy );
			
			if ( !$terms )
				continue;
				
			wp_set_post_terms( $to_id, $terms, $taxonomy );	
		
		}
		
		return true;
	
	}

}

$wp_fork = new WP_Fork();

