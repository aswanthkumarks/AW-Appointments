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

<script>
(function($){
	$('#aw-appointment-tab li>a').click(function(){
		var obj=$(this).attr('href');
		$('.active').removeClass('active');
		$(this).parent().addClass('active');
		$(obj).addClass('active');		
	});
})(jQuery);
</script>



</div>


</div>

</div>

