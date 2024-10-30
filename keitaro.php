<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Keitaro Tracker Integration
 * Plugin URI:        https://github.com/apliteni/keitaro-wordpress-plugin
 * Description:       This plugin integrates WP with Keitaro tracker.
 * Version:           0.8.8
 * Author:            Keitaro Team
 * Author URI:        https://github.com/apliteni
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       keitaro
 */

define( 'KEITARO_VERSION', file_get_contents(plugin_dir_path( __FILE__ ) . '/VERSION') );

if ( ! defined( 'WPINC' ) ) {
	die('ok');
}

function activate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-keitaro-activator.php';
	KEITARO_Activator::activate();
}

function deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-keitaro-deactivator.php';
	KEITARO_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_plugin_name' );
register_deactivation_hook( __FILE__, 'deactivate_plugin_name' );

require plugin_dir_path( __FILE__ ) . 'includes/class-keitaro.php';

function run_plugin_name() {
	$plugin = new Plugin_Keitaro();
	$plugin->run();
}
run_plugin_name();
