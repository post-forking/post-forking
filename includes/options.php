<?php
/**
 * Interface for accessing, storing, and editing plugin options
 * @package fork
 */
 
class Fork_Options {

	public $parent;
	public $key = 'fork';
	
	/**
	 * Hooks
	 */
	function __construct( &$parent ) {

		$this->parent = &$parent;
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		
	}
	
	/**
	 * Magic method to allow easy getting of options
	 */
	function __get( $key ) {
		
		$options = get_option( $this->key );
		
		if ( !$options )
			return false;
			
		if ( !isset( $options[ $key ] ) )
			return false;
			
		return $options[ $key ];
		
	}
	
	/**
	 * Magic method to allow setting of options
	 */
	function __set( $key, $value ) {
		
		$options = $this->get();
		$options[ $key ] = $value;
		$this->set( $options );
		return $options;
		
	}
	
	/**
	 * Get all options
	 */
	function get() {
		
		return get_option( $this->key );
		
	}
	
	/**
	 * Set all options
	 */
	function set( $options, $merge = true ) {
	
		if ( $merge )
			$options = array_merge( $options, $this->get() );
			
		return update_option( $this->key, $options );
		
	}
	
	/**
	 * Register Settings menu
	 */
	function register_menu() {
		add_submenu_page( 'edit.php?post_type=fork', 'Fork Settings', 'Settings', 'manage_options', 'fork_settings', array( $this, 'options' ) );
		
	}
	
	/**
	 * Hook into settings API
	 */
	function register_settings() {

		register_setting( 'fork', 'fork', array( $this, 'sanitize' ) );
		
	}
	
	/**
	 * Callback to render options page
	 */
	function options() {
		
		$this->parent->template( 'options' );
		
	}
	
	/**
	 * Sanitize options on save
	 */
	function sanitize( $options ) {

		foreach ( $options['post_types'] as &$post_type )
			$post_type = ( $post_type == 'on' );
		
		return $options;		
	
	}
	
}