<?php

namespace Swissup\SpeculationRules\Test\Unit\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\TestCase;
use Swissup\SpeculationRules\Model\RulesNormalizer;
use Swissup\SpeculationRules\ViewModel\Config;

class ConfigTest extends TestCase
{
    private function createViewModel(string $stored): Config
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturn($stored);

        return new Config($scopeConfig, $this->createMock(ObjectManagerInterface::class),
            new RulesNormalizer(new Json())
        );
    }

    public function testInjectedScriptIsNeverRendered(): void
    {
        $viewModel = $this->createViewModel('{}; alert(document.cookie); ({}');

        $this->assertSame('', $viewModel->getSpeculationRules());
    }

    public function testValidRulesAreRenderedAsJson(): void
    {
        $viewModel = $this->createViewModel('{"prerender":[{"source":"document"}]}');

        $this->assertSame('{"prerender":[{"source":"document"}]}', $viewModel->getSpeculationRules());
    }
}
