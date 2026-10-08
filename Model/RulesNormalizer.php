<?php

namespace Swissup\SpeculationRules\Model;

use Magento\Framework\Serialize\Serializer\Json;

/**
 * Validates the admin-supplied speculation rules and makes them safe to embed
 * into an inline script. Anything that is not a JSON object is dropped.
 */
class RulesNormalizer
{
    /**
     * @var Json
     */
    private $json;

    public function __construct(Json $json)
    {
        $this->json = $json;
    }

    public function normalize(string $rules): string
    {
        $rules = trim($rules);
        if ($rules === '' || $rules[0] !== '{') {
            return '';
        }

        try {
            $this->json->unserialize($rules);
        } catch (\InvalidArgumentException $e) {
            return '';
        }

        // Valid JSON has these characters only inside strings, where \u escapes are equivalent.
        return strtr($rules, [
            '<' => '\u003C',
            '>' => '\u003E',
            '&' => '\u0026',
            "'" => '\u0027',
        ]);
    }
}
