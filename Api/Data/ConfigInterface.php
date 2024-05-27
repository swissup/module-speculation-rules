<?php

namespace Swissup\SpeculationRules\Api\Data;

interface ConfigInterface
{
    const CONFIG_XML_PATH_RULES  = 'swissup_speculationrules/main/rules';

    public function getSpeculationRules(): string;
}
