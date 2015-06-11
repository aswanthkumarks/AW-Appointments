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
  <li role="presentation"><a href="#aw-applist" class="dashicons-before dashicons-backup" aria-controls="home" role="tab" data-toggle="tab">Appointments</a></li>
  <li role="presentation"  class="active"><a href="#aw-schedule" aria-controls="schedule" role="tab" data-toggle="tab">General Schedule</a></li>
  <li role="presentation"><a href="#aw-settings" class="dashicons-before dashicons-admin-generic" aria-controls="settings" role="tab" data-toggle="tab">Settings</a></li>
</ul>

<div class="tab-content">
  <div role="tabpanel" class="tab-pane" id="aw-applist">
  	<?php require_once 'tab.appointments.php';?>  
  </div>
  <div role="tabpanel" class="tab-pane active" id="aw-schedule">
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
	$('.toggleaw').click(function(){
		if($(this).parent().find('.settime').css('display')=='none'){
			$(this).parent().find('.settime').css('display','inline-block');
			}
		else{
			$(this).parent().find('.settime').css('display','none');
			}


		});

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

	$(document).on('click','.aw-remove',function(){
		var day=$(this).attr('aw-week');
		var rkey=$(this).attr('rel');
		var td=$(this).closest('td');
		ph=$('.aw-appointment .tab-pane.active').height();		
		var awcover=$('<div class="aw-cover-panel" style="height:'+ph+'px"><img src="<?php echo AW_APPOINTMENT_PLUGIN_URL;?>loading.gif"/></div>');		
		$('.aw-appointment .tab-pane.active').append(awcover);		
		var data={ action: 'aw_update_options' , field: 'deletesch', day: day, key: rkey };		
		$.post(ajaxurl, data, function(response) {
			if(response.resp) populateschedule(td,response.data);
		}).always(function() {
			awcover.remove();
		});
		
		});

	$('.scheduletime').click(function(){
		var obj=$(this).parent('.settime');
		var ft=obj.find('.timefrom').val();
		var tt=obj.find('.timeto').val();
		var nos=obj.find('.nos').val();

		var td=$(this).closest('.settime').closest('td');
		
		if(validatetime(ft) && validatetime(tt)){
			ph=$('.aw-appointment .tab-pane.active').height();	
			var awcover=$('<div class="aw-cover-panel" style="height:'+ph+'px"><img src="<?php echo AW_APPOINTMENT_PLUGIN_URL;?>loading.gif"/></div>');			
			$('.aw-appointment .tab-pane.active').append(awcover);			
			var skey= $(this).attr('rel');
			if(nos==''||Number(nos)<1) nos=5;		
			var data={ action: 'aw_update_options' , field: 'newsch', key: skey, from: ft, to: tt , noa: nos };	
			console.log(data);		
			$.post(ajaxurl, data, function(response) {
				console.log(response);
				if(response.resp) populateschedule(td,response.data);
			}).fail(function(response) {
				 
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

function populateschedule(obj,data){
	var wday=obj.find('.scheduletime').attr('rel');
	var awtime="";
	obj=obj.find('.aw-tslots');
	obj.find('.aw-time').remove();	
	if(data.s==1) obj.closest('tr').find('.cmn-toggle').attr('checked','checked');
	else obj.closest('tr').find('.cmn-toggle').removeAttr('checked');	
	jQuery.each(data.t, function(i, item) {
		awtime=jQuery('<div class="aw-time"><span rel="'+i+'" aw-week="'+wday+'" alt="X" class="aw-remove dashicons dashicons-dismiss"></span><label>'+item.f+'</label><label>'+item.t+'</label><label>'+item.n+'</label></div>');
		obj.append(awtime);
	});	
}

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
