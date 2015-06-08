<?php
class Appointmentadmin{
	
	private static $initiated = false;
	
	public static function init() {
		if ( ! self::$initiated ) {
			self::init_hooks();
		}
	
	}
	
	public static function init_hooks() {
		self::$initiated = true;
		add_action( 'admin_init', array( 'appointmentadmin', 'admin_init' ) );
		add_action( 'admin_menu', array( 'appointmentadmin', 'admin_menu' ) );
	}
	
	public static function admin_init() {
		load_plugin_textdomain( 'aw-appointments' );
	}
	
	public static function admin_menu() {
		add_menu_page('Appointments', 'Appointments', 'manage_options','aw-appointments/admin/index.php','','dashicons-backup', 4.55);
	}
	
	
}
?>
