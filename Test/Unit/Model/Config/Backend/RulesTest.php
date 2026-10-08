<?php

namespace Swissup\SpeculationRules\Test\Unit\Model\Config\Backend;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\TestCase;
use Swissup\SpeculationRules\Model\Config\Backend\Rules;
use Swissup\SpeculationRules\Model\RulesNormalizer;

class RulesTest extends TestCase
{
    private function createModel(string $value): Rules
    {
        $model = $this->getMockBuilder(Rules::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $model->setData('value', $value);
        $property = new \ReflectionProperty(Rules::class, 'normalizer');
        $property->setAccessible(true);
        $property->setValue($model, new RulesNormalizer(new Json()));

        return $model;
    }

    public function testInvalidValueIsRejected(): void
    {
        $this->expectException(LocalizedException::class);
        $this->createModel('{}; alert(1); ({}')->beforeSave();
    }
}
