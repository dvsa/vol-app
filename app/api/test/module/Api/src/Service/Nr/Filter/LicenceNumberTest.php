<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Nr\Filter;

use Dvsa\Olcs\Api\Service\Nr\Filter\LicenceNumber;

/**
 * Class LicenceNumberTest
 * @package Dvsa\OlcsTest\Api\Service\Nr\Filter
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class LicenceNumberTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that if a community licence number pattern is found then we split out the part we need.
     * Otherwise, just use the value as is
     *
     *
     * @param string $initialValue initial value
     * @param string $expectedResult expected result
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('filterProvider')]
    public function testFilter(mixed $initialValue, mixed $expectedResult): void
    {
        $value = ['communityLicenceNumber' => $initialValue];
        $expected = [
            'communityLicenceNumber' => $initialValue,
            'licenceNumber' => $expectedResult
        ];

        $sut = new LicenceNumber();

        $this->assertEquals($expected, $sut->filter($value));
    }

    /**
     * data provider for testFilter
     *
     * @return array
     */
    public static function filterProvider(): array
    {
        return [
            ['UKGB/OB1234567/00000', 'OB1234567'],
            ['OB1234567', 'OB1234567'],
        ];
    }
}
