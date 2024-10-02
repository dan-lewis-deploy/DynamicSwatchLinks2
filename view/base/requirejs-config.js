/*
 * @Author:    Dan Lewis (dan.lewis@deploy.co.uk)
 * @Copyright: 2024 Deploy Ecommerce (https://www.deploy.co.uk/)
 * @Package:   DeployEcommerce_DynamicSwatchLink
 */

var config = {
    config: {
        mixins: {
            'Magento_Swatches/js/swatch-renderer': {
                'DeployEcommerce_DynamicSwatchLink/js/swatch-renderer-mixin': true
            }
        }
    }
};
