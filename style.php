<style type="text/css">
.aw-appointment{
	border:1px solid #ccc;
}
.aw-appointment .aw-msg{
	font-size:12px;
}
.aw-appointment .aw-msg li{
	padding:0 5px; 
	list-style:none;
}
.aw-appointment .aw-msg.aw-shown{
	border:1px solid #ff0000;
	padding:5px;
}
.aw-appointment .aw-msg.aw-shown.aw-success{
border:1px solid #00a549;
}
.aw-appointment .aw-msg.aw-shown li{
	color:#ff0000;	
}
.aw-appointment .aw-msg.aw-shown.aw-success li{
color:#00a549;	
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

.aw-appointment input.aw-input,.aw-appointment textarea.aw-input{
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