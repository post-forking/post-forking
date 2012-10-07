<?php

class Post_Forking_Die_Handler {
    
    public $is_wp_die = false;  
    static $instance;
    
    /**
     * Hook into core API
     */
    function __construct() {
        
        self::$instance = &$this;
  		add_filter( 'wp_die_handler', array( $this, 'get_die_handler' ) );

    }      

    /** 
     * Callback to return our die handler
	 * @return array the handler
	 */
	function get_die_handler() {
		return array( &$this, 'die_handler' );
	}


	/**
	 * Handles wp_die without actually dieing
	 * @param sting msg the die msg
	 */
	function die_handler( $msg ) {

		$this->is_wp_die = true;

		echo $msg;

	}


	/**
	 * Whether we wp_die'd this test
	 * @return bool true/false
	 */
	function died() {
		
		return $this->is_wp_die;
		
	}
	
	/**
	 * Reset state to undead
	 */
	function reset() {
    	
    	$this->is_wp_die = false;
    	
	}
	
	

}