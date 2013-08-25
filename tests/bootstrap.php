<?php
/**
 * Bootstrap the testing environment
 * Uses wordpress tests (http://github.com/nb/wordpress-tests/) which uses PHPUnit
 * @package wordpress-plugin-tests
 *
 * Usage: change the below array to any plugin(s) you want activated during the tests
 *        value should be the path to the plugin relative to /wp-content/
 *
 * Note: Do note change the name of this file. PHPUnit will automatically fire this file when run.
 *
 */
 
require getenv( 'WP_TESTS_DIR' ) . '/includes/functions.php';
 
function _manually_load_plugin() {

	require dirname( __FILE__ ) . '/wp_die_handler.php';
	require dirname( __FILE__ ) . '/../post-forking.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require getenv( 'WP_TESTS_DIR' ) . '/includes/bootstrap.php';
require dirname( __FILE__ ) . '/post_forking_test.php';
