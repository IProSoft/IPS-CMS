(function(factory){
  'use strict';
  if (typeof define === 'function' && define.amd) {
    define(['jquery'], factory);
  } else {
    factory($);
  }
})(function ($){
  'use strict';
	var Idle = function(  ) {
		this.popup = "#widget-idle-popup";
		this.isShown = 0;
		this.time = ips_user.idle_time * 60 * 1000;
		this.bind();
		return this;
	};

	 // Idle methods and shared properties
	Idle.prototype = {
		// Reset constructor - http://goo.gl/EcWdiy
		constructor:  Idle,
		countdown: function() {
			clearTimeout(this.timer);
			var b = this;
			this.timer = setTimeout(function() {
				
				if( b.isShown == 0 )
				{
					b.showPopup()
				}
			}, this.time )
		},
		bind: function() {
			this.countdown();
			var c = this;
			
			setTimeout(function() {
				$(window).on(ips_click + ' scroll keyup', function(){
					$(c.popup).hide();
					c.isShown = 0;
					c.countdown();
				});
			}, this.time/2 );
		},
		showPopup: function() {
			$(this.popup).show();
		},
	}
	new Idle().countdown();
});


