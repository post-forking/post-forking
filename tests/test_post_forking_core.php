<?php

class WP_Test_Post_Forking_Merge extends WP_UnitTestCase {

	 function test_plugin_activated() {
		
		$this->assertTrue( class_exists( 'Fork' ) );
		 
	 }

}