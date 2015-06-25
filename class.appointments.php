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
	
	private static $noapp = ['f'=>'','t'=>''];
	private static $initiated = false;
	private static $settings = [];
	
	public static function init() {
		if ( ! self::$initiated ) {
			add_shortcode('aw-appointment', array('appointments', 'shortcode'));
			self::init_hooks();
			self::$settings=json_decode(get_option('aw-appointments'),true);
		}	
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
	
	public static function is_slot_awailable($vals,$settings){
		$slots=0;
		$awdate=date('Y-m-d',strtotime($vals['aw-date']));
		$return['st']=false;
		$return['val']='';
		$day=strtolower(date('D', strtotime($vals['aw-date'])));
		
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
			global $wpdb;		
			$ret=$wpdb->insert( self::appointmenttable(), 
					array( 
							'aw_date' => $date, 
							'docid' => $docid,
							'createdon'=> $now,
							'aw_details'=> json_encode($vals),
							'isdel'=>'0'
			 ));
			
			
			if($ret){
				$response['status']=true;
				$response['msg']="<li>Appointment booked Successfully</li>";
				$response['data']=$_POST;
				$vals['aw-date']=$date;
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
		];
		$i=1;
		foreach($vals as $k=>$v){
			if(!in_array($k, $remove)){
				$msg[$i]="<tr><td>".str_replace("aw-", "", $k)."</td><td>".$v."</td></tr>";
			}
			$i++;			
		}
		
		if($settings['email']['cc']!="") $headers[] = 'Cc: '.$settings['email']['cc'];
		if($settings['email']['bcc']!="") $headers[] = 'Bcc: '.$settings['email']['bcc'];		
		
		$sub="Appointment through ".get_site_url();
		add_filter( 'wp_mail_content_type', 'set_html_content_type' );
		function set_html_content_type() { return 'text/html';}
		
		if($settings['email']['to']!="") $to=explode(',',$settings['email']['to']);
		else $to="";
		
		$message=join($msg);

		wp_mail( $to, $sub, $message, $headers );
		
		if($vals['aw-email']!=""){
			$message="<p>Hi ".$vals['aw-name']."</p><p>Your appointment has been booked with the following details</p>".$message;
			wp_mail( $vals['aw-email'], $sub, $message );
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
		$opt=self::$settings;
		
		if(!isset($atts['theam'])) $atts['theam']="basic";

		switch($atts['theam']){
			case 'basic': require "template/basic.php"; break;
			default: require "theam/basic.php"; break;
		}
		
		add_action( 'wp_footer', array('appointments','aw_style'));
		add_action( 'wp_footer', array('appointments','aw_scripts'));
		
	}
	
	public static function aw_scripts(){		
		$opt=self::$settings;
		$disabled=[];
		$disweek=[];
		$min=date("n-j-Y",strtotime(date("Y-m-d", strtotime('now')) . " +".$opt['pm']."days"));
		$max=date("n-j-Y",strtotime(date("Y-m-d", strtotime('now')) . " +".$opt['px']."days"));
		
		foreach ($opt['timing'] as $key=>$val){
			if(!$val['s']) {
				array_push($disweek, self::$week[$key]);
			}
		}
		
		foreach($opt['noapp'] as $ap){
			$disabled=array_merge($disabled,self::between_dates($ap['f'], $ap['t']));
		}
		
		?>
				<script type="text/javascript">
				(function($){
					var ajaxurl='<?php echo admin_url( 'admin-ajax.php' ); ?>'
		
		
					var disableddates = ["12-3-2014", "12-11-2014", "12-25-2014", "12-20-2014"];
					function DisableSpecificDates(date) {
						var disweek = <?php echo json_encode($disweek); ?>;
						var disabledate =<?php echo json_encode($disabled); ?>;
						var off=false;
		
						var m = date.getMonth();
						var d = date.getDate();
						var y = date.getFullYear();
		
						
						var dnow=(m+1)+'-'+d+'-'+y;
						if ($.inArray(dnow, disabledate) !== -1 ) {
							off=true;
						 	return [false];
						}
						
						if(!off){	 
						 var day = date.getDay();
						 if ($.inArray(day,disweek) !== -1) {				 
						 	return [false] ;				 
						 }
						 else {					 			 				 
						  return [true] ;
						 }
						}
					 
					}
					
		    		$( ".aw-date" ).datepicker({
		    			 beforeShowDay: DisableSpecificDates,
		    			 minDate: new Date('<?php echo $min; ?>'),
		    			 maxDate: new Date('<?php echo $max; ?>'),
		    			 onSelect: function(d,i){
		    		          if(d !== i.lastVal){
		    		              $(this).change();
		    		          }
		    		     },
		    		 });
		
		      		
		
		      		 $(".aw-input").change(function(){
		      			var inputval=$(this).val();
		      			var valid=validateform($(this),inputval);       		
		          		if(valid){
		              		if($(this).hasClass('aw-date')){
		              			var d = new Date(inputval);
		              			var n = d.getDay();
		              			$('.aw-week').addClass('aw-hideday');
		              			$('#aw-slot-'+n).removeClass('aw-hideday');
		              			$('.aw-week input').removeAttr('checked');
		                  	}              		
		          			$(this).removeClass("aw-error");
		              		}
		          		else{
		          			$(this).addClass("aw-error");
		              		}
		          	});
		           	$('.aw-slot input').click(function(){
		               	$(this).closest('.aw-weeks').removeClass("aw-error");
		
		            });
		
		            $('.aw-btn-active').click(function(){
		               
		                var formvalid=true;
		            	var re = /^\d{1,2}\/\d{1,2}\/\d{4}$/;
		            	var obj=$(this).closest('.aw-appointment');
		            	var data=obj.find('form').serializeArray();
		            	var postdata='{ "action" : "save_appointment"';
		            	$.each(data,function(k,v){
		                	var valid=validateform(obj.find('[name='+v.name+']'),v.value);               	         		
		              		if(valid){
		              			obj.find('[name='+v.name+']').removeClass("aw-error");
		                  		}
		              		else{
		              			obj.find('[name='+v.name+']').addClass("aw-error");
		              			formvalid=false;
		                  		}
		              		postdata+=',"'+v.name+'" : "'+ v.value + '"';
		              		             		
		                	});
		            	postdata+='}';
		            	postdata=JSON.parse(postdata);
		            	
		            	if(typeof obj.find('[type="radio"]:checked').val() == 'undefined'){
		            		obj.find('.aw-weeks').addClass('aw-error');
		            		formvalid=false;
		                }
		            	
		            	if(formvalid){
		            		$(this).removeClass('aw-btn-active');
		            		$(this).addClass('aw-btn-deactive');
		            		                	
		            		$.post(ajaxurl, postdata, function(response) {
		                		
		            			if(response.status){
		            				
		            			}
		            			aw_showmsg(obj,response.msg);
		            			
		            		}).fail(function(response) {
		            			
		          		  }).always(function() {
			          		  			          		  
		          			changecaptcha(obj.find('.captcha'));
		          			var btn=obj.find('.aw-button');
	            			btn.removeClass('aw-btn-deactive');
	            			btn.addClass('aw-btn-active');
		          		});
		                
		                
		            	}
		            });
		
		            function aw_showmsg(obj,msg){
			            obj=obj.find('.aw-msg');
			            if(msg=='<li>Appointment booked Successfully</li>'){
			            	obj.addClass('aw-success');
			            	obj.closest('.aw-appointment').find('input[type=text],input[type=email],input[type=tel], textarea').val("");
				         }
			            else{
			            	obj.removeClass('aw-success');
			            }		
		                obj.html(msg);
		                obj.addClass('aw-shown');		
		             }
		
		
		                   
		
		            function validateform(obj,inputval){
		            	if(obj.hasClass('aw-date')){              		
		          			var re = /^\d{1,2}\/\d{1,2}\/\d{4}$/;
		          			if(!inputval.match(re)){
		          				valid=false;             	
		                    }
		            		else{
		            			valid=true;            			
		                    }
		              	}
		              		else if(obj.hasClass('aw-name')){
		                  		if(inputval.length<4){ valid=false; }
		                  		else{ valid=true; }
		                  	}
		              		else if(obj.hasClass('aw-email')){
		              			var eregx = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		                    	if(!eregx.test(inputval)){ valid=false; }
		                  		else{ valid=true; }
		                  	}
		              		else if(obj.hasClass('aw-phone')){
		                  		var preg=/^\+?([0-9]{2,3})\)?[-. ]?([0-9]{3,4})[-. ]?([0-9]{4,7})$/;
		                  		var preg1=/^\(?([0-9]{3,4})\)?[-. ]?([0-9]{4,7})$/;
		                  		var preg2=/^\(?([0-9]{2,3})\)?[-. ]?([0-9]{3,4})[-. ]?([0-9]{4,7})$/;
		                  		var preg3=/^\d{9,10}$/;
		                  		if(preg.test(inputval)||preg1.test(inputval)||preg2.test(inputval)||preg3.test(inputval)){
		                      		valid=true;
		                      	}
		                  		else{ valid=false; }
		                  	}
		              		else if(obj.hasClass('aw-city')){
		              			if(inputval.length<3) valid=false;
		              			else valid=true;
		                  	}
		              		else if(obj.hasClass('aw-country')){
		              			if(inputval.length<3) valid=false;
		              			else valid=true;
		                  	}
		              		else if(obj.hasClass('aw-address')){
		              			if(inputval.length<5||inputval.length>200) valid=false;
		              			else valid=true;
		                  	}
		              		else if(obj.hasClass('aw-captcha')){
		              			if(inputval.length<4) valid=false;
		              			else valid=true;
		                  	}
		
		              	return valid;
		
		            }

		            $('.aw-refresh').click(function(){
		            	changecaptcha($(this).closest('.captcha'));
			         });

		            function changecaptcha(obj){
		            	var cap=obj.find('.aw-captchaimg');
		            	var src=cap.attr('src');
		            	src=src.substring(0, src.indexOf('?'));
			            src=src+'?var='+obj.find('.aw-captchavar').val()+'&r='+(Math.floor(Math.random()*90000) + 10000);
			            cap.attr('src',src);

			           }
		     		 
				})(jQuery);
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
			$aw_appointments=json_decode($aw_appointments, true);
			if($aw_appointments==NULL) $aw_appointments=self::aw_appointments();
			

			if( $aw_appointments['ver'] == "0" ) {
				$aw_appointments['ver']=AW_APPOINTMENT_VERSION;
				$sql = "CREATE TABLE ".self::appointmenttable()." (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					`docid` smallint(6) unsigned DEFAULT NULL,
					`aw_date` date NOT NULL,
					`createdon` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
					`aw_details` longtext NOT NULL,
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
				$aw_appointments=self::aw_appointments();
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