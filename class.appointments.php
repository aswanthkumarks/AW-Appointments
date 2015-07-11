<?php
class Appointments{
	private static function aw_appointments(){
		return [	
			'ver'=>'0',
			'pm'=>'1',
			'px'=>'7',
			'timing'=>[
					'mon'=>['s'=>0,'t'=>[]],
					'tue'=>['s'=>0,'t'=>[]],
					'wed'=>['s'=>0,'t'=>[]],
					'thu'=>['s'=>0,'t'=>[]],
					'fri'=>['s'=>0,'t'=>[]],
					'sat'=>['s'=>0,'t'=>[]],
					'sun'=>['s'=>0,'t'=>[]],
			],
			'noapp'=>[],
			'email'=>[
					'to'=>'',
					'cc'=>'',
					'bcc'=>'',
			],
	];
	}
	
	private static $skype=[
			'timing'=>[
					'mon'=>['s'=>0,'t'=>[]],
					'tue'=>['s'=>0,'t'=>[]],
					'wed'=>['s'=>0,'t'=>[]],
					'thu'=>['s'=>0,'t'=>[]],
					'fri'=>['s'=>0,'t'=>[]],
					'sat'=>['s'=>0,'t'=>[]],
					'sun'=>['s'=>0,'t'=>[]],
					],
			'noapp'=>[],
			'skypeid'=>'',
	];
	
	private static $theme=0;
	
	private static $dayname=[
			'mon'=>'Monday',
			'tue'=>'Tuesday',
			'wed'=>'Wednesday',
			'thu'=>'Thursday',
			'fri'=>'Friday',
			'sat'=>'Saturday',
			'sun'=>'Sunday',
	];
	private static $week=[
			'mon'=>1,
			'tue'=>2,
			'wed'=>3,
			'thu'=>4,
			'fri'=>5,
			'sat'=>6,
			'sun'=>0,
	];
	private static $skype_settings = [];
	
	private static $noapp = ['f'=>'','t'=>''];
	private static $initiated = false;
	private static $settings = [];
	
	public static function init() {
		if ( ! self::$initiated ) {
			add_shortcode('aw-appointment', array('appointments', 'shortcode'));
			self::init_hooks();
			self::$settings=json_decode(get_option('aw-appointments'),true);
			self::$skype_settings=json_decode(get_option('aw_appointment_skype'),true);
		}	
	}
	
	public function getskypeSettings(){
		return self::$skype_settings;
	}
	
	public static function init_hooks() {
		self::$initiated = true;
		add_action( 'wp_enqueue_scripts', array( 'appointments', 'load_resources' ) );
		add_action('wp_ajax_save_appointment', array('appointments','save_appointment'));
		add_action( 'wp_ajax_nopriv_save_appointment', array('appointments','save_appointment'));
		
		
		
	}
	
	
	public static function load_resources(){
		wp_register_style( 'jquery-ui', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css', array(), 1.11 );
		wp_enqueue_style( 'jquery-ui');		
		wp_register_script( 'jquery-ui', '//code.jquery.com/ui/1.11.4/jquery-ui.js', array('jquery'), 1.11 );
		wp_enqueue_script( 'jquery-ui' );
		wp_register_script( 'aw-appointment', AW_APPOINTMENT_PLUGIN_URL . 'appointment.js', array('jquery'), 1.0, true );
		wp_enqueue_script( 'aw-appointment' );
	}
	public function getSettings(){
		return self::$settings;
	}
	
	private static function appointmenttable(){
		global $wpdb;
		return $wpdb->prefix . 'aw_appointments';
	}
	
	private static function between_dates($start,$end){
		$ret=[];
		$datediff = strtotime($end) - strtotime($start);
		$datediff = floor($datediff/(60*60*24));
		for($i = 0; $i < $datediff + 1; $i++){
			array_push($ret,date("n-j-Y", strtotime($start . ' + ' . $i . 'day')));
		}
		return $ret;
	}
	
	function get_domain($url)
	{
		$pieces = parse_url($url);
		$domain = isset($pieces['host']) ? $pieces['host'] : '';
		if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
			return $regs['domain'];
		}
		return false;
	}
	
	public static function is_slot_awailable($vals,$settings){
		$slots=0;
		$awdate=date('Y-m-d',strtotime($vals['aw-date']));
		$return['st']=false;
		$return['val']='';
		$day=strtolower(date('D', strtotime($vals['aw-date'])));
		
		if(isset($vals['aw-type'])){
			if($vals['aw-type']=="skype"){
				$settings=json_decode(get_option('aw_appointment_skype'),true);
			}
		}
		
		foreach($settings['timing'][$day]['t'] as $key=>$val){
			if($key==$vals['aw-slot']) {
				$slots=$val['n'];
				$return['val']=$val['f']."-".$val['t'];
			}
		}
		
		global $wpdb;
		
		$rows = $wpdb->get_results(
				"SELECT aw_details
				FROM ".self::appointmenttable()."
				WHERE aw_date = '".$awdate."'
				AND isdel = 0"
		);
		$i=0;
		
		foreach ( $rows as $r )
		{
			$data=json_decode($r->aw_details,true);
			if($data['aw-slot']==$vals['aw-slot']) $i++;
		}
		
		if($i<$slots) $return['st']=true;		
		else $return['st']=false;
		
		return $return;
		
	}
	
