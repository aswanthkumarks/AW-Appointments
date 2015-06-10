<?php
$awobj=new Appointmentadmin();
if(isset($_POST['aw-setting'])) $awobj->saveSettings($_POST['aw-setting']);


$settings=$awobj->getSettings();
?>


<div class="aw-appointment">
<div class="panel panel-default">
<div class="panel-heading">Appointments</div>
<div class="panel-body">



<ul class="nav-tabs" role="tablist" id="aw-appointment-tab">
  <li role="presentation" class="active"><a href="#aw-applist" class="dashicons-before dashicons-backup" aria-controls="home" role="tab" data-toggle="tab">Appointments</a></li>
  <li role="presentation"><a href="#aw-schedule" aria-controls="schedule" role="tab" data-toggle="tab">General Schedule</a></li>
  <li role="presentation"><a href="#aw-settings" class="dashicons-before dashicons-admin-generic" aria-controls="settings" role="tab" data-toggle="tab">Settings</a></li>
</ul>

<div class="tab-content">
  <div role="tabpanel" class="tab-pane active" id="aw-applist">
  	<?php require_once 'tab.appointments.php';?>  
  </div>
  <div role="tabpanel" class="tab-pane" id="aw-schedule">
  	<?php require_once 'tab.schedule.php';?>
  </div>
  <div role="tabpanel" class="tab-pane" id="aw-settings">
  <?php require_once 'tab.settings.php';?>
  
  </div>
</div>




</div>


</div>

</div>


<script>
(function($){

	h=$('.aw-appointment .panel-body').height();
	$('.aw-appointment .aw-cover-panel').css('height',h+'px');

	$('input[rel="time"]').ptTimeSelect();
	
	$('#aw-appointment-tab li>a').click(function(){
		var obj=$(this).attr('href');
		$('.active').removeClass('active');
		$(this).parent().addClass('active');
		$(obj).addClass('active');		
	});
	$('.cmn-toggle').change(function(){
		status=1;
		var skey= $(this).attr('rel');
		if(!$(this).attr('checked')){
			status=0;		
		}
		ph=$('.aw-appointment .tab-pane.active').height();
		var awcover=$('<div class="aw-cover-panel" style="height:'+ph+'px"><img src="<?php echo AW_APPOINTMENT_PLUGIN_URL;?>loading.gif"/></div>');
		$('.aw-appointment .tab-pane.active').append(awcover);
		var data={ action: 'aw_update_options' , field: 'onoff', key: skey,value: status };
		
		jQuery.post(ajaxurl, data, function(response) {
		     console.log (response);
		}).always(function() {
			awcover.remove();
		});
		
	});

	$('.aw-remove').click(function(){
		var day=$(this).attr('aw-week');
		var rkey=$(this).attr('rel');
		ph=$('.aw-appointment .tab-pane.active').height();		
		var awcover=$('<div class="aw-cover-panel" style="height:'+ph+'px"><img src="<?php echo AW_APPOINTMENT_PLUGIN_URL;?>loading.gif"/></div>');		
		$('.aw-appointment .tab-pane.active').append(awcover);		
				
		var data={ action: 'aw_update_options' , field: 'deletesch', day: day, key: rkey };		
		jQuery.post(ajaxurl, data, function(response) {
		     console.log (response);
		}).always(function() {
			awcover.remove();
		});


	});

	$('.scheduletime').click(function(){
		var obj=$(this).parent('.settime');
		var ft=obj.find('.timefrom').val();
		var tt=obj.find('.timeto').val();

		if(validatetime(ft) && validatetime(tt)){

			ph=$('.aw-appointment .tab-pane.active').height();
	
			var awcover=$('<div class="aw-cover-panel" style="height:'+ph+'px"><img src="<?php echo AW_APPOINTMENT_PLUGIN_URL;?>loading.gif"/></div>');
			
			$('.aw-appointment .tab-pane.active').append(awcover);
			
			var skey= $(this).attr('rel');
			
			var data={ action: 'aw_update_options' , field: 'newsch', key: skey, from: ft, to: tt , noa: '10' };
			
			jQuery.post(ajaxurl, data, function(response) {
			     console.log (response);
			}).always(function() {
				awcover.remove();
			});

			
			
		}
		else{
			$('.timemsg').html('');
			obj.find('.timemsg').html("Invalid Time");
		}	

	});

	
	
})(jQuery);

function validatetime(time){
	if(time=="") return false;
	var parts = time.split(':');
    if (Number(parts[0]) < 13){
    	var pt = parts[1].split(' ');
    	if( Number(pt[0]) < 61 && (pt[1] == "AM" || pt[1] == "PM")) return true;
        else return false;
    }
    else return false;
}


</script>
