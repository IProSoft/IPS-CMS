/**
 * Infinite Ajax Scroll, a jQuery plugin
 * Version 1.1.0
 * https://github.com/webcreate/infinite-ajax-scroll
 *
 * Copyright (c) 2011-2014 Jeroen Fiege
 * Licensed under MIT:
 * https://raw.github.com/webcreate/infinite-ajax-scroll/master/MIT-LICENSE.txt
 */
 
    var IAS = function ($element, options)
    {
		// setup
		this.isBound		  = false;
        this.opts             = $.extend({}, $.ias.defaults, options);
		
        this.util             = new $.ias.util();                                // utilities module
        this.paging           = new $.ias.paging(this.opts.scrollContainer);          // paging module
        this.hist             = (this.opts.history ? new $.ias.history() : false);    // history module
		this.opts.listeners = {
		  next:     new $.ias.callback(),
		  load:     new $.ias.callback(),
		  loaded:   new $.ias.callback(),
		  render:   new $.ias.callback(),
		  rendered: new $.ias.callback(),
		  scroll:   new $.ias.callback(),
		  noneLeft: new $.ias.callback(),
		  ready:    new $.ias.callback()
		};
        var _self            = this;

        /**
         * Initialize
         *
         * - tracks scrolling through pages
         * - remembers current page with the history module
         * - setup scroll event and hides pagination element
         * - loads and scrolls to previous page when we have something in our history
         *
         * @return self
         */
        this.init = function()
        {
            var pageNum;
			
            // track page number changes
            this.paging.onChangePage(function (pageNum, scrollOffset, pageUrl) {
                if (_self.hist) {
                    _self.hist.setPage(pageNum, pageUrl);
                }

                // call onPageChange event
                _self.opts.onPageChange.call(this, pageNum, pageUrl, scrollOffset);
            });

            if (this.opts.triggerPageThreshold > 0) {
                // setup scroll
                this.bind();
            } else if ( this.get_next_url() ) {
                var curScrOffset = this.util.getCurrentScrollOffset(this.opts.scrollContainer);
                
				this.show_trigger(function () {
                    _self.paginate(curScrOffset);
                });
            }

            // load and scroll to previous page
            if (this.hist && this.hist.havePage()) {
                this.unbind();

                pageNum = this.hist.getPage();

                this.util.forceScrollTop(function () {
                    var curThreshold;

                    if (pageNum > 1) {
                        _self.paginateToPage(pageNum);

                        curThreshold = _self.get_scroll_threshold(true);
                        $('html, body').scrollTop(curThreshold);
                    }
                    else {
                        _self.bind();
                    }
                });
            }

            return _self;
        }

        /**
         * Reset scrolling and hide pagination links
         *
         * @return void
         */
        this.bind = function ()
        {
            this.log( 'Function: bind scroll' );
			
			if( this.opts.triggerOnClick )
			{
				this.visible_trigger();
			}
			
			this.opts.scrollContainer.on( 'scroll', $.proxy( this.scroll_handler, this) );
        }

        /**
         * Scroll event handler
         *
         * @return void
         */
        this.scroll_handler = function ()
        {
            var curScrOffset,
                scrThreshold;

            curScrOffset = this.util.getCurrentScrollOffset(this.opts.scrollContainer);
            scrThreshold = this.get_scroll_threshold();

            if (curScrOffset >= scrThreshold) {
                if ( this.get_current_page() >= this.opts.triggerPageThreshold || this.opts.triggerOnClick ) {
                    this.unbind();
                    this.show_trigger(function () {
                        _self.paginate( curScrOffset );
                    });
                }
                else {
					this.paginate(curScrOffset);
                }
            }
            this.opts.onScroll(curScrOffset, this.get_current_page(), scrThreshold);
        }

        /**
         * Cancel scrolling
         *
         * @return void
         */
        this.unbind = function ()
        {
            this.log( 'Function: unbind scroll' );
			this.opts.scrollContainer.unbind( 'scroll', $.proxy( _self.scroll_handler, _self ) );
        }

        /**
         * Get scroll threshold based on the last item element
         *
         * @param boolean pure indicates if the thresholdMargin should be applied
         * @return integer threshold
         */
        this.get_scroll_threshold = function (pure)
        {
            var el,
                threshold;

            el = $(this.opts.container).find(this.opts.item).last();

            if (el.size() === 0) {
                return 0;
            }

            threshold = el.offset().top + el.height();

            if (!pure) {
                threshold += this.opts.thresholdMargin;
            }

            return threshold;
        }

        /**
         * Load the items from the next page.
         *
         * @param int      curScrOffset      current scroll offset
         * @param function onCompleteHandler callback function
         * @return void
         */
        this.paginate = function (curScrOffset, onCompleteHandler)
        {
            
			this.log( 'Function: paginate, curScrOffset = ' + curScrOffset );
			
			var urlNextPage = this.get_next_url();

            if ( !urlNextPage )
			{
                return this.unbind();
            }

            if (this.opts.beforePageChange && $.isFunction(this.opts.beforePageChange)) {
                if (this.opts.beforePageChange(curScrOffset, urlNextPage) === false) {
                    return;
                }
            }
			
			

			this.loadItems(urlNextPage, function (data, items) {
                
				// call the onLoadItems callback
                var curLastItem;

                if ( typeof items == 'undefined' || items.length == 0 )
				{
					this.remove_loader();
					
					this.on('next', function( url ) {
						return null;
					});
					
					this.fire('noneLeft', [$(this.opts.container).find(this.opts.item).last()]);
					this.opts.listeners['noneLeft'].disable();
					
					return this.unbind();
                }   
				else
				{
					_self.paging.pushPages(curScrOffset, urlNextPage);

					_self.fire('loaded', [data, items]);

					if( ips_config.version !== 'pinterest' )
					{
						$(items).hide();
						curLastItem = $(this.opts.container).find(this.opts.item).last().after( items );
					}
					
					_self.fire('rendered', [data, items]);
					
					this.bind();
                }
				
				
                this.remove_loader();

                if (onCompleteHandler)
				{
                    onCompleteHandler.call(this);
                }
				
            }, curScrOffset );
        }

        /**
         * Loads items from certain url, triggers
         * onComplete handler when finished
         *
         * @param string   the url to load
         * @param function the callback function
         * @param int      minimal time the loading should take, defaults to $.ias.default.loaderDelay
         * @return void
         */
        this.loadItems = function (url, onCompleteHandler, curScrOffset)
        {
			_self.log( 'Var: isBound ' + this.isBound );
			
			if (this.isBound) {
				return this.throttle( this.loadItems( url, onCompleteHandler, curScrOffset ), 150 );
			}
			
			this.isBound = true;
			
			this.unbind();
			
			var promise = this.fire('next', [url]);
			
			promise.done( function ()
			{
				_self.show_loader();
				
				_self.log( 'Function: loadItems ' + url );
				
				var loadEvent = {
					url: url
				};
				
				_self.fire('load', [loadEvent]);
				
				$.get( loadEvent.url, null, function (data) {
					
					_self.log( 'Inside function: loadItems '  );
					
					var items = [],
						container,
						startTime = Date.now(),
						diffTime;
					
					if( typeof data.compile != 'undefined' )
					{
						var data = doTCompile( data );
						
						var items = $(data).filter('.item').detach();
					}
					/** MUST stay ele if */
					else if( data.length > 0 )
					{
						// walk through the items on the next page
						// and add them to the items array
						container = $(_self.opts.container, data).eq(0);
						if (0 === container.length) {
							// incase the element is a root element (body > element),
							// try to filter it
							container = $(data).filter(_self.opts.container).eq(0);
						}
					
						if (container) {
							container.find(_self.opts.item).each(function () {
								items.push(this);
							});
						}
					}
					
					if (onCompleteHandler) {
						diffTime = Date.now() - startTime;
						if (diffTime < _self.opts.loaderDelay) {
							setTimeout(function () {
								onCompleteHandler.call(_self, data, items);
							}, _self.opts.loaderDelay - diffTime);
						} else {
							onCompleteHandler.call(_self, data, items);
						}
					}
					
					_self.isBound = false;
					
				}, _self.opts.dataType).done(function() {
					/* _self.log( 'Function: loadItems, done' ); */
				}).fail(function() {
					/* _self.log( 'Function: loadItems, fail' ); */
				}).always(function() {
					/* _self.log( 'Function: loadItems, always' ); */
				});
			});

			promise.fail(function ()
			{
				_self.log( 'Function: loadItems, promise:fail' );
				_self.bind();
			});

			return true;
			
			
			
        }

        /**
         * Paginate to a certain page number.
         *
         * - keeps paginating till the pageNum is reached
         *
         * @return void
         */
        this.paginateToPage = function (pageNum)
        {
            this.log( 'Function: paginateToPage ' + pageNum );
			
			var curThreshold = this.get_scroll_threshold(true);

            if (curThreshold > 0) {
                this.paginate(curThreshold, function () {
                    _self.unbind();

                    if ((_self.paging.getCurPageNum(curThreshold) + 1) < pageNum) {
                        _self.paginateToPage(pageNum);

                        $('html,body').animate({'scrollTop': curThreshold}, 400, 'swing');
                    }
                    else {
                        $('html,body').animate({'scrollTop': curThreshold}, 1000, 'swing');

                        _self.bind();
                    }
                });
            }
        }

        this.get_current_page = function ()
        {
            var curScrOffset = this.util.getCurrentScrollOffset(this.opts.scrollContainer);

            return this.paging.getCurPageNum(curScrOffset);
        }

        /**
         * Return the active loader or creates a new loader
         *
         * @return object loader jquery object
         */
        this.get_loader = function ()
        {
            var loader = $('.ias_loader');

            if ( loader.size() === 0 ) {
                loader = $( this.opts.loader );
                loader.hide();
            }
			
            return loader;
        }

        /**
         * Inserts the loader and does a fadeIn.
         *
         * @return void
         */
        this.show_loader = function show_loader()
        {
            this.log( 'Function: show_loader' );
			
			var loader = this.get_loader(),
                el;

            if (this.opts.customLoaderProc !== false) {
                this.opts.customLoaderProc(loader);
            } else {
                el = $(this.opts.container).find(this.opts.item).last();
                el.after(loader);
                loader.fadeIn();
            }
        }

        /**
         * Removes the loader.
         *
         * return void
         */
        this.remove_loader = function ()
        {
            this.get_loader().remove();
        }

        /**
         * Return the active trigger or creates a new trigger
         *
         * @return object trigger jquery object
         */
        this.get_trigger = function (callback)
        {
            var trigger = $( this.opts.trigger );

            if (trigger.size() === 0) {
                trigger = $('<div class="' + this.opts.trigger + '"><a href="#">' + this.opts.triggerText + '</a></div>');
                trigger.hide();
            }
			
            trigger.unbind('click');
			
			if( this.get_current_page() < this.opts.triggerPageThreshold )
			{
				trigger.bind('click', function () { 
					_self.remove_trigger();
					callback.call();
					return false;
				}) ;
			}
            return trigger;
        }
		
		/**
         * @param function callback of the trigger (get's called onClick)
         */
        this.visible_trigger = function ()
        {
			var trigger = $( this.opts.trigger );
			if( !trigger.is(':visible') )
			{
				trigger.css('display', 'block');
			}
        }
        /**
         * @param function callback of the trigger (get's called onClick)
         */
        this.show_trigger = function (callback)
        {
            this.log( 'Function: show_trigger' );
			
			var trigger = this.get_trigger(callback),
                el;

            if (this.opts.customTriggerProc !== false) {
                this.opts.customTriggerProc(trigger);
            } else {
                el = $(this.opts.container).find(this.opts.item).last().after(trigger);
                trigger.fadeIn();
            }
        }

        /**
         * Removes the trigger.
         *
         * return void
         */
        this.remove_trigger = function ()
        {
            this.get_trigger().fadeOut();
        }
		
		/**
		 * Fires an event with the ability to cancel further processing. This
		 * can be achieved by returning false in a listener.
		 *
		 * @param event
		 * @param args
		 * @returns {*}
		 */
		this.fire = function (event, args) {
			this.log( 'Function: fire, event = ' + event );
			return this.opts.listeners[event].fireWith( _self, args );
		};
		
		/**
		 * Throttles a method
		 *
		 * Adopted from Ben Alman's jQuery throttle/debounce plugin
		 *
		 * @param callback
		 * @param delay
		 * @return {object}
		 */
		this.throttle = function (callback, delay)
		{
			var last_exec = 0,
				wrapper,
				timeout_id;

			wrapper = function ()
			{
				var that = this,
					args = arguments,
					elapsed = +new Date() - last_exec;

				function exec()
				{
					last_exec = +new Date();
					callback.apply(that, args);
				}

				if (!timeout_id)
				{
					exec();
				}
				else
				{
					clearTimeout(timeout_id);
				}

				if (elapsed > delay)
				{
					exec();
				}
				else
				{
					timeout_id = setTimeout(exec, delay);
				}
			};

			if ($.guid)
			{
				wrapper.guid = callback.guid = callback.guid || $.guid++;
			}

			return wrapper;
		};
		this.parseLog = function ( log )
        {
			var index = log.indexOf('.js');

			var str = log.substr(0, index + 3);
			index = str.lastIndexOf('/');
			str = str.substr(index + 1, str.length);

			var info = " : " + str;

			var str = log.split(":");
		
			str = str[str.length - 2];
			info += " Line: " + str;
			
			return info;
		};
		/**
         * Triggers log .
         *
         * return void
         */
        this.log = function ( log_info )
        {
            if( this.opts.debug )
			{
				try { throw Error('') } catch(err) {

					var caller, log;

					if ($.browser.mozilla) {
						caller = err.stack.split("\n")[1];
						log = err.stack.split("\n")[2];
					} else {
						caller = err.stack.split("\n")[3];
						log = err.stack.split("\n")[4];
					}

					console.log( log_info + this.parseLog( caller ));
				}
				
			}
        }
		
		/**
		 * Returns the url for the next page
		 *
		 * @private
		 */
		this.get_next_url = function() {
			return $(this.opts.next).last().attr('href');
		};
		
		/**
		 * Destroy object
		 *
		 * @private
		 */
		this.destroy = function() {
			
			this.log( 'Function: destroy ' );
			this.paging.destroy();
			this.unbind();
			
		};
		
		 // initialize
		return this.init();
    };
	 /**
	* Registers an eventListener
	*
	* Note: chainable
	*
	* @public
	* @returns IAS
	*/
	IAS.prototype.on = function (event, callback, priority) {
		if (typeof this.opts.listeners[event] == 'undefined') {
			throw new Error('There is no event called "' + event + '"');
		}

		priority = priority || 0;

		this.opts.listeners[event].add($.proxy(callback, this), priority);

		return this;
	};
    /**
	 * Shortcut. Sets the window as scroll container.
	 *
	 * @public
	 * @param option
	 * @returns {*}
	 */
	$.ias = function (option) {
		var $window = $(window);

		return $window.ias.apply($window, arguments);
	};

	/**
	 * jQuery plugin initialization
	 *
	 * @public
	 * @param option
	 * @returns {*} the last IAS instance will be returned
	 */
	$.fn.ias = function (option) {
		var args = Array.prototype.slice.call(arguments);

			var $this = $(this),
				options = $.extend({uid:(new Date()).getTime()}, $.fn.ias.defaults, $this.data(), typeof option == 'object' && option),
				data = $this.data('ias_' + options.container);

			// set a new instance as data
			if (data) {
				data.destroy();
				$this.data('ias_' + options.container, null);
			}
			
			$this.data('ias_' + options.container, (data = new IAS($this, options)));

			$(document).ready( $.proxy(data.initialize, data) );
			
			// when the plugin is called with a method
			if (typeof option === 'string') {
				console.log('Plugin called with a method ' + ption);
				if (typeof data[option] !== 'function') {
					throw new Error('There is no method called "' + option + '"');
				}

				args.shift(); // remove first argument ('option')
				data[option].apply(data, args);

				if (option === 'destroy') {
					$this.data('ias_' + options.container, null);
				}
			}

		return $this.data('ias_' + options.container);
	};
	// plugin defaults
    $.ias.defaults = {
		container: '#container',
        scrollContainer: $(window),
        item: '.item',
        next: '.next',
		dataType: 'html',
        debug: false,
        loader: '<div class="ias_loader"><img width="48" height="48" src="/images/svg/spinner.svg"></div>',
        loaderDelay: 600,
        triggerPageThreshold: 3,
        triggerText: 'Load more items',
        trigger: '.ias_trigger',
        thresholdMargin: -100,
        history : true,
        onPageChange: function () {},
        beforePageChange: function () {},
        onScroll: function () {},
        customLoaderProc: false,
        customTriggerProc: false
    };

    // utility module
    $.ias.util = function ()
    {
        // setup
        var wndIsLoaded = false;
        var forceScrollTopIsCompleted = false;
        var self = this;

        /**
         * Initialize
         *
         * @return void
         */
        function init()
        {
            $(document).ready(function () {
                wndIsLoaded = true;
            });
        }

        // initialize
        init();

        /**
         * Force browsers to scroll to top.
         *
         * - When you hit back in you browser, it automatically scrolls
         *   back to the last position. There is no way to stop this
         *   in a nice way, so this function does it the hard way.
         *
         * @param function onComplete callback function
         * @return void
         */
        this.forceScrollTop = function (onCompleteHandler)
        {
            $('html,body').scrollTop(0);

            if (!forceScrollTopIsCompleted) {
                if (!wndIsLoaded) {
                    setTimeout(function () {self.forceScrollTop(onCompleteHandler); }, 1);
                } else {
                    onCompleteHandler.call();
                    forceScrollTopIsCompleted = true;
                }
            }
        };

        this.getCurrentScrollOffset = function (container)
        {
            var scrTop,
                wndHeight;

            // the way we calculate if we have to load the next page depends on which container we have
            if (container.get(0) === window) {
                scrTop = container.scrollTop();
            } else {
                scrTop = container.offset().top;
            }

            wndHeight = container.height();

            return scrTop + wndHeight;
        };
    };

    // paging module
    $.ias.paging = function ()
    {
        // setup
        this.pagebreaks        = [[0, document.location.toString()]];
        this.changePageHandler = function () {};
        this.lastPageNum       = 1;
        this.util              = new $.ias.util();

        /**
         * Initialize
         *
         * @return void
         */
        this.init = function ()
        {
			$(window).on( 'scroll', $.proxy( this.scroll_handler, this ) );
			
			return this
        }

        /**
         * Scroll handler
         *
         * - Triggers changePage event
         *
         * @return void
         */
        this.scroll_handler = function ()
        {
            var curScrOffset,
                curPageNum,
                curPagebreak,
                scrOffset,
                urlPage;

            curScrOffset = this.util.getCurrentScrollOffset($(window));

            curPageNum = this.getCurPageNumber(curScrOffset);
            curPagebreak = this.getCurPagebreak(curScrOffset);

            if (this.lastPageNum !== curPageNum) {
                scrOffset = curPagebreak[0];
                urlPage = curPagebreak[1];
                this.changePageHandler.call({}, curPageNum, scrOffset, urlPage); // @todo fix for window height
            }

            this.lastPageNum = curPageNum;
        }

        /**
         * Returns current page number based on scroll offset
         *
         * @param int scroll offset
         * @return int current page number
         */
        this.getCurPageNumber = function (scrollOffset)
        {
            for (var i = (this.pagebreaks.length - 1); i > 0; i--) {
                if (scrollOffset > this.pagebreaks[i][0]) {
                    return i + 1;
                }
            }
            return 1;
        }

        /**
         * Public function for getCurPageNum
         *
         * @param int scrollOffset defaulst to the current
         * @return int current page number
         */
        this.getCurPageNum = function (scrollOffset)
        {
            scrollOffset = scrollOffset || this.util.getCurrentScrollOffset($(window));

            return this.getCurPageNumber(scrollOffset);
        };

        /**
         * Returns current pagebreak information based on scroll offset
         *
         * @param int scroll offset
         * @return array pagebreak information
         */
        this.getCurPagebreak = function (scrollOffset)
        {
            for (var i = (this.pagebreaks.length - 1); i >= 0; i--) {
                if (scrollOffset > this.pagebreaks[i][0]) {
                    return this.pagebreaks[i];
                }
            }
            return null;
        }

        /**
         * Sets onchangePage event handler
         *
         * @param function event handler
         * @return void
         */
        this.onChangePage = function (fn)
        {
            this.changePageHandler = fn;
        };

        /**
         * pushes the pages tracker
         *
         * @param int scroll offset for the new page
         * @return void
         */
        this.pushPages = function (scrollOffset, urlNextPage)
        {
            this.pagebreaks.push([scrollOffset, urlNextPage]);
        };
		/**
		 * Destroy object
		 *
		 * @private
		 */
		this.destroy = function() {
			$(window).unbind( 'scroll', $.proxy( this.scroll_handler, this ) );
		};
		
        // initialize
		return this.init();
    };

    // history module
    $.ias.history = function ()
    {
        // setup
        var isPushed = false;
        var isHtml5 = false;

        /**
         * Initialize
         *
         * @return void
         */
        function init()
        {
            isHtml5 = !!(window.history && history.pushState && history.replaceState);
        }

        // initialize
        init();

        /**
         * Sets page to history
         *
         * @return void;
         */
        this.setPage = function (pageNum, pageUrl)
        {
            this.updateState({page : pageNum}, '', pageUrl);
        };

        /**
         * Checks if we have a page set in the history
         *
         * @return bool returns true when we have a previous page, false otherwise
         */
        this.havePage = function ()
        {
            return (this.getState() !== false);
        };

        /**
         * Gets the previous page from history
         *
         * @return int page number of previous page
         */
        this.getPage = function ()
        {
            var stateObj;

            if (this.havePage()) {
                stateObj = this.getState();
                return stateObj.page;
            }
            return 1;
        };

        /**
         * Returns current state
         *
         * @return object stateObj
         */
        this.getState = function ()
        {
            var haveState,
                stateObj,
                pageNum;

            if (isHtml5) {
                stateObj = history.state;
                if (stateObj && stateObj.ias) {
                    return stateObj.ias;
                }
            }
            else {
                haveState = (window.location.hash.substring(0, 7) === '#/page/');
                if (haveState) {
                    pageNum = parseInt(window.location.hash.replace('#/page/', ''), 10);
                    return { page : pageNum };
                }
            }

            return false;
        };

        /**
         * Pushes state when not pushed already, otherwise
         * replaces the state.
         *
         * @param obj stateObj
         * @param string title
         * @param string url
         * @return void
         */
        this.updateState = function (stateObj, title, url)
        {
            if (isPushed) {
                this.replaceState(stateObj, title, url);
            }
            else {
                this.pushState(stateObj, title, url);
            }
        };

        /**
         * Pushes state to history.
         *
         * @param obj stateObj
         * @param string title
         * @param string url
         * @return void
         */
        this.pushState = function (stateObj, title, url)
        {
            var hash;

            if (isHtml5) {
                history.pushState({ 
					ias : stateObj
				}, title, url);
            }
            else {
                hash = (stateObj.page > 0 ? '#/page/' + stateObj.page : '');
                window.location.hash = hash;
            }

            isPushed = true;
        };

        /**
         * Replaces current history state.
         *
         * @param obj stateObj
         * @param string title
         * @param string url
         * @return void
         */
        this.replaceState = function (stateObj, title, url)
        {
            if (isHtml5) {
                history.replaceState({ ias : stateObj }, title, url);
            }
            else {
                this.pushState(stateObj, title, url);
            }
        };
    };
	
	$.ias.callback = function ()
    {
	  this.list = [];
	  this.fireStack = [];
	  this.isFiring = false;
	  this.isDisabled = false;

	  /**
	   * Calls all added callbacks
	   *
	   * @private
	   * @param args
	   */
	  this.fire = function (args) {
		var context = args[0],
			deferred = args[1],
			callbackArguments = args[2];
		this.isFiring = true;

		for (var i = 0, l = this.list.length; i < l; i++) {
		  if (false === this.list[i].fn.apply(context, callbackArguments)) {
			deferred.reject();

			break;
		  }
		}

		this.isFiring = false;

		deferred.resolve();

		if (this.fireStack.length) {
		  this.fire(this.fireStack.shift());
		}
	  };

	  /**
	   * Returns index of the callback in the list in a similar way as
	   * the indexOf function.
	   *
	   * @param callback
	   * @param {number} index index to start the search from
	   * @returns {number}
	   */
	  this.inList = function (callback, index) {
		index = index || 0;

		for (var i = index, length = this.list.length; i < length; i++) {
		  if (this.list[i].fn === callback || (callback.guid && this.list[i].fn.guid && callback.guid === this.list[i].fn.guid)) {
			return i;
		  }
		}

		return -1;
	  };


	  /**
	   * Adds a callback
	   *
	   * @param callback
	   * @returns {IASCallbacks}
	   * @param priority
	   */
	  this.add = function (callback, priority) {
		var callbackObject = {fn: callback, priority: priority};

		priority = priority || 0;

		for (var i = 0, length = this.list.length; i < length; i++) {
		  if (priority > this.list[i].priority) {
			this.list.splice(i, 0, callbackObject);

			return this;
		  }
		}

		this.list.push(callbackObject);

		return this;
	  },

	  /**
	   * Removes a callback
	   *
	   * @param callback
	   * @returns {IASCallbacks}
	   */
	  this.remove = function (callback) {
		var index = 0;

		while (( index = this.inList(callback, index) ) > -1) {
		  this.list.splice(index, 1);
		}

		return this;
	  },

	  /**
	   * Checks if callback is added
	   *
	   * @param callback
	   * @returns {*}
	   */
	  this.has = function (callback) {
		return (this.inList(callback) > -1);
	  },


	  /**
	   * Calls callbacks with a context
	   *
	   * @param context
	   * @param args
	   * @returns {object|void}
	   */
	  this.fireWith = function (context, args) {
		var deferred = $.Deferred();

		if (this.isDisabled) {
		  return deferred.reject();
		}

		args = args || [];
		args = [ context, deferred, args.slice ? args.slice() : args ];

		if (this.isFiring) {
		  this.fireStack.push(args);
		} else {
		  this.fire(args);
		}

		return deferred;
	  },

	  /**
	   * Disable firing of new events
	   */
	  this.disable = function () {
		this.isDisabled = true;
	  },

	  /**
	   * Enable firing of new events
	   */
	  this.enable = function () {
		this.isDisabled = false;
	  }
	};