	public static function aw_style(){
		require_once 'style.php';
	}
	
	
	
	public static function save_appointment(){
		session_start();
		$response = array();
		$response['status']=false;
		$response['msg']="Invalid request";	
		if(isset($_POST['aw-captcha'])&&isset($_POST['aw-var'])){
			$sess=$_POST['aw-var'];
		if(isset($_SESSION[$sess])&&($_POST['aw-captcha']==$_SESSION[$sess])){
												
		$settings=json_decode(get_option('aw-appointments'),true);
		$validate=self::validate_data($_POST,$settings);
		if($validate['status']){
			$vals=$_POST;
			$vals['ip']=self::get_client_ip();
			$now=current_time( 'mysql' );
			unset($vals['action']);			
			
			if(isset($vals['aw-docid'])){
				$docid=$vals['aw-docid'];
				unset($vals['aw-docid']);
			}
			else $docid=0;
			
			$slst=self::is_slot_awailable($vals,$settings);
			$vals['aw-time']=$slst['val'];
			if($slst['st']){				
			$date=date('Y-m-d',strtotime($vals['aw-date']));
			unset($vals['aw-date']);
			
			/*
			 * Normal Appointment
			 */
			$aw_type=0;
			$success_msg="Appointment booked Successfully"; 
			if(isset($vals['aw-type'])){
				/*
				 * Skype appointment
				 */
				if($vals['aw-type']=="skype"){
					$success_msg="Your Skype appointment booked Successfully";
					$aw_type=1;
				}
				unset($vals['aw-type']);			
			}
			
			
			global $wpdb;		
			$ret=$wpdb->insert( self::appointmenttable(), 
					array( 
							'aw_date' => $date, 
							'docid' => $docid,
							'createdon'=> $now,
							'aw_details'=> json_encode($vals),
							'isdel'=>'0',
							'aw_type'=>$aw_type
			 ));
			
			
			if($ret){
				$response['status']=true;
				$response['msg']="<li class='alert-success'>".$success_msg."</li>";
				$response['data']=$_POST;
				$vals['aw-date']=$date;
				$vals['aw-type']=$aw_type;
				self::send_email_alert($vals,$settings);
				unset($_SESSION[$sess]);
				
			}
			else{
				$response['msg']="<li>Failed to book appointment</li>";
			}
			}
			else{
				$response['msg']="<li>Apointment Closed for ".$vals['aw-time']." time slot</li>";
			}
				
		}
		else{
			$response['msg']=$validate['msg'];
		}
		}
		else{
			$response['msg']="<li>Invalid Captcha</li>";
		}	
		}
		else{
			$response['msg']="<li>Invalid Request</li>";
		}
		
				
		header( "Content-Type: application/json" );		
		echo json_encode($response);
		wp_die();
	}
	
	public static function send_email_alert($vals,$settings){
		$msg[0]='<table style="background-color:#fff; width:100%; max-width:500px;"><tbody>';
		$remove=['aw-slot',
				'aw-var',
				'aw-captcha',
				'aw-type',
		];
		$i=1;
		foreach($vals as $k=>$v){
			if(!in_array($k, $remove)){
				$msg[$i]="<tr><td>".str_replace("aw-", "", $k)."</td><td>".$v."</td></tr>";
			}
			$i++;			
		}
		$msg[$i]="</table>";
		
		if($settings['email']['cc']!="") $headers[] = 'Cc: '.$settings['email']['cc'];
		if($settings['email']['bcc']!="") $headers[] = 'Bcc: '.$settings['email']['bcc'];		
		$url=get_site_url();
		$domain=self::get_domain($url);
		
		$sub="Appointment through ".$domain;
		$skmsg="";
		$skypedetails="";
		$fromemail="no-replay@".$domain;
		
		$headers[] = 'From: '.$domain.' <'.$fromemail.'>';
		$head[] = 'From: '.$domain.' <'.$fromemail.'>';
		if($vals['aw-type']==1){
			$sub="Skype appointment through ".$domain;
			$skmsg="skype ";
			$sksetting=json_decode(get_option('aw_appointment_skype'),true);
			$skypedetails="<p>you will be receiving a skype call from the skype id <b>".$sksetting['skypeid']."</b></p>";
		}
		
		add_filter( 'wp_mail_content_type', 'set_html_content_type' );
		
		function set_html_content_type() { return 'text/html';}
		
		if($settings['email']['to']!="") $to=explode(',',$settings['email']['to']);
		else $to="";
		
		$message=join($msg);

		wp_mail( $to, $sub, $message, $headers );
		
		if($vals['aw-email']!=""){
			$message="<p>Hi ".$vals['aw-name']."</p><p>Your ".$skmsg." appointment has been booked with the following details</p>".$message.$skypedetails;
			wp_mail( $vals['aw-email'], $sub, $message, $head );
		}
			
	}
	
