
/**
 * |-----------------|
 * | jQuery-Clickout |
 * |-----------------|
 *  jQuery-Clickout is freely distributable under the MIT license.
 *
 *  <a href="https://github.com/chalbert/Backbone-Elements">More details & documentation</a>
 *
 * @author Nicolas Gilbert
 *
 * @requires jQuery
 */

(function(factory){
  'use strict';

  if (typeof define === 'function' && define.amd) {
    define(['jquery'], factory);
  } else {
    factory($);
  }

})(function ($){
  'use strict';

     /**
      * A static counter is tied to the doc element to track click-out registration
      * @static
      */
  var counter = 0,

     /**
      * On mobile Touch browsers, 'click' are not triggered on every element.
      * Touchstart is.
      * @static
      */
      is_touch_device = 'ontouchstart' in document.documentElement,
	  click = is_touch_device ? 'touchstart' : 'mousedown';


  /**
   * Shortcut for .on('clickout')
   *
   * @param data
   * @param fn
   */

  $.fn.clickout = function(data, fn) {
    if (!fn) {
      fn = data;
      data = null;
    }

    if (arguments.length > 0) {
      this.on('clickout', data, fn);
    } else {
      return this.trigger('clickout');
    }

  };

  /**
   * Implements the 'special' jQuery event interface
   * Native way to add non-conventional events
   */
  jQuery.event.special.clickout = {

    /**
     * When the event is added
     * @param handleObj Event handler
     */

    add: function(handleObj){
      counter++;

      // Add counter to element
      var target = handleObj.selector
          ? $(this).find(handleObj.selector)
          : $(this);
      target.attr('data-clickout', counter);
		
      // When the click is inside, extend the Event object to mark it as so
      $(this).on(click + '.clickout' + counter, handleObj.selector, function(e){
        e.originalEvent.clickin = $(this).attr('data-clickout');
      });

      // Bind a click event to the document, to be cought after bubbling
      $(document).bind(click + '.clickout' + counter, (function(id){
      // A closure is used create a static id
        return function(e){
          // If the click is not inside the element, call the callback
          if (!(e.target.clickin && e.target.clickin == id) && $(handleObj.selector ? handleObj.selector : target).is(":visible")) {
            handleObj.handler.apply(this, arguments);
          }
        };
      })(counter));
    },

    /**
     * When the event is removed
     * @param handleObj Event handler
     */
    remove: function(handleObj) {
      var target = handleObj.selector
          ? $(this).find(handleObj.selector)
          : $(this)
        , id = target.attr('data-clickout');

      target.removeAttr('data-clickout');

      $(document).unbind(click + '.clickout' + id);
      $(this).off(click + '.clickout' + id, handleObj.selector);
      return $(this);
    }
  };

  return $;

});


/*!
 * jQuery Cookie Plugin v1.4.1
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2006, 2014 Klaus Hartl
 * Released under the MIT license
 */
(function (factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD
		define(['jquery'], factory);
	} else if (typeof exports === 'object') {
		// CommonJS
		factory(require('jquery'));
	} else {
		// Browser globals
		factory(jQuery);
	}
}(function ($) {

	var pluses = /\+/g;

	function encode(s) {
		return config.raw ? s : encodeURIComponent(s);
	}

	function decode(s) {
		return config.raw ? s : decodeURIComponent(s);
	}

	function stringifyCookieValue(value) {
		return encode(config.json ? JSON.stringify(value) : String(value));
	}

	function parseCookieValue(s) {
		if (s.indexOf('"') === 0) {
			// This is a quoted cookie as according to RFC2068, unescape...
			s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
		}

		try {
			// Replace server-side written pluses with spaces.
			// If we can't decode the cookie, ignore it, it's unusable.
			// If we can't parse the cookie, ignore it, it's unusable.
			s = decodeURIComponent(s.replace(pluses, ' '));
			return config.json ? JSON.parse(s) : s;
		} catch(e) {}
	}

	function read(s, converter) {
		var value = config.raw ? s : parseCookieValue(s);
		return $.isFunction(converter) ? converter(value) : value;
	}

	var config = $.cookie = function (key, value, options) {

		// Write

		if (arguments.length > 1 && !$.isFunction(value)) {
			options = $.extend({}, config.defaults, options);

			if ( typeof options.expires == 'string' && options.expires.match(/^[+-]?[0-9]+[ywdhms]$/) !== null ) 
			{
				var match = options.expires.match(/^([+-]?[0-9]+)([ywdhms])$/);
				/* EDITED PART IPS*/
				var time = parseInt( match[1], 10) * (({
					"y": (60 * 60 * 24 * 365),
					"w": (60 * 60 * 24 * 7),
					"d": (60 * 60 * 24),
					"h": (60 * 60),
					"m": (60),
					"s": (1)
				}[match[2]]) || 0 );
				t = options.expires = new Date();
				t.setTime( t.getTime() + (time * 1000) );
			}
			else if (typeof options.expires === 'number')
			{
				var days = options.expires, 
				t = options.expires = new Date();
				t.setTime(+t + days * 864e+5);
			}

			return (document.cookie = [
				encode(key), '=', stringifyCookieValue(value),
				options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
				options.path    ? '; path=' + options.path : '',
				options.domain  ? '; domain=' + options.domain : '',
				options.secure  ? '; secure' : ''
			].join(''));
		}

		// Read

		var result = key ? undefined : {};

		// To prevent the for loop in the first place assign an empty array
		// in case there are no cookies at all. Also prevents odd result when
		// calling $.cookie().
		var cookies = document.cookie ? document.cookie.split('; ') : [];

		for (var i = 0, l = cookies.length; i < l; i++) {
			var parts = cookies[i].split('=');
			var name = decode(parts.shift());
			var cookie = parts.join('=');

			if (key && key === name) {
				// If second argument (value) is a function it's a converter...
				result = read(cookie, value);
				break;
			}

			// Prevent storing a cookie that we couldn't decode.
			if (!key && (cookie = read(cookie)) !== undefined) {
				result[name] = cookie;
			}
		}

		return result;
	};

	config.defaults = {};

	$.removeCookie = function (key, options) {
		if ($.cookie(key) === undefined) {
			return false;
		}

		// Must not alter options, thus extending a fresh object...
		$.cookie(key, '', $.extend({}, options, { expires: -1 }));
		return !$.cookie(key);
	};

}));



/*!
 * jQuery JSON
 * Released under the MIT license
 */
