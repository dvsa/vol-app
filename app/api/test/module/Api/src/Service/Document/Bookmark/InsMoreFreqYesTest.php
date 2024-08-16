<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\InsMoreFreqYes;

/**
 * InsMoreFreqYes bookmark test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InsMoreFreqYesTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery()
    {
        $bookmark = new InsMoreFreqYes();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    /**
     * @dataProvider safetyInsProvider
     */
    public function testRenderWithInsMoreFreqYes($flag, $expected)
    {
        $bookmark = new InsMoreFreqYes();
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
            [1, 'X'],
            [0, '']
        ];
    }
}