	public static function validate_data($data,$settings){
		
		$return['status']=true;
		$return['msg']='';
		$msgs=[];		
			foreach($data as $key=>$value){
				if($key=="aw-email"){
					if(!filter_var($value, FILTER_VALIDATE_EMAIL)){
						$return['status']=false;
						array_push($msgs, "<li>Invalid Email id</li>");						
					}					
				}
				elseif($key=="aw-phone"){
					
					  
					 if(!preg_match("/^\+?([0-9]{2,3})\)?[-. ]?([0-9]{3,4})[-. ]?([0-9]{4,7})$/", $value)){
						if(!preg_match("/^\(?([0-9]{3,4})\)?[-. ]?([0-9]{4,7})$/", $value)){
							if(!preg_match("/^\(?([0-9]{2,3})\)?[-. ]?([0-9]{3,4})[-. ]?([0-9]{4,7})$/", $value)){
								if(!preg_match("/^\d{9,10}$/", $value)){
									$return['status']=false;
									array_push($msgs, "<li>Invalid Phone Number id</li>");
								}		
							}
						}
						
					}
					
				}
				elseif($key=="aw-name"){
					if(!preg_match("/^[a-zA-Z ]*$/",$value)||strlen($value)<3){
						$return['status']=false;
						array_push($msgs, "<li>Only letters and white space allowed in name</li>");						
					}
				}
				elseif($key=="aw-country"){
					if(!preg_match("/^[a-zA-Z ]*$/",$value)||strlen($value)<3){
						$return['status']=false;
						array_push($msgs, "<li>Only letters and white space allowed in country</li>");
					}
				}
				elseif($key=="aw-captcha"){
					if(strlen($value)<3){
						$return['status']=false;
						array_push($msgs, "<li>Invalid Captcha</li>");
					}
				
				
				
				}	
				
			
		}
		$return['msg']=join($msgs);
			
			
		return $return;
	}
	
	private static function get_client_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
		$ip = $_SERVER['REMOTE_ADDR'];
		}
		return apply_filters( 'wpb_get_ip', $ip );
	}
	public static function shortcode($atts){
		ob_start();
		$opt=self::$settings;
		$sopt=self::$skype_settings;
		if(!isset($atts['theam'])) $atts['theam']="basic";
		elseif($atts['theam']=="skype"){
			self::$theme=1;
		}
		

		switch($atts['theam']){
			case 'basic': require "template/basic.php"; break;
			case 'skype': require "template/skype.php"; break;
			default: require "theam/basic.php"; break;
		}
		
		$output_string=ob_get_contents();
		ob_end_clean();
		add_action( 'wp_footer', array('appointments','aw_style'));
		add_action( 'wp_footer', array( __CLASS__,'aw_scripts'));
		
		return $output_string;
	}

public static function aw_scripts(){
		$opt=self::$settings;
		$disabled=[];
		$disweek=[];
		$min=date("n-j-Y",strtotime(date("Y-m-d", strtotime('now')) . " +".$opt['pm']."days"));
		$max=date("n-j-Y",strtotime(date("Y-m-d", strtotime('now')) . " +".$opt['px']."days"));
		
		$timings=$opt;
		if(self::$theme==1){
			$timings=self::$skype_settings;
		}
		
		foreach ($timings['timing'] as $key=>$val){
			if(!$val['s']) {
				array_push($disweek, self::$week[$key]);
			}
		}
		
		foreach($opt['noapp'] as $ap){
			$disabled=array_merge($disabled,self::between_dates($ap['f'], $ap['t']));
		}
		
		?>
				<script type="text/javascript">
				var aw_app={
						ajaxurl : "<?php echo admin_url( 'admin-ajax.php' ); ?>",
						disweek : <?php echo json_encode($disweek); ?>,
						disdate : <?php echo json_encode($disabled); ?>,
						apmin : '<?php echo $min; ?>',
						apmax : '<?php echo $max; ?>',
						};
		  		</script>
				
				<?php 
		
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
			$aw_skype = json_decode(get_option("aw_appointment_skype"), true);
			$aw_appointments=json_decode($aw_appointments, true);
			
			if($aw_appointments==NULL) $aw_appointments=self::aw_appointments();
			if($aw_skype==NULL) $aw_skype = self::$skype;
			
			$awtable=self::appointmenttable();

			
				/*
				 * update plugin
				 * 
				 */
				$sql = "CREATE TABLE ".$awtable." (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					`docid` smallint(6) unsigned DEFAULT NULL,
					`aw_date` date NOT NULL,
					`createdon` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
					`aw_details` longtext NOT NULL,
					`aw_type` tinyint(2) unsigned NOT NULL DEFAULT '0',
					`isdel` tinyint(1) unsigned NOT NULL DEFAULT '0',
					PRIMARY KEY (`id`)) ".$wpdb->get_charset_collate();
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				dbDelta( $sql );
				
				$aw_appointments['ver']=AW_APPOINTMENT_VERSION;
				$aw_appointments=json_encode($aw_appointments);
				update_option("aw-appointments", $aw_appointments );
				update_option("aw_appointment_skype", json_encode($aw_skype) );			
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