define(
    [
        'jquery',
        'underscore',
        'Magento_Ui/js/form/form',
        'ko',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/create-shipping-address',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-address/form-popup-state',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Ui/js/modal/modal',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Checkout/js/checkout-data',
        'uiRegistry',
        'mage/translate',
        'Magento_Checkout/js/model/shipping-rate-service',
        'mage/url',
        'Magento_Ui/js/modal/alert',
        'Magento_Checkout/js/model/full-screen-loader'  
        ],function (
        $,
        _,
        Component,
        ko,
        customer,
        addressList,
        addressConverter,
        quote,
        createShippingAddress,
        selectShippingAddress,
        shippingRatesValidator,
        formPopUpState,
        shippingService,
        selectShippingMethodAction,
        rateRegistry,
        setShippingInformationAction,
        stepNavigator,
        modal,
        checkoutDataResolver,
        checkoutData,
        registry,
        $t,
        shippingRateService,
        url,
        alert,
        fullScreenLoader
        ) {
    'use strict';
     

    var mixin = {
        
        validateShippingInformation: function(){
            
            var $this = this;
            var flag =  this._super();
            var message = "";
            if (flag) 
            {
                fullScreenLoader.startLoader();
                $this.isLoading(true);
                var addr_data = JSON.stringify(quote.shippingAddress());
                $.ajax({
                    method: "POST",
                    url: url.build('mobi_delivery_location/validate'),
                    data: JSON.parse(addr_data),
                    dataType: "json",
                    async: false,
                    showLoader: true ,
                    success:function(data)
                    {
                        fullScreenLoader.stopLoader();
                        $this.isLoading(false);
                        if(data.status)
                        {
                            flag = true;
                        }
                        else
                        {
                            flag = false
                            message = data.message;                            
                        }
                    }
                });

                if(!flag)
                {
                    alert({content:$t(message)});
                }
            }
            

            return flag;            
        }
    };

    return function (target) { // target == Result that Magento_Ui/.../default returns.
        return target.extend(mixin); // new result that all other modules receive 
    };
});