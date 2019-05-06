define(
    [
        'jquery',
        'underscore',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'mage/storage',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/select-billing-address'
    ],
    function (
        $,
        _,
        ko,
        quote,
        resourceUrlManager,
        storage,
        paymentService,
        methodConverter,
        errorProcessor,
        fullScreenLoader,
        selectBillingAddressAction
    ) {
        'use strict';

        return {
            saveShippingInformation: function () {
                var payload;

                if (!quote.billingAddress()) {
                    selectBillingAddressAction(quote.shippingAddress());
                }

                payload = {
                    addressInformation: {
                        shipping_address: quote.shippingAddress(),
                        billing_address: quote.billingAddress(),
                        shipping_method_code: quote.shippingMethod().method_code,
                        shipping_carrier_code: quote.shippingMethod().carrier_code
                    }
                };

                this.extendPayload(payload);

                fullScreenLoader.startLoader();

                return storage.post(
                    resourceUrlManager.getUrlForSetShippingInformation(quote),
                    JSON.stringify(payload)
                ).done(
                    function (response) {
                        quote.setTotals(response.totals);
                        paymentService.setPaymentMethods(methodConverter(response.payment_methods));
                        fullScreenLoader.stopLoader();
                    }
                ).fail(
                    function (response) {
                        errorProcessor.process(response);
                        fullScreenLoader.stopLoader();
                    }
                );
            },

            extendPayload: function (payload) {
                quote.mobicommerceDeliveryDate = [];
                quote.mobicommerceDeliveryDate.date = quote.mobicommerceDeliveryDateDate || $('[name="mobideliverydate_date"]').val();
                quote.mobicommerceDeliveryDate.dateFormated = $('[name="mobideliverydate_date"]').val();
                quote.mobicommerceDeliveryDate.time = $('[name="mobideliverydate_time"]').val() ?
                    $('[name="mobideliverydate_time"] option:selected').text() : '';
                quote.mobicommerceDeliveryDate.comment = $('[name="mobideliverydate_comment"]').val();

                var deliveryData = {
                    mobideliverydate_date: quote.mobicommerceDeliveryDate.date,
                    mobideliverydate_time: $('[name="mobideliverydate_time"]').val(),
                    mobideliverydate_comment: quote.mobicommerceDeliveryDate.comment
                };

                if (!payload.addressInformation.hasOwnProperty('extension_attributes')) {
                    payload.addressInformation.extension_attributes = {};
                }

                payload.addressInformation.extension_attributes = _.extend(
                    payload.addressInformation.extension_attributes,
                    deliveryData
                )
            }
        };
    }
);
