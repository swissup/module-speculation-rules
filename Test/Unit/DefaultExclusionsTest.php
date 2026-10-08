<?php

namespace Swissup\SpeculationRules\Test\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * href_matches strings are URLPattern pathnames: they must start with "/" and
 * match the whole path, otherwise they are resolved against the current page
 * and only ever match one exact URL.
 */
class DefaultExclusionsTest extends TestCase
{
    private function getExcludedPatterns(): array
    {
        $xml = simplexml_load_file(__DIR__ . '/../../etc/config.xml');
        $rules = json_decode((string) $xml->default->swissup_speculationrules->main->rules);
        $this->assertNotNull($rules, 'Default rules must be valid JSON');

        foreach ($rules->prerender[0]->where->and as $condition) {
            if (isset($condition->not->href_matches)) {
                return $condition->not->href_matches;
            }
        }

        return [];
    }

    private function globMatches(string $pattern, string $path): bool
    {
        $regex = '#^' . str_replace('\*', '.*', preg_quote($pattern, '#')) . '$#';

        return (bool) preg_match($regex, $path);
    }

    public static function excludedPathProvider(): array
    {
        return [
            'logout'            => ['/customer/account/logout/'],
            'logout no slash'   => ['/customer/account/logout'],
            'logout success'    => ['/customer/account/logoutSuccess/'],
            'customer account'  => ['/customer/account/'],
            'cart'              => ['/checkout/cart/'],
            'checkout'          => ['/checkout/'],
            'onepage'           => ['/checkout/onepage/success/'],
            'search results'    => ['/catalogsearch/result/'],
            'advanced search'   => ['/catalogsearch/advanced/'],
            'wishlist'          => ['/wishlist/'],
            'compare add'       => ['/catalog/product_compare/add/'],
            'guest order view'  => ['/sales/guest/view/'],
            'reorder'           => ['/sales/order/reorder/order_id/1/'],
        ];
    }

    #[DataProvider('excludedPathProvider')]
    public function testSensitivePathIsExcluded(string $path): void
    {
        $patterns = $this->getExcludedPatterns();
        $excluded = array_filter($patterns, fn ($pattern) => $this->globMatches($pattern, $path));

        $this->assertNotEmpty($excluded, "$path is not excluded from prerender");
    }

    public static function allowedPathProvider(): array
    {
        return [
            'home'     => ['/'],
            'category' => ['/women/tops-women.html'],
            'product'  => ['/joust-duffle-bag.html'],
            'cms'      => ['/about-us'],
        ];
    }

    #[DataProvider('allowedPathProvider')]
    public function testCatalogPathIsNotExcluded(string $path): void
    {
        foreach ($this->getExcludedPatterns() as $pattern) {
            $this->assertFalse($this->globMatches($pattern, $path), "$pattern excludes $path");
        }
    }

    public function testPatternsAreAbsolute(): void
    {
        foreach ($this->getExcludedPatterns() as $pattern) {
            $this->assertStringStartsWith('/', $pattern);
        }
    }
}
