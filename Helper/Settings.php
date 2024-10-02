<?php
/*
 * @Author:    Dan Lewis (dan.lewis@deploy.co.uk)
 * @Copyright: 2024 Deploy Ecommerce (https://www.deploy.co.uk/)
 * @Package:   DeployEcommerce_DynamicSwatchLink
 */

declare(strict_types=1);

namespace DeployEcommerce\DynamicSwatchLink\Helper;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Swatches\Helper\Data as SwatchData;
use Psr\Log\LoggerInterface;

class Settings extends AbstractHelper
{
    public const LOG_PREFIX = "DeployEcommerce - DynamicSwatchLink";
    public const MODULE_ENABLED = "dynamic_swatch_link/general_settings/enable";
    public const MODULE_ENABLE_PRE_SELECT_LINKS = "dynamic_swatch_link/listing_page/enable";
    public const MODULE_ENABLED_MATRIX = "dynamic_swatch_link/display_page/enable";
    public const DEFAULT_FORM_TEMPLATE = "Magento_Catalog::product/view/form.phtml";
    public const MATRIX_FORM_TEMPLATE = "DeployEcommerce_DynamicSwatchLink::product/view/form.phtml";


    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param DirectoryList $directoryList
     * @param Filesystem $fileSystem
     * @param File $file
     * @param Product $product
     * @param WriterInterface $configWriter
     * @param SwatchData $swatchData
     * @param Registry $registry
     */
    public function __construct(
        public Context                  $context,
        protected LoggerInterface       $logger,
        protected StoreManagerInterface $storeManager,
        protected DirectoryList         $directoryList,
        protected Filesystem            $fileSystem,
        protected File                  $file,
        protected Product               $product,
        protected WriterInterface       $configWriter,
        protected SwatchData            $swatchData,
        protected Registry              $registry
    )
    {
        parent::__construct($context);
    }

    /**
     * @param $config
     * @return mixed
     */
    public function getConfig($config): mixed
    {
        return $this->scopeConfig->getValue($config, $this->getStoreScope());
    }

    /**
     * @return string
     */
    private function getStoreScope(): string
    {
        return ScopeInterface::SCOPE_STORE;
    }

    /**
     * @param $config
     * @param $value
     * @return bool
     */
    public function setConfig($config, $value): bool
    {
        try {
            $this->configWriter->save($config, $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
        } catch (Exception $exception) {
            $this->logError($exception->getMessage());
            return false;
        }
        return true;
    }

    /**
     * @param string $message
     * @param $resource
     * @return void
     */
    public function logError(string $message = "", $resource = null): void
    {
        $this->logger->error(
            self::LOG_PREFIX .
            " - ERROR - " .
            $message
        );
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getMatrixTemplate(): string
    {
        $template = self::DEFAULT_FORM_TEMPLATE;
        if (
            $this->isEnabled() &&
            $this->isEnabled(self::MODULE_ENABLED_MATRIX) &&
            $this->swatchData->isProductHasSwatch($this->registry->registry('current_product'))
        ) {
            $template = self::MATRIX_FORM_TEMPLATE;
        }
        return $template;
    }

    /**
     * @param $config
     * @return mixed
     * @throws Exception
     */
    public function isEnabled($config = null): mixed
    {
        $status = $this->scopeConfig->getValue(self::MODULE_ENABLED, $this->getStoreScope());
        if (!$status) {
            $phrase = __('%1: Module Disabled', self::LOG_PREFIX);
            $this->logError((string)$phrase);
            //throw new Exception((string)$phrase);
        }
        if ($config) {
            $status = $this->scopeConfig->getValue($config, $this->getStoreScope());
            if (!$status) {
                $phrase = __('%1: Configuration Disabled', self::LOG_PREFIX);
                $this->logError((string)$phrase);
                //throw new Exception((string)$phrase);
            }
        }
        return $status;
    }

}

