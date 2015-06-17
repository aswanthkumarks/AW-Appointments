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
  <li><a href="#aw-trash" aria-controls="trash" role="tab" data-toggle="tab">Trash</a></li>
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
  <div role="tabpanel" class="tab-pane" id="aw-trash">
  <?php require_once 'tab.trash.php';?>  
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
			$(this).removeClass('dashicons-plus');
			$(this).addClass('dashicons-minus');
			}
		else{
			$(this).parent().find('.settime').css('display','none');
			$(this).removeClass('dashicons-minus');
			$(this).addClass('dashicons-plus');
			}
		return false;
	});

	h=$('.aw-appointment .panel-body').height();
	$('.aw-appointment .aw-cover-panel').css('height',h+'px');

	$('input[rel="time"]').ptTimeSelect();
	
	$('#aw-appointment-tab li>a').click(function(){
		var obj=$(this).attr('href');
		$('.active').removeClass('active');
		$(this).parent().addClass('active');
		$(obj).addClass('active');
		return false;
	});
	$('.cmn-toggle').change(function(){
		status=1;
		var skey= $(this).attr('rel');
		if(!$(this).attr('checked')){
			status=0;		
		}
		var awcover=aw_show_loading();
		var data={ action: 'aw_update_options' , field: 'onoff', key: skey,value: status };
		
		jQuery.post(ajaxurl, data, function(response) {
		     
		}).always(function() {
			awcover.remove();
		});
		
	});

	$(document).on('click','.aw-remove',function(){
		var day=$(this).attr('aw-week');
		var rkey=$(this).attr('rel');
		var td=$(this).closest('td');
		var awcover=aw_show_loading();	
		var data={ action: 'aw_update_options' , field: 'deletesch', day: day, key: rkey };		
		$.post(ajaxurl, data, function(response) {
			if(response.resp) populateschedule(td,response.data);
		}).always(function() {
			awcover.remove();
		});
		
		});

	$(document).on('click','.removedisabled',function(){
		var key=$(this).attr('data-key');
		var obj=$(this);
		var awcover=aw_show_loading();	
		var data={ action: 'aw_update_options' , field: 'removedisabled', key: key};	
		$.post(ajaxurl, data, function(response) {
			if(response.resp){
				aw_show_msg(obj.closest('.wrap'),"Settings updated successfully");
				
				populatedisabled(response.data);			
			}
		}).fail(function(response) {
			 
		  }).always(function() {
			awcover.remove();
		});
		return false;
		
		});
	$('.scheduletime').click(function(){
		var obj=$(this).parent('.settime');
		var ft=obj.find('.timefrom').val();
		var tt=obj.find('.timeto').val();
		var nos=obj.find('.nos').val();

		var td=$(this).closest('.settime').closest('td');
		
		if(validatetime(ft) && validatetime(tt)){
			var awcover=aw_show_loading();
					
			var skey= $(this).attr('rel');
			if(nos==''||Number(nos)<1) nos=5;		
			var data={ action: 'aw_update_options' , field: 'newsch', key: skey, from: ft, to: tt , noa: nos };	
					
			$.post(ajaxurl, data, function(response) {
				
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

$("#aw-settings1").click(function(){
	var eto=$('#eto').val();
	var ecc=$('#ecc').val();
	var ebcc=$('#ebcc').val();
	var obj=$(this);
	if(aw_validateemail(eto)&&aw_validateemail(ecc)&&aw_validateemail(ebcc)){

		var awcover=aw_show_loading();
		
		var data={ action: 'aw_update_options' , field: 'alertdetails', to: eto, cc: ecc, bcc: ebcc, prmax: $('#eprimax').val(), prmin: $('#eprimin').val() };	
		$.post(ajaxurl, data, function(response) {
			if(response.resp){
				aw_show_msg(obj.closest('.wrap'),"Settings updated successfully");
			}
		}).fail(function(response) {
			 
		  }).always(function() {
			awcover.remove();
		});
		
	}
	else aw_show_msg(obj.closest('.wrap'),"Invalid Emails");
	
});

function populatedisabled(data){
	$("#aw-disableditems tbody").html('');
	$("#aw-disableditems tbody").append($('<tr><th>From Date</th><th>To date</th><th></th></tr>'));
	$.each(data,function(k,d){
		$("#aw-disableditems tbody").append($('<tr><td>'+d.f+'</td><td>'+d.t+'</td><td><a data-key="'+k+'" class="removedisabled dashicons dashicons-dismiss" href="#"></a></td></tr>'));
	});
}

function aw_show_loading(){
	ph=$('.aw-appointment .tab-pane.active').height();	
	var awcover=$('<div class="aw-cover-panel" style="height:'+ph+'px"><img src="<?php echo AW_APPOINTMENT_PLUGIN_URL;?>loading.gif"/></div>');			
	$('.aw-appointment .tab-pane.active').append(awcover);
	return awcover;
}
function aw_show_msg(obj,msg){
	$('.aw-appointment .timemsg').html("");
	$('.aw-appointment .timemsg').removeClass("aw-shown");
	var msgbox=obj.find('.timemsg');
	msgbox.addClass("aw-shown");
	msgbox.html(msg);
}

$("#aw-settings2").click(function(){
	var dt=$('#aw-datet').val();
	var df=$('#aw-datef').val();
	var obj=$(this);

	if(aw_validatedate(dt)&&aw_validatedate(df)){
		
		var awcover=aw_show_loading();
		var data={ action: 'aw_update_options' , field: 'disabledetails', f: df, t: dt };
		
		$.post(ajaxurl, data, function(response) {
			if(response.resp){
				aw_show_msg(obj.closest('.wrap'),response.msg);
				populatedisabled(response.data);		
			}
		}).fail(function(response) {
			
		  }).always(function() {
			awcover.remove();
		});
		
	}
	else aw_show_msg(obj.closest('.wrap'),"Invalid Dates");
	
});

$('.aw_page').click(function(){
	var pg=$(this).attr('data-page');
	var d=$(this).attr('data-isdel');
	var obj=$(this);
	var parent=obj.closest('.tab-pane');
	parent.find('.aw_page').removeClass('aw_current');	
	var awcover=aw_show_loading();
	var data={ action: 'appointment_list' , p: pg ,d : d};
	$.post(ajaxurl, data, function(response) {
		if(response.substr(response.length - 1)==Number(0)){
			response=response.slice(0, -1);
		} 
		parent.find('.aw_appointmenttab').html(response);
		obj.addClass('aw_current');
		no=(Number(pg)*10)+1;
		len=((parent.find('.aw_appointmenttab table tr').length-1)/2)-1;
		parent.find(".aw_pagination_no").html("Showing result from "+no+" to "+(no+len));
	}).fail(function(response) {
		
	  }).always(function() {
		awcover.remove();
	});
	return false;
	
});


$(document).on('click','.aw_trash_app',function(){
	if(confirm("Are you sure ?")){
		var awcover=aw_show_loading();
		var obj=$(this).closest('.tab-pane');
		var data={ action: 'appointment_trash' , id: $(this).attr('data-id'), d: $(this).attr('data-isdel') };
		
		$.post(ajaxurl, data, function() {
			obj.find( ".aw_page.aw_current" ).trigger( "click" );					
		}).fail(function(response) {
			
		  }).always(function() {
			awcover.remove();
		});
		
	}
	return false;
	});
	

	
	
})(jQuery);

function aw_validatedate(dt){

	return true;
}
function aw_validateemail(email){
	if(email!=""){
	var emails=email.split(',');
	var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	var validstatus=true;
	jQuery.each(emails,function(k,e){
		if(!regex.test(e)) validstatus=false;
	});
	return validstatus;	
	}
	else return true;
}

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
