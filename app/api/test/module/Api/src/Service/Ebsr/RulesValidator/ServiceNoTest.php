<?php

declare(strict_types=1);

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
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isValidProvider')]
    public function testIsValid(mixed $serviceNo, mixed $isValid): void
    {
        $value = ['lineNames' => [$serviceNo]];

        $sut = new ServiceNo();
        $this->assertEquals($isValid, $sut->isValid($value));
    }

    /**
     * data provider for testIsValid
     *
     * @return array
     */
    public static function isValidProvider(): array
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
