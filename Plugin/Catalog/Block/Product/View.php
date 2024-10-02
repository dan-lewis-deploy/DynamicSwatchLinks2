<?php
/*
 * @Author:    Dan Lewis (dan.lewis@deploy.co.uk)
 * @Copyright: 2024 Deploy Ecommerce (https://www.deploy.co.uk/)
 * @Package:   DeployEcommerce_DynamicSwatchLink
 */

namespace DeployEcommerce\DynamicSwatchLink\Plugin\Catalog\Block\Product;

use DeployEcommerce\DynamicSwatchLink\Helper\Settings;
use Exception;
use Magento\Catalog\Block\Product\View as MagentoView;
use Magento\Framework\Registry;
use Magento\Swatches\Helper\Data as SwatchData;

class View
{

    /**
     * @param Settings $settingsHelper
     * @param Registry $registry
     * @param SwatchData $swatchData
     */
    public function __construct(
        protected Settings   $settingsHelper,
        protected Registry   $registry,
        protected SwatchData $swatchData
    )
    {
    }

    /**
     * @param MagentoView $subject
     * @param bool $result
     * @return bool
     * @throws Exception
     */
    public function afterShouldRenderQuantity(MagentoView $subject, bool $result): bool
    {
        if (
            $this->settingsHelper->isEnabled() &&
            $this->settingsHelper->isEnabled(Settings::MODULE_ENABLED_MATRIX) &&
            $this->swatchData->isProductHasSwatch($this->registry->registry('current_product'))
        )
            return false;
        return $result;
    }
}
