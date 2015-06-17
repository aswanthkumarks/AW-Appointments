<?php
$res=$awobj->load_appointments(1);
?>

<div class="aw_pagination_no">Showing result from <?php echo $res['f']+1; ?> to <?php echo $res['c']+$res['f']; ?></div>
<div class="aw_appointmenttab">
<?php 
$awobj->appointment_list($res);
?>

</div>
<?php 
$awobj->load_pagination($res['f'],$res['c'],$res['no'],1);
?>
