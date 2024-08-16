<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\OpNameOnly;

/**
 * OpNameOnly bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OpNameOnlyTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery()
    {
        $bookmark = new OpNameOnly();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRenderWithNoOpName()
    {
        $bookmark = new OpNameOnly();
        $bookmark->setData([]);

        $this->assertEquals(
            '',
            $bookmark->render()
        );
    }

    public function testRender()
    {
        $bookmark = new OpNameOnly();
        $bookmark->setData(
            [
                'organisation' => [
                    'name' => 'foo'
                ]
            ]
        );

        $this->assertEquals('foo', $bookmark->render());
    }
}
