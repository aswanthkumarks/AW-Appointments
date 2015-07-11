(function($){
					var ajaxurl=aw_app.ajaxurl;
		
		
					function DisableSpecificDates(date) {
						var disweek = aw_app.disweek;
						var disabledate = aw_app.disdate;
						var off=false;
		
						var m = date.getMonth();
						var d = date.getDate();
						var y = date.getFullYear();
		
						
						var dnow=(m+1)+'-'+d+'-'+y;
						if ($.inArray(dnow, disabledate) !== -1 ) {
							off=true;
						 	return [false];
						}
						
						if(!off){	 
						 var day = date.getDay();
						 if ($.inArray(day,disweek) !== -1) {				 
						 	return [false] ;				 
						 }
						 else {					 			 				 
						  return [true] ;
						 }
						}
					 
					}
					
		    		$( ".aw-date" ).datepicker({
		    			 beforeShowDay: DisableSpecificDates,
		    			 minDate: new Date(aw_app.apmin),
		    			 maxDate: new Date(aw_app.apmax),
		    			 onSelect: function(d,i){
		    		          if(d !== i.lastVal){
		    		              $(this).change();
		    		          }
		    		     },
		    		 });
		
		      		
		
		      		 $(".aw-input").change(function(){
		      			var inputval=$(this).val();
		      			var valid=validateform($(this),inputval);       		
		          		if(valid){
		              		if($(this).hasClass('aw-date')){
		              			var d = new Date(inputval);
		              			var n = d.getDay();
		              			$('.aw-week').addClass('aw-hideday');
		              			$('#aw-slot-'+n).removeClass('aw-hideday');
		              			$('.aw-week input').removeAttr('checked');
		                  	}              		
		          			$(this).removeClass("aw-error");
		              		}
		          		else{
		          			$(this).addClass("aw-error");
		              		}
		          	});
		           	$('.aw-slot input').click(function(){
		               	$(this).closest('.aw-weeks').removeClass("aw-error");
		
		            });
		
		            $('.aw-btn-active').click(function(){
		               
		                var formvalid=true;
		            	var re = /^\d{1,2}\/\d{1,2}\/\d{4}$/;
		            	var obj=$(this).closest('.aw-appointment');
		            	var data=obj.find('form').serializeArray();
		            	var postdata='{ "action" : "save_appointment"';
		            	$.each(data,function(k,v){
		                	var valid=validateform(obj.find('[name='+v.name+']'),v.value);               	         		
		              		if(valid){
		              			obj.find('[name='+v.name+']').removeClass("aw-error");
		                  		}
		              		else{
		              			obj.find('[name='+v.name+']').addClass("aw-error");
		              			formvalid=false;
		                  		}
		              		postdata+=',"'+v.name+'" : "'+ v.value + '"';
		              		             		
		                	});
		            	postdata+='}';
		            	postdata=JSON.parse(postdata);
		            	
		            	if(typeof obj.find('[type="radio"]:checked').val() == 'undefined'){
		            		obj.find('.aw-weeks').addClass('aw-error');
		            		formvalid=false;
		                }
		            	
		            	if(formvalid){
		            		$(this).removeClass('aw-btn-active');
		            		$(this).addClass('aw-btn-deactive');
		            		                	
		            		$.post(ajaxurl, postdata, function(response) {
		                		
		            			if(response.status){
		            				
		            			}
		            			aw_showmsg(obj,response.msg);
		            			
		            		}).fail(function(response) {
		            			
		          		  }).always(function() {
			          		  			          		  
		          			changecaptcha(obj.find('.captcha'));
		          			var btn=obj.find('.aw-button');
	            			btn.removeClass('aw-btn-deactive');
	            			btn.addClass('aw-btn-active');
		          		});
		                
		                
		            	}
		            });
		
		            function aw_showmsg(obj,msg){
			            obj=obj.find('.aw-msg');
			            if(msg.indexOf("class='alert-success'")!==-1){
			            	obj.addClass('aw-success');
			            	obj.closest('.aw-appointment').find('input[type=text],input[type=email],input[type=tel], textarea').val("");
				         }
			            else{
			            	obj.removeClass('aw-success');
			            }		
		                obj.html(msg);
		                obj.addClass('aw-shown');		
		             }
		
		
		                   
		
		            function validateform(obj,inputval){
		            	if(obj.hasClass('aw-date')){              		
		          			var re = /^\d{1,2}\/\d{1,2}\/\d{4}$/;
		          			if(!inputval.match(re)){
		          				valid=false;             	
		                    }
		            		else{
		            			valid=true;            			
		                    }
		              	}
		              		else if(obj.hasClass('aw-name')){
		                  		if(inputval.length<4){ valid=false; }
		                  		else{ valid=true; }
		                  	}
		              		else if(obj.hasClass('aw-email')){
		              			var eregx = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		                    	if(!eregx.test(inputval)){ valid=false; }
		                  		else{ valid=true; }
		                  	}
		              		else if(obj.hasClass('aw-phone')){
		                  		var preg=/^\+?([0-9]{2,3})\)?[-. ]?([0-9]{3,4})[-. ]?([0-9]{4,7})$/;
		                  		var preg1=/^\(?([0-9]{3,4})\)?[-. ]?([0-9]{4,7})$/;
		                  		var preg2=/^\(?([0-9]{2,3})\)?[-. ]?([0-9]{3,4})[-. ]?([0-9]{4,7})$/;
		                  		var preg3=/^\d{9,10}$/;
		                  		if(preg.test(inputval)||preg1.test(inputval)||preg2.test(inputval)||preg3.test(inputval)){
		                      		valid=true;
		                      	}
		                  		else{ valid=false; }
		                  	}
		              		else if(obj.hasClass('aw-city')){
		              			if(inputval.length<3) valid=false;
		              			else valid=true;
		                  	}
		              		else if(obj.hasClass('aw-country')){
		              			if(inputval.length<3) valid=false;
		              			else valid=true;
		                  	}
		              		else if(obj.hasClass('aw-address')){
		              			if(inputval.length<5||inputval.length>200) valid=false;
		              			else valid=true;
		                  	}
		              		else if(obj.hasClass('aw-captcha')){
		              			if(inputval.length<4) valid=false;
		              			else valid=true;
		                  	}
		              		else if(obj.hasClass('aw-skypeid')){
		              			if(inputval.length<3) valid=false;
		              			else valid=true;
		                  	}
		
		              	return valid;
		
		            }

		            $('.aw-refresh').click(function(){
		            	changecaptcha($(this).closest('.captcha'));
			         });

		            function changecaptcha(obj){
		            	var cap=obj.find('.aw-captchaimg');
		            	var src=cap.attr('src');
		            	src=src.substring(0, src.indexOf('?'));
			            src=src+'?var='+obj.find('.aw-captchavar').val()+'&r='+(Math.floor(Math.random()*90000) + 10000);
			            cap.attr('src',src);

			           }
		     		 
				})(jQuery);