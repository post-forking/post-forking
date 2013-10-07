<?php
/**
 * Interface for the contextual help on settingspage
 * 
 * @package fork
 * @since   10/07/2013
 */

class Fork_Options_Help {
	
	public $parent;
	public $key = 'fork';
	public $current_screen = 'fork_page_fork_settings';
	public $tabs = array();
	
	/**
	 * Hooks
	 * Define different tabs and his content and his templates
	 * 
	 * @since  10/07/2013
	 * @return void
	 */
	public function __construct( &$parent ) {

		$this->parent = &$parent;
		
		// define the tabs on the settings page
		$this->tabs = array(
			'overview' => array(
				'id'      => 'fork_settings_help_overview',
				'title'   => __( 'Overview', 'post-forking' ),
				'content' => $this->get_content()
			),
			'post-types' => array(
				'id'    => 'fork_settings_help_post_types',
				'title' => __( 'Post Types', 'post-forking' ),
				'content' => $this->get_content( 'post-types' )
			)
		);
		
		add_filter( 'contextual_help', array( $this, 'show' ), 10, 3 );
	}
	
	/**
	 * Create tabs and add this to the contextual help on settings page
	 * 
	 * @since  10/07/2013
	 * @return void
	 */
	public function show( $contextual_help, $screen_id, $screen ) {
		
		// check for the right page
		if ( $this->current_screen !== $screen_id )
			return;
		
		foreach ( $this->tabs as $id => $data ) {
			
			$screen->add_help_tab( array(
				'id'      => $id,
				'title'   => $data['title'],
				'content' => $data['content']
			) );
			
		}
		
		// add additional informations in sidebar of help
		$screen->set_help_sidebar(
			$this->get_content( 'sidebar' )
		);
	}
	
	/**
	 * Include template files, parse content and return this
	 * 
	 * @since  10/07/2013
	 * @return void
	 */
	public function get_content( $template = FALSE ) {
		
		// add string for different templates for the help tabs
		if ( ! empty( $template ) )
			$template = '-' . esc_attr( $template );
		
		ob_start();
		// enhance the fucntion to for different templates on the helping screen
		$this->parent->template( 'options-help' . $template );
		$content = ob_get_contents();
		ob_end_clean();
		
		return $content;
	}
	
} // end class
