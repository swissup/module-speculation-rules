<?php

namespace Swissup\SpeculationRules\Model\Config\Backend;

use Magento\Framework\Exception\LocalizedException;
use Swissup\SpeculationRules\Model\RulesNormalizer;

class Rules extends \Magento\Framework\App\Config\Value
{
    /**
     * @var RulesNormalizer
     */
    private $normalizer;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        RulesNormalizer $normalizer,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->normalizer = $normalizer;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = (string) $this->getValue();
        if (trim($value) !== '' && $this->normalizer->normalize($value) === '') {
            throw new LocalizedException(
                __('Speculation Rules must be a valid JSON object (double quotes, no trailing commas).')
            );
        }

        return parent::beforeSave();
    }
}
