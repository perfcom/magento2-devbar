<?php
/**
 * @copyright   perfcom.dev - https://perfcom.dev
 */

declare(strict_types=1);

namespace Perfcom\Devbar\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;

class Sidebar extends Template
{
    public function __construct(
        Template\Context $context,
        private readonly ResourceConnection $resourceConnection,
        private readonly ProductMetadataInterface $productMetadata,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly UrlInterface $urlBuilder,
        private readonly State $appState,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getDatabaseName()
    {
        return $this->resourceConnection->getConnection()->getConfig()['dbname'] ?? 'Unknown';
    }

    public function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }

    public function getElasticsearchVersion()
    {
        return $this->scopeConfig->getValue('catalog/search/engine') ?: 'Not configured';
    }

    public function isCspStrictMode()
    {
        return $this->scopeConfig->isSetFlag('csp/mode/storefront/report_only') ? 'No' : 'Yes';
    }

    public function getBackendUrl()
    {
        return $this->urlBuilder->getUrl('admin');
    }

    public function getLoginUrl()
    {
        return $this->urlBuilder->getUrl('devbar/customer/login');
    }

    public function getAddToCartUrl()
    {
        return $this->urlBuilder->getUrl('devbar/cart/add');
    }

    public function getCheckoutUrl()
    {
        return $this->urlBuilder->getUrl('checkout');
    }

    protected function _toHtml()
    {
        if ($this->appState->getMode() !== State::MODE_DEVELOPER) {
            return '';
        }

        return parent::_toHtml();
    }
}
