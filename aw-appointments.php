<?php
/*
 * Plugin Name: AW Appointments
 * Author: Aswanth kumar
 * Text Domain: aw-appointments
 * Description: Plugin for taking appointments from visitors
 * Version: 0.3
 */
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}
define( 'AW_APPOINTMENT_VERSION', '0.2' );
define( 'AW_APPOINTMENT_MINIMUM_WP_VERSION', '3.1' );
define( 'AW_APPOINTMENT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AW_APPOINTMENT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AW_APPOINTMENT_DELETE_LIMIT', 100000 );

register_activation_hook( __FILE__, array( 'Appointments', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'Appointments', 'plugin_deactivation' ) );

require_once( AW_APPOINTMENT_PLUGIN_DIR . 'class.singleton.php' );
require_once( AW_APPOINTMENT_PLUGIN_DIR . 'class.appointments.php' );
add_action( 'init', array( 'appointments', 'init' ) );

if ( is_admin() ) {
	require_once( AW_APPOINTMENT_PLUGIN_DIR . 'class.appointments-admin.php' );
	add_action( 'init', array( 'appointmentadmin', 'init' ) );
}
?>
