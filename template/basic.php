<div class="aw-appointment basic">
<h2 class="aw-head">Take Appointment</h2>
<form>
<input type="text" class="aw-date aw-input" placeholder="Choose Date" name="aw-date"/>

<div class="aw-weeks">
<?php foreach($opt['timing'] as $key=>$value){
	if($value['s']){
		echo '<div id="aw-slot-'.appointments::$week[$key].'" class="aw-week aw-hideday"><div class="aw-slotkey">'.appointments::$dayname[$key].' :</div>';
		foreach($value['t'] as $skey=>$slot){
			echo '<div class="aw-slot"><input name="aw-slot" data-slot="'.$slot['n'].'" id="aw-'.$key.'-'.$skey.'" type="radio" value="'.$skey.'"/><label for="aw-'.$key.'-'.$skey.'">'.$slot["f"].'-'.$slot["t"].'</label></div>';
		}		
		echo '</div>';
	}	
	
} ?>

</div>
<input type="text" class="aw-name aw-input" placeholder="Your name" name="aw-name"/>
<input type="email" class="aw-email aw-input" placeholder="Your email" name="aw-email"/>
<input type="tel" class="aw-phone aw-input" placeholder="Your phone" name="aw-phone"/>
<hr/>
<div class="aw-locations"><label>Your Location Details</label>
<textarea class="aw-address aw-input" name="aw-address" placeholder="Your address"></textarea>
<input type="text" class="aw-city aw-input" placeholder="City" name="aw-city"/>
<input type="text" class="aw-country aw-input" placeholder="Country" name="aw-country"/>
</div>
<div class="captcha">
<?php $aw_rand='aw-'.rand(0,999); ?>
	<input type="hidden" class="aw-captchavar" name="aw-var" value="<?php echo $aw_rand; ?>"/>
	<input type="text" class="aw-captcha aw-input" placeholder="Captcha" name="aw-captcha"/>
	<img class="aw-captchaimg" src="<?php echo AW_APPOINTMENT_PLUGIN_URL.'captcha.php?var='.$aw_rand; ?>"/>
	<img class="aw-refresh" src="<?php echo AW_APPOINTMENT_PLUGIN_URL; ?>refresh.png"/>
</div>
<div class="aw-msg"></div>
<div class="aw-submit"><button type="button" class="aw-button aw-btn-active">Submit</button></div>
</form>
</div>


<style type="text/css">
.aw-appointment{
	border:1px solid #ccc;
}
.aw-appointment hr{
padding:0; 
margin:5px 0;
}
.aw-appointment .aw-submit{
text-align:center;
margin:5px 0;
}
.aw-appointment .captcha{
	text-align:center;
}
.aw-appointment .aw-captchaimg{
	border:1px solid #ccc;
}
.aw-appointment .aw-captcha{
	max-width:150px;
}
.aw-appointment .aw-refresh{
	height:45px;
	cursor:pointer;
}
.aw-appointment .aw-refresh:active{
	position:relative;
	top:-1px;
}
.aw-appointment button.aw-button{
	background-color: #0392ff;
    border: 0;
    outline:0;
    padding:0;
    margin:0 auto;
    color: #FFF;
    cursor: pointer;
    height: 35px;
    width: 100px;
    font-size:13px;
    line-height:35px;
    box-shadow:0 0 2px #333;
}
.aw-appointment button.aw-button:hover{
	background-color:#299FF9;
}
.aw-appointment button.aw-button:active{
	position:relative;
	top:-1px;
}
.aw-appointment .aw-btn-deactive{
	background: #0392ff url('<?php echo AW_APPOINTMENT_PLUGIN_URL; ?>loadingbtn.gif') no-repeat;
	  padding-left: 29px !important;
  background-position-y: center;
  background-size: 25px 25px;
  background-position-x: 4px;
}

.aw-appointment input.aw-input{
	margin:2px 0;
	width:100%;
	padding: 0.5278em;
	background:#F7F7F7;
	color:#707070;
	color:rgba(51, 51, 51, 0.7);
}
.aw-appointment .aw-locations{
	margin: 0.5em 1em;
}
.aw-appointment input.aw-input:focus{
  outline:0;
  background-color: #fff;
  border: 1px solid #c1c1c1;
  border: 1px solid rgba(51, 51, 51, 0.3);
  color: #333;

}

.aw-appointment.basic{
	padding:3px;
}
.aw-appointment .aw-hideday{
display:none;
}
.aw-appointment .aw-week{
	border:1px solid #ccc;
	margin:3px 0;	
	font-size:12px;
}
.aw-appointment .aw-slot{
	display:inline-block;
	font-size:12px;
	margin:2px 5px;		
	padding:3px;
}
.aw-appointment .aw-slotkey{
	display:inline-block;
	padding:3px;
}

.aw-appointment .aw-slot label{
	display:inline-block;
	cursor:pointer;
}
.aw-appointment .aw-slot input{
	margin:0 4px;
	cursor:pointer;
}
.aw-appointment h2.aw-head{
	font-size:16px;
	padding:3px;
	margin:4px 0;
}
.aw-appointment .aw-error{
	border:1px solid #ea1010;
}
</style>