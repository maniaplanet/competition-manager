/*
 * Datetime picker for jQuery Mobile
 *
 * UI inspired from:
 *   - jQuery-UI Datepicker by jQuery team https://github.com/jquery/jquery-ui
 *   - jQuery Mobile Datebox by JTSage https://github.com/jtsage/jquery-mobile-datebox
 * Date functions inspired from:
 *   - flexible-js-formatting by Baron Schwartz http://code.google.com/p/flexible-js-formatting/
 */

(function($, undefined) {
	$.widget('mobile.datetimepicker', $.mobile.widget, {
		options: {
			initSelector: ":jqmData(role='datetime-picker')",
			
			theme: 'c',
			themeHeader: 'a',
			themeSelected: 'b',
			themeToday: 'e',
			startDay: 1,
			showDays: true,
			showTime: true,
			showSeconds: false,
			showNow: true,
			
			minDatetime: null,
			maxDatetime: null,
			stepMinutes: 1,
			stepSeconds: 1,
			
			format: 'Y-m-d H:i',
			
			langs: {
				'': {
					now: 'Now',
					today: 'Today',
					done: 'Done',
					nextMonth: 'Next Month',
					previousMonth: 'Previous Month'
				}
			}
		},
		
		_create: function() {
			var self = this,
				el = this.element;
				
			if(typeof this.options.minDatetime === 'string')
				this.options.minDatetime = Date.parse(this.options.minDatetime, this.options.format);
			if(typeof this.options.maxDatetime === 'string')
				this.options.maxDatetime = Date.parse(this.options.maxDatetime, this.options.format);
			
			var popup = $('<div data-role="popup" class="datetimepicker ui-corner-all"/>'),
				headerBar = $('<div data-role="header" data-theme="' + this.options.themeHeader + '"/>')
					.addClass('ui-header ui-bar-' + this.options.themeHeader)
					.appendTo(popup),
				title = $('<h1 class="ui-title"/>').appendTo(headerBar),
				prevBtn = $('<span>' + this.options.langs['']['previousMonth'] + '</span>')
					.prependTo(headerBar)
					.buttonMarkup({icon: 'arrow-l', iconpos: 'notext', inline: true, theme: this.options.themeHeader})
					.click(function() {
						self._month.setMonth(self._month.getMonth() - 1);
						self.refresh();
					})
					.addClass('ui-btn-left'),
				nextBtn = $('<span>' + this.options.langs['']['nextMonth'] + '</span>')
					.appendTo(headerBar)
					.buttonMarkup({icon: 'arrow-r', iconpos: 'notext', inline: true, theme: this.options.themeHeader})
					.click(function() {
						self._month.setMonth(self._month.getMonth() + 1);
						self.refresh();
					})
					.addClass('ui-btn-right'),
				content = $('<div class="ui-body-c ui-corner-bottom"/>').appendTo(popup),
				calendar = $('<div class="datetimepicker-calendar"/>').appendTo(content),
				clock = $('<div class="datetimepicker-clock"/>').appendTo(content),
				buttonBar = $('<div class="datetimepicker-buttons"/>').appendTo(content),
				nowBtn = $('<div>' + this.options.langs['']['now'] + '</div>')
					.appendTo(buttonBar)
					.buttonMarkup({icon: null, inline: true, mini:true, theme: this.options.themeToday})
					.click(function() {
						self._setToNow();
						self._month = new Date(self._date.getFullYear(), self._date.getMonth(), 1);
						self._update();
						self.refresh();
					}),
				doneBtn = $('<div>' + this.options.langs['']['done'] + '</div>')
					.appendTo(buttonBar)
					.buttonMarkup({icon: null, inline: true, mini:true, theme: this.options.themeSelected})
					.click(function() {
						self._ui.picker.popup('close');
					}),
				
				
				clickHandler = function(e) {
					e.preventDefault();
					
					if(self._ui.daySelected !== null) {
						var theme = self._ui.dayToday !== null && self._ui.daySelected.jqmData('day') === self._ui.dayToday.jqmData('day')
									? self.options.themeToday
									: self.options.theme;
						self._ui.daySelected
							.removeClass('ui-btn-up-' + self.options.themeSelected)
							.jqmData('theme', theme)
							.addClass('ui-btn-up-' + theme);
					}
					
					self._date.setFullYear(self._month.getFullYear());
					self._date.setMonth(self._month.getMonth());
					self._date.setDate($(this).jqmData('day'));
					self._keepDateInBounds();
					self._update();
					self._refreshClock();
					
					theme = $(this).jqmData('theme');
					$(this)
						.removeClass('ui-btn-up-' + theme + ' ui-btn-down-' + theme + ' ui-btn-hover-' + theme)
						.jqmData('theme', self.options.themeSelected)
						.addClass('ui-btn-hover-' + self.options.themeSelected);
					self._ui.daySelected = $(this);
				},
				hoverInHandler = function() {
					var theme = $(this).jqmData('theme');
					$(this).removeClass('ui-btn-up-' + theme + ' ui-btn-down-' + theme)
						.addClass('ui-btn-hover-' + theme)
				},
				hoverOutHandler = function() {
					var theme = $(this).jqmData('theme');
					$(this).removeClass('ui-btn-down-' + theme + ' ui-btn-hover-' + theme)
						.addClass('ui-btn-up-' + theme)
				},
				mouseDownHandler = function() {
					var theme = $(this).jqmData('theme');
					$(this).removeClass('ui-btn-up-' + theme + ' ui-btn-hover-' + theme)
						.addClass('ui-btn-down-' + theme)
				},
				startDragHandler = function(e) {
					var drag = self._ui.drag;
					if(!drag.isMoving) {
						drag.isMoving = true;
						drag.element = $(this).children().first();
						drag.origin = parseInt(drag.element.css('margin-top').replace(/px/i, ''));
						drag.start = e.pageY;
						drag.end = null;
						drag.min = $(drag.element).jqmData('minMargin');
						drag.max = $(drag.element).jqmData('maxMargin');
						e.stopPropagation();
						e.preventDefault();
					}
				},
				moveDragHandler = function(e) {
					var drag = self._ui.drag;
					if(drag.isMoving) {
						drag.end = e.pageY;
						drag.element.css('margin-top', self._keepBetween(drag.origin + drag.end - drag.start, drag.min, drag.max));
						e.preventDefault();
						e.stopPropagation();
					}
				},
				stopDragHandler = function(e) {
					var drag = self._ui.drag;
					if(drag.isMoving) {
						drag.isMoving = false;
						if(drag.end !== null) {
							var diff = Math.round((self._keepBetween(drag.origin + drag.end - drag.start, drag.min, drag.max) - drag.origin) / 31),
								type = drag.element.jqmData('type'),
								step = drag.element.jqmData('step');
							if(diff != 0) {
								self._date['set'+type](self._date['get'+type]() + diff * step);
								self._keepDateInBounds();
								self._update();
								self._refreshClock();
							}
							else
								drag.element.css('margin-top', drag.origin);
							e.preventDefault();
							e.stopPropagation();
						}
						drag.start = null;
						drag.end = null;
					}
				},
				addHoursHandler = function() {
					self._date.setHours(self._date.getHours() + 1);
					self._keepDateInBounds();
					self._update();
					self._refreshClock();
				},
				subHoursHandler = function() {
					self._date.setHours(self._date.getHours() - 1);
					self._keepDateInBounds();
					self._update();
					self._refreshClock();
				},
				addMinutesHandler = function() {
					self._date.setMinutes(self._date.getMinutes() + self.options.stepMinutes);
					self._keepDateInBounds();
					self._update();
					self._refreshClock();
				},
				subMinutesHandler = function() {
					self._date.setMinutes(self._date.getMinutes() - self.options.stepMinutes);
					self._keepDateInBounds();
					self._update();
					self._refreshClock();
				},
				addSecondsHandler = function() {
					self._date.setSeconds(self._date.getSeconds() + self.options.stepSeconds);
					self._keepDateInBounds();
					self._update();
					self._refreshClock();
				},
				subSecondsHandler = function() {
					self._date.setSeconds(self._date.getSeconds() - self.options.stepSeconds);
					self._keepDateInBounds();
					self._update();
					self._refreshClock();
				};
			
			$(document).on('vmousemove', moveDragHandler);
			$(document).on('vmouseup', stopDragHandler);
			
			$.extend(this, {
				_ui: {
					picker: popup,
					
					title: title,
					prevBtn: prevBtn,
					nextBtn: nextBtn,
					
					calendar: calendar,
					dayHandlers: {
						click: clickHandler,
						hoverIn: hoverInHandler,
						hoverOut: hoverOutHandler,
						mouseDown: mouseDownHandler,
						mouseUp: hoverInHandler
					},
					daySelected: null,
					dayToday: null,
					
					clock: clock,
					rollerHandlers: {
						addHours: addHoursHandler,
						subHours: subHoursHandler,
						addMinutes: addMinutesHandler,
						subMinutes: subMinutesHandler,
						addSeconds: addSecondsHandler,
						subSeconds: subSecondsHandler
					},
					drag: {
						element: null,
						origin: null,
						start: null,
						end: null,
						min: null,
						max: null,
						mouseDown: startDragHandler,
						isMoving: false
					},
					
					nowBtn: nowBtn
				},
				_date: null,
				_month: null
			});
			
			$(el).focus(function() {
				self._open();
			})
			
			popup.insertAfter(el).popup({history: false, theme: this.options.theme, positionTo: el, transition: 'pop'});
		},
		_open: function() {
			this._date = Date.parse($(this.element).val(), this.options.format);
			if(this._date === null)
				this._setToNow();
			this._month = new Date(this._date.getFullYear(), this._date.getMonth(), 1);
			this.refresh();
			this._ui.picker.popup('open');
		},
		_refreshHeader: function() {
			var isFirstAllowedMonth = this.options.minDatetime !== null && this._month < this.options.minDatetime,
				isLastAllowedMonth = this.options.maxDatetime !== null && new Date(this._month.getFullYear(), this._month.getMonth() + 1, 1) > this.options.maxDatetime;
			
			this._ui.title.text(Date.monthNames[this._month.getMonth()] + ' ' + this._month.getFullYear());
			this._ui.prevBtn.toggleClass('ui-disabled', isFirstAllowedMonth);
			this._ui.nextBtn.toggleClass('ui-disabled', isLastAllowedMonth);
		},
		_createInactiveDay: function(number) {
			return $('<div class="datetimepicker-calendar-day datetimepicker-inactive">' + number + '</div>');
		},
		_createActiveDay: function(number, theme) {
			return $('<div class="datetimepicker-calendar-day datetimepicker-active">' + number + '</div>')
				.jqmData('day', number)
				.jqmData('theme', theme)
				.on('vclick', this._ui.dayHandlers.click)
				.on('vmouseover', this._ui.dayHandlers.hoverIn)
				.on('vmouseout', this._ui.dayHandlers.hoverOut)
				.on('vmousedown', this._ui.dayHandlers.mouseDown)
				.on('vmouseup', this._ui.dayHandlers.mouseUp)
				.addClass('ui-corner-all ui-btn-up-' + theme);
		},
		_refreshCalendar: function() {
			var now = new Date(),
				isFirstAllowedMonth = this.options.minDatetime !== null && this._month < this.options.minDatetime,
				isLastAllowedMonth = this.options.maxDatetime !== null && new Date(this._month.getFullYear(), this._month.getMonth() + 1, 1) > this.options.maxDatetime,
				isSelectedMonth = this._month.getFullYear() === this._date.getFullYear() && this._month.getMonth() === this._date.getMonth(),
				isCurrentMonth = this._month.getFullYear() === now.getFullYear() && this._month.getMonth() === now.getMonth(),
				firstAllowedDate = isFirstAllowedMonth ? this.options.minDatetime.getDate() : 0,
				lastAllowedDate = isLastAllowedMonth ? this.options.maxDatetime.getDate() : 32;
				
			this._ui.calendar.empty();
			
			if(this.options.showDays) {
				var thead = $('<div class="datetimepicker-calendar-header"/>').appendTo(this._ui.calendar);
				for(var i=0; i<7; ++i)
					thead.append('<div class="datetimepicker-calendar-day">' + Date.shortDayNames[(i + this.options.startDay) % 7] + '</div>');
			}
			
			// Adding days
			var daysInMonth = this._month.getDaysInMonth(),
				daysInPrevMonth = new Date(this._month.getFullYear(), this._month.getMonth() - 1, 1).getDaysInMonth(),
				toFirstOfMonth = (7 + this._month.getDay() - this.options.startDay) % 7,
				trow = $('<div class="datetimepicker-calendar-row"/>').appendTo(this._ui.calendar),
				trowCount = 0,
				trowDay = daysInPrevMonth + 1 - toFirstOfMonth;
			
			// Previous month
			for(; trowCount<toFirstOfMonth; ++trowCount)
				trow.append(this._createInactiveDay(trowDay + trowCount));
			// Current month
			for(trowDay=1; trowDay<=daysInMonth; ++trowDay, ++trowCount) {
				if(trowCount == 7) {
					trow = $('<div class="datetimepicker-calendar-row"/>').appendTo(this._ui.calendar);
					trowCount = 0;
				}
				
				var isSelected = isSelectedMonth && trowDay == this._date.getDate(),
					isToday = isCurrentMonth && trowDay == now.getDate(),
					theme = isSelected ? this.options.themeSelected : (isToday ? this.options.themeToday : this.options.theme),
					dayBtn = this._createActiveDay(trowDay, theme);
				if(isSelected)
					this._ui.daySelected = dayBtn;
				if(isToday)
					this._ui.dayToday = dayBtn;
				if(trowDay < firstAllowedDate || trowDay > lastAllowedDate)
					dayBtn.addClass('ui-disabled');
				trow.append(dayBtn);
			}
			// Next month
			for(trowDay=1; trowCount<7; ++trowDay, ++trowCount)
				trow.append(this._createInactiveDay(trowDay));
		},
		_createRoller: function(options) {
			var self = this,
				roller = $('<div class="datetimepicker-clock-roller"/>')
					.addClass('ui-block-' + options.block),
				upBtn = $('<div/>')
					.appendTo(roller)
					.buttonMarkup({icon: 'plus', iconpos: 'notext', inline: true, theme: this.options.themeHeader})
					.click(self._ui.rollerHandlers['add'+options.type]),
				list = $('<ul class="ui-overlay-shadow"/>')
					.appendTo(roller)
					.on('vmousedown', this._ui.drag.mouseDown)
					.on('mousewheel', function(e,d,dX,dY) {
						e.preventDefault();
						if(dY > 0 && !upBtn.is('.ui-disabled'))
							self._ui.rollerHandlers['add'+options.type]();
						else if(dY < 0 && !downBtn.is('.ui-disabled'))
							self._ui.rollerHandlers['sub'+options.type]();
					}),
				downBtn = $('<div/>')
					.appendTo(roller)
					.buttonMarkup({icon: 'minus', iconpos: 'notext', inline: true, theme: this.options.themeHeader})
					.click(self._ui.rollerHandlers['sub'+options.type]);
			
			var marginTop = 14;
			for(var i=options.min; i<=options.max; i+=options.step) {
				var isSelected = i === self._date['get'+options.type](),
					isCurrent = options.now !== null && i === options.now['get'+options.type](),
					theme = isSelected ? this.options.themeSelected : (isCurrent ? this.options.themeToday : this.options.theme),
					state = isSelected ? 'datetimepicker-selected' : (isCurrent ? 'datetimepicker-current' : '');
				
				if(i > self._date['get'+options.type]())
					marginTop -= 31;
				
				$('<li>' + String.pad(i, 2, '0', String.padding.left) + '</li>')
					.prependTo(list)
					.addClass('ui-body-' + theme + ' ' + state);
			}
			
			var marginMax = 14,
				marginMin = 45 - 31 * list.children().length;
			upBtn.toggleClass('ui-disabled', marginTop === marginMax);
			downBtn.toggleClass('ui-disabled', marginTop === marginMin);
			list.children().first()
				.jqmData('minMargin', marginMin)
				.jqmData('maxMargin', marginMax)
				.jqmData('type', options.type)
				.jqmData('step', options.step)
				.css({
					'border-top-width': 1,
					'margin-top': marginTop
				});
			list.children('.datetimepicker-current')
				.prev().not('.datetimepicker-selected').css('border-bottom-width', 0);
			list.children('.datetimepicker-selected')
				.prev().css('border-bottom-width', 0)
				.next().next('.datetimepicker-current').css('border-top-width', 0);
			
			return roller;
		},
		_refreshClock: function() {
			var now = new Date();
			now.roundMinutes(this.options.stepMinutes);
			now.roundSeconds(this.options.stepSeconds);
			
			var isMinMonth = this.options.minDatetime !== null && this._month < this.options.minDatetime,
				isMaxMonth = this.options.maxDatetime !== null && new Date(this._month.getFullYear(), this._month.getMonth() + 1, 1) > this.options.maxDatetime,
				isMinDate = isMinMonth && this.options.minDatetime.getDate() === this._date.getDate(),
				isMaxDate = isMaxMonth && this.options.maxDatetime.getDate() === this._date.getDate(),
				isMinHour = isMinDate && this.options.minDatetime.getHours() === this._date.getHours(),
				isMaxHour = isMaxDate && this.options.maxDatetime.getHours() === this._date.getHours(),
				isMinMinute = isMinHour && this.options.minDatetime.getMinutes() === this._date.getMinutes(),
				isMaxMinute = isMaxHour && this.options.maxDatetime.getMinutes() === this._date.getMinutes(),
				isCurrentHour = this._date.getHours() === now.getHours(),
				isCurrentMinute = isCurrentHour && this._date.getMinutes() === now.getMinutes(),
				firstHour = isMinDate ? this.options.minDatetime.getHours() : 0,
				lastHour = isMaxDate ? this.options.maxDatetime.getHours() : 23,
				firstMinute = isMinHour ? this.options.minDatetime.getMinutes() : 0,
				lastMinute = isMaxHour ? this.options.maxDatetime.getMinutes() : 59,
				firstSecond = isMinMinute ? this.options.minDatetime.getSeconds() : 0,
				lastSecond = isMaxMinute ? this.options.maxDatetime.getSeconds() : 59,
				a = 42;
			
			this._ui.clock.empty();
			this._ui.clock.append(
				this._createRoller({min: firstHour, max: lastHour, step: 1, type: 'Hours', now: now, block: 'a'})
			);
			this._ui.clock.append(
				this._createRoller({min: firstMinute, max: lastMinute, step: this.options.stepMinutes, type: 'Minutes', now: isCurrentHour ? now : null, block: 'b'})
			);
			if(this.options.stepMinutes === 1 && this.options.showSeconds) {
				this._ui.clock.removeClass('ui-grid-a').addClass('ui-grid-b');
				this._ui.clock.append(
					this._createRoller({min: firstSecond, max: lastSecond, step: this.options.stepSeconds, type: 'Seconds', now: isCurrentMinute ? now : null, block: 'c'})
				);
			}
			else
				this._ui.clock.removeClass('ui-grid-b').addClass('ui-grid-a');
			
			$('<div class="datetimepicker-clock-highlight ui-overlay-shadow"/>')
				.appendTo(this._ui.clock)
				.on('vmousedown mousewheel', function(e,d,dX,dY) {
					$(this).parent().find('ul').each(function() {
						var innerLeft = e.pageX - $(this).offset().left;
						if(innerLeft >= 0 && innerLeft < $(this).width())
							$(this).trigger(e, [d,dX,dY]);
					})
				});
		},
		_refreshButtons: function() {
			if(this.options.showNow) {
				this._ui.nowBtn.find('.ui-btn-text').text(this.options.langs[''][this.options.showTime ? 'now' : 'today']).show();
			}
			else {
				this._ui.nowBtn.hide();
			}
		},
		refresh: function() {
			this._ui.daySelected = null;
			this._ui.dayToday = null;
			
			this._refreshHeader();
			this._refreshCalendar();
			
			if(this.options.showTime) {
				this._refreshClock();
				this._ui.clock.show();
			}
			else
				this._ui.clock.hide();
			
			this._refreshButtons();
		},
		_update: function() {
			if(this._date !== null)
				$(this.element).val(this._date.format(this.options.format));
		},
		_setToNow: function() {
			this._date = new Date();
			this._date.roundMinutes(this.options.stepMinutes);
			this._date.roundSeconds(this.options.stepSeconds);
			this._keepDateInBounds();
		},
		_keepBetween: function(value, min, max) {
			if(value < min)
				return min;
			if(value > max)
				return max;
			return value;
		},
		_keepDateInBounds: function() {
			var types = ['FullYear', 'Month', 'Date', 'Hours', 'Minutes', 'Seconds', 'Milliseconds'],
				override = null;
			if(this.options.minDatetime !== null && this._date < this.options.minDatetime)
				override = this.options.minDatetime;
			if(this.options.maxDatetime !== null && this._date > this.options.maxDatetime)
				override = this.options.maxDatetime;
			if(override !== null) {
				for(var i=0; i<types.length; ++i) {
					this._date['set' + types[i]](override['get' + types[i]]());
				}
			}
		}
	});

	//auto self-init widgets
	$(document).bind('pagecreate create', function(e) {
		$.mobile.datetimepicker.prototype.enhanceWithin(e.target, true);
	});
	
	
	
	
	// extending JS objects
	$.extend(Date, {
		monthNames: ["January","February","March","April","May","June","July","August","September","October","November","December"],
		shortMonthNames: ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
		dayNames: ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],
		shortDayNames: ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],
		y2kYear: 50,
		patterns: {
			ISO8601LongPattern: "Y-m-d\\TH:i:sO",
			ISO8601ShortPattern: "Y-m-d",
			ShortDatePattern: "n/j/Y",
			LongDatePattern: "l, F d, Y",
			FullDateTimePattern: "l, F d, Y g:i:s A",
			MonthDayPattern: "F d",
			ShortTimePattern: "g:i A",
			LongTimePattern: "g:i:s A",
			SortableDateTimePattern: "Y-m-d\\TH:i:s",
			UniversalSortableDateTimePattern: "Y-m-d H:i:sO",
			YearMonthPattern: "F, Y"
		},

		_parsers: [],
		_regexes: [],
		_formatters: [],
		
		_createFormatter: function(format) {
			var funcName = "format" + Date._formatters.length;
			Date._formatters[format] = funcName;

			var code = [],
				special = false,
				ch = '';

			for(var i = 0; i < format.length; ++i) {
				ch = format[i];
				// escaped character
				if (special) {
					special = false;
					code.push("'" + String.escape(ch) + "'");
				}
				// escape character start
				else if(ch == "\\") {
					special = true;
				}
				// escaped string
				else if (ch == '"') {
					var end = format.indexOf('"', i+1);
					if (end==-1)
					{
						end = format.length;
					}
					code.push("'" + String.escape(format.substring(i+1, end)) + "'");
					i = end;
				}
				else {
					code.push(Date._getFormatSnippet(ch));
				}
			}
			eval("Date.prototype." + funcName + " = function(){return " + code.join('+') + ";}");
		},
		_getFormatSnippet: function(character) {
			switch (character) {
				case "d":
					return "String.pad(this.getDate(), 2, '0', String.padding.left)";
				case "D":
					return "Date.dayNames[this.getDay()].substring(0, 3)";
				case "j":
					return "this.getDate()";
				case "l":
					return "Date.dayNames[this.getDay()]";
				case "S":
					return "this.getSuffix()";
				case "w":
					return "this.getDay()";
				case "z":
					return "this.getDayOfYear()";
				case "W":
					return "this.getWeekOfYear()";
				case "F":
					return "Date.monthNames[this.getMonth()]";
				case "m":
					return "String.pad(this.getMonth() + 1, 2, '0', String.padding.left)";
				case "M":
					return "Date.monthNames[this.getMonth()].substring(0, 3)";
				case "n":
					return "(this.getMonth() + 1)";
				case "t":
					return "this.getDaysInMonth()";
				case "L":
					return "(this.isLeapYear() ? 1 : 0)";
				case "Y":
					return "this.getFullYear()";
				case "y":
					return "('' + this.getFullYear()).substring(2, 4)";
				case "a":
					return "(this.getHours() < 12 ? 'am' : 'pm')";
				case "A":
					return "(this.getHours() < 12 ? 'AM' : 'PM')";
				case "g":
					return "(this.getHours() % 12 ? this.getHours() % 12 : 12)";
				case "G":
					return "this.getHours()";
				case "h":
					return "String.pad(this.getHours() % 12 ? this.getHours() % 12 : 12, 2, '0', String.padding.left)";
				case "H":
					return "String.pad(this.getHours(), 2, '0', String.padding.left)";
				case "i":
					return "String.pad(this.getMinutes(), 2, '0', String.padding.left)";
				case "s":
					return "String.pad(this.getSeconds(), 2, '0', String.padding.left)";
				case "X":
					return "String.pad(this.getMilliseconds(), 3, '0', String.padding.left)";
				case "O":
					return "this.getUTCOffset()";
				case "T":
					return "this.getTimezone()";
				case "Z":
					return "(this.getTimezoneOffset() * 60)";
				default:
					return "'" + String.escape(character) + "'";
			}
		},
		parse: function(input, format) {
			if (Date._parsers[format] == null) {
				Date._createParser(format);
			}
			var func = Date._parsers[format];
			return Date[func](input);
		},
		_createParser: function(format) {
			var funcName = "parse" + Date._parsers.length;
			var regexNum = Date._regexes.length;
			var currentGroup = 1;
			Date._parsers[format] = funcName;

			var code = "Date." + funcName + " = function(input){\n"
				+ "var y = -1, m = -1, d = -1, h = -1, i = -1, s = -1, ms = -1, z = 0;\n"
				+ "var date = new Date();\n"
				+ "y = date.getFullYear();\n"
				+ "m = date.getMonth();\n"
				+ "d = date.getDate();\n"
				+ "var results = input.match(Date._regexes[" + regexNum + "]);\n"
				+ "if (results && results.length > 0) {" ;
			var regex = "";

			var special = false;
			var ch = '';
			for (var i = 0; i < format.length; ++i) {
				ch = format.charAt(i);
				if(special) {
					special = false;
					regex += RegExp.escape(ch);
				}
				else if(ch == "\\") {
					special = true;
				}
				else {
					var obj = Date._getFormatRegex(ch, currentGroup);
					currentGroup += obj.g;
					regex += obj.s;
					if (obj.g && obj.c) {
						code += obj.c;
					}
				}
			}

			code += 'if(y>0){if(m>=0){if(d>0){if(h>=0){if(i>=0){if(s>=0){if(ms>=0)'
				+ 'return new Date(y,m,d,h,i,s,ms).applyOffset(z);'
				+ 'return new Date(y,m,d,h,i,s).applyOffset(z);}'
				+ 'return new Date(y,m,d,h,i).applyOffset(z);}'
				+ 'return new Date(y,m,d,h).applyOffset(z);}'
				+ 'return new Date(y,m,d).applyOffset(z);}'
				+ 'return new Date(y,m,1).applyOffset(z);}'
				+ 'return new Date(y,0,1).applyOffset(z);}}'
				+ 'return null;}'

			Date._regexes[regexNum] = new RegExp("^" + regex + "$");
			eval(code);
		},
		_getFormatRegex: function(character, currentGroup) {
			switch (character) {
				case "d":
				case "j":
					return {
						g:1,
						c:"d = parseInt(results[" + currentGroup + "], 10);",
						s:"(\\d{1,2})"
					};
				case "D":
					return {
						g:0,
						c:null,
						s:"(?:" + Date.shortDayNames.join("|") + ")"
					};
				case "l":
					return {
						g:0,
						c:null,
						s:"(?:" + Date.dayNames.join("|") + ")"
						};
				case "S":
					return {
						g:0,
						c:null,
						s:"(?:st|nd|rd|th)"
					};
				case "z":
					return {
						g:0,
						c:null,
						s:"(?:\\d{1,3})"
					};
				case "F":
					return {
						g:1,
						c:"m = Date.monthNames.indexOf(results[" + currentGroup + "]);",
						s:"(" + Date.monthNames.join("|") + ")"
					};
				case "M":
					return {
						g:1,
						c:"m = Date.shortMonthNames.indexOf(results[" + currentGroup + "]);",
						s:"(" + Date.shortMonthNames.join("|") + ")"
					};
				case "m":
					return {
						g:1,
						c:"m = parseInt(results[" + currentGroup + "], 10) - 1;",
						s:"(0[1-9]|1[0-2])"
					};
				case "n":
					return {
						g:1,
						c:"m = parseInt(results[" + currentGroup + "], 10) - 1;",
						s:"(1[0-2]*|[2-9])"
					};
				case "Y":
					return {
						g:1,
						c:"y = parseInt(results[" + currentGroup + "], 10);",
						s:"(\\d{4})"
					};
				case "y":
					return {
						g:1,
						c:"var ty = parseInt(results[" + currentGroup + "], 10);"
						+ "y = ty > Date.y2kYear ? 1900 + ty : 2000 + ty;",
						s:"(\\d{1,2})"
					};
				case "a":
					return {
						g:1,
						c:"if(results[" + currentGroup + "] == 'am') {"
						+ "if(h == 12) { h = 0; }"
						+ "} else if (h < 12) { h += 12; }",
						s:"(am|pm)"
					};
				case "A":
					return {
						g:1,
						c:"if(results[" + currentGroup + "] == 'AM') {"
						+ "if(h == 12) { h = 0; }"
						+ "} else if (h < 12) { h += 12; }",
						s:"(AM|PM)"
					};
				case "g":
					return {
						g:1,
						c:"h = parseInt(results[" + currentGroup + "], 10);",
						s:"(1[0-2]*|[2-9])"
					};
				case "h":
					return {
						g:1,
						c:"h = parseInt(results[" + currentGroup + "], 10);",
						s:"(0[0-9]|1[0-2])"
					};
				case "G":
					return {
						g:1,
						c:"h = parseInt(results[" + currentGroup + "], 10);",
						s:"(0|1[0-9]*|2[0-3]*|[3-9])"
					};
				case "H":
					return {
						g:1,
						c:"h = parseInt(results[" + currentGroup + "], 10);",
						s:"([01][0-9]|[2][0-3])"
					};
				case "i":
					return {
						g:1,
						c:"i = parseInt(results[" + currentGroup + "], 10);",
						s:"([0-5][0-9])"
					};
				case "s":
					return {
						g:1,
						c:"s = parseInt(results[" + currentGroup + "], 10);",
						s:"([0-5][0-9])"
					};
				case "X":
					return {
						g:1,
						c:"ms = parseInt(results[" + currentGroup + "], 10);",
						s:"(\\d{3})"
					};
				case "O":
				case "P":
					return {
						g:1,
						c:"z = Date._parseOffset(results[" + currentGroup + "], 10);",
						s:"(Z|[+-]\\d{2}:?\\d{2})"
					}; // "Z", "+05:00", "+0500" all acceptable.
				case "T":
					return {
						g:0,
						c:null,
						s:"[A-Z]{3}"
					};
				case "Z":
					return {
						g:1,
						c:"s = parseInt(results[" + currentGroup + "], 10);",
						s:"([+-]\\d{1,5})"
					};
				default:
					return {
						g:0,
						c:null,
						s:RegExp.escape(character)
					};
			}
		},
		_parseOffset: function(str) {
			if(str == "Z") {
				return 0 ;
			} // UTC, no offset.
			var seconds = parseInt(str[0] + str[1] + str[2]) * 3600 ; // e.g., "+05" or "-08"
			if(str[3] == ":") {            // "+HH:MM" is preferred iso8601 format ("O")
				seconds += parseInt(str[4] + str[5]) * 60;
			} else {                      // "+HHMM" is frequently used, though. ("P")
				seconds += parseInt(str[3] + str[4]) * 60;
			}
			return seconds;
		}
	});
	
	$.extend(Date.prototype, {
		format: function(format, ignore_offset) {
			if (Date._formatters[format] == null) {
				Date._createFormatter(format);
			}
			var func = Date._formatters[format];
			if (ignore_offset || ! this.offset) {
				return this[func]();
			} else {
				return new Date(this.getTime() + this.offset * 60000)[func]();
			}
		},
		applyOffset: function(offset) {
			this.offset = offset;
			this.setMinutes(this.getMinutes() - this.offset);
			return this ;
		},
		getTimezone: function() {
			return this.toString().replace(
				/^.*? ([A-Z]{3}) [0-9]{4}.*$/, "$1").replace(
				/^.*?\(([A-Z])[a-z]+ ([A-Z])[a-z]+ ([A-Z])[a-z]+\)$/, "$1$2$3").replace(
				/^.*?[0-9]{4} \(([A-Z]{3})\)/, "$1");
		},
		getUTCOffset: function() {
			return (this.getTimezoneOffset() > 0 ? "-" : "+")
				+ String.pad(Math.floor(this.getTimezoneOffset() / 60), 2, "0", String.padding.left)
				+ String.pad(this.getTimezoneOffset() % 60, 2, "0", String.padding.left);
		},
		getDayOfYear: function() {
			return [0,31,59,90,120,151,181,212,243,273,304,334][this.getMonth()]
				+ (this.getMonth() > 1 && this.isLeapYear() ? 1 : 0)
				+ this.getDate() - 1;
		},
		getWeekOfYear: function() {
			var jan1 = new Date(this.getFullYear(), 0, 1);
			return String.pad((jan1.getDay() - this.getDay() + this.getDayOfYear()) / 7, 2, "0", String.padding.left);
		},
		isLeapYear: function() {
			var year = this.getFullYear();
			return ((year & 3) == 0 && (year % 100 || (year % 400 == 0 && year)));
		},
		getFirstDayOfMonth: function() {
			var day = (this.getDay() - (this.getDate() - 1)) % 7;
			return (day < 0) ? (day + 7) : day;
		},
		getLastDayOfMonth: function() {
			var day = (this.getDay() + (Date.getDaysInMonth() - this.getDate())) % 7;
			return (day < 0) ? (day + 7) : day;
		},
		getDaysInMonth: function() {
			return [31,this.isLeapYear()?29:28,31,30,31,30,31,31,30,31,30,31][this.getMonth()];
		},
		getSuffix: function() {
			switch (this.getDate()) {
				case 1:
				case 21:
				case 31:
					return "st";
				case 2:
				case 22:
					return "nd";
				case 3:
				case 23:
					return "rd";
				default:
					return "th";
			}
		},
		roundMinutes: function(step) {
			if(step > 1)
				this.setMinutes(Math.round(this.getMinutes() / step) * step);
		},
		roundSeconds: function(step) {
			if(step > 1)
				this.setSeconds(Math.round(this.getSeconds() / step) * step);
		}
	});

	$.extend(String, {
		padding: {
			left: 1,
			right: 2,
			both: 3
		},
		pad: function(input, length, str, direction) {
			input = '' + input;
			if(typeof length === 'undefined')
				length = 0;
			if(typeof str === 'undefined')
				str = ' ';
			if(typeof direction === 'undefined')
				direction = String.padding.right;

			var needed = length - input.length;
			if(needed <= 0)
				return input;

			var repeater = function(s, len) {
				var collect = '';

				while(collect.length < len)
					collect += s;

				return collect.substr(0, len);
			};

			switch(direction) {
				case String.padding.left:
					return repeater(str, needed) + input;
				case String.padding.both:
					var half = repeater(str, Math.ceil(needed / 2));
					return (half + input + half).substr(0, length);
				default:
					return input + repeater(str, needed);
			}
		},
		escape: function(string) {
			return string.replace(/('|\\)/g, "\\$1");
		}
	});

	$.extend(RegExp, {
		escape: function(string) {
			return string.replace(/[\\[\]{}().+*?^$]/g, "\\$1");
		}
	});
})(jQuery);
