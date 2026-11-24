<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\Filter\Format;

use Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\Subsidy;
use PHPUnit\Framework\TestCase as TestCase;

/**
 * Class SubsidyTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\Filter\Format
 */
final class SubsidyTest extends TestCase
{
    /**
     * @param $expected
     * @param $value
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideFilter')]
    public function testFilter(mixed $expected, mixed $value): void
    {
        $sut = new Subsidy();

        $result = $sut->filter(['subsidised' => $value]);
        $this->assertEquals($expected, $result['subsidised']);
    }

    public static function provideFilter(): \Iterator
    {
        yield ['bs_no', 'none'];
        yield ['bs_yes', 'full'];
        yield ['bs_in_part', 'partial'];
        yield ['bs_no', null];
    }
    public function testKeepsAllOriginalAuthorityNamesInComments(): void
    {
        $result = (new Subsidy())->filter([
            'subsidised' => 'partial',
            'subsidyAuthorityNames' => ['Milton Keynes Council', 'CPCA'],
        ]);

        $this->assertSame("Milton Keynes Council\nCPCA", $result['subsidyDetail'] ?? null);
        $this->assertSame(['Milton Keynes Council', 'CPCA'], $result['subsidyAuthorityNames']);
        $this->assertSame('bs_in_part', $result['subsidised']);
    }
    public function testNoSubsidyProvidesAnEmptyProviderList(): void
    {
        $result = (new Subsidy())->filter(['subsidised' => 'none']);
        $this->assertSame([], $result['subsidyAuthorityNames'] ?? null);
        $this->assertSame('bs_no', $result['subsidised']);
    }
}
