<?php

declare(strict_types=1);

namespace CommonTest\View\Helper;

use Common\View\Helper\CurrencyFormatter;

/**
 * Test CurrencyFormatter view helper
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
final class CurrencyFormatterTest extends \PHPUnit\Framework\TestCase
{
    public $viewHelper;
    /**
     * Setup the view helper
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->viewHelper = new CurrencyFormatter();
    }

    /**
     * Test invoke
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('currencyDataProvider')]
    public function testInvokeDefaultFields($value, $expected): void
    {
        $viewHelper = $this->viewHelper;
        $this->assertEquals($expected, $viewHelper($value));
    }

    /**
     * @return \Iterator<(int | string), array<string>>
     *
     * @psalm-return list{list{'10.00', '£10'}, list{'1', '£1'}, list{'10.56', '£10.56'}, list{'3000', '£3,000'}, list{'3000.00', '£3,000'}, list{'3000.56', '£3,000.56'}, list{'24567', '£24,567'}, list{'24567.00', '£24,567'}, list{'24567.22', '£24,567.22'}, list{'ABCXYZ', '£ABC,XYZ'}, list{'ABC', '£ABC'}, list{'ABC.DEF.HIJ', '£ABC.DEF.HIJ'}}
     */
    public static function currencyDataProvider(): \Iterator
    {
        yield ['10.00', '£10'];
        // Full length fee ending in '00'
        yield ['1', '£1'];
        // Single digit fee
        yield ['10.56', '£10.56'];
        // Full length fee ending in non-'00'
        yield ['3000', '£3,000'];
        // Thousands without pence
        yield ['3000.00', '£3,000'];
        // Thousands with explicit zero pence
        yield ['3000.56', '£3,000.56'];
        // Thousands with non-zero pence
        yield ['24567', '£24,567'];
        // Tens of thousands without pence
        yield ['24567.00', '£24,567'];
        // Tens of thousands with explicit zero pence
        yield ['24567.22', '£24,567.22'];
        // Tens of thousands with non-zero pence
        yield ['ABCXYZ', '£ABC,XYZ'];
        // Unexpected input format
        yield ['ABC', '£ABC'];
        // Unexpected input format
        yield ['ABC.DEF.HIJ', '£ABC.DEF.HIJ'];
    }
}
