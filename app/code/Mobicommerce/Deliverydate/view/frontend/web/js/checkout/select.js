define([
    'ko',
    'jquery',
    'Magento_Ui/js/form/element/select'
    ], function(
        ko,
        $,
        AbstractField
    ){
        'use strict';

        return AbstractField.extend({
            defaults: {
                deliverydateConfig: window.checkoutConfig.mobicommerce.deliverydate,
                elementTmpl: 'Mobicommerce_Deliverydate/form/element/select'
            },

            onUpdate: function () {
                this.bubble('update', this.hasChanged());
            },

            initObservable: function () {
                this._super();

                var newOptions = [];
                this.options.each(function (obj) {
                    newOptions.push({
                        value: obj.value,
                        label: obj.label,
                        disabled: obj.disabled,
                    });
                });

                this.options(newOptions);

                return this;
            }

        });
    }
);


