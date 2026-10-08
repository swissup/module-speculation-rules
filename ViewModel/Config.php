<?php

namespace Swissup\SpeculationRules\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Swissup\SpeculationRules\Api\Data\ConfigInterface;
use Swissup\SpeculationRules\Model\RulesNormalizer;

class Config implements ArgumentInterface, ConfigInterface
{
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var RulesNormalizer
     */
    private $rulesNormalizer;

    /**
     * @var \Magento\Csp\Helper\CspNonceProvider|null
     *
     */
    private $cspNonceProvider = null;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        RulesNormalizer $rulesNormalizer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->rulesNormalizer = $rulesNormalizer;
        $className = \Magento\Csp\Helper\CspNonceProvider::class;
        $this->cspNonceProvider = class_exists($className) ? $objectManager->create($className) : null;
    }

    /**
     *
     * @return string [json] safe to embed into an inline script, empty when the config is not a valid JSON object
     */
    public function getSpeculationRules(): string
    {
        return $this->rulesNormalizer->normalize((string) $this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_RULES,
            ScopeInterface::SCOPE_STORE
        ));
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
