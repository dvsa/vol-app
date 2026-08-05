<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator\ShortNotice;

use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ShortNotice\MissingSection;

/**
 * Class RegisteredBusRouteTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator\ShortNotice
 */
final class MissingSectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * tests whether the short notice section exists correctly
     *
     *
     * @param string $isShortNotice
     * @param array $value
     * @param bool $valid
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isValidProvider')]
    public function testIsValid(mixed $isShortNotice, mixed $value, mixed $valid): void
    {
        $sut = new MissingSection();
        $busReg = new BusRegEntity();
        $busReg->setIsShortNotice($isShortNotice);

        $context = ['busReg' => $busReg];

        $this->assertEquals($valid, $sut->isValid($value, $context));
    }

    /**
     * Provider for testIsValid
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function isValidProvider(): \Iterator
    {
        yield ['N', [], true];
        yield ['N', ['busShortNotice' => []], true];
        yield ['N', ['busShortNotice' => null], true];
        yield ['N', ['busShortNotice' => 'content'], true];
        yield ['Y', [], false];
        yield ['Y', ['busShortNotice' => []], false];
        yield ['Y', ['busShortNotice' => null], false];
        yield ['Y', ['busShortNotice' => 'content'], true];
    }
}
