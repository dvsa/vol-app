<?php

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
     * @dataProvider filterProvider
     *
     * @param string $initialValue initial value
     * @param string $expectedResult expected result
     */
    public function testFilter($initialValue, $expectedResult)
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
    public function filterProvider()
    {
        return [
            ['UKGB/OB1234567/00000', 'OB1234567'],
            ['OB1234567', 'OB1234567'],
        ];
    }
}
