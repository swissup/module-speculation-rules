<?php

namespace Swissup\SpeculationRules\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Swissup\SpeculationRules\Api\Data\ConfigInterface;

class Config implements ArgumentInterface, ConfigInterface
{
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Csp\Helper\CspNonceProvider|null
     *
     */
    private $cspNonceProvider = null;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $className = \Magento\Csp\Helper\CspNonceProvider::class;
        $this->cspNonceProvider = class_exists($className) ? $objectManager->create($className) : null;
    }

    /**
     *
     * @return string [json]
     */
    public function getSpeculationRules(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_RULES,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getNonce()
    {
        return $this->cspNonceProvider ? $this->cspNonceProvider->generateNonce() : null;
    }
}
