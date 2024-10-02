<?php
/*
 * @Author:    Dan Lewis (dan.lewis@deploy.co.uk)
 * @Copyright: 2024 Deploy Ecommerce (https://www.deploy.co.uk/)
 * @Package:   DeployEcommerce_DynamicSwatchLink
 */

namespace DeployEcommerce\DynamicSwatchLink\Observer;

use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class Predispatch implements ObserverInterface
{

    /**
     * @param Http $redirect
     * @param Configurable $productTypeConfigurable
     * @param ProductRepository $productRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        protected Http                  $redirect,
        protected Configurable          $productTypeConfigurable,
        protected ProductRepository     $productRepository,
        protected StoreManagerInterface $storeManager
    )
    {
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer): void
    {
        $pathInfo = $observer->getEvent()->getRequest()->getPathInfo();
        if (!str_contains($pathInfo, 'product'))
            return;
        $request = $observer->getEvent()->getRequest();
        $simpleProductId = $request->getParam('selected_id');
        if (!$simpleProductId)
            return;
        $simpleProduct = $this->productRepository->getById($simpleProductId, false, $this->storeManager->getStore()->getId());
        if (!$simpleProduct || $simpleProduct->getTypeId() != Type::TYPE_SIMPLE) {
            return;
        }
        $configProductId = $this->productTypeConfigurable->getParentIdsByChild($simpleProductId);
        if (isset($configProductId[0])) {
            $configProduct = $this->productRepository->getById($configProductId[0], false, $this->storeManager->getStore()->getId());
            $configType = $configProduct->getTypeInstance();
            $attributes = $configType->getConfigurableAttributesAsArray($configProduct);
            $options = [];
            foreach ($attributes as $attribute) {
                $id = $attribute['attribute_id'];
                $value = $simpleProduct->getData($attribute['attribute_code']);
                $options[$id] = $value;
            }
            $options = http_build_query($options);
            $hash = $options ? '#' . $options : '';
            $configProductUrl = $configProduct->getUrlModel()
                    ->getUrl($configProduct) . $hash;
            $this->redirect->setRedirect($configProductUrl, 301);
        }
    }
}
