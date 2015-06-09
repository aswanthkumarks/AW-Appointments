<?php
class Appointmentadmin{
	
	private static $initiated = false;	
	
	private static $apdata = [];
	
	private static $settings = [];
		
	public static function init() {
		if ( ! self::$initiated ) {
			self::init_hooks();
			self::$settings=json_decode(get_option('aw-appointments'),true);
		}	
	}
	public function getSettings(){
		return self::$settings;
	}	
	
		
	public static function init_hooks() {
		self::$initiated = true;
		add_action( 'admin_init', array( 'appointmentadmin', 'admin_init' ) );
		add_action( 'admin_menu', array( 'appointmentadmin', 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( 'appointmentadmin', 'load_resources' ) );
	}
	
	public static function admin_init() {
		load_plugin_textdomain( 'aw-appointments' );
	}
	
	public static function admin_menu() {
		add_menu_page('Appointments', 'Appointments', 'manage_options','aw-appointments/admin/index.php','','dashicons-backup', 4.55);
	}
	
	public static function load_resources(){	
		wp_register_style( 'aw-appointment-admin', AW_APPOINTMENT_PLUGIN_URL . 'admin/style.css', array(), AW_APPOINTMENT_VERSION );
		wp_enqueue_style( 'aw-appointment-admin');
	}
	
	public function checkmydate($date) {
		$tempDate = explode('-', $date);
		if (checkdate($tempDate[1], $tempDate[2], $tempDate[0])) {
			return true;
		} else {
			return false;
		}
	}
		
	public function saveSettings($vals){		
		$settings=self::$settings;
		$settings['email']['to']=$vals['email'];
		$settings['email']['cc']=$vals['cc'];
		$settings['email']['bcc']=$vals['bcc'];

		if($vals['fdate']!=''&&$vals['todate']!=''){
			
			echo $vals['fdate'];
			echo $vals['todate'];
			if(self::checkmydate($vals['fdate']) && self::checkmydate($vals['todate'])) {
				$notfount=true;
				foreach ($settings['noapp'] as $dt){
					if($dt['f']==$vals['fdate']&&$dt['t']==$vals['todate']){
						$notfount=false;
					}
				}				
				if($notfount){
					if(count($settings['noapp'])<5) array_push($settings['noapp'],['f'=>$vals['fdate'],'t'=>$vals['todate']]);		
				}
			}
		}
		
		self::$settings=$settings;
		update_option('aw-appointments', json_encode($settings));
	}
	
	
}
?>
