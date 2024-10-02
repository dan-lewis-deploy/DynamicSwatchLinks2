<?php
/*
 * @Author:    Dan Lewis (dan.lewis@deploy.co.uk)
 * @Copyright: 2024 Deploy Ecommerce (https://www.deploy.co.uk/)
 * @Package:   DeployEcommerce_DynamicSwatchLink
 */

namespace DeployEcommerce\DynamicSwatchLink\Plugin\Swatches\Block\Product\Renderer;

use DeployEcommerce\DynamicSwatchLink\Helper\Settings as DynamicSwatchLinkSettings;
use Exception;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Swatches\Block\Product\Renderer\Configurable as SwatchConfigurable;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\SwatchAttributesProvider;

class Configurable extends SwatchConfigurable
{

    const string DYNAMIC_SWATCH_RENDERER_TEMPLATE = 'DeployEcommerce_DynamicSwatchLink::product/view/renderer.phtml';

    /**
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param EncoderInterface $jsonEncoder
     * @param Data $helper
     * @param CatalogProduct $catalogProduct
     * @param CurrentCustomer $currentCustomer
     * @param PriceCurrencyInterface $priceCurrency
     * @param ConfigurableAttributeData $configurableAttributeData
     * @param SwatchData $swatchHelper
     * @param DynamicSwatchLinkSettings $dynamicSwatchLinkSettings
     * @param PricingHelper $pricingHelper
     * @param Media $swatchMediaHelper
     * @param array $data
     * @param SwatchAttributesProvider|null $swatchAttributesProvider
     * @param UrlBuilder|null $imageUrlBuilder
     */
    public function __construct(
        Context                             $context,
        ArrayUtils                          $arrayUtils,
        EncoderInterface                    $jsonEncoder,
        Data                                $helper, CatalogProduct $catalogProduct,
        CurrentCustomer                     $currentCustomer,
        PriceCurrencyInterface              $priceCurrency,
        ConfigurableAttributeData           $configurableAttributeData,
        SwatchData                          $swatchHelper,
        protected DynamicSwatchLinkSettings $dynamicSwatchLinkSettings,
        protected PricingHelper             $pricingHelper,
        Media                               $swatchMediaHelper, array $data = [],
        SwatchAttributesProvider            $swatchAttributesProvider = null,
        UrlBuilder                          $imageUrlBuilder = null
    )
    {
        parent::__construct(
            $context,
            $arrayUtils,
            $jsonEncoder,
            $helper,
            $catalogProduct,
            $currentCustomer,
            $priceCurrency,
            $configurableAttributeData,
            $swatchHelper,
            $swatchMediaHelper,
            $data,
            $swatchAttributesProvider,
            $imageUrlBuilder
        );
    }

