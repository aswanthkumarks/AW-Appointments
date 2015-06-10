<?php
class Appointments{
	private static function aw_appointments(){
		return [	
			'ver'=>'0',
			'timing'=>[
					'mon'=>['s'=>true,'t'=>[]],
					'tue'=>['s'=>true,'t'=>[]],
					'wed'=>['s'=>true,'t'=>[]],
					'thu'=>['s'=>true,'t'=>[]],
					'fri'=>['s'=>true,'t'=>[]],
					'sat'=>['s'=>true,'t'=>[]],
					'sun'=>['s'=>true,'t'=>[]],
			],
			'noapp'=>[],
			'email'=>[
					'to'=>'',
					'cc'=>'',
					'bcc'=>'',
			],
	];
	}
	
	private static $noapp = ['f'=>'','t'=>''];
	
	public static function init() {
		add_shortcode('aw-appointments', array('appointments', 'shortcode'));		
	}
	
	private function appointmenttable(){
		global $wpdb;
		return $wpdb->prefix . 'aw_appointments';
	}	
	
	public function shortcode(){
		
		
		
	}	
	
	/**
	 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
	 * @static
	 */
	public static function plugin_activation() {
		if ( version_compare( $GLOBALS['wp_version'], AW_APPOINTMENT_MINIMUM_WP_VERSION, '<' ) ) {
			load_plugin_textdomain( 'aw-appointments' );				
			$message = '<strong>'.sprintf(esc_html__( 'AW Appointments %s requires WordPress %s or higher.' , 'aw-appointments'), AW_APPOINTMENT_VERSION, AW_APPOINTMENT_MINIMUM_WP_VERSION ).'</strong> '.sprintf(__('Please <a href="%1$s">upgrade WordPress</a> to a current version', 'aw-appointments'), 'https://codex.wordpress.org/Upgrading_WordPress', '');
			Appointments::bail_on_activation( $message );
		}
		else{
			global $wpdb;
			$aw_appointments = get_option( "aw-appointments" );
			$aw_appointments=json_decode($aw_appointments, true);
			if($aw_appointments==NULL) $aw_appointments=self::aw_appointments();
			

			if( $aw_appointments['ver'] == "0" ) {
				$aw_appointments['ver']=AW_APPOINTMENT_VERSION;
				$sql = "CREATE TABLE ".self::appointmenttable()." (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					`docid` smallint(6) unsigned DEFAULT NULL,
					`appointmenttime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
					`createdon` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
					`patientdetails` longtext NOT NULL,
					`isdel` tinyint(1) unsigned NOT NULL DEFAULT '0',
					PRIMARY KEY (`id`)) ".$wpdb->get_charset_collate();				
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				dbDelta( $sql );
				$aw_appointments=json_encode($aw_appointments);
				add_option( "aw-appointments", $aw_appointments );
			}
			elseif($aw_appointments['ver']!=AW_APPOINTMENT_VERSION){
				/*
				 * update plugin
				 * 
				 */
				//$aw_appointments=self::aw_appointments();
				$aw_appointments['ver']=AW_APPOINTMENT_VERSION;
				$aw_appointments=json_encode($aw_appointments);
				update_option("aw-appointments", $aw_appointments );
				
			}
			
		}
	}
	
	/**
	 * Removes all connection options
	 * @static
	 */
	public static function plugin_deactivation( ) {
		//tidy up
	}
	
	private static function bail_on_activation( $message, $deactivate = true ) {
		?>
	<!doctype html>
	<html>
	<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<style>
	* {
		text-align: center;
		margin: 0;
		padding: 0;
		font-family: "Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif;
	}
	p {
		margin-top: 1em;
		font-size: 18px;
	}
	</style>
	<body>
	<p><?php echo esc_html( $message ); ?></p>
	</body>
	</html>
	<?php
			if ( $deactivate ) {
				$plugins = get_option( 'active_plugins' );
				$aw = plugin_basename( AKISMET__PLUGIN_DIR . 'aw-appointments.php' );
				$update  = false;
				foreach ( $plugins as $i => $plugin ) {
					if ( $plugin === $aw ) {
						$plugins[$i] = false;
						$update = true;
					}
				}
	
				if ( $update ) {
					update_option( 'active_plugins', array_filter( $plugins ) );
				}
			}
			exit;
		}
		
		
}
?>
