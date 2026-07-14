<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator;

use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ServiceNo;

/**
 * Class ServiceNoTest
 */
final class ServiceNoTest extends \PHPUnit\Framework\TestCase
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
     * @return \Iterator<(int | string), mixed>
     */
    public static function isValidProvider(): \Iterator
    {
        yield ['', false];
        yield [null, false];
        yield [false, false];
        yield [0, true];
        yield ['0', true];
        yield [111, true];
        yield ['111', true];
        yield ['service no as name', true];
    }
}
