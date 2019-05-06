define([
        'underscore',
		'ko',
		'jquery',
        'mageUtils',
		'Magento_Ui/js/form/element/date',
		'Mobicommerce_Deliverydate/js/checkout/datepicker'
	], function(
	    _,
		ko,
		$,
        utils,
		AbstractField
	){
		'use strict';
		return AbstractField.extend({
			defaults: {
				elementTmpl: 'Mobicommerce_Deliverydate/form/element/date',
				options: {}
			},
			initConfig: function () {
				this._super();

				this.amdeliveryconf = this.options.amdeliveryconf;
				this.options.beforeShowDay = this.restrictDates.bind(this);

				return this;
			},
			getElem: function(){
				return this;
			},
            prepareDateTimeFormats: function () {
                this.pickerDateTimeFormat = this.options.dateFormat;

                if (this.options.showsTime) {
                    this.pickerDateTimeFormat += ' ' + this.options.timeFormat;
                }
                this.pickerDateTimeFormat = this.normalizeDate(this.pickerDateTimeFormat);

                if (this.dateFormat) {
                    this.inputDateFormat = this.dateFormat;
                }
                this.inputDateFormat = this.normalizeDate(this.inputDateFormat);
                this.outputDateFormat = this.normalizeDate(this.outputDateFormat);

                this.validationParams.dateFormat = this.outputDateFormat;
            },

            /**
             * Converts mage date format to a moment.js format.
             *
             * @param {String} mageFormat
             * @returns {String}
             */
            normalizeDate: function (mageFormat) {
                var result = mageFormat;

                _.each(this.map, function (moment, mage) {
                    result = result.replace(new RegExp(mage,'g'), moment);
                });

                return result;
            },
            map : {
                'D': 'DDD',
                'd': 'D',
                'EEEE': 'dddd',
                'EEE': 'ddd',
                'e': 'd',
                'y': 'Y',
                'a': 'A'
            },
			milliseconds: 3600 * 24 * 1000,

			restrictDates: function (d) {
				if (this.disableSameDay(d)
					|| this.disableNextDay(d)
					|| this.restrictByQuota(d)
					|| this.restrictDateLessToday(d)
				) {
					return [false, ""];
				} else if (this.notRestrictWorkingDays(d)) {
					return [true, ""];
				} else if (this.minDays(d)
							|| this.maxDays(d)
							|| this.restrictDateInterval(d)
							|| this.restrictHolidays(d)
							|| this.daysOfWeek(d)
				) {
					return [false, ""];
				}
				return [true, ""];
			},

			restrictDateLessToday: function (d) {
				var today = new Date(),
					todayYMD = this._getDateYMD(today),
					currentYMD = this._getDateYMD(d);
				return currentYMD < todayYMD;
			},

			notRestrictWorkingDays: function (d) {
				return this.restrictDate(d, this.amdeliveryconf.workingdays);
			},

			daysOfWeek: function (d) {
				return !!(this.amdeliveryconf.days_week && $.inArray(d.getDay(), this.amdeliveryconf.days_week) != -1);
			},

			disableSameDay: function (d) {
				if (this.amdeliveryconf.enabled_same_day == 1) {
					var today = new Date();
					if (today.getYear() == d.getYear()
						&& today.getMonth() == d.getMonth()
						&& today.getDate() == d.getDate()
					) {
                        today = this.setDateTime(this.amdeliveryconf.time_same_day, today);

						var todayCurentTime = new Date();
						var curentH = todayCurentTime.getHours() + this.amdeliveryconf.time_offset;
						todayCurentTime.setHours(curentH);

						if ((+todayCurentTime) > (+today)) {
							return true;
						}
					}
				}
				return false;
			},

			disableNextDay: function (d) {
				if (this.amdeliveryconf.enabled_next_day == 1) {
					var today = new Date(),
						tomorrow = new Date((+today) + this.milliseconds); // restrict only tomorrow
					if (tomorrow.getYear() == d.getYear()
						&& tomorrow.getMonth() == d.getMonth()
						&& tomorrow.getDate() == d.getDate()
					) {
                        today = this.setDateTime(this.amdeliveryconf.time_next_day, today);

						var todayCurrentTime = new Date();

						if ((+todayCurrentTime) > (+today)) {
							return true;
						}
					}
				}
				return false;
			},

            setDateTime: function(stringTime, day) {
                var time = stringTime.split(',');
                day.setHours(time[0]);
                day.setMinutes(time[1]);
                day.setSeconds(time[2]);

                return day;
            },

            /**
             * Is need to restrict day by Quota
             * Is limit for shipping quota of day is not exceeded
             *
             * @param {Date} d
             * @returns {boolean}
             */
			restrictByQuota: function (d) {
				return !!(this.amdeliveryconf.quota[d.getFullYear()]
                && this.amdeliveryconf.quota[d.getFullYear()][d.getMonth() + 1]
                && this.amdeliveryconf.quota[d.getFullYear()][d.getMonth() + 1][d.getDate()]);
			},

            /**
             * Is need to restrict day by Date Interval
             *
             * @param {Date} d
             * @returns {boolean}
             */
			restrictDateInterval: function (d) {
                var isNeedRestrict = false;
                _.each(this.amdeliveryconf.dintervals, function (interval) {
                    var fromYear = interval.from.year;
                    var toYear   = interval.to.year;
                    // prepare month for js
                    var fromMonth = interval.from.month - 1;
                    var toMonth = interval.to.month - 1;
                    var fromDay = interval.from.day;
                    var toDay = interval.to.day;
                    if (toYear == 0 || fromYear == 0) {
                        // is interval for each year
                        fromYear = toYear = d.getFullYear();
                    }
                    if (interval.from.month == 0 || interval.to.month == 0) {
                        // is interval for each month
                        toMonth = fromMonth = d.getMonth();
                    }
                    var inputDate = new Date(d.getFullYear(), d.getMonth(), d.getDate());
                    var fromDate  = new Date(fromYear, fromMonth, fromDay);
                    var toDate    = new Date(toYear, toMonth, toDay);
                    if (fromDate > toDate) {
                        // revert interval
                        // restrict all days in same year from fromDate and to toDate
                        if ((fromYear <= d.getFullYear() && toYear >= d.getFullYear())
                            && (inputDate >= fromDate || inputDate <= toDate)
                        ) {
                            isNeedRestrict = true;
                            return false;
                        }
                    } else {
                        if (inputDate >= fromDate && inputDate <= toDate) {
                            isNeedRestrict = true;
                            return false;
                        }
                    }
                });

                return isNeedRestrict;
			},


            /**
             * Is need to restrict day as Holidays
             *
             * @param {Date} d
             * @returns {boolean}
             */
			restrictHolidays: function (d) {
				return this.restrictDate(d, this.amdeliveryconf.holidays);
			},

            /**
             * Is need to restrict day
             *
             * @param {Date} d
             * @param {Object[]} type
             * @param {Object[]} type.year - year can be 0, if 0 then apply for all years
             * @param {Object[]} type.year.month - month from 0 to 12, where 0 is all months
             * @param {bool} type.year.month.day
             * @returns boolean
             */
            restrictDate: function (d, type) {
                var isset = function(ear, month, day) {
                    return (type[ear] !== void(0)
                    && type[ear][month] !== void(0)
                    && type[ear][month][day] !== void(0));
                };

                if (isset(d.getFullYear(), d.getMonth() + 1, d.getDate())) {
                    return type[d.getFullYear()][d.getMonth() + 1][d.getDate()];
                }
                // 0 - for all month
                if (isset(d.getFullYear(), 0, d.getDate())) {
                    return type[d.getFullYear()][0][d.getDate()];
                }
                // 0 - for all year
                if (isset(0, d.getMonth() + 1, d.getDate())) {
                    return type[0][d.getMonth() + 1][d.getDate()];
                }
                if (isset(0, 0, d.getDate())) {
                    return type[0][0][d.getDate()];
                }

				return false;
			},

			minDays: function (d) {
				if (this.amdeliveryconf.min_days > 0) {
					var today = new Date(),
						resctrict = new Date((+today) + this.amdeliveryconf.min_days * this.milliseconds),
						todayDate = this._getDateYMD(today),
						resctrictDate = this._getDateYMD(resctrict),
						currentDate = this._getDateYMD(d);

					if (currentDate >= todayDate
						&& currentDate < resctrictDate
					) {
						return true;
					}
				}
				return false;
			},

			maxDays: function (d) {
				if (this.amdeliveryconf.max_days > 0) {
					var today = new Date(),
					 	resctrict = new Date((+today) + this.amdeliveryconf.max_days * this.milliseconds),
					 	resctrictDate = this._getDateYMD(resctrict),
					 	currentDate = this._getDateYMD(d);

					if (currentDate >= resctrictDate) {
						return true;
					}
				}
				return false;
			},

			_getDateYMD: function (date) {
				return new Date(date.getFullYear(), date.getMonth(), date.getDate());
			}
		});
	}
);