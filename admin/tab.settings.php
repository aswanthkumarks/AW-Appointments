 <form method="POST">
  <div class="form-div wrap">
  <table class="form-table">
  <tbody>
  <tr><th scope="row"><label>Email Alert To:</label> </th><td><input type="text" class="regular-text code" name="aw-setting[email]" value="<?php echo $settings['email']['to']; ?>"/>
  <p class="description">seperate emails with comma (,)</p>
  </td></tr>
  <tr><th scope="row">CC: </th><td><input type="text" class="regular-text code" name="aw-setting[cc]" value="<?php echo $settings['email']['cc']; ?>"/> </td></tr>
  <tr><th scope="row">BCC: </th><td><input type="text" class="regular-text code" name="aw-setting[bcc]" value="<?php echo $settings['email']['bcc']; ?>"/> </td></tr>
  
  </tbody>
  </table>
  </div>
  <div class="form-div wrap">
  <h2>Disable Appointments</h2>
  <table class="form-table">
  <tbody>
  <tr><th scope="row"><label>From Date</label> </th><td><input type="date" class="regular-text code" name="aw-setting[fdate]" value="<?php echo $settings['email']['to']; ?>"/>
  <p class="description">Date Format dd-mm-yyyy</p>
  </td></tr>
  <tr><th scope="row"><label>To date</label> </th><td><input type="date" class="regular-text code" name="aw-setting[todate]" value=""/>
  <p class="description">Date Format dd-mm-yyyy</p>
  </td></tr>    
  </tbody>
  </table>
  <div>
  <table class="aw-nops">
  <tbody>
  <?php 
  if(count($settings['noapp'])>0) echo "<tr><th>From Date</th><th>To date</th></tr>";
  foreach ($settings['noapp'] as $nop){
  	echo "<tr><td>".$nop['f']."</td><td>".$nop['t']."</td><td><a class='dashicons dashicons-dismiss' href='".AW_APPOINTMENT_PLUGIN_URL."'></a></td></tr>"; 	  	
}  
  ?>
  </tbody>
  </table>
  </div>
  
  
  </div>
    
  <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
  </form>