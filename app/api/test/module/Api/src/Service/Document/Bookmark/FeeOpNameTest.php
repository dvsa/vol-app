<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\FeeOpName;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Fee Op Name test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class FeeOpNameTest extends MockeryTestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new FeeOpName();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRender(): void
    {
        $bookmark = new FeeOpName();
        $bookmark->setData(
            [
                'organisation' => [
                    'tradingNames' => [
                        ['name' => 'a1234567890'],
                        ['name' => 'b1234567890'],
                        ['name' => 'c1234567890'],
                        ['name' => 'd1234567890']
                    ],
                    'name' => 'orgname'
                ],
            ]
        );

        $expected = implode("\n", ['orgname', 'TA a1234567890, b1234567890, c1234567...']);

        $this->assertEquals($expected, $bookmark->render());
    }
}
