<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator;

use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\EffectiveDate;
use PHPUnit\Framework\TestCase;

/**
 * Class EffectiveDateTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator
 */
class EffectiveDateTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('isValidProvider')]
    public function testIsValid(mixed $data, mixed $validity): void
    {
        $sut = new EffectiveDate();
        $this->assertEquals($validity, $sut->isValid($data));
    }

    public static function isValidProvider(): array
    {
        $today = strtotime(date('Y-m-d'));

        return [
            [['txcAppType' => BusRegEntity::TXC_APP_NEW, 'effectiveDate' => date('Y-m-d', $today - 86400)], false],
            [['txcAppType' => BusRegEntity::TXC_APP_NEW, 'effectiveDate' => date('Y-m-d', $today + 86400)], true],
            [['txcAppType' => BusRegEntity::TXC_APP_CANCEL, 'effectiveDate' => date('Y-m-d', $today - 86400)], true],
            [['txcAppType' => BusRegEntity::TXC_APP_CANCEL, 'effectiveDate' => date('Y-m-d', $today + 86400)], true],
            [
                ['txcAppType' => BusRegEntity::TXC_APP_CHARGEABLE, 'effectiveDate' => date('Y-m-d', $today - 86400)],
                true
            ],
            [
                ['txcAppType' => BusRegEntity::TXC_APP_CHARGEABLE, 'effectiveDate' => date('Y-m-d', $today + 86400)],
                true
            ],
            [
                [
                    'txcAppType' => BusRegEntity::TXC_APP_NON_CHARGEABLE,
                    'effectiveDate' => date('Y-m-d', $today - 86400)
                ],
                true
            ],
            [
                [
                    'txcAppType' => BusRegEntity::TXC_APP_NON_CHARGEABLE,
                    'effectiveDate' => date('Y-m-d', $today + 86400)
                ],
                true
            ],
        ];
    }
}
