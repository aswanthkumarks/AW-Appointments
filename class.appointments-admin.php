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

	public function getDayname($day){
		$dayname=[
				'mon'=>'Monday',
				'tue'=>'Tuesday',
				'wed'=>'Wednesday',
				'thu'=>'Thursday',
				'fri'=>'Friday',
				'sat'=>'Saturday',
				'sun'=>'Sunday',				
		];
		return $dayname[$day];
	}
	
		
	public static function init_hooks() {
		self::$initiated = true;
		add_action( 'admin_init', array( 'appointmentadmin', 'admin_init' ) );
		add_action( 'admin_menu', array( 'appointmentadmin', 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( 'appointmentadmin', 'load_resources' ) );
		add_action('wp_ajax_aw_update_options', array('appointmentadmin','aw_update_options'));
	}
	
	public static function admin_init() {
		load_plugin_textdomain( 'aw-appointments' );
	}
	
	public static function admin_menu() {
		add_menu_page('Appointments', 'Appointments', 'manage_options','aw-appointments/admin/index.php','','dashicons-backup', 4.55);
	}
	
	public static function load_resources(){	
		wp_register_style( 'aw-appointment-admin', AW_APPOINTMENT_PLUGIN_URL . 'admin/style.css', array(), AW_APPOINTMENT_VERSION );
		wp_register_style( 'jqueryui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.21/themes/redmond/jquery-ui.css', array(), 1.8 );
		wp_enqueue_style( 'aw-appointment-admin');
		wp_enqueue_style( 'jqueryui');
		
	
		wp_register_script( 'ptTimeSelectjs', AW_APPOINTMENT_PLUGIN_URL . 'jquery.ptTimeSelect.js', array('jquery'), 1.0 );
		wp_enqueue_script( 'ptTimeSelectjs' );
	}
	
	public static function checkmydate($date) {
		$tempDate = explode('-', $date);
		if (checkdate($tempDate[1], $tempDate[2], $tempDate[0])) {
			return true;
		} else {
			return false;
		}
	}
	
	public static function aw_update_options(){
		$response = array();
		$response['resp']="";		
		if(!empty($_POST['field'])){
			$opt=json_decode(get_option('aw-appointments'),true);
			if($_POST['field']=='onoff'){
				$opt['timing'][$_POST['key']]['s']=$_POST['value'];
				$response['resp']=true;
				$response['data']=$opt['timing'][$_POST['key']];
			}
			elseif($_POST['field']=='newsch'){
				$opt['timing'][$_POST['key']]['s']=1;
				$found=false;
				foreach($opt['timing'][$_POST['key']]['t'] as $key=>$value){
					if($value['f']==$_POST['from']&&$value['t']==$_POST['to']){
						$found=true;
					}
				}				
				if(!$found){
					array_push($opt['timing'][$_POST['key']]['t'], [
							'f'=>$_POST['from'],
							't'=>$_POST['to'],
							'n'=>$_POST['noa'],
					]);
				}
				$response['resp']=true;
				$response['data']=$opt['timing'][$_POST['key']];
			}
			elseif ($_POST['field']=='deletesch'){
					unset($opt['timing'][$_POST['day']]['t'][$_POST['key']]);
					if(count($opt['timing'][$_POST['day']]['t'])==0) $opt['timing'][$_POST['day']]['s']=0;
					$response['resp']=true;
					$response['data']=$opt['timing'][$_POST['day']];
			}
			elseif($_POST['field']=="alertdetails"){
				$opt['email']['to']=$_POST['to'];
				$opt['email']['cc']=$_POST['cc'];
				$opt['email']['bcc']=$_POST['bcc'];
				
				$opt['px']=$_POST['prmax'];
				$opt['pm']=$_POST['prmin'];
				
				if($opt['px']<1) $opt['px']=1;
				if($opt['pm']<0) $opt['pm']=0;
				
				$response['resp']=true;
			}
			elseif($_POST['field']=="disabledetails"){
				if(self::checkmydate($_POST['f']) && self::checkmydate($_POST['t'])) {
					$fd=$_POST['f'];
					$td=$_POST['t'];
					
					if(strtotime($fd)>strtotime($td)){
						$temp=$fd;
						$fd=$td;
						$td=$temp;
					}
					
					$notfount=true;
					foreach ($opt['noapp'] as $dt){
						if($dt['f']==$fd&&$dt['t']==$td){
							$notfount=false;
						}
					}
					if($notfount){
						if(count($opt['noapp'])<10){
							array_push($opt['noapp'],['f'=>$fd,'t'=>$td]);
							$response['msg']="Disable date updated successfully";
						}
						else{
							$response['msg']="Limit exceeded, you can have maximum of 10 disabled date range";							
						}
					}
					else{
						$response['msg']="This data is already present";
					}
					
				}
				else{
					$response['msg']="Invalid Date ".fd.' - '. $td;
				}
				
				$response['resp']=true;
				$response['data']=$opt['noapp'];
				
			}
			elseif($_POST['field']=="removedisabled"){
				foreach ($opt['noapp'] as $key=>$value){
					if($key==$_POST['key']) unset($opt['noapp'][$key]);
				}
				$response['resp']=true;
				$response['data']=$opt['noapp'];
				
			}			
			update_option('aw-appointments', json_encode($opt));
			
		} else {
			$response['resp'] = "You didn't send the param";
		}
		
		header( "Content-Type: application/json" );
		echo json_encode($response);
		wp_die();
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
