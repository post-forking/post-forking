<?php

class Fork_Options {

	public $parent;
	public $key = 'fork';
	
	function __construct( &$parent ) {

		$this->parent = &$parent;
		add_action( 'admin_menu', array( &$this, 'register_menu' ) );
		add_action( 'admin_init', array( &$this, 'register_settings' ) );
		
	}
	
	function __get( $key ) {
		
		$options = get_option( $this->key );
		
		if ( !$options )
			return false;
			
		if ( !isset( $options[ $key ] ) )
			return false;
			
		return $options[ $key ];
		
	}
	
	function __set( $key, $value ) {
		
		$options = $this->get();
		$options[ $key ] = $value;
		$this->set( $options );
		return $options;
		
	}
	
	function get() {
		
		return get_option( $this->key );
		
	}
	
	function set( $options, $merge = true ) {
	
		if ( $merge )
			$options = array_merge( $options, $this->get() );
			
		return update_option( $this->key, $options );
		
	}
	
	function register_menu() {
		add_submenu_page( 'edit.php?post_type=fork', 'Fork Settings', 'Fork Settings', 'manage_options', 'fork_settings', array( &$this, 'options' ) );
		
	}
	
	function register_settings() {

		register_setting( 'fork', 'fork', array( &$this, 'sanitize' ) );
		
	}
	
	function options() {
		
		$this->parent->template( 'options' );
		
	}
	
	function sanitize( $options ) {

		foreach ( $options['post_types'] as &$post_type )
			$post_type = ( $post_type == 'on' );
		
		return $options;		
	
	}
	
}