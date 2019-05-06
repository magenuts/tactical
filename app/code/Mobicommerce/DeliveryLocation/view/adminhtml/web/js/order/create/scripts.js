/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require([
    "jquery",
    'Magento_Ui/js/modal/alert'    
], function($, alert){
    $('#submit_order_top_button,button[onclick*="order.submit()"]').attr("onclick","")
    
    $('#submit_order_top_button,button[onclick=""]').on("click",function()
        {
            $.ajax({
                method: "POST",
                url: window.DeliveryLocationValidateUrl,
                data: $("#edit_form").serialize('shippingAddress'),
                dataType: "json",
                showLoader: true ,
                success:function(data)
                {
                    if(data.status)
                    {
                        order.submit();
                    }
                    else
                    {
                        alert({content:data.message})
                        return false;
                    }
                }
            });
            
        }
    );
});

