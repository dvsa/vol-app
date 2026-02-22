<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\BusRegBundle as Qry;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Service\Document\Bookmark\BrNumDays as BrNumDays;

/**
 * BrNumDays test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BrNumDaysTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new BrNumDays();
        $this->assertInstanceOf(Qry::class, $bookmark->getQuery([DynamicBookmark::PARAM_BUSREG_ID => 123]));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('renderDataProvider')]
    public function testRender(mixed $status, mixed $expected): void
    {
        $data =                 [
            'status' => [
                'id' => $status
            ],
            'busNoticePeriod' => [
                'standardPeriod' => 42,
                'cancellationPeriod' => 90
            ]
        ];

        $bookmark = new BrNumDays();
        $bookmark->setData($data);
        $this->assertEquals($expected, $bookmark->render());
    }

    /**
     * @return array
     */
    public static function renderDataProvider(): array
    {
        return [
            [BusRegEntity::STATUS_REGISTERED, 42],
            [BusRegEntity::STATUS_VAR, 42],
            [BusRegEntity::STATUS_CANCEL, 90],
        ];
    }
}
