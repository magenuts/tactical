define(
    [
        'jquery',
        'uiComponent',
        'Magento_Checkout/js/model/quote'
    ],
    function($, Component, quote) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Mobicommerce_Deliverydate/deliverydate-sidebar'
            },

            getDeliveryDate: function() {
               if (quote.mobicommerceDeliveryDate && quote.mobicommerceDeliveryDate.dateFormated) {
                   return quote.mobicommerceDeliveryDate.dateFormated;
               }
                return '';
            },

            getDeliveryTime: function() {
               if (quote.mobicommerceDeliveryDate && quote.mobicommerceDeliveryDate.time) {
                   return quote.mobicommerceDeliveryDate.time;
               }
                return '';
            },

            getDeliveryComment: function() {
               if (quote.mobicommerceDeliveryDate && quote.mobicommerceDeliveryDate.comment) {
                   return quote.mobicommerceDeliveryDate.comment;
               }
                return '';
            },

            isModuleEnabled: function() {
                return window.checkoutConfig.mobicommerce.deliverydate.moduleEnabled;
            }
        });
    }
);
