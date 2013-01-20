/*****************************************************************/
/* 5grid 0.3 by n33.co | MIT+GPLv2 license licensed              */
/* init.js: Init script                                          */
/*****************************************************************/

/*********************/
/* Object Setup      */
/*********************/

	var _5gridC = function()
	{
		this.events = new Array();

		this.isReady = false;
		this.isMobile = false;
		this.isDesktop = false;
		this.isFluid = false;
		this.is1000px = false;
		this.is1200px = false;
	}

	_5gridC.prototype.bind = function(name, f)
	{
		if (!this.events[name])
			this.events[name] = new Array();
		
		this.events[name].push(f);
	}

	_5gridC.prototype.trigger = function(name)
	{
		if (!this.isReady || !this.events[name] || this.events[name].length < 1)
			return;
			
		for (i in this.events[name])
			(this.events[name][i])();
	}

	_5gridC.prototype.ready = function(f) { this.bind('ready', f); }
	_5gridC.prototype.orientationChange = function(f) { this.bind('orientationChange', f); }
	_5gridC.prototype.mobileUINavOpen = function(f) { this.bind('mobileUINavOpen', f); }
	_5gridC.prototype.mobileUINavClose = function(f) { this.bind('mobileUINavClose', f); }

	_5gridC.prototype.readyCheck = function()
	{
		var x = this;
		
		window.setTimeout(function() {
			if (x.isReady)
				x.trigger('ready');
			else
				x.readyCheck();
		}, 50);
	}

	var _5grid = new _5gridC;


