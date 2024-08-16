<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\InsMoreFreqNo;

/**
 * InsMoreFreqNo bookmark test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InsMoreFreqNoTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery()
    {
        $bookmark = new InsMoreFreqNo();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    /**
     * @dataProvider safetyInsProvider
     * @group InsMoreFreqNoTest
     */
    public function testRenderWithInsMoreFreqNo($flag, $expected)
    {
        $bookmark = new InsMoreFreqNo();
        $bookmark->setData(
            [
                'safetyInsVaries' => $flag
            ]
        );

        $this->assertEquals(
            $expected,
            $bookmark->render()
        );
    }

    public function safetyInsProvider()
    {
        return [
            [0, 'X'],
            [1, '']
        ];
    }
}
