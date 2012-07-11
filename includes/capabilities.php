<?php
/**
 * Register default roles and capabilities
 */
 
class Fork_Capabilities {

	public $defaults = array( 
		'administrator' => array( 
			'edit_fork'              => true,
			'edit_others_forks'      => true,
			'edit_private_forks'     => true,
			'edit_published_forks'   => false,
			'read_fork'              => true,
			'read_private_forks'     => true,
			'delete_fork'            => true,
			'delete_others_forks'    => true,
			'delete_private_forks'   => true,
			'delete_published_forks' => true,
			'publish_forks'          => true,
			'fork_post'              => true,
			'branch_post'            => true,

		),
		'subscriber' => array( 
			'edit_fork'              => true,
			'edit_others_forks'      => false,
			'edit_private_forks'     => false,
			'edit_published_forks'   => false,
			'read_fork'              => true,
			'read_private_forks'     => false,
			'delete_fork'            => true,
			'delete_others_forks'    => false,
			'delete_private_forks'   => false,
			'delete_published_forks' => false,
			'publish_forks'          => false,
			'fork_post'              => true,
		),
	);

	/**
	 * Register with WordPress API
	 */
	function __construct() {

		add_action( 'init', array( &$this, 'add_caps' ) );

	}


	/**
	 * Adds plugin-specific caps to all roles so that 3rd party plugins can manage them
	 */
	function add_caps() {

		global $wp_roles;
		if ( ! isset( $wp_roles ) )
			$wp_roles = new WP_Roles;

		foreach (  $wp_roles->role_names as $role=>$label ) {

			//if the role is a standard role, map the default caps, otherwise, map as a subscriber
			$caps = ( array_key_exists( $role, $this->defaults ) ) ? $this->defaults[$role] : $this->defaults['subscriber'];

			//loop and assign
			foreach ( $caps as $cap=>$grant ) {

				//check to see if the user already has this capability, if so, don't re-add as that would override grant
				if ( !isset( $wp_roles->roles[$role]['capabilities'][$cap] ) )
					$wp_roles->add_cap( $role, $cap, $grant );

			}
		}

	}


}