(function() {

/*********************/
/* Initialize        */
/*********************/

	// Vars
		var	_baseURL, _opts,
			_fluid, _1000px, _1200px, _mobile, _desktop,
			_window = $(window), _head = $('head'), _document = $(document),
			_headQueue = new Array(), _isLocked = false, _isTouch = !!('ontouchstart' in window), _eventType = (_isTouch ? 'touchend' : 'click'),
			v, w, x, y;

	// Shortcut methods
		_headQueue.pushI_5grid = function(s) { _headQueue.push('<style>' + s + '</style>'); };
		_headQueue.pushE_5grid = function(s) { _headQueue.push('<link rel="stylesheet" href="' + s + '" />'); };
		_headQueue.process_5grid = function() { _head.append(_headQueue.join('')); };
		$.fn.disableSelection_5grid = function() { return $(this).css('user-select', 'none').css('-khtml-user-select', 'none').css('-moz-user-select', 'none').css('-o-user-select', 'none').css('-webkit-user-select', 'none'); }
		$.fn.enableSelection_5grid = function() { return $(this).css('user-select', 'auto').css('-khtml-user-select', 'auto').css('-moz-user-select', 'auto').css('-o-user-select', 'auto').css('-webkit-user-select', 'auto'); }
		$.fn.accelerate_5grid = function() { return $(this).css('-webkit-transform', 'translateZ(0)').css('-webkit-backface-visibility', 'hidden').css('-webkit-perspective', '1000'); }

	// Determine base URL, opts
		x = $('script').filter(function() { return this.src.match(/5grid\/init\.js/); }).first();
		y = x.attr('src').split('?');
		_baseURL = y[0].replace(/5grid\/init\.js/, '');
		_opts = new Array();

		// Default opts
			_opts['use'] = 'mobile,desktop';
			_opts['prefix'] = 'style';
			_opts['mobileUI'] = 0;
			_opts['mobileUI.force'] = 0;
			_opts['mobileUI.titleBarHeight'] = 44;
			_opts['mobileUI.openerWidth'] = 60;
			_opts['mobileUI.openerText'] = '=';
			_opts['mobileUI.titleBarFixed'] = 1;
			_opts['mobileUI.theme'] = 'beveled';
			_opts['mobileUI.themeTitleBarColor'] = '#444444';
			_opts['mobileUI.themeNavColor'] = '#272727';
			_opts['mobileUI.hideAddressBar'] = 0;

		// Custom opts
			if (y.length > 1)
			{ 
				x = y[1].split('&');
				for (v in x)
				{
					w = x[v].split('=');
					_opts[w[0]] = w[1];
				}
			}
		
	// Determine viewing modes
		_desktop = _mobile = _fluid = _1000px = _1200px = false;
		v = _opts['use'].split(',');
		
		if ($.inArray('fluid', v) > -1)
			_fluid = true;
		if ($.inArray('desktop', v) > -1)
			_desktop = true;
		if ($.inArray('1000px', v) > -1)
			_1000px = true;
		if ($.inArray('1200px', v) > -1)
			_1200px = true;
		if ($.inArray('mobile', v) > -1)
			_mobile = true;

		if (_mobile && !_fluid && !_1000px && !_1200px && !_desktop)
			_desktop = true;

/*********************/
/* Core              */
/*********************/

	// Legacy IE fixes
		if ($.browser.msie)
		{
			// HTML5 Shiv
				if ($.browser.version < 9)
					_head.append('<script type="text/javascript" src="' + _baseURL + '5grid/html5shiv.js" />');

			// Versions that don't support CSS3 pseudo classes
				if ($.browser.version < 8)
				{
					$(function() {
						$('.5grid, .5grid-layout, .do-5grid').after('<div style="clear: both;"></div>');
					});
				}
		}

	// Insert stylesheets
		_headQueue.pushE_5grid(_baseURL + '5grid/core.css')
		_headQueue.pushE_5grid(_baseURL + _opts['prefix'] + '.css');

/*********************/
/* Responsive        */
/*********************/

	(function() {
		var ww = _window.width(), sw = screen.width, orientation = window.orientation;

		// Fix: On iOS, screen.width is always the width of the device held in portrait mode.
		// Android, however, sets it to the width of the device in its current orientation.
		// This ends up breaking our detection on HD devices held in landscape mode, so we
		// do a little trick here to detect this condition and make things right.
		if (screen.width > screen.height
		&&	Math.abs(orientation) == 90)
			sw = screen.height;

		// Mobile (exclusive)
		if (_mobile && (ww <= 480 || sw <= 480))
		{
			_5grid.isMobile = true;
			_head.prepend('<meta name="viewport" content="initial-scale=1.0; minimum-scale=1.0; maximum-scale=1.0;" />');
			_headQueue.pushE_5grid(_baseURL + '5grid/core-mobile.css');
			
			if (_opts['mobileUI'] == 1)
			{
				_opts['mobileUI.force'] = 1;

				if (_opts['mobileUI.theme'] != 'none')
				{
					_headQueue.pushE_5grid(_baseURL + '5grid/mobileUI-' + _opts['mobileUI.theme'] + '.css');

					if (_opts['mobileUI.themeTitleBarColor'])
						_headQueue.pushI_5grid('#mobileUI-site-titlebar { background: ' + _opts['mobileUI.themeTitleBarColor'] + '; }');

					if (_opts['mobileUI.themeNavColor'])
						_headQueue.pushI_5grid('#mobileUI-site-nav { background: ' + _opts['mobileUI.themeNavColor'] + '; }');
				}
			}

			_headQueue.pushE_5grid(_baseURL + _opts['prefix'] + '-mobile.css');
		}
		else
		{
			// Fluid (exclusive)
			if (_fluid)
			{
				_5grid.isFluid = true;
				_head.prepend('<meta name="viewport" content="width=1280" />');
				_headQueue.pushE_5grid(_baseURL + '5grid/core-desktop.css');
				_headQueue.pushE_5grid(_baseURL + '5grid/core-fluid.css');
				_headQueue.pushE_5grid(_baseURL + _opts['prefix'] + '-fluid.css');
			}
			// Desktop
			else if (_desktop)
			{
				_5grid.isDesktop = true;
				_headQueue.pushE_5grid(_baseURL + '5grid/core-desktop.css');
				_headQueue.pushE_5grid(_baseURL + _opts['prefix'] + '-desktop.css');
			
				// 1200px
				if (ww >= 1200)
				{
					_5grid.is1200px = true;
					_head.prepend('<meta name="viewport" content="width=1280" />');
					_headQueue.pushE_5grid(_baseURL + '5grid/core-1200px.css');
					
					// Load 1200px stylesheet if 1200px was explicitly enabled
					if (_1200px)
						_headQueue.pushE_5grid(_baseURL + _opts['prefix'] + '-1200px.css');
				}
				// 1000px
				else
				{
					_5grid.is1000px = true;
					_head.prepend('<meta name="viewport" content="width=1040" />');
					_headQueue.pushE_5grid(_baseURL + '5grid/core-1000px.css');

					// Load 1000px stylesheet if 1000px was explicitly enabled
					if (_1000px)
						_headQueue.pushE_5grid(_baseURL + _opts['prefix'] + '-1000px.css');
				}
			}
			else
			{
				// 1000px (exclusive)
				if (_1000px && (ww < 1200 || !_1200px))
				{
					_5grid.is1000px = true;
					_head.prepend('<meta name="viewport" content="width=1080" />');
					_headQueue.pushE_5grid(_baseURL + '5grid/core-desktop.css');
					_headQueue.pushE_5grid(_baseURL + '5grid/core-1000px.css');
					_headQueue.pushE_5grid(_baseURL + _opts['prefix'] + '-1000px.css');
				}
				// 1200px (exclusive)
				else if (_1200px && (ww >= 1200 || !_1000px))
				{
					_5grid.is1200px = true;
					_head.prepend('<meta name="viewport" content="width=1280" />');
					_headQueue.pushE_5grid(_baseURL + '5grid/core-desktop.css');
					_headQueue.pushE_5grid(_baseURL + '5grid/core-1200px.css');
					_headQueue.pushE_5grid(_baseURL + _opts['prefix'] + '-1200px.css');
				}
			}
		}

		$(function() { $('.5grid-layout').addClass('5grid'); });
	})();

/*********************/
/* MobileUI          */
/*********************/

	if (_opts['mobileUI.force'] == 1)
		$(function() {
			var body = $('body'), speed = 0, easing = 'swing';
			body.wrapInner('<div id="mobileUI-site-wrapper" />');
		
			// Move primary content
				var main_content = $('.mobileUI-main-content'), main_content_target = $('.mobileUI-main-content-target');
				
				if (main_content.length > 0)
					if (main_content_target.length > 0)
						main_content.prependTo(main_content_target);
					else
						main_content.prependTo(main_content.parent());
		
			// Get site name, nav options
				var site_name = $('.mobileUI-site-name').text();
				var site_nav_options = new Array();
				
				$('.mobileUI-site-nav a').each(function() {
					var t = $(this), indent;
					indent = Math.max(0,t.parents('li').length - 1);
					site_nav_options.push(
						'<a href="' + t.attr('href') + '"><span class="indent-' + indent + '"></span>' + t.text() + '</a>'
					);
				});

			// Configure elements
				var mobileUI_site_titlebar = $('<div id="mobileUI-site-titlebar"><div id="mobileUI-site-title">' + site_name + '</div></div>');
				var mobileUI_site_nav = $('<div id="mobileUI-site-nav"><nav>' + site_nav_options.join('') + '</nav></div>');
				var mobileUI_site_nav_opener = $('<div id="mobileUI-site-nav-opener">' + _opts['mobileUI.openerText'] + '</div>');
				var mobileUI_site_wrapper = $('#mobileUI-site-wrapper');
				var mobileUI_site_group = $().add(mobileUI_site_wrapper).add(mobileUI_site_titlebar);

				body.bind('touchmove', function(e) {
					if (mobileUI_site_nav.isOpen_5grid)
					{
						e.stopPropagation();
						e.preventDefault();
					}
				});

				// Mobile Site Wrapper
					mobileUI_site_wrapper
						.css('position', 'relative')
						.css('z-index', '100')
						.css('top', _opts['mobileUI.titleBarHeight'] + 'px')
						.css('width', '100%')
						//.css('overflow', 'auto')
						.bind(_eventType, function(e) {
							if (mobileUI_site_nav.isOpen_5grid)
							{
								e.preventDefault();
								body.trigger('5grid_closeNav');
							}
						})
						.bind('5grid_top', function(e) {
							if (_isLocked)
								return;
							_isLocked = true;
							body.animate({ scrollTop: 0 }, 400, 'swing', function() { _isLocked = false; });
						});

				// Mobile Site Nav Opener
					mobileUI_site_nav_opener
						.css('position', 'absolute')
						.css('z-index', '152')
						.css('cursor', 'pointer')
						.disableSelection_5grid()
						.appendTo(mobileUI_site_titlebar)
						.bind(_eventType, function(e) {
							e.stopPropagation();
							e.preventDefault();
							body.trigger('5grid_toggleNav');
						});
						
				// Mobile Site Bar
					mobileUI_site_titlebar
						.css('position', (_opts['mobileUI.titleBarFixed'] == 1 ? 'fixed' : 'absolute'))
						.css('z-index', '151')
						.css('top', '0')
						.css('width', '100%')
						.css('overflow', 'hidden')
						.css('height', _opts['mobileUI.titleBarHeight'] + 'px')
						.css('line-height', _opts['mobileUI.titleBarHeight'] + 'px')
						.disableSelection_5grid()
						.prependTo(body);
						
				// Mobile Site Nav
					mobileUI_site_nav
						.css('position', 'fixed')
						.css('z-index', '150')
						.css('top', '0')
						.css('height', '100%')
						.disableSelection_5grid()
						.prependTo(body);

					mobileUI_site_nav
						.css('left', -1 * mobileUI_site_nav.width())
						.hide()
						.click(function(e) {
							e.stopPropagation();
						});
						
					mobileUI_site_nav.find('a')
						.click(function(e) {
							e.preventDefault();
							e.stopPropagation();
							body.trigger('5grid_closeNav', [$(this).attr('href')]);
						});

					if (_isTouch) {
						var _mobileUI_site_nav_pos = 0;
						mobileUI_site_nav
							.css('overflow', 'hidden')
							.bind('touchstart', function(e) {
								_mobileUI_site_nav_pos = mobileUI_site_nav.scrollTop() + event.touches[0].pageY;
							})
							.bind('touchmove', function(e) {
								e.preventDefault();
								e.stopPropagation();
								mobileUI_site_nav.scrollTop(_mobileUI_site_nav_pos - event.touches[0].pageY);
							});
					}
					else
						mobileUI_site_nav.css('overflow', 'auto');

					mobileUI_site_nav.isOpen_5grid = false;

				// Body
					body	
						.css('overflow', (_isTouch ? 'hidden' : 'visible'))
						.bind('5grid_toggleNav', function() {
							if (mobileUI_site_nav.isOpen_5grid)
								body.trigger('5grid_closeNav');
							else
								body.trigger('5grid_openNav');
						})
						.bind('5grid_openNav', function() {
							if (_isLocked)
								return true;
							_isLocked = true;
							var nw = $(window).width() - _opts['mobileUI.openerWidth'];
							mobileUI_site_group
								.css('width', $(window).width())
								.disableSelection_5grid();
							mobileUI_site_nav
								.show()
								.scrollTop(0)
								.css('width', nw)
								.css('left', -1 * nw);
							mobileUI_site_nav.animate({ left: 0 }, speed, easing);
							mobileUI_site_group.animate({ left: nw }, speed, easing, function() {
								_isLocked = false;
								mobileUI_site_nav.isOpen_5grid = true;
								_5grid.trigger('mobileUINavOpen');
							});
						})
						.bind('5grid_closeNav', function(e, url) {
							if (_isLocked)
								return true;
							_isLocked = true;
							var nw = mobileUI_site_nav.width();
							mobileUI_site_nav.animate({ left: -1 * nw }, speed, easing);
							mobileUI_site_group.animate({ left: 0 }, speed, easing, function() {
								mobileUI_site_group
									.css('width', '100%')
									.css('overflow', 'visible')
									.enableSelection_5grid();
								mobileUI_site_wrapper.css('position', 'relative');
								mobileUI_site_titlebar.css('position', (_opts['mobileUI.titleBarFixed'] == 1 ? 'fixed' : 'absolute'));
								mobileUI_site_nav.isOpen_5grid = false;
								mobileUI_site_nav.hide();
								_5grid.trigger('mobileUINavclose');
								_isLocked = false;
								
								if (url)
									window.setTimeout(function() {
										window.location.href = url;
									}, 150);
							});
						});
				
					// Window
						_window
							.bind('orientationchange', function(e) {
								if (mobileUI_site_nav.isOpen_5grid) {
									var nw = $(window).width() - _opts['mobileUI.openerWidth'];
									mobileUI_site_nav.css('width', nw);
									mobileUI_site_group.css('left', nw);
								}
								_5grid.trigger('orientationChange');
							});
				
			// Remove mobileUI-hide elements
				$('.mobileUI-hide').remove();
				
			// Remove address bar
				if (_opts['mobileUI.hideAddressBar'] == 1 && _window.scrollTop() == 0)
					window.scrollTo(0, 1);
		});

/*********************/
/* Head Queue        */
/*********************/

	_headQueue.process_5grid();
	_5grid.isReady = true;

	$(function() { _5grid.readyCheck(); });

})();