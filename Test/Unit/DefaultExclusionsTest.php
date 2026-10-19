<?php

namespace Swissup\SpeculationRules\Test\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * href_matches strings are URLPattern pathnames: they must start with "/" and
 * match the whole path, otherwise they are resolved against the current page
 * and only ever match one exact URL. A store code or a subfolder base URL puts
 * extra segments in front of the route, so every route needs a prefixed form
 * next to the root-anchored one.
 */
class DefaultExclusionsTest extends TestCase
{
    private function getExcludedPatterns(): array
    {
        $xml = simplexml_load_file(__DIR__ . '/../../etc/config.xml');
        $rules = json_decode((string) $xml->default->swissup_speculationrules->main->rules);
        $this->assertNotNull($rules, 'Default rules must be valid JSON');

        $patterns = [];
        foreach ($rules->prerender[0]->where->and as $condition) {
            if (isset($condition->not->href_matches)) {
                $patterns = array_merge($patterns, (array) $condition->not->href_matches);
            }
        }

        return $patterns;
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

            'store code logout'      => ['/de/customer/account/logout/'],
            'store code account'     => ['/de/customer/account/'],
            'store code cart'        => ['/de/checkout/cart/'],
            'store code checkout'    => ['/de/checkout/'],
            'store code search'      => ['/de/catalogsearch/result/'],
            'store code wishlist'    => ['/de/wishlist/'],
            'store code compare'     => ['/de/catalog/product_compare/add/'],
            'store code order view'  => ['/de/sales/order/view/order_id/1/'],
            'subfolder account'      => ['/shop/customer/account/'],
            'subfolder and store'    => ['/shop/de/checkout/'],
            'store switch'        => ['/stores/store/redirect/___store/de/___from_store/en/uenc/XX/'],
            'store switch code'   => ['/de/stores/store/redirect/___store/fr/___from_store/de/uenc/XX/'],
            'store switch target' => ['/stores/store/switch/'],

            'not you'            => ['/persistent/index/unsetCookie/'],
            'not you store code' => ['/de/persistent/index/unsetCookie/'],
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

            'store code home'     => ['/de/'],
            'store code category' => ['/de/women/tops-women.html'],
            'store code product'  => ['/de/joust-duffle-bag.html'],
            'subfolder product'   => ['/shop/de/joust-duffle-bag.html'],

            'cms starting with a route name' => ['/customer-service'],
            'cms with a route name inside'   => ['/checkout-policy'],
            'category with a route name'     => ['/blog/customer-stories.html'],
            'product with a route name'      => ['/gear/bags/search-light.html'],
            'cms with a route name' => ['/stores-near-you'],
            'persistent in the product slug' => ['/brands/persistent-co.html'],
        ];
    }

    #[DataProvider('allowedPathProvider')]
    public function testCatalogPathIsNotExcluded(string $path): void
    {
        foreach ($this->getExcludedPatterns() as $pattern) {
            $this->assertFalse($this->globMatches($pattern, $path), "$pattern excludes $path");
        }
    }

    /**
     * Without canRestore the "Use system value" checkbox is not rendered at Default scope
     * (Magento\Config\Block\System\Config\Form::isCanRestoreToDefault), so a store that
     * once saved the field can never adopt a new default shipped in etc/config.xml.
     */
    public function testStoredRulesCanBeRestoredToTheShippedDefault(): void
    {
        $xml = simplexml_load_file(__DIR__ . '/../../etc/adminhtml/system.xml');
        $field = $xml->xpath('//section[@id="swissup_speculationrules"]//field[@id="rules"]');

        $this->assertNotEmpty($field, 'The rules field must exist');
        $this->assertSame('1', (string) $field[0]['canRestore']);
    }

    public function testPatternsAreAbsolute(): void
    {
        foreach ($this->getExcludedPatterns() as $pattern) {
            $this->assertStringStartsWith('/', $pattern);
        }
    }

    /**
     * globMatches() models URLPattern with "*" as the only metacharacter. Named groups,
     * optional groups and regexp groups are valid URLPattern syntax that it would get
     * wrong, so the shipped patterns have to stay inside the subset it can represent.
     */
    public function testPatternsStayInsideTheModelledSyntax(): void
    {
        foreach ($this->getExcludedPatterns() as $pattern) {
            $this->assertMatchesRegularExpression('#^[A-Za-z0-9._/*-]+$#', (string) $pattern);
        }
    }
}
