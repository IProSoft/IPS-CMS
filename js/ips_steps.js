(function( $ ){
	$.fn.IPSsteps = function( options ) {
		var settings = $.extend( {
			steps_wrap		: '#ui-progressbar li',
			steps_buttons	: '.ui-progress-scroller',
			current_step	: 1,
			animating		: false,
			max_step		: 0,
			end_function	: function(){}
		}, options );
		
		var plugin = this;
		plugin.end = function() {
			settings.end_function
		}
		plugin.reload_buttons = function() {
			if( settings.current_step <= 1 )
			{
				$(settings.steps_buttons + ".ui-previous").fadeOut( 500 );
			}
			else if( settings.current_step >= settings.max_step )
			{
				$(settings.steps_buttons + ".ui-next").fadeOut( 300, function(){
					$(settings.steps_buttons + ".ui-end").fadeIn();
				});
			}
			else if( settings.current_step < settings.max_step && $(settings.steps_buttons + ".ui-end:visible").length > 0 )
			{
				$(settings.steps_buttons + ".ui-end").fadeOut( 300, function(){
					$(settings.steps_buttons + ".ui-next").fadeIn();
				});
			}
			else if( settings.current_step < settings.max_step && $(settings.steps_buttons + ".ui-previous:visible").length == 0 )
			{
				$(settings.steps_buttons + ".ui-previous").fadeIn( 500 );
			}
		};
		
		plugin.change = function( arguments ) {
			
			if( settings.animating )
			{
				return false;
			}
			if( arguments.end )
			{
				return plugin.end();
			}
			settings.animating = true;
			
			var current_fs = $('div[data-step="' + settings.current_step + '"]');
			var to_show = arguments.next ? current_fs.next() : current_fs.prev();
			
			if( arguments.prev )
			{
				$( settings.steps_wrap ).eq($("div.ui-step-wrapper").index(current_fs)).removeClass("active");
			}
			else
			{
				$( settings.steps_wrap ).eq($("div.ui-step-wrapper").index(to_show)).addClass("active");
			}
			to_show.show(); 
			
			current_fs.animate({opacity: 0}, {
				step: function(now, mx) {
					scale = 0.8 + (1 - now) * 0.2;
					left = ((1-now) * 50)+"%";
					opacity = 1 - now;
					current_fs.css({'left': left});
					to_show.css({'transform': 'scale('+scale+')', 'opacity': opacity});
				}, 
				duration: 800, 
				complete: function(){
					current_fs.hide();
					current_fs.css({'left': 0});
					settings.animating = false;
				}, 
				easing: 'easeInOutBack'
			});
			settings.current_step = arguments.next ? settings.current_step + 1 : settings.current_step - 1;
			plugin.reload_buttons();
		}
		
		
		if( settings.max_step == 0 )
		{
			settings.max_step = $( settings.steps_wrap ).length;
		}
		
		return this.each(function() {
			$(this).find( settings.steps_wrap ).each(function() {
				$(this).on( 'click', function(){
					return methods['change_progressbar'].apply( this, arguments );
				});
			});
			$(this).find( settings.steps_buttons ).each(function() {
				$(this).on( 'click', function(){
					var arguments = {
						prev	: $(this).hasClass('ui-previous'),
						next	: $(this).hasClass('ui-next'),
						end		: $(this).hasClass('ui-end'),
					}
					return plugin.change( arguments );
				});
			});
		});
	};	
})(jQuery);
jQuery.easing.jswing=jQuery.easing.swing;jQuery.extend(jQuery.easing,{def:"easeOutQuad",swing:function(e,f,a,h,g){return jQuery.easing[jQuery.easing.def](e,f,a,h,g)},easeInQuad:function(e,f,a,h,g){return h*(f/=g)*f+a},easeOutQuad:function(e,f,a,h,g){return -h*(f/=g)*(f-2)+a},easeInOutQuad:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f+a}return -h/2*((--f)*(f-2)-1)+a},easeInCubic:function(e,f,a,h,g){return h*(f/=g)*f*f+a},easeOutCubic:function(e,f,a,h,g){return h*((f=f/g-1)*f*f+1)+a},easeInOutCubic:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f*f+a}return h/2*((f-=2)*f*f+2)+a},easeInQuart:function(e,f,a,h,g){return h*(f/=g)*f*f*f+a},easeOutQuart:function(e,f,a,h,g){return -h*((f=f/g-1)*f*f*f-1)+a},easeInOutQuart:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f*f*f+a}return -h/2*((f-=2)*f*f*f-2)+a},easeInQuint:function(e,f,a,h,g){return h*(f/=g)*f*f*f*f+a},easeOutQuint:function(e,f,a,h,g){return h*((f=f/g-1)*f*f*f*f+1)+a},easeInOutQuint:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f*f*f*f+a}return h/2*((f-=2)*f*f*f*f+2)+a},easeInSine:function(e,f,a,h,g){return -h*Math.cos(f/g*(Math.PI/2))+h+a},easeOutSine:function(e,f,a,h,g){return h*Math.sin(f/g*(Math.PI/2))+a},easeInOutSine:function(e,f,a,h,g){return -h/2*(Math.cos(Math.PI*f/g)-1)+a},easeInExpo:function(e,f,a,h,g){return(f==0)?a:h*Math.pow(2,10*(f/g-1))+a},easeOutExpo:function(e,f,a,h,g){return(f==g)?a+h:h*(-Math.pow(2,-10*f/g)+1)+a},easeInOutExpo:function(e,f,a,h,g){if(f==0){return a}if(f==g){return a+h}if((f/=g/2)<1){return h/2*Math.pow(2,10*(f-1))+a}return h/2*(-Math.pow(2,-10*--f)+2)+a},easeInCirc:function(e,f,a,h,g){return -h*(Math.sqrt(1-(f/=g)*f)-1)+a},easeOutCirc:function(e,f,a,h,g){return h*Math.sqrt(1-(f=f/g-1)*f)+a},easeInOutCirc:function(e,f,a,h,g){if((f/=g/2)<1){return -h/2*(Math.sqrt(1-f*f)-1)+a}return h/2*(Math.sqrt(1-(f-=2)*f)+1)+a},easeInElastic:function(f,h,e,l,k){var i=1.70158;var j=0;var g=l;if(h==0){return e}if((h/=k)==1){return e+l}if(!j){j=k*0.3}if(g<Math.abs(l)){g=l;var i=j/4}else{var i=j/(2*Math.PI)*Math.asin(l/g)}return -(g*Math.pow(2,10*(h-=1))*Math.sin((h*k-i)*(2*Math.PI)/j))+e},easeOutElastic:function(f,h,e,l,k){var i=1.70158;var j=0;var g=l;if(h==0){return e}if((h/=k)==1){return e+l}if(!j){j=k*0.3}if(g<Math.abs(l)){g=l;var i=j/4}else{var i=j/(2*Math.PI)*Math.asin(l/g)}return g*Math.pow(2,-10*h)*Math.sin((h*k-i)*(2*Math.PI)/j)+l+e},easeInOutElastic:function(f,h,e,l,k){var i=1.70158;var j=0;var g=l;if(h==0){return e}if((h/=k/2)==2){return e+l}if(!j){j=k*(0.3*1.5)}if(g<Math.abs(l)){g=l;var i=j/4}else{var i=j/(2*Math.PI)*Math.asin(l/g)}if(h<1){return -0.5*(g*Math.pow(2,10*(h-=1))*Math.sin((h*k-i)*(2*Math.PI)/j))+e}return g*Math.pow(2,-10*(h-=1))*Math.sin((h*k-i)*(2*Math.PI)/j)*0.5+l+e},easeInBack:function(e,f,a,i,h,g){if(g==undefined){g=1.70158}return i*(f/=h)*f*((g+1)*f-g)+a},easeOutBack:function(e,f,a,i,h,g){if(g==undefined){g=1.70158}return i*((f=f/h-1)*f*((g+1)*f+g)+1)+a},easeInOutBack:function(e,f,a,i,h,g){if(g==undefined){g=1.70158}if((f/=h/2)<1){return i/2*(f*f*(((g*=(1.525))+1)*f-g))+a}return i/2*((f-=2)*f*(((g*=(1.525))+1)*f+g)+2)+a},easeInBounce:function(e,f,a,h,g){return h-jQuery.easing.easeOutBounce(e,g-f,0,h,g)+a},easeOutBounce:function(e,f,a,h,g){if((f/=g)<(1/2.75)){return h*(7.5625*f*f)+a}else{if(f<(2/2.75)){return h*(7.5625*(f-=(1.5/2.75))*f+0.75)+a}else{if(f<(2.5/2.75)){return h*(7.5625*(f-=(2.25/2.75))*f+0.9375)+a}else{return h*(7.5625*(f-=(2.625/2.75))*f+0.984375)+a}}}},easeInOutBounce:function(e,f,a,h,g){if(f<g/2){return jQuery.easing.easeInBounce(e,f*2,0,h,g)*0.5+a}return jQuery.easing.easeOutBounce(e,f*2-g,0,h,g)*0.5+h*0.5+a}});