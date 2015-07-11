<?php
class Appointmentadmin{
	
	private static $initiated = false;	
	
	private static $apdata = [];
	
	private static $settings = [];
	
	private static $skype_settings = [];
		
	public static function init() {
		if ( ! self::$initiated ) {
			self::init_hooks();
			self::$settings=json_decode(get_option('aw-appointments'),true);
			self::$skype_settings=json_decode(get_option('aw_appointment_skype'),true);
		}	
	}
	public function getSettings(){
		return self::$settings;
	}
	
	public function getskypeSettings(){
		return self::$skype_settings;
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
		add_action('wp_ajax_appointment_list', array('appointmentadmin','appointment_list'));
		add_action('wp_ajax_appointment_trash', array('appointmentadmin','appointment_trash'));
	}
	private static function appointmenttable(){
		global $wpdb;
		return $wpdb->prefix . 'aw_appointments';
	}
	
	public static function admin_init() {
		load_plugin_textdomain( 'aw-appointments' );
	}
	
	public static function admin_menu() {
		add_menu_page('Appointments', 'Appointments', 2,'aw-appointments/admin/index.php','','dashicons-backup', 4.55);
	}
	
	public static function load_resources(){	
		wp_register_style( 'aw-appointment-admin', AW_APPOINTMENT_PLUGIN_URL . 'admin/style.css', array(), AW_APPOINTMENT_VERSION );
		wp_register_style( 'jqueryui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.21/themes/redmond/jquery-ui.css', array(), 1.8 );
		wp_enqueue_style( 'aw-appointment-admin');
		wp_enqueue_style( 'jqueryui');
		
	
		wp_register_script( 'ptTimeSelectjs', AW_APPOINTMENT_PLUGIN_URL . 'jquery.ptTimeSelect.js', array('jquery'), 1.0 );
		wp_enqueue_script( 'ptTimeSelectjs' );
	}
	
	
	public static function load_appointments($isdel=0){
		
		$f=0;
		global $wpdb;
		if(isset($_POST['p'])){
			$f=$_POST['p']*10;			
		}
		
		
		$c=10;		
		
		$return['no'] = $wpdb->get_var("SELECT COUNT(*) FROM ".self::appointmenttable()."
				WHERE isdel = ".$isdel);
		
		$return['result'] = $wpdb->get_results(
				"SELECT * FROM ".self::appointmenttable()."
				WHERE isdel = ".$isdel." order by id desc LIMIT ".$f.",".$c
		);
		$return['f']=$f;
		$return['c']=$c;
		return $return;
	}
	
	public static function load_pagination($f,$c,$n,$isdel=0){
		$nop=$n/$c;
		$i=0;
		echo "<div class='aw_pagination'>";
		while($i<$nop){
			$class="";
			if($i==$f) $class="aw_current";
			echo '<a href="#" data-isdel="'.$isdel.'" class="aw_page '.$class.'" data-page="'.$i.'">'.($i+1).'</a>';
			$i++;
		}
		echo "</div>";		
	}
	
public static function appointment_list($res=""){
		if($res==""){
			 $res=self::load_appointments($_POST['d']);
		}
		
		
		$html[0]='<table class="aw-schedule">
		<tbody>
		<tr>
		<th>Name</th>
		<th>Email</th>
		<th>Phone</th>
		<th>Appointment Date</th>
		<th>Time</th>
		<th>Appointment Type</th>
		<th>Appointment taken on</th>
		</tr>';
		

		$remove=['aw-slot','aw-name',
				'aw-email',
				'aw-phone',
				'aw-var',
				'aw-captcha',
				'aw-time',
				'aw-type',
				'paystatus'
		];
		$class="odd";
		foreach($res['result'] as $r){
			if($r->isdel==1) {
				$isdel="dashicons-undo";
				$deltitle="Restore";
			}
			else{
				$isdel="dashicons-trash";
				$deltitle="Delete";
			}
				
			$details=json_decode($r->aw_details,true);			
			array_push($html, '<tr class="'.$class.'">');
			array_push($html, "<td>".$details['aw-name'].$r->isdel."</td>");
			array_push($html, "<td>".$details['aw-email']."</td>");
			array_push($html, "<td>".$details['aw-phone']."</td>");
			array_push($html, "<td>".date('d-m-Y',strtotime($r->aw_date))."</td>");
			array_push($html, "<td>");
						
			if(isset($details['aw-time'])) array_push($html, $details['aw-time']);
			
			array_push($html, "</td>");
			if($r->aw_type==0)	array_push($html, "<td>Normal</td>");
			elseif($r->aw_type==1)	array_push($html, "<td>Skype</td>");
			
			array_push($html, "<td>".date('d-m-Y H:i:s',strtotime($r->createdon))."</td>");
			array_push($html,"<td><a title='$deltitle' class='aw_trash_app dashicons $isdel' data-isdel='".$r->isdel."' data-id='".$r->id."' href='#'></a></td>");
			array_push($html,"</tr>");
			array_push($html,'<tr class="'.$class.'">');
			array_push($html,"<td colspan='6'>");
			foreach ($details as $dk=>$dv){
				if(!in_array($dk, $remove)){
					array_push($html, "<span class='aw_more'>".str_replace("aw-", "", $dk)." : ". $dv."</span>");
				}
			}

		



			array_push($html,"</td>");
			array_push($html,"</tr>");
			if($class=="odd") $class="even";
			else $class="odd";		
		}
		array_push($html,"</tbody></table>");
		echo join($html);
		
		return "";		
	}
	
	public static function appointment_trash(){
		if(isset($_POST['id'])){
			global $wpdb;
			
			if($_POST['d']==0) $wpdb->query("UPDATE ".self::appointmenttable()." SET isdel = 1 WHERE ID = ".$_POST['id']);
			else  $wpdb->query("UPDATE ".self::appointmenttable()." SET isdel = 0 WHERE ID = ".$_POST['id']);
			
		}
		
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
			
			if(isset($_POST['shtype'])){
				if($_POST['shtype']==1){
					$opt=json_decode(get_option('aw_appointment_skype'),true);
					
				}
			}
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
				
				$skype_opt=json_decode(get_option('aw_appointment_skype'),true);
				$skype_opt['skypeid']=$_POST['skype'];
				update_option('aw_appointment_skype', json_encode($skype_opt));
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

			
			
			/*
			 * Update Options
			 */
			$skoption=false;
			if(isset($_POST['shtype'])){
				if($_POST['shtype']==1){
					update_option('aw_appointment_skype', json_encode($opt));
					$skoption=true;
						
				}
			}
			if(!$skoption) update_option('aw-appointments', json_encode($opt));
			
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
