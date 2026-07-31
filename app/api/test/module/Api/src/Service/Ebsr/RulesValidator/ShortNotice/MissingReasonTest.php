<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator\ShortNotice;

use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ShortNotice\MissingReason;

/**
 * Class MissingReasonTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator\ShortNotice
 */
final class MissingReasonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * tests whether the short notice section exists correctly
     *
     *
     * @param string $isShortNotice
     * @param array $busShortNotice
     * @param bool $valid
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isValidProvider')]
    public function testIsValid(mixed $isShortNotice, mixed $busShortNotice, mixed $valid): void
    {
        $sut = new MissingReason();
        $busReg = new BusRegEntity();
        $busReg->setIsShortNotice($isShortNotice);

        $context = ['busReg' => $busReg];

        $value = ['busShortNotice' => $busShortNotice];

        $this->assertEquals($valid, $sut->isValid($value, $context));
    }

    /**
     * Provider for testIsValid
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function isValidProvider(): \Iterator
    {
        $invalidSn = [
            'bankHolidayChange' => 'N',
            'unforseenChange' => 'N',
            'timetableChange' => 'N',
            'replacementChange' => 'N',
            'holidayChange' => 'N',
            'trcChange' => 'N',
            'policeChange' => 'N',
            'specialOccasionChange' => 'N',
            'connectionChange' => 'N',
            'notAvailableChange' => 'N'
        ];

        //creates a version of the the array with one sn reason set
        $validSn1 = array_merge($invalidSn, ['bankHolidayChange' => 'Y']);
        $validSn2 = array_merge($invalidSn, ['unforseenChange' => 'Y']);
        $validSn3 = array_merge($invalidSn, ['timetableChange' => 'Y']);
        $validSn4 = array_merge($invalidSn, ['replacementChange' => 'Y']);
        $validSn5 = array_merge($invalidSn, ['holidayChange' => 'Y']);
        $validSn6 = array_merge($invalidSn, ['trcChange' => 'Y']);
        $validSn7 = array_merge($invalidSn, ['policeChange' => 'Y']);
        $validSn8 = array_merge($invalidSn, ['specialOccasionChange' => 'Y']);
        $validSn9 = array_merge($invalidSn, ['connectionChange' => 'Y']);
        $validSn10 = array_merge($invalidSn, ['notAvailableChange' => 'Y']);
        yield ['Y', $validSn1, true];
        yield ['Y', $validSn2, true];
        yield ['Y', $validSn3, true];
        yield ['Y', $validSn4, true];
        yield ['Y', $validSn5, true];
        yield ['Y', $validSn6, true];
        yield ['Y', $validSn7, true];
        yield ['Y', $validSn8, true];
        yield ['Y', $validSn9, true];
        yield ['Y', $validSn10, true];
        yield ['N', $invalidSn, true];
        //record not short notice
        yield ['Y', $invalidSn, false];
    }
}
