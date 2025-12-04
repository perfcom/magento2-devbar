<?php
/**
 * @copyright   perfcom.dev - https://perfcom.dev
 */

declare(strict_types=1);

namespace Perfcom\Devbar\Controller\Customer;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\State;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;

class Login implements HttpPostActionInterface
{
    public function __construct(
        private readonly CollectionFactory $customerCollectionFactory,
        private readonly Session $customerSession,
        private readonly ManagerInterface $messageManager,
        private readonly RedirectFactory $resultRedirectFactory,
        private readonly State $appState
    ) {
    }

    public function execute()
    {
        if ($this->appState->getMode() !== State::MODE_DEVELOPER) {
            return $this->resultRedirectFactory->create()->setRefererUrl();
        }

        $collection = $this->customerCollectionFactory->create();
        $collection->getSelect()->orderRand()->limit(1);

        if ($customer = $collection->getFirstItem()) {
            $this->customerSession->setCustomerAsLoggedIn($customer);
            $this->messageManager->addSuccessMessage('Logged in as: ' . $customer->getEmail());
        } else {
            $this->messageManager->addErrorMessage('No customers available to login');
        }

        return $this->resultRedirectFactory->create()->setRefererUrl();
    }
}
