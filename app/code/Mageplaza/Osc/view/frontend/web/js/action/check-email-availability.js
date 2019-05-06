/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'mage/storage',
    'Mageplaza_Osc/js/model/resource-url-manager',
    'Magento_Checkout/js/model/quote',
], function (storage, resourceUrlManager, quote) {
    'use strict';

    return function (email) {
        return storage.post(
            resourceUrlManager.getUrlForCheckIsEmailAvailable(quote),
            JSON.stringify({
                customerEmail: email
            }),
            false
        );
    };
});
