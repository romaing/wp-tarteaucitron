<?php
/*
Plugin Name: wp-tarteaucitron
Plugin URI: https://github.com/romaing/wp-tarteaucitron
Description: Cookie manager
Version: 1.0.4
Author: romain Gires
Author URI: http://romain.gires.net
Script original : https://opt-out.ferank.eu/


Text Domain: wp-tarteaucitron
Domain Path: languages
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define( 'WP_TAC_FILE'            	, __FILE__ );
define( 'WP_TAC_PATH'       		, realpath( plugin_dir_path( WP_TAC_FILE ) ) . '/' );
/**
 * Traductions
 */
function wp_tac_load_textdomain() {
    load_plugin_textdomain( 'wp_tac', false, WP_TAC_PATH . '/languages' ); 
}
add_action( 'plugins_loaded', 'wp_tac_load_textdomain' );

require(WP_TAC_PATH . '/admin.php');


/**
 * CSS et Javascript
 */
function wp_tac_user_css_js() {
	wp_register_style('wp_tac', plugins_url('wp-tarteaucitron/css/user.css'));
    wp_enqueue_style( 'wp_tac');
	wp_enqueue_script( 'wp_tac', plugins_url('wp-tarteaucitron/tarteaucitron/tarteaucitron.js'), '', '', TRUE );
}
add_action('wp_enqueue_scripts', 'wp_tac_user_css_js');
/**
 * CSS et Javascript
 */
function tac_admin_css() {
    wp_enqueue_script('switchButton', "http://olance.github.io/jQuery-switchButton/jquery.switchButton.js", '', '', TRUE );
	wp_register_style('tac', plugins_url('wp-tarteaucitron/css/admin.css'));
    wp_enqueue_style('tac');
    wp_enqueue_script('tac', plugins_url('wp-tarteaucitron/js/admin.js'));
}
add_action('admin_enqueue_scripts', 'tac_admin_css');



