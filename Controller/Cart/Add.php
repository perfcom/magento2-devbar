<?php
/**
 * @copyright   perfcom.dev - https://perfcom.dev
 */

declare(strict_types=1);

namespace Perfcom\Devbar\Controller\Cart;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\State;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;

class Add implements HttpPostActionInterface
{
    private Quote $quote;

    public function __construct(
        private readonly ManagerInterface $messageManager,
        private readonly CollectionFactory $productCollectionFactory,
        private readonly Session $checkoutSession,
        private readonly QuoteRepository $quoteRepository,
        private readonly RedirectFactory $resultRedirectFactory,
        private readonly State $appState
    ) {
        $this->quote = $this->checkoutSession->getQuote();
    }

    public function execute()
    {
        if ($this->appState->getMode() !== State::MODE_DEVELOPER) {
            return $this->resultRedirectFactory->create()->setRefererUrl();
        }

        $attempts = 0;
        $maxAttempts = 5;
        $productAdded = false;

        while ($attempts < $maxAttempts && !$productAdded) {
            $attempts++;

            $collection = $this->productCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('type_id', 'simple')
                ->addAttributeToFilter('status', 1);
            $collection->getSelect()->orderRand()->limit(1);

            if ($product = $collection->getFirstItem()) {
                try {
                    $this->quote->addProduct($product, 1);
                    $this->quoteRepository->save($this->quote);
                    $this->messageManager->addSuccessMessage('Added: ' . $product->getName());
                    $productAdded = true;
                } catch (\Exception $e) {
                    if ($attempts >= $maxAttempts) {
                        $this->messageManager->addErrorMessage('Failed to add product after ' . $maxAttempts . ' attempts');
                    }
                    // Continue to next attempt
                }
            } else {
                if ($attempts >= $maxAttempts) {
                    $this->messageManager->addErrorMessage('No products available to add');
                }
            }
        }

        return $this->resultRedirectFactory->create()->setRefererUrl();
    }
}