    /**
     * @return false|string
     */
    public function renderTableData(): false|string
    {
        $data = [];
        foreach ($this->getTableData() as $dataKey => $dataValue) {
            $dataRow = [];
            foreach ($dataValue as $dataValueKey => $dataValueValue) {
                if (is_array($dataValueValue)) {
                    if (empty($dataRow[$dataValueKey])) $dataRow[$dataValueKey] = '';
                    $swatchId = (string)array_key_first($dataValueValue);
                    $swatchData = $dataValueValue[$swatchId];
                    switch ($swatchData['type']) {
                        case '3':
                            $dataRow[$dataValueKey] .= '<div class="swatch-option ' . ($swatchData["custom_style"] ?? "") . '" ';
                            $dataRow[$dataValueKey] .= 'tabindex="-1" ';
                            $dataRow[$dataValueKey] .= 'data-option-type="3" ';
                            $dataRow[$dataValueKey] .= 'data-option-id="' . $swatchId . '" ';
                            $dataRow[$dataValueKey] .= 'data-option-label="' . ($swatchData["label"] ?? "") . '" ';
                            $dataRow[$dataValueKey] .= 'data-option-tooltip-thumb="" ';
                            $dataRow[$dataValueKey] .= 'data-option-tooltip-value="" ';
                            $dataRow[$dataValueKey] .= '></div>';
                            break;
                        case '2':
                            $swatchThumbPath = $this->getSwatchPath('swatch_thumb', $swatchData['value']);
                            $swatchImagePath = $this->getSwatchPath('swatch_image', $swatchData['value']);
                            $dataRow[$dataValueKey] .= '<div class="swatch-option image ' . ($swatchData["custom_style"] ?? "") . '" ';
                            $dataRow[$dataValueKey] .= 'tabindex="-1" ';
                            $dataRow[$dataValueKey] .= 'data-option-type="2" ';
                            $dataRow[$dataValueKey] .= 'data-option-id="' . $swatchId . '" ';
                            $dataRow[$dataValueKey] .= 'data-option-label="' . ($swatchData['label'] ?? "") . '" ';
                            $dataRow[$dataValueKey] .= 'data-option-tooltip-thumb="' . $swatchThumbPath . '" ';
                            $dataRow[$dataValueKey] .= 'data-option-tooltip-value="" ';
                            $dataRow[$dataValueKey] .= '></div>';
                            break;
                        case '1':
                            $dataRow[$dataValueKey] .= '<div class="swatch-option color ' . ($swatchData['custom_style'] ?? "") . '" ';
                            $dataRow[$dataValueKey] .= 'tabindex="-1" ';
                            $dataRow[$dataValueKey] .= 'data-option-type="1" ';
                            $dataRow[$dataValueKey] .= 'data-option-id="' . $swatchId . '" ';
                            $dataRow[$dataValueKey] .= 'data-option-label="' . ($swatchData['label'] ?? "") . '" ';
                            $dataRow[$dataValueKey] .= 'data-option-tooltip-thumb="" ';
                            $dataRow[$dataValueKey] .= 'data-option-tooltip-value="' . $swatchData['value'] . '" ';
                            $dataRow[$dataValueKey] .= '></div>';
                            break;
                        default:
                            $dataRow[$dataValueKey] .= '<div class="swatch-option text ' . ($swatchData['custom_style'] ?? "") . '" ';
                            $dataRow[$dataValueKey] .= 'tabindex="-1" ';
                            $dataRow[$dataValueKey] .= 'data-option-type="0" ';
                            $dataRow[$dataValueKey] .= 'data-option-id="' . $swatchId . '" ';
                            $dataRow[$dataValueKey] .= 'data-option-label="' . ($swatchData['label'] ?? "") . '" ';
                            $dataRow[$dataValueKey] .= 'data-option-tooltip-thumb="" ';
                            $dataRow[$dataValueKey] .= 'data-option-tooltip-value="" ';
                            $dataRow[$dataValueKey] .= '>' . $swatchData['value'];
                            $dataRow[$dataValueKey] .= '</div>';
                            break;
                    }
                } else {
                    $dataRow[$dataValueKey] = $dataValueValue;
                }
            }
            $dataRow["qty"] = '<div class="qty-box">';
            $dataRow["qty"] .= '<span class="decreaseqty qty-button">-</span>';
            $dataRow["qty"] .= '<input class="qty-input" name="matrix-inputs[' . $dataValue["sku"] . ']" type="number" min="0" step="1" qtyincrements="1" value="0" max="100" style="width: 54px;">';
            $dataRow["qty"] .= '<span class="increaseqty qty-button">+</span>';
            $dataRow["qty"] .= '</div>';
            $data[] = $dataRow;
        }
        return json_encode($data);
    }

    /**
     * @return array
     */
    public function getTableData(): array
    {
        $configurationData = [];
        foreach ($this->getAllowProducts() as $simpleProduct) {
            foreach ($this->getTableHeaders() as $headerKey => $headerLabel) {
                $swatchData = $this->swatchHelper->getSwatchesByOptionsId([$simpleProduct->getData($headerKey)]);
                if (!empty($swatchData) && isset($swatchData[array_key_first($swatchData)]['type'])) {
                    $configurationData[$simpleProduct->getId()][$headerKey] = $swatchData;
                } else {
                    $configurationData[$simpleProduct->getId()][$headerKey] = $simpleProduct->getData($headerKey);
                }
            }
            $configurationData[$simpleProduct->getId()]['sku'] = $simpleProduct->getSku();
            $configurationData[$simpleProduct->getId()]['price'] = $this->pricingHelper->currency(
                $simpleProduct->getFinalPrice(),
                true,
                false
            );
        }
        return $configurationData;
    }

    /**
     * @return array
     */
    public function getTableHeaders(): array
    {
        $headers = [];
        foreach ($this->getAllowAttributes() as $attribute) {
            $headers[$attribute->getProductAttribute()->getAttributeCode()] = $attribute->getLabel();
        }
        $headers['sku'] = __('SKU');
        $headers['price'] = __('Price');
        return $headers;
    }

    /**
     * @param $type
     * @param $filename
     * @return string
     */
    public function getSwatchPath($type, $filename): string
    {
        return $this->swatchMediaHelper->getSwatchAttributeImage($type, $filename);
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function getRendererTemplate(): string
    {
        if (
            $this->isProductHasSwatchAttribute() &&
            $this->dynamicSwatchLinkSettings->isEnabled() &&
            $this->dynamicSwatchLinkSettings->isEnabled(DynamicSwatchLinkSettings::MODULE_ENABLED_MATRIX)
        ) {
            return self::DYNAMIC_SWATCH_RENDERER_TEMPLATE;
        }
        return $this->isProductHasSwatchAttribute() ?
            self::SWATCH_RENDERER_TEMPLATE : self::CONFIGURABLE_RENDERER_TEMPLATE;
    }


}
