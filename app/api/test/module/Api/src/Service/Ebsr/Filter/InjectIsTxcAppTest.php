<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\Filter;

use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Service\Ebsr\Filter\InjectIsTxcApp;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class InjectIsTxcAppTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\Filter
 */
class InjectIsTxcAppTest extends TestCase
{
    public function testFilter(): void
    {
        $sut = new InjectIsTxcApp();
        $return = $sut->filter([]);

        $this->assertEquals('Y', $return['isTxcApp']);
    }

    /**
     * @param $appType
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideEbsrRefresh')]
    public function testFilterInjectsEbsrRefresh(mixed $appType, mixed $expected): void
    {
        $sut = new InjectIsTxcApp();
        $return = $sut->filter(['txcAppType' => $appType]);

        $this->assertEquals($expected, $return['ebsrRefresh']);
    }

    public static function provideEbsrRefresh(): array
    {
        return [
            [BusRegEntity::TXC_APP_NON_CHARGEABLE, 'Y'],
            [BusRegEntity::TXC_APP_NEW, 'N'],
            [BusRegEntity::TXC_APP_CANCEL, 'N'],
            [BusRegEntity::TXC_APP_CHARGEABLE, 'N']
        ];
    }
}
