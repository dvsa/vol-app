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
final class InjectIsTxcAppTest extends TestCase
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

    public static function provideEbsrRefresh(): \Iterator
    {
        yield [BusRegEntity::TXC_APP_NON_CHARGEABLE, 'Y'];
        yield [BusRegEntity::TXC_APP_NEW, 'N'];
        yield [BusRegEntity::TXC_APP_CANCEL, 'N'];
        yield [BusRegEntity::TXC_APP_CHARGEABLE, 'N'];
    }
}
