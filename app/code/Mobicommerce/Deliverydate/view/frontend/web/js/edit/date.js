define([
		'jquery',
        'Mobicommerce_Deliverydate/js/checkout/date'
	], function(
		$,
        checkoutDate
	){
		'use strict';
		return  checkoutDate.extend({

            defaults  : {
                options : {}
            },
            initConfig: function (options) {
                // drown UI functional
                this.amdeliveryconf = options.amdeliveryconf;
            },
            initObservable: function() {
                // drown UI functional
                return this;
            },
            setInitialValue: function() {
                // drown UI functional
                return this;
            },
            initSwitcher: function() {
                // drown UI functional
                return this;
            }
        });
	}
);