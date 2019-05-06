define(
    [
        'jquery',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'ko'
    ],
    function($, Component, quote, ko) {
        'use strict';

        function visibilityFields(currentMethod, configMethods, field) {
            if(field) {
                if ($.inArray(currentMethod, configMethods) != -1) {
                    field.visible(true);
                } else {
                    field.visible(false);
                }
            }
        }

        return Component.extend({
            defaults: {
                formSelector: '#checkout-step-shipping_method button',
                template: 'Mobicommerce_Deliverydate/deliverydate',
                deliverydateConfig: window.checkoutConfig.mobicommerce.deliverydate,
                modules: {
                    deliverydateDate: '${ $.name }.deliverydate_date',
                    deliverydateTime: '${ $.name }.deliverydate_time',
                    deliverydateComment: '${ $.name }.deliverydate_comment'
                },
                listens: {
                    '${ $.name }.deliverydate_date:value': 'onChangeDate'
                }
            },
            onChangeDate: function(val){
                if(this.deliverydateConfig.moduleEnabled) {
                    quote.mobicommerceDeliveryDateDate = val;
                    if (this.deliverydateTime()) {
                        if (val) {
                            this.deliverydateTime().disabled(false);
                        } else {
                            this.deliverydateTime().disabled("disabled");
                        }
                        var options = this.deliverydateTime().options();
                        var tintervals = this.deliverydateConfig.restrictTinterval;
                        this.deliverydateTime().value('');
                        var date = new Date(val);
                        var values = [date.getDate(), date.getMonth() + 1];
                        for(var id in values) {
                            values[id] = values[id].toString().replace( /^([0-9])$/, '0$1' );
                        }
                        val = date.getFullYear() + '-' + values[1] + '-' + values[0];
                        $.each(options, function (i, obj) {
                            obj.disabled = tintervals[val] && $.inArray(obj.value, tintervals[val]) != -1;
                        }.bind(this));
                        this.deliverydateTime().options(options);
                        this.deliverydateTime().initObservable();
                    }
                }
            },

            initialize: function () {
                _.bindAll(
                    this,
                    'initShipping'
                );

                this._super();
                if(this.deliverydateConfig.moduleEnabled) {
                    quote.shippingMethod.subscribe(function(){
                        var currentShippingMethod = quote.shippingMethod()['carrier_code']
                            + '_' + quote.shippingMethod()['method_code'];

                        if(this.deliverydateConfig.dateEnabledCarriers) {
                            visibilityFields(currentShippingMethod,
                                this.deliverydateConfig.dateShippingMethods,
                                this.deliverydateDate()
                            );
                        }

                        if (this.deliverydateConfig.timeEnabledCarriers) {
                            visibilityFields(currentShippingMethod,
                                this.deliverydateConfig.timeShippingMethods,
                                this.deliverydateTime()
                            );
                        }

                        if(this.deliverydateConfig.commentEnabledCarriers) {
                            visibilityFields(currentShippingMethod,
                                this.deliverydateConfig.commentShippingMethods,
                                this.deliverydateComment()
                            );
                        }
                    }.bind(this));

                    $.async(this.formSelector, this.initShipping);
                }

                return this;
            },

            initShipping: function (ctx) {
                $(this.formSelector).on( "click", function () {
                    return this.validate();
                }.bind(this));
            },

            validate: function () {
                var allFieldsValid = true;
                if (this.deliverydateDate() && !this.deliverydateDate().validate().valid) {
                    allFieldsValid = false;
                }

                if (this.deliverydateTime() && !this.deliverydateTime().validate().valid) {
                    allFieldsValid = false;
                }

                if (this.deliverydateComment() && !this.deliverydateComment().validate().valid) {
                    allFieldsValid = false;
                }

                return allFieldsValid;
            },

            showGeneralComment: function() {
                return this.deliverydateConfig.generalComment;
            },

            isModuleEnabled: function() {
                return this.deliverydateConfig.moduleEnabled;
            },

            styleMagentoNotice: function () {
                return this.deliverydateConfig.generalCommentStyle == 'notice';
            }
        });
    }
);
