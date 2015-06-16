<div class="aw-appointment basic">
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