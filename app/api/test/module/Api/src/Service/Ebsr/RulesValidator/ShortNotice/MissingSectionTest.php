<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator\ShortNotice;

use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ShortNotice\MissingSection;

/**
 * Class RegisteredBusRouteTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator\ShortNotice
 */
class MissingSectionTest extends \PHPUnit\Framework\TestCase
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
     * @return array
     */
    public static function isValidProvider(): array
    {
        return [
            ['N', [], true],
            ['N', ['busShortNotice' => []], true],
            ['N', ['busShortNotice' => null], true],
            ['N', ['busShortNotice' => 'content'], true],
            ['Y', [], false],
            ['Y', ['busShortNotice' => []], false],
            ['Y', ['busShortNotice' => null], false],
            ['Y', ['busShortNotice' => 'content'], true]
        ];
    }
}