(function (factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD
		define(['jquery'], factory);
	} else if (typeof exports === 'object') {
		// CommonJS
		factory(require('jquery'));
	} else {
		// Browser globals
		factory(jQuery);
	}
}(function ($) {

	var m = {
		'\b': '\\b',
		'\t': '\\t',
		'\n': '\\n',
		'\f': '\\f',
		'\r': '\\r',
		'"' : '\\"',
		'\\': '\\\\'
	},
	s = {
		'array': function (x) {
			var a = ['['], b, f, i, l = x.length, v;
			for (i = 0; i < l; i += 1) {
				v = x[i];
				f = s[typeof v];
				if (f) {
					v = f(v);
					if (typeof v == 'string') {
						if (b) {
							a[a.length] = ',';
						}
						a[a.length] = v;
						b = true;
					}
				}
			}
			a[a.length] = ']';
			return a.join('');
		},
		'boolean': function (x) {
			return String(x);
		},
		'null': function (x) {
			return "null";
		},
		'number': function (x) {
			return isFinite(x) ? String(x) : 'null';
		},
		'object': function (x) {
			if (x) {
				if (x instanceof Array) {
					return s.array(x);
				}
				var a = ['{'], b, f, i, v;
				for (i in x) {
					v = x[i];
					f = s[typeof v];
					if (f) {
						v = f(v);
						if (typeof v == 'string') {
							if (b) {
								a[a.length] = ',';
							}
							a.push(s.string(i), ':', v);
							b = true;
						}
					}
				}
				a[a.length] = '}';
				return a.join('');
			}
			return 'null';
		},
		'string': function (x) {
			if (/["\\\x00-\x1f]/.test(x)) {
				x = x.replace(/([\x00-\x1f\\"])/g, function(a, b) {
					var c = m[b];
					if (c) {
						return c;
					}
					c = b.charCodeAt();
					return '\\u00' +
						Math.floor(c / 16).toString(16) +
						(c % 16).toString(16);
				});
			}
			return '"' + x + '"';
		}
	};

	$.toJSON = function(v) {
		var f = isNaN(v) ? s[typeof v] : s['number'];
		if (f) return f(v);
	};
	
	$.parseJSON = function(v, safe) {
		if (safe === undefined) safe = $.parseJSON.safe;
		if (safe && !/^("(\\.|[^"\\\n\r])*?"|[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t])+?$/.test(v))
			return undefined;
		return eval('('+v+')');
	};
	
	$.parseJSON.safe = false;

}));


/*!
 * jQuery lockfixed plugin
 * http://www.directlyrics.com/code/lockfixed/
 *
 * Copyright 2012 Yvo Schaap
 * Released under the MIT license
 * http://www.directlyrics.com/code/lockfixed/license.txt
 *
 * Date: Sun March 4 2014 12:00:01 GMT
 */
(function($, undefined){
	$.fn.extend({
		/**
		 * Lockfixed initiated
		 * @param {Element} el - a jquery element, DOM node or selector string
		 * @param {Object} config - offset - forcemargin
		 */
		"lockfixed": function(config){
			if (config && config.offset) {
				config.offset.bottom = parseInt(config.offset.bottom,10);
				config.offset.top = parseInt(config.offset.top,10);
			}else{
				config.offset = {bottom: 100, top: 50};	
			}
			
			var el = $(this);
			if(el && el.offset()){
				var el_position = el.css("position"),
					el_margin_top = parseInt(el.css("marginTop"),10),
					el_position_top = el.css("top"),
					el_top = el.offset().top,
					pos_not_fixed = false;
				
				/* 
				 * We prefer feature testing, too much hassle for the upside 
				 * while prettier to use position: fixed (less jitter when scrolling)
				 * iOS 5+ + Android has fixed support, but issue with toggeling between fixed and not and zoomed view
				 */
				if (config.forcemargin === true || navigator.userAgent.match(/\bMSIE (4|5|6)\./) || navigator.userAgent.match(/\bOS ([0-9])_/) || navigator.userAgent.match(/\bAndroid ([0-9])\./i)){
					pos_not_fixed = true;
				}
				$(window).bind('DOMContentLoaded load scroll resize orientationchange lockfixed:pageupdate',el,function(e){
					// if we have a input focus don't change this (for smaller screens)
					if(pos_not_fixed && document.activeElement && document.activeElement.nodeName === "INPUT"){
						return;	
					}
					

					var top = 0,
						el_height = el.outerHeight(),
						max_height = $(document).height() - config.offset.bottom,
						scroll_top = $(window).scrollTop();
 
					if (el.css("position") !== "fixed" && !pos_not_fixed) {
						el_top = el.offset().top;
						el_position_top = el.css("top");
					}

					if (scroll_top >= (el_top-(el_margin_top ? el_margin_top : 0)-config.offset.top)){

						if(max_height < (scroll_top + el_height + el_margin_top + config.offset.top)){
							top = (scroll_top + el_height + el_margin_top + config.offset.top) - max_height;
						}else{
							top = 0;	
						}

						if (pos_not_fixed){
							el.css({'marginTop': (parseInt(scroll_top - el_top - top,10) + (2 * config.offset.top))+'px'}).removeClass('is-fixed');
						}else{
							el.css({'position': 'fixed','top':(config.offset.top-top)+'px'}).addClass('is-fixed');
						}
					}else{
						el.css({'position': el_position,'top': el_position_top, 'marginTop': (el_margin_top && !pos_not_fixed ? el_margin_top : 0)+"px"}).removeClass('is-fixed');
					}
					if( el.hasClass('hidden-opacity') )
					{
						el.removeClass('hidden-opacity')
					}
				});	
			}
		}
	});
})(jQuery);
/**
 * |-----------------|
 * | jQuery-IPS_Menus|
 * |-----------------|
 *
 * @author IPS
 *
 * @requires jQuery
 */

(function(factory){
  'use strict';

  if (typeof define === 'function' && define.amd) {
    define(['jquery'], factory);
  } else {
    factory($);
  }

})(function ($){
  'use strict';
	var IPS_Menus = function( responsive_icon ) {
		this.responsive_icon = responsive_icon,
		this.responsive_ui = this.responsive_icon.parent('.responsive-ui'),
		this.active = this.responsive_ui.hasClass('ui-active');
		return this;
	};

	 // IPS_Menus methods and shared properties
	IPS_Menus.prototype = {
		// Reset constructor - http://goo.gl/EcWdiy
		constructor:  IPS_Menus,
		init:  function() {
			
			$(".fancy-menu.responsive-slide").hide().off('clickout').parents('.responsive-ui').removeClass('ui-active');
			
			var responsive = this.responsive_ui.find('.responsive-slide');
			
			if( !this.active )
			{
				responsive.css({
					left: this.responsive_ui.offset().left,
					top: this.responsive_ui.offset().top - $(window).scrollTop() + ( this.responsive_ui.height() * 1.1 )
				})
				
				this.responsive_ui.clickout(function(e){
					if( $(e.target).parents('.responsive-ui').length == 0 )
					{	
						responsive.parents('.ui-active').removeClass('ui-active');
						responsive.hide().off('clickout');
					}
				});

				if( ips_config.version == 'kwejk' && responsive.hasClass('base-user-ui') )
				{
					responsive.css( { left : -responsive.width()/2 + this.responsive_icon.width()/2 } );
				}
				
				this.responsive_ui.addClass('ui-active');
				
				responsive.show();
			}
		},
		closeAll: function() {
			
		}
	}

	
	$.fn.IpsMenu = function() {
		return new IPS_Menus( $(this) ).init()
	};
	
	// Expose defaults and Constructor (allowing overriding of prototype methods for example)
	$.fn.IpsMenu.IPS_Menus = IPS_Menus;
});

/**
 * author Christopher Blum
 *    - based on the idea of Remy Sharp, http://remysharp.com/2009/01/26/element-in-view-event-plugin/
 *    - forked from http://github.com/zuk/jquery.inview/
 */
(function ($) {
  var inviewObjects = {}, viewportSize, viewportOffset,
      d = document, w = window, documentElement = d.documentElement, expando = $.expando, timer;

  $.event.special.inview = {
    add: function(data) {
      inviewObjects[data.guid + "-" + this[expando]] = { data: data, $element: $(this) };

      // Use setInterval in order to also make sure this captures elements within
      // "overflow:scroll" elements or elements that appeared in the dom tree due to
      // dom manipulation and reflow
      // old: $(window).scroll(checkInView);
      //
      // By the way, iOS (iPad, iPhone, ...) seems to not execute, or at least delays
      // intervals while the user scrolls. Therefore the inview event might fire a bit late there
      //
      // Don't waste cycles with an interval until we get at least one element that
      // has bound to the inview event.
      if (!timer && !$.isEmptyObject(inviewObjects)) {
         timer = setInterval(checkInView, 250);
      }
    },

    remove: function(data) {
      try { delete inviewObjects[data.guid + "-" + this[expando]]; } catch(e) {}

      // Clear interval when we no longer have any elements listening
      if ($.isEmptyObject(inviewObjects)) {
         clearInterval(timer);
         timer = null;
      }
    }
  };

  function getViewportSize() {
    var mode, domObject, size = { height: w.innerHeight, width: w.innerWidth };

    // if this is correct then return it. iPad has compat Mode, so will
    // go into check clientHeight/clientWidth (which has the wrong value).
    if (!size.height) {
      mode = d.compatMode;
      if (mode || !$.support.boxModel) { // IE, Gecko
        domObject = mode === 'CSS1Compat' ?
          documentElement : // Standards
          d.body; // Quirks
        size = {
          height: domObject.clientHeight,
          width:  domObject.clientWidth
        };
      }
    }

    return size;
  }

  function getViewportOffset() {
    return {
      top:  w.pageYOffset || documentElement.scrollTop   || d.body.scrollTop,
      left: w.pageXOffset || documentElement.scrollLeft  || d.body.scrollLeft
    };
  }

  function checkInView() {
    var $elements = [], elementsLength, i = 0;

    $.each(inviewObjects, function(i, inviewObject) {
      var selector  = inviewObject.data.selector,
          $element  = inviewObject.$element;
      $elements.push(selector ? $element.find(selector) : $element);
    });

    elementsLength = $elements.length;
    if (elementsLength) {
      viewportSize   = viewportSize   || getViewportSize();
      viewportOffset = viewportOffset || getViewportOffset();

      for (; i<elementsLength; i++) {
        // Ignore elements that are not in the DOM tree
        if (!$.contains(documentElement, $elements[i][0])) {
          continue;
        }

        var $element      = $($elements[i]),
            elementSize   = { height: $element.height(), width: $element.width() },
            elementOffset = $element.offset(),
            inView        = $element.data('inview'),
            visiblePartX,
            visiblePartY,
            visiblePartsMerged;

        // Don't ask me why because I haven't figured out yet:
        // viewportOffset and viewportSize are sometimes suddenly null in Firefox 5.
        // Even though it sounds weird:
        // It seems that the execution of this function is interferred by the onresize/onscroll event
        // where viewportOffset and viewportSize are unset
        if (!viewportOffset || !viewportSize) {
          return;
        }

        if (elementOffset.top + elementSize.height > viewportOffset.top &&
            elementOffset.top < viewportOffset.top + viewportSize.height &&
            elementOffset.left + elementSize.width > viewportOffset.left &&
            elementOffset.left < viewportOffset.left + viewportSize.width) {
          visiblePartX = (viewportOffset.left > elementOffset.left ?
            'right' : (viewportOffset.left + viewportSize.width) < (elementOffset.left + elementSize.width) ?
            'left' : 'both');
          visiblePartY = (viewportOffset.top > elementOffset.top ?
            'bottom' : (viewportOffset.top + viewportSize.height) < (elementOffset.top + elementSize.height) ?
            'top' : 'both');
          visiblePartsMerged = visiblePartX + "-" + visiblePartY;
          if (!inView || inView !== visiblePartsMerged) {
            $element.data('inview', visiblePartsMerged).trigger('inview', [true, visiblePartX, visiblePartY]);
          }
        } else if (inView) {
          $element.data('inview', false).trigger('inview', [false]);
        }
      }
    }
  }

  $(w).bind("scroll resize scrollstop", function() {
    viewportSize = viewportOffset = null;
  });

  // IE < 9 scrolls to focused elements without firing the "scroll" event
  if (!documentElement.addEventListener && documentElement.attachEvent) {
    documentElement.attachEvent("onfocusin", function() {
      viewportOffset = null;
    });
  }
})(jQuery);

/*!
 * imagesLoaded PACKAGED v3.1.8
 * JavaScript is all like "You images are done yet or what?"
 * MIT License
 */
(function() {
    function e() {}

    function t(e, t) {
        for (var n = e.length; n--;)
            if (e[n].listener === t) return n;
        return -1
    }

    function n(e) {
        return function() {
            return this[e].apply(this, arguments)
        }
    }
    var i = e.prototype,
        r = this,
        o = r.EventEmitter;
    i.getListeners = function(e) {
        var t, n, i = this._getEvents();
        if ("object" == typeof e) {
            t = {};
            for (n in i) i.hasOwnProperty(n) && e.test(n) && (t[n] = i[n])
        } else t = i[e] || (i[e] = []);
        return t
    }, i.flattenListeners = function(e) {
        var t, n = [];
        for (t = 0; e.length > t; t += 1) n.push(e[t].listener);
        return n
    }, i.getListenersAsObject = function(e) {
        var t, n = this.getListeners(e);
        return n instanceof Array && (t = {}, t[e] = n), t || n
    }, i.addListener = function(e, n) {
        var i, r = this.getListenersAsObject(e),
            o = "object" == typeof n;
        for (i in r) r.hasOwnProperty(i) && -1 === t(r[i], n) && r[i].push(o ? n : {
            listener: n,
            once: !1
        });
        return this
    }, i.on = n("addListener"), i.addOnceListener = function(e, t) {
        return this.addListener(e, {
            listener: t,
            once: !0
        })
    }, i.once = n("addOnceListener"), i.defineEvent = function(e) {
        return this.getListeners(e), this
    }, i.defineEvents = function(e) {
        for (var t = 0; e.length > t; t += 1) this.defineEvent(e[t]);
        return this
    }, i.removeListener = function(e, n) {
        var i, r, o = this.getListenersAsObject(e);
        for (r in o) o.hasOwnProperty(r) && (i = t(o[r], n), -1 !== i && o[r].splice(i, 1));
        return this
    }, i.off = n("removeListener"), i.addListeners = function(e, t) {
        return this.manipulateListeners(!1, e, t)
    }, i.removeListeners = function(e, t) {
        return this.manipulateListeners(!0, e, t)
    }, i.manipulateListeners = function(e, t, n) {
        var i, r, o = e ? this.removeListener : this.addListener,
            s = e ? this.removeListeners : this.addListeners;
        if ("object" != typeof t || t instanceof RegExp)
            for (i = n.length; i--;) o.call(this, t, n[i]);
        else
            for (i in t) t.hasOwnProperty(i) && (r = t[i]) && ("function" == typeof r ? o.call(this, i, r) : s.call(this, i, r));
        return this
    }, i.removeEvent = function(e) {
        var t, n = typeof e,
            i = this._getEvents();
        if ("string" === n) delete i[e];
        else if ("object" === n)
            for (t in i) i.hasOwnProperty(t) && e.test(t) && delete i[t];
        else delete this._events;
        return this
    }, i.removeAllListeners = n("removeEvent"), i.emitEvent = function(e, t) {
        var n, i, r, o, s = this.getListenersAsObject(e);
        for (r in s)
            if (s.hasOwnProperty(r))
                for (i = s[r].length; i--;) n = s[r][i], n.once === !0 && this.removeListener(e, n.listener), o = n.listener.apply(this, t || []), o === this._getOnceReturnValue() && this.removeListener(e, n.listener);
        return this
    }, i.trigger = n("emitEvent"), i.emit = function(e) {
        var t = Array.prototype.slice.call(arguments, 1);
        return this.emitEvent(e, t)
    }, i.setOnceReturnValue = function(e) {
        return this._onceReturnValue = e, this
    }, i._getOnceReturnValue = function() {
        return this.hasOwnProperty("_onceReturnValue") ? this._onceReturnValue : !0
    }, i._getEvents = function() {
        return this._events || (this._events = {})
    }, e.noConflict = function() {
        return r.EventEmitter = o, e
    }, "function" == typeof define && define.amd ? define("eventEmitter/EventEmitter", [], function() {
        return e
    }) : "object" == typeof module && module.exports ? module.exports = e : this.EventEmitter = e
}).call(this),
    function(e) {
        function t(t) {
            var n = e.event;
            return n.target = n.target || n.srcElement || t, n
        }
        var n = document.documentElement,
            i = function() {};
        n.addEventListener ? i = function(e, t, n) {
            e.addEventListener(t, n, !1)
        } : n.attachEvent && (i = function(e, n, i) {
            e[n + i] = i.handleEvent ? function() {
                var n = t(e);
                i.handleEvent.call(i, n)
            } : function() {
                var n = t(e);
                i.call(e, n)
            }, e.attachEvent("on" + n, e[n + i])
        });
        var r = function() {};
        n.removeEventListener ? r = function(e, t, n) {
            e.removeEventListener(t, n, !1)
        } : n.detachEvent && (r = function(e, t, n) {
            e.detachEvent("on" + t, e[t + n]);
            try {
                delete e[t + n]
            } catch (i) {
                e[t + n] = void 0
            }
        });
        var o = {
            bind: i,
            unbind: r
        };
        "function" == typeof define && define.amd ? define("eventie/eventie", o) : e.eventie = o
    }(this),
    function(e, t) {
        "function" == typeof define && define.amd ? define(["eventEmitter/EventEmitter", "eventie/eventie"], function(n, i) {
            return t(e, n, i)
        }) : "object" == typeof exports ? module.exports = t(e, require("wolfy87-eventemitter"), require("eventie")) : e.imagesLoaded = t(e, e.EventEmitter, e.eventie)
    }(window, function(e, t, n) {
        function i(e, t) {
            for (var n in t) e[n] = t[n];
            return e
        }

        function r(e) {
            return "[object Array]" === d.call(e)
        }

        function o(e) {
            var t = [];
            if (r(e)) t = e;
            else if ("number" == typeof e.length)
                for (var n = 0, i = e.length; i > n; n++) t.push(e[n]);
            else t.push(e);
            return t
        }

        function s(e, t, n) {
            if (!(this instanceof s)) return new s(e, t);
            "string" == typeof e && (e = document.querySelectorAll(e)), this.elements = o(e), this.options = i({}, this.options), "function" == typeof t ? n = t : i(this.options, t), n && this.on("always", n), this.getImages(), a && (this.jqDeferred = new a.Deferred);
            var r = this;
            setTimeout(function() {
                r.check()
            })
        }

        function f(e) {
            this.img = e
        }

        function c(e) {
            this.src = e, v[e] = this
        }
        var a = e.jQuery,
            u = e.console,
            h = u !== void 0,
            d = Object.prototype.toString;
        s.prototype = new t, s.prototype.options = {}, s.prototype.getImages = function() {
            this.images = [];
            for (var e = 0, t = this.elements.length; t > e; e++) {
                var n = this.elements[e];
                "IMG" === n.nodeName && this.addImage(n);
                var i = n.nodeType;
                if (i && (1 === i || 9 === i || 11 === i))
                    for (var r = n.querySelectorAll("img"), o = 0, s = r.length; s > o; o++) {
                        var f = r[o];
                        this.addImage(f)
                    }
            }
        }, s.prototype.addImage = function(e) {
            var t = new f(e);
            this.images.push(t)
        }, s.prototype.check = function() {
            function e(e, r) {
                return t.options.debug && h && u.log("confirm", e, r), t.progress(e), n++, n === i && t.complete(), !0
            }
            var t = this,
                n = 0,
                i = this.images.length;
            if (this.hasAnyBroken = !1, !i) return this.complete(), void 0;
            for (var r = 0; i > r; r++) {
                var o = this.images[r];
                o.on("confirm", e), o.check()
            }
        }, s.prototype.progress = function(e) {
            this.hasAnyBroken = this.hasAnyBroken || !e.isLoaded;
            var t = this;
            setTimeout(function() {
                t.emit("progress", t, e), t.jqDeferred && t.jqDeferred.notify && t.jqDeferred.notify(t, e)
            })
        }, s.prototype.complete = function() {
            var e = this.hasAnyBroken ? "fail" : "done";
            this.isComplete = !0;
            var t = this;
            setTimeout(function() {
                if (t.emit(e, t), t.emit("always", t), t.jqDeferred) {
                    var n = t.hasAnyBroken ? "reject" : "resolve";
                    t.jqDeferred[n](t)
                }
            })
        }, a && (a.fn.imagesLoaded = function(e, t) {
            var n = new s(this, e, t);
            return n.jqDeferred.promise(a(this))
        }), f.prototype = new t, f.prototype.check = function() {
            var e = v[this.img.src] || new c(this.img.src);
            if (e.isConfirmed) return this.confirm(e.isLoaded, "cached was confirmed"), void 0;
            if (this.img.complete && void 0 !== this.img.naturalWidth) return this.confirm(0 !== this.img.naturalWidth, "naturalWidth"), void 0;
            var t = this;
            e.on("confirm", function(e, n) {
                return t.confirm(e.isLoaded, n), !0
            }), e.check()
        }, f.prototype.confirm = function(e, t) {
            this.isLoaded = e, this.emit("confirm", this, t)
        };
        var v = {};
        return c.prototype = new t, c.prototype.check = function() {
            if (!this.isChecked) {
                var e = new Image;
                n.bind(e, "load", this), n.bind(e, "error", this), e.src = this.src, this.isChecked = !0
            }
        }, c.prototype.handleEvent = function(e) {
            var t = "on" + e.type;
            this[t] && this[t](e)
        }, c.prototype.onload = function(e) {
            this.confirm(!0, "onload"), this.unbindProxyEvents(e)
        }, c.prototype.onerror = function(e) {
            this.confirm(!1, "onerror"), this.unbindProxyEvents(e)
        }, c.prototype.confirm = function(e, t) {
            this.isConfirmed = !0, this.isLoaded = e, this.emit("confirm", this, t)
        }, c.prototype.unbindProxyEvents = function(e) {
            n.unbind(e.target, "load", this), n.unbind(e.target, "error", this)
        }, s
    });

/*
CSS Browser Selector v0.4.0 (Nov 02, 2010)
Rafael Lima (http://rafael.adm.br)
http://rafael.adm.br/css_browser_selector
License: http://creativecommons.org/licenses/by/2.5/
Contributors: http://rafael.adm.br/css_browser_selector#contributors
*/

function css_browser_selector(u) {
    var ua = u.toLowerCase(),
        is = function(t) {
            return ua.indexOf(t) > -1
        },
        g = 'gecko',
        w = 'webkit',
        s = 'safari',
        o = 'opera',
        m = 'mobile',
        h = document.documentElement,
        b = [(!(/opera|webtv/i.test(ua)) && /msie\s(\d)/.test(ua)) ? ('ie ie' + RegExp.$1) : is('firefox/2') ? g + ' ff2' : is('firefox/3.5') ? g + ' ff3 ff3_5' : is('firefox/3.6') ? g + ' ff3 ff3_6' : is('firefox/3') ? g + ' ff3' : is('gecko/') ? g : is('opera') ? o + (/version\/(\d+)/.test(ua) ? ' ' + o + RegExp.$1 : (/opera(\s|\/)(\d+)/.test(ua) ? ' ' + o + RegExp.$2 : '')) : is('konqueror') ? 'konqueror' : is('blackberry') ? m + ' blackberry' : is('android') ? m + ' android' : is('chrome') ? w + ' chrome' : is('iron') ? w + ' iron' : is('applewebkit/') ? w + ' ' + s + (/version\/(\d+)/.test(ua) ? ' ' + s + RegExp.$1 : '') : is('mozilla/') ? g : '', is('j2me') ? m + ' j2me' : is('iphone') ? m + ' iphone' : is('ipod') ? m + ' ipod' : is('ipad') ? m + ' ipad' : is('mac') ? 'mac' : is('darwin') ? 'mac' : is('webtv') ? 'webtv' : is('win') ? 'win' + (is('windows nt 6.0') ? ' vista' : '') : is('freebsd') ? 'freebsd' : (is('x11') || is('linux')) ? 'linux' : '', 'js'];
    c = b.join(' ');
    h.className += ' ' + c;
    return c;
};


/*jQuery throttle / debounce*/
(function(b, c) {
    var $ = b.jQuery || b.Cowboy || (b.Cowboy = {}),
        a;
    $.throttle = a = function(e, f, j, i) {
        var h, d = 0;
        if (typeof f !== "boolean") {
            i = j;
            j = f;
            f = c
        }

        function g() {
            var o = this,
                m = +new Date() - d,
                n = arguments;

            function l() {
                d = +new Date();
                j.apply(o, n)
            }

            function k() {
                h = c
            }
            if (i && !h) {
                l()
            }
            h && clearTimeout(h);
            if (i === c && m > e) {
                l()
            } else {
                if (f !== true) {
                    h = setTimeout(i ? k : l, i === c ? e - m : e)
                }
            }
        }
        if ($.guid) {
            g.guid = j.guid = j.guid || $.guid++
        }
        return g
    };
    $.debounce = function(d, e, f) {
        return f === c ? a(d, e, false) : a(d, f, e !== false)
    }
})(this);

/* Return outerHTML for the first element in a jQuery object, or an empty string if the jQuery object is empty; */
$.fn.outerHTML = function() {
    return (this[0]) ? this[0].outerHTML : '';
};
/*! isOnScreen */

$.fn.isOnScreen = function(reserve) {
    var viewport = {};
    viewport.top = $(window).scrollTop();
    viewport.bottom = viewport.top + $(window).height();
    var bounds = {};
    bounds.top = this.offset().top;
    bounds.bottom = bounds.top + this.outerHeight();
    return ((bounds.top - ( typeof reserve != 'undefined' ? reserve : 0 ) <= viewport.bottom) && (bounds.bottom >= viewport.top));
};

/*! head.core - v1.0.2 */

(function(n, t) {
    "use strict";

    function r(n) {
        a[a.length] = n
    }

    function k(n) {
        var t = new RegExp(" ?\\b" + n + "\\b");
        c.className = c.className.replace(t, "")
    }

    function p(n, t) {
        for (var i = 0, r = n.length; i < r; i++) t.call(n, n[i], i)
    }

    function tt() {
        var t, e, f, o;
        c.className = c.className.replace(/ (w-|eq-|gt-|gte-|lt-|lte-|portrait|no-portrait|landscape|no-landscape)\d+/g, "");
        t = n.innerWidth || c.clientWidth;
        e = n.outerWidth || n.screen.width;
        u.screen.innerWidth = t;
        u.screen.outerWidth = e;
        r("w-" + t);
        p(i.screens, function(n) {
            t > n ? (i.screensCss.gt && r("gt-" + n), i.screensCss.gte && r("gte-" + n)) : t < n ? (i.screensCss.lt && r("lt-" + n), i.screensCss.lte && r("lte-" + n)) : t === n && (i.screensCss.lte && r("lte-" + n), i.screensCss.eq && r("e-q" + n), i.screensCss.gte && r("gte-" + n))
        });
        f = n.innerHeight || c.clientHeight;
        o = n.outerHeight || n.screen.height;
        u.screen.innerHeight = f;
        u.screen.outerHeight = o;
        u.feature("portrait", f > t);
        u.feature("landscape", f < t)
    }

    function it() {
        n.clearTimeout(b);
        b = n.setTimeout(tt, 50)
    }
    var y = n.document,
        rt = n.navigator,
        ut = n.location,
        c = y.documentElement,
        a = [],
        i = {
            screens: [240, 320, 480, 640, 768, 800, 1024, 1280, 1440, 1680, 1920],
            screensCss: {
                gt: !0,
                gte: !1,
                lt: !0,
                lte: !1,
                eq: !1
            },
            browsers: [{
                ie: {
                    min: 6,
                    max: 11
                }
            }],
            browserCss: {
                gt: !0,
                gte: !1,
                lt: !0,
                lte: !1,
                eq: !0
            },
            html5: !0,
            page: "-page",
            section: "-section",
            head: "head"
        },
        v, u, s, w, o, h, l, d, f, g, nt, e, b;
    if (n.head_conf)
        for (v in n.head_conf) n.head_conf[v] !== t && (i[v] = n.head_conf[v]);
    u = n[i.head] = function() {
        u.ready.apply(null, arguments)
    };
    u.feature = function(n, t, i) {
        return n ? (Object.prototype.toString.call(t) === "[object Function]" && (t = t.call()), r((t ? "" : "no-") + n), u[n] = !!t, i || (k("no-" + n), k(n), u.feature()), u) : (c.className += " " + a.join(" "), a = [], u)
    };
    u.feature("js", !0);
    s = rt.userAgent.toLowerCase();
    w = /mobile|android|kindle|silk|midp|phone|(windows .+arm|touch)/.test(s);
    u.feature("mobile", w, !0);
    u.feature("desktop", !w, !0);
    s = /(chrome|firefox)[ \/]([\w.]+)/.exec(s) || /(iphone|ipad|ipod)(?:.*version)?[ \/]([\w.]+)/.exec(s) || /(android)(?:.*version)?[ \/]([\w.]+)/.exec(s) || /(webkit|opera)(?:.*version)?[ \/]([\w.]+)/.exec(s) || /(msie) ([\w.]+)/.exec(s) || /(trident).+rv:(\w.)+/.exec(s) || [];
    o = s[1];
    h = parseFloat(s[2]);
    switch (o) {
        case "msie":
        case "trident":
            o = "ie";
            h = y.documentMode || h;
            break;
        case "firefox":
            o = "ff";
            break;
        case "ipod":
        case "ipad":
        case "iphone":
            o = "ios";
            break;
        case "webkit":
            o = "safari"
    }
    for (u.browser = {
            name: o,
            version: h
        }, u.browser[o] = !0, l = 0, d = i.browsers.length; l < d; l++)
        for (f in i.browsers[l])
            if (o === f)
                for (r(f), g = i.browsers[l][f].min, nt = i.browsers[l][f].max, e = g; e <= nt; e++) h > e ? (i.browserCss.gt && r("gt-" + f + e), i.browserCss.gte && r("gte-" + f + e)) : h < e ? (i.browserCss.lt && r("lt-" + f + e), i.browserCss.lte && r("lte-" + f + e)) : h === e && (i.browserCss.lte && r("lte-" + f + e), i.browserCss.eq && r("eq-" + f + e), i.browserCss.gte && r("gte-" + f + e));
            else r("no-" + f);
    r(o);
    r(o + parseInt(h, 10));
    i.html5 && o === "ie" && h < 9 && p("abbr|article|aside|audio|canvas|details|figcaption|figure|footer|header|hgroup|main|mark|meter|nav|output|progress|section|summary|time|video".split("|"), function(n) {
        y.createElement(n)
    });
    p(ut.pathname.split("/"), function(n, u) {
        if (this.length > 2 && this[u + 1] !== t) u && r(this.slice(u, u + 1).join("-").toLowerCase() + i.section);
        else {
            var f = n || "index",
                e = f.indexOf(".");
            e > 0 && (f = f.substring(0, e));
            c.id = f.toLowerCase() + i.page;
            u || r("root" + i.section)
        }
    });
    u.screen = {
        height: n.screen.height,
        width: n.screen.width
    };
    tt();
    b = 0;
    n.addEventListener ? n.addEventListener("resize", it, !1) : n.attachEvent("onresize", it)
})(window);

/*! head.css3 - v1.0.0 */

(function(n, t) {
    "use strict";

    function a(n) {
        for (var r in n)
            if (i[n[r]] !== t) return !0;
        return !1
    }

    function r(n) {
        var t = n.charAt(0).toUpperCase() + n.substr(1),
            i = (n + " " + c.join(t + " ") + t).split(" ");
        return !!a(i)
    }
    var h = n.document,
        o = h.createElement("i"),
        i = o.style,
        s = " -o- -moz- -ms- -webkit- -khtml- ".split(" "),
        c = "Webkit Moz O ms Khtml".split(" "),
        l = n.head_conf && n.head_conf.head || "head",
        u = n[l],
        f = {
            gradient: function() {
                var n = "background-image:";
                return i.cssText = (n + s.join("gradient(linear,left top,right bottom,from(#9f9),to(#fff));" + n) + s.join("linear-gradient(left top,#eee,#fff);" + n)).slice(0, -n.length), !!i.backgroundImage
            },
            rgba: function() {
                return i.cssText = "background-color:rgba(0,0,0,0.5)", !!i.backgroundColor
            },
            opacity: function() {
                return o.style.opacity === ""
            },
            textshadow: function() {
                return i.textShadow === ""
            },
            multiplebgs: function() {
                i.cssText = "background:url(https://),url(https://),red url(https://)";
                var n = (i.background || "").match(/url/g);
                return Object.prototype.toString.call(n) === "[object Array]" && n.length === 3
            },
            boxshadow: function() {
                return r("boxShadow")
            },
            borderimage: function() {
                return r("borderImage")
            },
            borderradius: function() {
                return r("borderRadius")
            },
            cssreflections: function() {
                return r("boxReflect")
            },
            csstransforms: function() {
                return r("transform")
            },
            csstransitions: function() {
                return r("transition")
            },
            touch: function() {
                return "ontouchstart" in n
            },
            retina: function() {
                return n.devicePixelRatio > 1
            },
            fontface: function() {
                var t = u.browser.name,
                    n = u.browser.version;
                switch (t) {
                    case "ie":
                        return n >= 9;
                    case "chrome":
                        return n >= 13;
                    case "ff":
                        return n >= 6;
                    case "ios":
                        return n >= 5;
                    case "android":
                        return !1;
                    case "webkit":
                        return n >= 5.1;
                    case "opera":
                        return n >= 10;
                    default:
                        return !1
                }
            }
        };
    for (var e in f) f[e] && u.feature(e, f[e].call(), !0);
    u.feature()
})(window);

/*! head.load - v1.0.3 */

(function(n, t) {
    "use strict";

    function w() {}

    function u(n, t) {
        if (n) {
            typeof n == "object" && (n = [].slice.call(n));
            for (var i = 0, r = n.length; i < r; i++) t.call(n, n[i], i)
        }
    }

    function it(n, i) {
        var r = Object.prototype.toString.call(i).slice(8, -1);
        return i !== t && i !== null && r === n
    }

    function s(n) {
        return it("Function", n)
    }

    function a(n) {
        return it("Array", n)
    }

    function et(n) {
        var i = n.split("/"),
            t = i[i.length - 1],
            r = t.indexOf("?");
        return r !== -1 ? t.substring(0, r) : t
    }

    function f(n) {
        (n = n || w, n._done) || (n(), n._done = 1)
    }

    function ot(n, t, r, u) {
        var f = typeof n == "object" ? n : {
                test: n,
                success: !t ? !1 : a(t) ? t : [t],
                failure: !r ? !1 : a(r) ? r : [r],
                callback: u || w
            },
            e = !!f.test;
        return e && !!f.success ? (f.success.push(f.callback), i.load.apply(null, f.success)) : e || !f.failure ? u() : (f.failure.push(f.callback), i.load.apply(null, f.failure)), i
    }

    function v(n) {
        var t = {},
            i, r;
        if (typeof n == "object")
            for (i in n) !n[i] || (t = {
                name: i,
                url: n[i]
            });
        else t = {
            name: et(n),
            url: n
        };
        return (r = c[t.name], r && r.url === t.url) ? r : (c[t.name] = t, t)
    }

    function y(n) {
        n = n || c;
        for (var t in n)
            if (n.hasOwnProperty(t) && n[t].state !== l) return !1;
        return !0
    }

    function st(n) {
        n.state = ft;
        u(n.onpreload, function(n) {
            n.call()
        })
    }

    function ht(n) {
        n.state === t && (n.state = nt, n.onpreload = [], rt({
            url: n.url,
            type: "cache"
        }, function() {
            st(n)
        }))
    }

    function ct() {
        var n = arguments,
            t = n[n.length - 1],
            r = [].slice.call(n, 1),
            f = r[0];
        return (s(t) || (t = null), a(n[0])) ? (n[0].push(t), i.load.apply(null, n[0]), i) : (f ? (u(r, function(n) {
            s(n) || !n || ht(v(n))
        }), b(v(n[0]), s(f) ? f : function() {
            i.load.apply(null, r)
        })) : b(v(n[0])), i)
    }

    function lt() {
        var n = arguments,
            t = n[n.length - 1],
            r = {};
        return (s(t) || (t = null), a(n[0])) ? (n[0].push(t), i.load.apply(null, n[0]), i) : (u(n, function(n) {
            n !== t && (n = v(n), r[n.name] = n)
        }), u(n, function(n) {
            n !== t && (n = v(n), b(n, function() {
                y(r) && f(t)
            }))
        }), i)
    }

    function b(n, t) {
        if (t = t || w, n.state === l) {
            t();
            return
        }
        if (n.state === tt) {
            i.ready(n.name, t);
            return
        }
        if (n.state === nt) {
            n.onpreload.push(function() {
                b(n, t)
            });
            return
        }
        n.state = tt;
        rt(n, function() {
            n.state = l;
            t();
            u(h[n.name], function(n) {
                f(n)
            });
            o && y() && u(h.ALL, function(n) {
                f(n)
            })
        })
    }

    function at(n) {
        n = n || "";
        var t = n.split("?")[0].split(".");
        return t[t.length - 1].toLowerCase()
    }

    function rt(t, i) {
        function e(t) {
            t = t || n.event;
            u.onload = u.onreadystatechange = u.onerror = null;
            i()
        }

        function o(f) {
            f = f || n.event;
            (f.type === "load" || /loaded|complete/.test(u.readyState) && (!r.documentMode || r.documentMode < 9)) && (n.clearTimeout(t.errorTimeout), n.clearTimeout(t.cssTimeout), u.onload = u.onreadystatechange = u.onerror = null, i())
        }

        function s() {
            if (t.state !== l && t.cssRetries <= 20) {
                for (var i = 0, f = r.styleSheets.length; i < f; i++)
                    if (r.styleSheets[i].href === u.href) {
                        o({
                            type: "load"
                        });
                        return
                    }
                t.cssRetries++;
                t.cssTimeout = n.setTimeout(s, 250)
            }
        }
        var u, h, f;
        i = i || w;
        h = at(t.url);
        /*IPS t.name */
		h === "css" || t.name == 'css' ? (u = r.createElement("link"), u.type = "text/" + (t.type || "css"), u.rel = "stylesheet", u.href = t.url, t.cssRetries = 0, t.cssTimeout = n.setTimeout(s, 500)) : (u = r.createElement("script"), u.type = "text/" + (t.type || "javascript"), u.src = t.url);
        u.onload = u.onreadystatechange = o;
        u.onerror = e;
        u.async = !1;
        u.defer = !1;
        t.errorTimeout = n.setTimeout(function() {
            e({
                type: "timeout"
            })
        }, 7e3);
        f = r.head || r.getElementsByTagName("head")[0];
        f.insertBefore(u, f.lastChild)
    }

    function vt() {
        for (var t, u = r.getElementsByTagName("script"), n = 0, f = u.length; n < f; n++)
            if (t = u[n].getAttribute("data-headjs-load"), !!t) {
                i.load(t);
                return
            }
    }

    function yt(n, t) {
        var v, p, e;
        return n === r ? (o ? f(t) : d.push(t), i) : (s(n) && (t = n, n = "ALL"), a(n)) ? (v = {}, u(n, function(n) {
            v[n] = c[n];
            i.ready(n, function() {
                y(v) && f(t)
            })
        }), i) : typeof n != "string" || !s(t) ? i : (p = c[n], p && p.state === l || n === "ALL" && y() && o) ? (f(t), i) : (e = h[n], e ? e.push(t) : e = h[n] = [t], i)
    }

    function e() {
        if (!r.body) {
            n.clearTimeout(i.readyTimeout);
            i.readyTimeout = n.setTimeout(e, 50);
            return
        }
        o || (o = !0, vt(), u(d, function(n) {
            f(n)
        }))
    }

    function k() {
        r.addEventListener ? (r.removeEventListener("DOMContentLoaded", k, !1), e()) : r.readyState === "complete" && (r.detachEvent("onreadystatechange", k), e())
    }
    var r = n.document,
        d = [],
        h = {},
        c = {},
        ut = "async" in r.createElement("script") || "MozAppearance" in r.documentElement.style || n.opera,
        o, g = n.head_conf && n.head_conf.head || "head",
        i = n[g] = n[g] || function() {
            i.ready.apply(null, arguments)
        },
        nt = 1,
        ft = 2,
        tt = 3,
        l = 4,
        p;
    if (r.readyState === "complete") e();
    else if (r.addEventListener) r.addEventListener("DOMContentLoaded", k, !1), n.addEventListener("load", e, !1);
    else {
        r.attachEvent("onreadystatechange", k);
        n.attachEvent("onload", e);
        p = !1;
        try {
            p = !n.frameElement && r.documentElement
        } catch (wt) {}
        p && p.doScroll && function pt() {
            if (!o) {
                try {
                    p.doScroll("left")
                } catch (t) {
                    n.clearTimeout(i.readyTimeout);
                    i.readyTimeout = n.setTimeout(pt, 50);
                    return
                }
                e()
            }
        }()
    }
    i.load = i.js = ut ? lt : ct;
    i.test = ot;
    i.ready = yt;
    i.ready(r, function() {
        y() && u(h.ALL, function(n) {
            f(n)
        });
        i.feature && i.feature("domloaded", !0)
    })
})(window);

/************ jStorage API *************/

(function() {
    function D() {
        var a = "{}";
        if ("userDataBehavior" == k) {
            d.load("jStorage");
            try {
                a = d.getAttribute("jStorage")
            } catch (b) {}
            try {
                r = d.getAttribute("jStorage_update")
            } catch (c) {}
            h.jStorage = a
        }
        E();
        x();
        F()
    }

    function u() {
        var a;
        clearTimeout(G);
        G = setTimeout(function() {
            if ("localStorage" == k || "globalStorage" == k) a = h.jStorage_update;
            else if ("userDataBehavior" == k) {
                d.load("jStorage");
                try {
                    a = d.getAttribute("jStorage_update")
                } catch (b) {}
            }
            if (a && a != r) {
                r = a;
                var l = m.parse(m.stringify(c.__jstorage_meta.CRC32)),
                    p;
                D();
                p = m.parse(m.stringify(c.__jstorage_meta.CRC32));
                var e, z = [],
                    f = [];
                for (e in l) l.hasOwnProperty(e) && (p[e] ? l[e] != p[e] && "2." == String(l[e]).substr(0, 2) && z.push(e) : f.push(e));
                for (e in p) p.hasOwnProperty(e) && (l[e] || z.push(e));
                s(z, "updated");
                s(f, "deleted")
            }
        }, 25)
    }

    function s(a, b) {
        a = [].concat(a || []);
        if ("flushed" == b) {
            a = [];
            for (var c in g) g.hasOwnProperty(c) && a.push(c);
            b = "deleted"
        }
        c = 0;
        for (var p = a.length; c < p; c++) {
            if (g[a[c]])
                for (var e = 0, d = g[a[c]].length; e < d; e++) g[a[c]][e](a[c], b);
            if (g["*"])
                for (e = 0, d = g["*"].length; e < d; e++) g["*"][e](a[c], b)
        }
    }

    function v() {
        var a = (+new Date).toString();
        if ("localStorage" == k || "globalStorage" == k) try {
            h.jStorage_update = a
        } catch (b) {
            k = !1
        } else "userDataBehavior" == k && (d.setAttribute("jStorage_update", a), d.save("jStorage"));
        u()
    }

    function E() {
        if (h.jStorage) try {
            c = m.parse(String(h.jStorage))
        } catch (a) {
            h.jStorage = "{}"
        } else h.jStorage = "{}";
        A = h.jStorage ? String(h.jStorage).length : 0;
        c.__jstorage_meta || (c.__jstorage_meta = {});
        c.__jstorage_meta.CRC32 || (c.__jstorage_meta.CRC32 = {})
    }

    function w() {
        if (c.__jstorage_meta.PubSub) {
            for (var a = +new Date - 2E3, b = 0, l = c.__jstorage_meta.PubSub.length; b <
                l; b++)
                if (c.__jstorage_meta.PubSub[b][0] <= a) {
                    c.__jstorage_meta.PubSub.splice(b, c.__jstorage_meta.PubSub.length - b);
                    break
                }
            c.__jstorage_meta.PubSub.length || delete c.__jstorage_meta.PubSub
        }
        try {
            h.jStorage = m.stringify(c), d && (d.setAttribute("jStorage", h.jStorage), d.save("jStorage")), A = h.jStorage ? String(h.jStorage).length : 0
        } catch (p) {}
    }

    function q(a) {
        if (!a || "string" != typeof a && "number" != typeof a) throw new TypeError("Key name must be string or numeric");
        if ("__jstorage_meta" == a) throw new TypeError("Reserved key name");
        return !0
    }

    function x() {
        var a, b, l, d, e = Infinity,
            h = !1,
            f = [];
        clearTimeout(H);
        if (c.__jstorage_meta && "object" == typeof c.__jstorage_meta.TTL) {
            a = +new Date;
            l = c.__jstorage_meta.TTL;
            d = c.__jstorage_meta.CRC32;
            for (b in l) l.hasOwnProperty(b) && (l[b] <= a ? (delete l[b], delete d[b], delete c[b], h = !0, f.push(b)) : l[b] < e && (e = l[b]));
            Infinity != e && (H = setTimeout(x, e - a));
            h && (w(), v(), s(f, "deleted"))
        }
    }

    function F() {
        var a;
        if (c.__jstorage_meta.PubSub) {
            var b, l = B;
            for (a = c.__jstorage_meta.PubSub.length - 1; 0 <= a; a--)
                if (b = c.__jstorage_meta.PubSub[a],
                    b[0] > B) {
                    var l = b[0],
                        d = b[1];
                    b = b[2];
                    if (t[d])
                        for (var e = 0, h = t[d].length; e < h; e++) t[d][e](d, m.parse(m.stringify(b)))
                }
            B = l
        }
    }
    var y = window.jQuery || window.$ || (window.$ = {}),
        m = {
            parse: window.JSON && (window.JSON.parse || window.JSON.decode) || String.prototype.evalJSON && function(a) {
                return String(a).evalJSON()
            } || y.parseJSON || y.evalJSON,
            stringify: Object.toJSON || window.JSON && (window.JSON.stringify || window.JSON.encode) || y.toJSON
        };
    if (!("parse" in m && "stringify" in m)) throw Error("No JSON support found, include //cdnjs.cloudflare.com/ajax/libs/json2/20110223/json2.js to page");
    var c = {
            __jstorage_meta: {
                CRC32: {}
            }
        },
        h = {
            jStorage: "{}"
        },
        d = null,
        A = 0,
        k = !1,
        g = {},
        G = !1,
        r = 0,
        t = {},
        B = +new Date,
        H, C = {
            isXML: function(a) {
                return (a = (a ? a.ownerDocument || a : 0).documentElement) ? "HTML" !== a.nodeName : !1
            },
            encode: function(a) {
                if (!this.isXML(a)) return !1;
                try {
                    return (new XMLSerializer).serializeToString(a)
                } catch (b) {
                    try {
                        return a.xml
                    } catch (c) {}
                }
                return !1
            },
            decode: function(a) {
                var b = "DOMParser" in window && (new DOMParser).parseFromString || window.ActiveXObject && function(a) {
                    var b = new ActiveXObject("Microsoft.XMLDOM");
                    b.async =
                        "false";
                    b.loadXML(a);
                    return b
                };
                if (!b) return !1;
                a = b.call("DOMParser" in window && new DOMParser || window, a, "text/xml");
                return this.isXML(a) ? a : !1
            }
        };
    y.jStorage = {
        version: "0.4.4",
        set: function(a, b, d) {
            q(a);
            d = d || {};
            if ("undefined" == typeof b) return this.deleteKey(a), b;
            if (C.isXML(b)) b = {
                _is_xml: !0,
                xml: C.encode(b)
            };
            else {
                if ("function" == typeof b) return;
                b && "object" == typeof b && (b = m.parse(m.stringify(b)))
            }
            c[a] = b;
            for (var h = c.__jstorage_meta.CRC32, e = m.stringify(b), k = e.length, f = 2538058380 ^ k, g = 0, n; 4 <= k;) n = e.charCodeAt(g) & 255 |
                (e.charCodeAt(++g) & 255) << 8 | (e.charCodeAt(++g) & 255) << 16 | (e.charCodeAt(++g) & 255) << 24, n = 1540483477 * (n & 65535) + ((1540483477 * (n >>> 16) & 65535) << 16), n ^= n >>> 24, n = 1540483477 * (n & 65535) + ((1540483477 * (n >>> 16) & 65535) << 16), f = 1540483477 * (f & 65535) + ((1540483477 * (f >>> 16) & 65535) << 16) ^ n, k -= 4, ++g;
            switch (k) {
                case 3:
                    f ^= (e.charCodeAt(g + 2) & 255) << 16;
                case 2:
                    f ^= (e.charCodeAt(g + 1) & 255) << 8;
                case 1:
                    f ^= e.charCodeAt(g) & 255, f = 1540483477 * (f & 65535) + ((1540483477 * (f >>> 16) & 65535) << 16)
            }
            f ^= f >>> 13;
            f = 1540483477 * (f & 65535) + ((1540483477 * (f >>> 16) &
                65535) << 16);
            h[a] = "2." + ((f ^ f >>> 15) >>> 0);
            this.setTTL(a, d.TTL || 0);
            s(a, "updated");
            return b
        },
        get: function(a, b) {
            q(a);
            return a in c ? c[a] && "object" == typeof c[a] && c[a]._is_xml ? C.decode(c[a].xml) : c[a] : "undefined" == typeof b ? null : b
        },
        deleteKey: function(a) {
            q(a);
            return a in c ? (delete c[a], "object" == typeof c.__jstorage_meta.TTL && a in c.__jstorage_meta.TTL && delete c.__jstorage_meta.TTL[a], delete c.__jstorage_meta.CRC32[a], w(), v(), s(a, "deleted"), !0) : !1
        },
        setTTL: function(a, b) {
            var d = +new Date;
            q(a);
            b = Number(b) || 0;
            return a in
                c ? (c.__jstorage_meta.TTL || (c.__jstorage_meta.TTL = {}), 0 < b ? c.__jstorage_meta.TTL[a] = d + b : delete c.__jstorage_meta.TTL[a], w(), x(), v(), !0) : !1
        },
        getTTL: function(a) {
            var b = +new Date;
            q(a);
            return a in c && c.__jstorage_meta.TTL && c.__jstorage_meta.TTL[a] ? (a = c.__jstorage_meta.TTL[a] - b) || 0 : 0
        },
        flush: function() {
            c = {
                __jstorage_meta: {
                    CRC32: {}
                }
            };
            w();
            v();
            s(null, "flushed");
            return !0
        },
        storageObj: function() {
            function a() {}
            a.prototype = c;
            return new a
        },
        index: function() {
            var a = [],
                b;
            for (b in c) c.hasOwnProperty(b) && "__jstorage_meta" !=
                b && a.push(b);
            return a
        },
        storageSize: function() {
            return A
        },
        currentBackend: function() {
            return k
        },
        storageAvailable: function() {
            return !!k
        },
        listenKeyChange: function(a, b) {
            q(a);
            g[a] || (g[a] = []);
            g[a].push(b)
        },
        stopListening: function(a, b) {
            q(a);
            if (g[a])
                if (b)
                    for (var c = g[a].length - 1; 0 <= c; c--) g[a][c] == b && g[a].splice(c, 1);
                else delete g[a]
        },
        subscribe: function(a, b) {
            a = (a || "").toString();
            if (!a) throw new TypeError("Channel not defined");
            t[a] || (t[a] = []);
            t[a].push(b)
        },
        publish: function(a, b) {
            a = (a || "").toString();
            if (!a) throw new TypeError("Channel not defined");
            c.__jstorage_meta || (c.__jstorage_meta = {});
            c.__jstorage_meta.PubSub || (c.__jstorage_meta.PubSub = []);
            c.__jstorage_meta.PubSub.unshift([+new Date, a, b]);
            w();
            v()
        },
        reInit: function() {
            D()
        }
    };
    (function() {
        var a = !1;
        if ("localStorage" in window) try {
            window.localStorage.setItem("_tmptest", "tmpval"), a = !0, window.localStorage.removeItem("_tmptest")
        } catch (b) {}
        if (a) try {
            window.localStorage && (h = window.localStorage, k = "localStorage", r = h.jStorage_update)
        } catch (c) {} else if ("globalStorage" in window) try {
            window.globalStorage && (h =
                "localhost" == window.location.hostname ? window.globalStorage["localhost.localdomain"] : window.globalStorage[window.location.hostname], k = "globalStorage", r = h.jStorage_update)
        } catch (g) {} else if (d = document.createElement("link"), d.addBehavior) {
            d.style.behavior = "url(#default#userData)";
            document.getElementsByTagName("head")[0].appendChild(d);
            try {
                d.load("jStorage")
            } catch (e) {
                d.setAttribute("jStorage", "{}"), d.save("jStorage"), d.load("jStorage")
            }
            a = "{}";
            try {
                a = d.getAttribute("jStorage")
            } catch (m) {}
            try {
                r = d.getAttribute("jStorage_update")
            } catch (f) {}
            h.jStorage =
                a;
            k = "userDataBehavior"
        } else {
            d = null;
            return
        }
        E();
        x();
        "localStorage" == k || "globalStorage" == k ? "addEventListener" in window ? window.addEventListener("storage", u, !1) : document.attachEvent("onstorage", u) : "userDataBehavior" == k && setInterval(u, 1E3);
        F();
        "addEventListener" in window && window.addEventListener("pageshow", function(a) {
            a.persisted && u()
        }, !1)
    })()
})();

/*! Lazy Load 1.9.3 - MIT license - Copyright 2010-2013 Mika Tuupola */

! function(a, b, c, d) {
    var e = a(b);
    a.fn.lazyload = function(f) {
        function g() {
            var b = 0;
            i.each(function() {
                var c = a(this);
                if (!j.skip_invisible || c.is(":visible"))
                    if (a.abovethetop(this, j) || a.leftofbegin(this, j));
                    else if (a.belowthefold(this, j) || a.rightoffold(this, j)) {
                    if (++b > j.failure_limit) return !1
                } else c.trigger("appear"), b = 0
            })
        }
        var h, i = this,
            j = {
                threshold: 0,
                failure_limit: 0,
                event: "scroll",
                effect: "show",
                container: b,
                data_attribute: "original",
                skip_invisible: !0,
                appear: null,
                load: null,
                placeholder: "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAANSURBVBhXYzh8+PB/AAffA0nNPuCLAAAAAElFTkSuQmCC"
            };
        return f && (d !== f.failurelimit && (f.failure_limit = f.failurelimit, delete f.failurelimit), d !== f.effectspeed && (f.effect_speed = f.effectspeed, delete f.effectspeed), a.extend(j, f)), h = j.container === d || j.container === b ? e : a(j.container), 0 === j.event.indexOf("scroll") && h.bind(j.event, function() {
            return g()
        }), this.each(function() {
            var b = this,
                c = a(b);
            b.loaded = !1, (c.attr("src") === d || c.attr("src") === !1) && c.is("img") && c.attr("src", j.placeholder), c.one("appear", function() {
                if (!this.loaded) {
                    if (j.appear) {
                        var d = i.length;
                        j.appear.call(b, d, j)
                    }
                    a("<img />").bind("load", function() {
                        var d = c.attr("data-" + j.data_attribute);
                        c.hide(), c.is("img") ? c.attr("src", d) : c.css("background-image", "url('" + d + "')"), c[j.effect](j.effect_speed), b.loaded = !0;
                        var e = a.grep(i, function(a) {
                            return !a.loaded
                        });
                        if (i = a(e), j.load) {
                            var f = i.length;
                            j.load.call(b, f, j)
                        }
                    }).attr("src", c.attr("data-" + j.data_attribute))
                }
            }), 0 !== j.event.indexOf("scroll") && c.bind(j.event, function() {
                b.loaded || c.trigger("appear")
            })
        }), e.bind("resize", function() {
            g()
        }), /(?:iphone|ipod|ipad).*os 5/gi.test(navigator.appVersion) && e.bind("pageshow", function(b) {
            b.originalEvent && b.originalEvent.persisted && i.each(function() {
                a(this).trigger("appear")
            })
        }), a(c).ready(function() {
            g()
        }), this
    }, a.belowthefold = function(c, f) {
        var g;
        return g = f.container === d || f.container === b ? (b.innerHeight ? b.innerHeight : e.height()) + e.scrollTop() : a(f.container).offset().top + a(f.container).height(), g <= a(c).offset().top - f.threshold
    }, a.rightoffold = function(c, f) {
        var g;
        return g = f.container === d || f.container === b ? e.width() + e.scrollLeft() : a(f.container).offset().left + a(f.container).width(), g <= a(c).offset().left - f.threshold
    }, a.abovethetop = function(c, f) {
        var g;
        return g = f.container === d || f.container === b ? e.scrollTop() : a(f.container).offset().top, g >= a(c).offset().top + f.threshold + a(c).height()
    }, a.leftofbegin = function(c, f) {
        var g;
        return g = f.container === d || f.container === b ? e.scrollLeft() : a(f.container).offset().left, g >= a(c).offset().left + f.threshold + a(c).width()
    }, a.inviewport = function(b, c) {
        return !(a.rightoffold(b, c) || a.leftofbegin(b, c) || a.belowthefold(b, c) || a.abovethetop(b, c))
    }, a.extend(a.expr[":"], {
        "below-the-fold": function(b) {
            return a.belowthefold(b, {
                threshold: 0
            })
        },
        "above-the-top": function(b) {
            return !a.belowthefold(b, {
                threshold: 0
            })
        },
        "right-of-screen": function(b) {
            return a.rightoffold(b, {
                threshold: 0
            })
        },
        "left-of-screen": function(b) {
            return !a.rightoffold(b, {
                threshold: 0
            })
        },
        "in-viewport": function(b) {
            return a.inviewport(b, {
                threshold: 0
            })
        },
        "above-the-fold": function(b) {
            return !a.belowthefold(b, {
                threshold: 0
            })
        },
        "right-of-fold": function(b) {
            return a.rightoffold(b, {
                threshold: 0
            })
        },
        "left-of-fold": function(b) {
            return !a.rightoffold(b, {
                threshold: 0
            })
        }
    })
}(jQuery, window, document);

/*! tinyscrollbar - v2.1.9 - 2014-12-01 http://www.baijs.com/tinyscrollbar*/

! function(a) {
    "function" == typeof define && define.amd ? define(["jquery"], a) : a("object" == typeof exports ? require("jquery") : jQuery)
}(function(a) {
    "use strict";

    function b(b, e) {
        function f() {
            return m.update(), h(), m
        }

        function g() {
            r.css(x, m.contentPosition / m.trackRatio), o.css(x, -m.contentPosition), p.css(w, m.trackSize), q.css(w, m.trackSize), r.css(w, m.thumbSize)
        }

        function h() {
            u ? n[0].ontouchstart = function(a) {
                1 === a.touches.length && (a.stopPropagation(), i(a.touches[0]))
            } : (r.bind("mousedown", i), q.bind("mousedown", k)), a(window).resize(function() {
                m.update("relative")
            }), m.options.wheel && window.addEventListener ? b[0].addEventListener(v, j, !1) : m.options.wheel && (b[0].onmousewheel = j)
        }

        function i(b) {
            a("body").addClass("noSelect"), s = t ? b.pageX : b.pageY, m.thumbPosition = parseInt(r.css(x), 10) || 0, u ? (document.ontouchmove = function(a) {
                a.preventDefault(), k(a.touches[0])
            }, document.ontouchend = l) : (a(document).bind("mousemove", k), a(document).bind("mouseup", l), r.bind("mouseup", l))
        }

        function j(c) {
            if (m.contentRatio < 1) {
                var d = c || window.event,
                    e = -(d.deltaY || d.detail || -1 / 3 * d.wheelDelta) / 40,
                    f = 1 === d.deltaMode ? m.options.wheelSpeed : 1;
                m.contentPosition -= e * f * m.options.wheelSpeed, m.contentPosition = Math.min(m.contentSize - m.viewportSize, Math.max(0, m.contentPosition)), b.trigger("move"), r.css(x, m.contentPosition / m.trackRatio), o.css(x, -m.contentPosition), (m.options.wheelLock || m.contentPosition !== m.contentSize - m.viewportSize && 0 !== m.contentPosition) && (d = a.event.fix(d), d.preventDefault())
            }
        }

        function k(a) {
            if (m.contentRatio < 1) {
                var c = t ? a.pageX : a.pageY,
                    d = c - s;
                m.options.scrollInvert && u && (d = s - c);
                var e = Math.min(m.trackSize - m.thumbSize, Math.max(0, m.thumbPosition + d));
                m.contentPosition = e * m.trackRatio, b.trigger("move"), r.css(x, e), o.css(x, -m.contentPosition)
            }
        }

        function l() {
            a("body").removeClass("noSelect"), a(document).unbind("mousemove", k), a(document).unbind("mouseup", l), r.unbind("mouseup", l), document.ontouchmove = document.ontouchend = null
        }
        this.options = a.extend({}, d, e), this._defaults = d, this._name = c;
        var m = this,
            n = b.find(".viewport"),
            o = b.find(".overview"),
            p = b.find(".scrollbar"),
            q = p.find(".track"),
            r = p.find(".thumb"),
            s = 0,
            t = "x" === this.options.axis,
            u = "ontouchstart" in document.documentElement,
            v = "onwheel" in document.createElement("div") ? "wheel" : void 0 !== document.onmousewheel ? "mousewheel" : "DOMMouseScroll",
            w = t ? "width" : "height",
            x = t ? "left" : "top";
        return this.contentPosition = 0, this.viewportSize = 0, this.contentSize = 0, this.contentRatio = 0, this.trackSize = 0, this.trackRatio = 0, this.thumbSize = 0, this.thumbPosition = 0, this.update = function(a) {
            var b = w.charAt(0).toUpperCase() + w.slice(1).toLowerCase();
            switch (this.viewportSize = n[0]["offset" + b], this.contentSize = o[0]["scroll" + b], this.contentRatio = this.viewportSize / this.contentSize, this.trackSize = this.options.trackSize || this.viewportSize, this.thumbSize = Math.min(this.trackSize, Math.max(0, this.options.thumbSize || this.trackSize * this.contentRatio)), this.trackRatio = this.options.thumbSize ? (this.contentSize - this.viewportSize) / (this.trackSize - this.thumbSize) : this.contentSize / this.trackSize, s = q.offset().top, p.toggleClass("disable", this.contentRatio >= 1), a) {
                case "bottom":
                    this.contentPosition = Math.max(this.contentSize - this.viewportSize, 0);
                    break;
                case "relative":
                    this.contentPosition = Math.min(Math.max(this.contentSize - this.viewportSize, 0), Math.max(0, this.contentPosition));
                    break;
                default:
                    this.contentPosition = parseInt(a, 10) || 0
            }
            return g(), m
        }, f()
    }
    var c = "tinyscrollbar",
        d = {
            axis: "y",
            wheel: !0,
            wheelSpeed: 40,
            wheelLock: !0,
            scrollInvert: !1,
            trackSize: !1,
            thumbSize: !1
        };
    a.fn[c] = function(d) {
        return this.each(function() {
            a.data(this, "plugin_" + c) || a.data(this, "plugin_" + c, new b(a(this), d))
        })
    }
});


var Queue = (function () {

    Queue.prototype.autorun = true;
    Queue.prototype.dequeueCall = true;
    Queue.prototype.running = false;
    Queue.prototype.queue = [];

    function Queue(autorun, dequeueCall) {
        if (typeof autorun !== "undefined") {
            this.autorun = autorun;
        }
		if (typeof dequeueCall !== "undefined") {
            this.dequeueCall = dequeueCall;
        }
        this.queue = []; //initialize the queue
    };

    Queue.prototype.add = function (callback) {
        var _this = this;
        //add callback to the queue
        this.queue.push(function () {
            var finished = callback();
            if ( _this.dequeueCall && ( typeof finished === "undefined" || finished ) ) {
                //  if callback returns `false`, then you have to 
                //  call `next` somewhere in the callback
                _this.dequeue();
            }
        });
		
		this.lastPush = this.queue.length - 1;
		
        if (this.autorun && !this.running) {
            // if nothing is running, then start the engines!
            this.dequeue();
        }

        return this; // for chaining fun!
    };
	Queue.prototype.remove = function (index) {
        
		if( typeof this.queue[index] == 'function' )
		{
			this.queue.splice(index, 1);
		}

        return this; // for chaining fun!
    };
    Queue.prototype.dequeue = function () {
        this.running = false;
        //get the first element off the queue
        var shift = this.queue.shift();
        if (shift) {
            this.running = true;
            shift();
        }
        return shift;
    };

    Queue.prototype.next = Queue.prototype.dequeue;
	
    return Queue;

})();



/*
 * TipTip
 * Copyright 2010 Drew Wilson
 * www.drewwilson.com
 * code.drewwilson.com/entry/tiptip-jquery-plugin
 *
 * Version 1.3   -   Updated: Mar. 23, 2010
 *
 * This Plug-In will create a custom tooltip to replace the default
 * browser tooltip. It is extremely lightweight and very smart in
 * that it detects the edges of the browser window and will make sure
 * the tooltip stays within the current window size. As a result the
 * tooltip will adjust itself to be displayed above, below, to the left 
 * or to the right depending on what is necessary to stay within the
 * browser window. It is completely customizable as well via CSS.
 *
 * This TipTip jQuery plug-in is dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */

(function($){
	$.fn.tipTip = function(options) {
		var defaults = { 
			activation: "hover",
			keepAlive: false,
			maxWidth: "200px",
			edgeOffset: 3,
			defaultPosition: "bottom",
			delay: 400,
			fadeIn: 200,
			fadeOut: 200,
			attribute: "title",
			content: false, // HTML or String to fill TipTIp with
		  	enter: function(){},
		  	exit: function(){}
	  	};
	 	var opts = $.extend(defaults, options);
	 	
	 	// Setup tip tip elements and render them to the DOM
	 	if( typeof tiptip_holder == 'undefined' ){
	 		
			tiptip_holder = $('<div id="tips_widget" class="small_tip" style="max-width:'+ opts.maxWidth +';"></div>');
			var tiptip_content = $('<div class="tips_content"></div>');
			var tiptip_arrow = $('<div class="tips_arrow"></div>');
			$("body").append(tiptip_holder.html(tiptip_content).prepend(tiptip_arrow.html('<div class="tips_arrow_inner"></div>')));
		
		} else {
			tiptip_holder = $("#tips_widget.small_tip");
			var tiptip_content = $(".tips_content");
			var tiptip_arrow = $(".tips_arrow");
		}
		
		return this.each(function(){
			var org_elem = $(this);
			if(opts.content){
				var org_title = opts.content;
			} else {
				var org_title = org_elem.attr(opts.attribute);
			}

			if( typeof org_title == 'string' ){
				if(!opts.content){
					org_elem.removeAttr(opts.attribute); //remove original Attribute
				}
				var timeout = false;
				
				if(opts.activation == "hover"){
					org_elem.hover(function(){
						active_tiptip();
					}, function(){
						if(!opts.keepAlive){
							deactive_tiptip();
						}
					});
					if(opts.keepAlive){
						tiptip_holder.hover(function(){}, function(){
							deactive_tiptip();
						});
					}
				} else if(opts.activation == "focus"){
					org_elem.focus(function(){
						active_tiptip();
					}).blur(function(){
						deactive_tiptip();
					});
				} else if(opts.activation == "click"){
					org_elem.click(function(){
						active_tiptip();
						return false;
					}).hover(function(){},function(){
						if(!opts.keepAlive){
							deactive_tiptip();
						}
					});
					if(opts.keepAlive){
						tiptip_holder.hover(function(){}, function(){
							deactive_tiptip();
						});
					}
				}
			
				function active_tiptip(){
					opts.enter.call(this);
					tiptip_content.html(org_title);
					tiptip_holder.hide().removeAttr("class").css("margin","0");
					tiptip_arrow.removeAttr("style");
					
					var top = parseInt(org_elem.offset()['top']);
					var left = parseInt(org_elem.offset()['left']);
					var org_width = parseInt(org_elem.outerWidth());
					var org_height = parseInt(org_elem.outerHeight());
					var tip_w = tiptip_holder.outerWidth();
					var tip_h = tiptip_holder.outerHeight();
					var w_compare = Math.round((org_width - tip_w) / 2);
					var h_compare = Math.round((org_height - tip_h) / 2);
					var marg_left = Math.round(left + w_compare);
					var marg_top = Math.round(top + org_height + opts.edgeOffset);
					var t_class = "";
					var arrow_top = "";
					var arrow_left = Math.round(tip_w - 12) / 2;

                    var dir = org_elem.parents('.tip-direction');
					
					if ( dir.length > 0 || org_elem.hasClass('tip-direction')){
						t_class = "_" + dir.attr('data-tip');
					} else if(opts.defaultPosition == "bottom"){
                    	t_class = "_bottom";
                   	} else if(opts.defaultPosition == "top"){ 
                   		t_class = "_top";
                   	} else if(opts.defaultPosition == "left"){
                   		t_class = "_left";
                   	} else if(opts.defaultPosition == "right"){
                   		t_class = "_right";
                   	}
					
					var right_compare = (w_compare + left) < parseInt($(window).scrollLeft());
					var left_compare = (tip_w + left) > parseInt($(window).width());
					
					if((right_compare && w_compare < 0) || (t_class == "_right" && !left_compare) || (t_class == "_left" && left < (tip_w + opts.edgeOffset + 5))){
						t_class = "_right";
						arrow_top = Math.round(tip_h - 13) / 2;
						arrow_left = -12;
						marg_left = Math.round(left + org_width + opts.edgeOffset);
						marg_top = Math.round(top + h_compare);
					} else if((left_compare && w_compare < 0) || (t_class == "_left" && !right_compare)){
						t_class = "_left";
						arrow_top = Math.round(tip_h - 13) / 2;
						arrow_left =  Math.round(tip_w);
						marg_left = Math.round(left - (tip_w + opts.edgeOffset + 5));
						marg_top = Math.round(top + h_compare);
					}

					var top_compare = (top + org_height + opts.edgeOffset + tip_h + 8) > parseInt($(window).height() + $(window).scrollTop());
					var bottom_compare = ((top + org_height) - (opts.edgeOffset + tip_h + 8)) < 0;
					
					if(top_compare || (t_class == "_bottom" && top_compare) || (t_class == "_top" && !bottom_compare)){
						if(t_class == "_top" || t_class == "_bottom"){
							t_class = "_top";
						} else {
							t_class = t_class+"_top";
						}
						arrow_top = tip_h;
						marg_top = Math.round(top - (tip_h + 5 + opts.edgeOffset));
					} else if(bottom_compare | (t_class == "_top" && bottom_compare) || (t_class == "_bottom" && !top_compare)){
						if(t_class == "_top" || t_class == "_bottom"){
							t_class = "_bottom";
						} else {
							t_class = t_class+"_bottom";
						}
						arrow_top = -12;						
						marg_top = Math.round(top + org_height + opts.edgeOffset);
					}
				
					if(t_class == "_right_top" || t_class == "_left_top"){
						marg_top = marg_top + 5;
					} else if(t_class == "_right_bottom" || t_class == "_left_bottom"){		
						marg_top = marg_top - 5;
					}
					if(t_class == "_left_top" || t_class == "_left_bottom"){	
						marg_left = marg_left + 5;
					}
					tiptip_arrow.css({"margin-left": arrow_left+"px", "margin-top": arrow_top+"px"});
					tiptip_holder.css({"margin-left": marg_left+"px", "margin-top": marg_top+"px"}).attr("class","tip"+t_class);
					
					if (timeout){ clearTimeout(timeout); }
					timeout = setTimeout(function(){ tiptip_holder.stop(true,true).fadeIn(opts.fadeIn); }, opts.delay);	
				}
				
				function deactive_tiptip(){
					opts.exit.call(this);
					if (timeout){ clearTimeout(timeout); }
					tiptip_holder.fadeOut(opts.fadeOut);
				}
			}				
		});
	}
})(jQuery);  	