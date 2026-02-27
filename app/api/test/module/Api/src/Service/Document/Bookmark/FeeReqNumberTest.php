<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\FeeReqNumber;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Fee Req Number test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class FeeReqNumberTest extends MockeryTestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new FeeReqNumber();
        $query = $bookmark->getQuery(['fee' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRender(): void
    {
        $bookmark = new FeeReqNumber();
        $bookmark->setData(
            [
                'licence' => [
                    'licNo' => '123'
                ],
                'id' => 2
            ]
        );

        $this->assertEquals('123/2', $bookmark->render());
    }
}
