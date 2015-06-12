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
		add_action('wp_ajax_aw_appointment', array('appointments','aw_appointment'));
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
	
	private function appointmenttable(){
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
	
	public static function shortcode($atts){
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
		
	
		switch($atts['theam']){
			case 'basic': require_once "template/basic.php"; break;
			default: require_once "theam/basic.php"; break;
		}
		
		?>
		<script type="text/javascript">
		(function($){


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
            	
            	$.each(data,function(k,v){
                	var valid=validateform(obj.find('[name='+v.name+']'),v.value);               	         		
              		if(valid){
              			obj.find('[name='+v.name+']').removeClass("aw-error");
                  		}
              		else{
              			obj.find('[name='+v.name+']').addClass("aw-error");
              			formvalid=false;
                  		}
                	});
            	
            	if(typeof obj.find('[type="radio"]:checked').val() == 'undefined'){
            		obj.find('.aw-weeks').addClass('aw-error');
            		formvalid=false;
                }
            	
            	if(formvalid){
                $(this).removeClass('aw-btn-active');
                $(this).addClass('aw-btn-deactive');
            	}
            });

            

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

              	return valid;

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
