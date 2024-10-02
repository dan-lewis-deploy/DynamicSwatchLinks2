<?php
/*
 * @Author:    Dan Lewis (dan.lewis@deploy.co.uk)
 * @Copyright: 2024 Deploy Ecommerce (https://www.deploy.co.uk/)
 * @Package:   DeployEcommerce_DynamicSwatchLink
 */

namespace DeployEcommerce\DynamicSwatchLink\Plugin\Checkout\Controller\Cart;

use DeployEcommerce\DynamicSwatchLink\Helper\Settings;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Controller\Cart\Add as CheckoutAdd;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Checkout\Model\Cart\RequestQuantityProcessor;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Add
{

    /**
     * @param RequestInterface $request
     * @param Settings $settings
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param CustomerCart $cart
     * @param ManagerInterface $messageManager
     * @param RequestQuantityProcessor $requestQuantityProcessor
     * @param EventManagerInterface $eventManager
     * @param ResponseInterface $response
     * @param CheckoutSession $checkoutSession
     * @param UrlInterface $url
     */
    public function __construct(
        protected RequestInterface           $request,
        protected Settings                   $settings,
        protected ProductRepositoryInterface $productRepository,
        protected StoreManagerInterface      $storeManager,
        protected CustomerCart               $cart,
        protected ManagerInterface           $messageManager,
        protected RequestQuantityProcessor   $requestQuantityProcessor,
        protected EventManagerInterface      $eventManager,
        protected ResponseInterface          $response,
        protected CheckoutSession            $checkoutSession,
        protected UrlInterface               $url
    )
    {
    }

    /**
     * @param CheckoutAdd $subject
     * @param callable $proceed
     * @return ResponseInterface|void
     */
    public function aroundExecute(CheckoutAdd $subject, callable $proceed)
    {
        $proceedFlag = true;
        $params = $this->request->getParams();
        if (
            array_key_exists('matrix-flag', $params) &&
            array_key_exists('matrix-inputs', $params) &&
            !empty($params['matrix-inputs'])

        ) {
            foreach ($params['matrix-inputs'] as $sku => $qty) {
                if ($qty < 1) continue;
                try {
                    $storeId = $this->storeManager->getStore()->getId();
                    $product = $this->productRepository->get($sku, false, $storeId);
                    $this->cart->addProduct($product->getId(), $qty);
                    $this->eventManager->dispatch(
                        'checkout_cart_add_product_complete',
                        ['product' => $product, 'request' => $this->request, 'response' => $this->response]
                    );
                    if ($this->settings->getConfig('checkout/cart/redirect_to_cart')) {
                        $message = __(
                            'You added %1 to your shopping cart.',
                            $product->getName()
                        );
                        $this->messageManager->addSuccessMessage($message);
                    } else {
                        $this->messageManager->addComplexSuccessMessage(
                            'addCartSuccessMessage',
                            [
                                'product_name' => $product->getName(),
                                'cart_url' => $this->url->getUrl('checkout/cart', ['_secure' => true])
                            ]
                        );
                    }
                } catch (Exception $exception) {
                    $this->settings->logError('Unable to add matrix item on add plugin because ' . $exception->getMessage());
                    continue;
                }
            }
            if (!$this->checkoutSession->getNoCartRedirect(true)) {
                if ($this->cart->getQuote()->getHasError()) {
                    $errors = $this->cart->getQuote()->getErrors();
                    foreach ($errors as $error) {
                        $this->messageManager->addErrorMessage($error->getText());
                    }
                }
                $this->cart->save();
                return $this->response;
            }
        }
        $proceed();
    }

}
