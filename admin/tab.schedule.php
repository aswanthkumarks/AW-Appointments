
<table class="aw-schedule">
<tbody>
<tr><th>Day</th><th>Schedule</th><th>On or Off Day</th></tr>
<?php
foreach($settings['timing'] as $key=>$value){
	echo "<tr><th>";
	echo $awobj->getDayname($key);
	?>
	</th><td>
	<a href="#" class="dashicons dashicons-admin-post toggleaw"></a>
	<div class="aw-tslots">
	<?php
	foreach($value['t'] as $tkey=>$times){
		echo '<div class="aw-time"><span rel="'.$tkey.'" aw-week="'.$key.'" alt="X" class="aw-remove dashicons dashicons-dismiss"></span><label>'.$times['f'].'</label><label>'.$times['t'].'</label><label>'.$times['n'].'</label></div>';		
	}
	echo '</div>';
	?>
		<div class="settime">
		<div class="timemsg"></div>
		<table><tr><td>From <br/><input type='text' class='timefrom timeinput' rel='time'/></td>
		<td>To <br/><input type='text' class='timeto timeinput' rel='time'/></td></tr></table>
		<i>No of slots</i>
		<input class="nos" type="number" min='1' value="10" placeholder="No slots" />
		<input type='button' rel="<?php echo $key; ?>" class='scheduletime' value="Set time"/>		
		</div>
	
	
	</td>
	
	<?php 
	$status="";
	if($value['s']) $status='checked="checked"';
	echo '<td><div class="switch">
            <input id="cmn-toggle-'.$key.'" class="cmn-toggle cmn-toggle-round" rel="'.$key.'" '.$status.' type="checkbox">
            <label for="cmn-toggle-'.$key.'"></label>
          </div>			
			</td>';
	echo "</tr>";
}
?>
</tbody>
</table>