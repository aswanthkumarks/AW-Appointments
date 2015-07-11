 <div class="form-div wrap">
 <div class="timemsg"></div>
  <table class="form-table">
  <tbody>
  <tr><th scope="row"><label>Email Alert To:</label> </th><td><input type="text" class="regular-text code" id="eto" name="aw-setting[email]" value="<?php echo $settings['email']['to']; ?>"/>
  <p class="description">seperate emails with comma (,)</p>
  </td></tr>
  <tr><th scope="row">CC: </th><td><input type="text" class="regular-text code" id="ecc" name="aw-setting[cc]" value="<?php echo $settings['email']['cc']; ?>"/> </td></tr>
  <tr><th scope="row">BCC: </th><td><input type="text" class="regular-text code" id="ebcc" name="aw-setting[bcc]" value="<?php echo $settings['email']['bcc']; ?>"/> </td></tr>
  
  <tr><th scope="row">Days between appointment date and booking date: </th><td>
  <table>
  <tr><td>
  <input type="number" min="0" class="regular-text" style="width:100px;" id="eprimin" name="aw-setting[primin]" value="<?php echo $settings['pm']; ?>"/>
  <p class="description">Minimum</p>  
  </td><td>
  <input type="number" min="1" class="regular-text" id="eprimax" name="aw-setting[primax]" style="width:100px;" value="<?php echo $settings['px']; ?>"/>
  <p class="description">Maximum</p>
  </td></tr>
  </table>
  </td></tr>
  <tr><td>Skype Id </td><td><input type="text" class="regular-text code" id="aw_skypeid" name="aw-setting[skype]" value="<?php echo $skype_settings['skypeid']; ?>"/></td></tr>
  </tbody>
  </table>
  <p class="submit"><input type="button" name="submit" id="aw-settings1" class="button button-primary" value="Save Changes"></p>
  </div>
  <div class="form-div wrap">
  <h2>Disable Appointments</h2>
<div class="timemsg"></div>
  <div class="aw-inline">
  <table class="form-table">
  <tbody>
  <tr><th scope="row"><label>From Date</label> </th><td><input type="date" id="aw-datef" class="regular-text code" name="aw-setting[fdate]" value=""/>
  <p class="description">Date Format dd-mm-yyyy</p>
  </td></tr>
  <tr><th scope="row"><label>To date</label> </th><td><input type="date" id="aw-datet" class="regular-text code" name="aw-setting[todate]" value=""/>
  <p class="description">Date Format dd-mm-yyyy</p>
  </td></tr>    
  </tbody>
  </table>
  <p class="submit"><input type="button" name="submit" id="aw-settings2" class="button button-primary" value="Save Changes"></p>
  </div>
  <div  class="aw-inline">
  <table id="aw-disableditems" class="aw-nops">
  <tbody>
  <?php 
  if(count($settings['noapp'])>0) echo "<tr><th>From Date</th><th>To date</th></tr>";
  foreach ($settings['noapp'] as $nk=>$nop){
  	echo "<tr><td>".$nop['f']."</td><td>".$nop['t']."</td><td><a data-key='".$nk."' class='removedisabled dashicons dashicons-dismiss' href='#'></a></td></tr>"; 	  	
}  
  ?>
  </tbody>
  </table>
  </div>
  <div style="clear:both;"></div>
  
  
  </div>
   
  
