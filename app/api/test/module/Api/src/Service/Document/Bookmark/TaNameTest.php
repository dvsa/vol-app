<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\TaName;

/**
 * TA Name test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class TaNameTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new TaName();
        $this->assertNull($bookmark->getQuery([]));

        $query = $bookmark->getQuery(['licence' => 123]);
        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRender(): void
    {
        $bookmark = new TaName();
        $bookmark->setData(
            [
                'trafficArea' => [
                    'name' => 'TA Name 1'
                ]
            ]
        );

        $this->assertEquals(
            'TA Name 1',
            $bookmark->render()
        );
    }
}
