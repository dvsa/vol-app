<?php

namespace Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator;

use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ServiceNo;

/**
 * Class ServiceNoTest
 */
class ServiceNoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * test isValid
     *
     * @param mixed $serviceNo service number
     * @param bool  $isValid   whether it's valid
     *
     * @dataProvider isValidProvider
     */
    public function testIsValid(mixed $serviceNo, $isValid)
    {
        $value = ['serviceNo' => $serviceNo];

        $sut = new ServiceNo();
        $this->assertEquals($isValid, $sut->isValid($value));
    }

    /**
     * data provider for testIsValid
     *
     * @return array
     */
    public function isValidProvider()
    {
        return [
            ['', false],
            [null, false],
            [false, false],
            [0, true],
            ['0', true],
            [111, true],
            ['111', true],
            ['service no as name', true],
        ];
    }
}
