<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\ApplicationVehicleLimit;

/**
 * Application Vehicle Limit test
 *
 * @author Alex Peskov <alex.peshkov@valtech.co.uk>
 */
class ApplicationVehicleLimitTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new ApplicationVehicleLimit();
        $query = $bookmark->getQuery(['case' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRender(): void
    {
        $bookmark = new ApplicationVehicleLimit();
        $bookmark->setData(
            [
                'application' => [
                    'totAuthVehicles' => 1
                ]
            ]
        );

        $this->assertEquals(1, $bookmark->render());
    }
}
