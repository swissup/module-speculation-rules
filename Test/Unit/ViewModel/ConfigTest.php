<?php

namespace Swissup\SpeculationRules\Test\Unit\ViewModel;

use Magento\Csp\Helper\CspNonceProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\TestCase;
use Swissup\SpeculationRules\Model\RulesNormalizer;
use Swissup\SpeculationRules\ViewModel\Config;

class ConfigTest extends TestCase
{
    private function createViewModel(string $stored, ?ObjectManagerInterface $objectManager = null): Config
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturn($stored);

        return new Config($scopeConfig, $objectManager ?: $this->createMock(ObjectManagerInterface::class),
            new RulesNormalizer(new Json())
        );
    }

    public function testNonceProviderIsResolvedAsShared(): void
    {
        if (!class_exists(CspNonceProvider::class)) {
            $this->markTestSkipped('Magento_Csp is not installed, the ViewModel never asks for a provider.');
        }

        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->expects($this->once())
            ->method('get')
            ->with(CspNonceProvider::class);
        $objectManager->expects($this->never())->method('create');

        $this->createViewModel('{}', $objectManager);
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
