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

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
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
}
