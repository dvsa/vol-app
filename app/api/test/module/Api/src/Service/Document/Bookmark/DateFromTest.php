<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Service\Document\Bookmark\DateFrom;

/**
 * Date From test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class DateFromTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery()
    {
        $bookmark = new DateFrom();
        $query = $bookmark->getQuery(['communityLic' => 123, 'application' => 456]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query[0]);
        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query[1]);
    }

    /**
     * @dataProvider specifiedDateProvider
     */
    public function testRender($specifiedDate)
    {
        $bookmark = new DateFrom();
        $bookmark->setData(
            [
                [
                    'specifiedDate' => $specifiedDate
                ],
                [
                    'Count' => 0, 'Results' => []
                ]
            ]
        );

        $this->assertEquals(
            '03/02/2014',
            $bookmark->render()
        );
    }

    public function specifiedDateProvider()
    {
        return [
            [new \DateTime('2014-02-03 11:12:34')],
            ['2014-02-03 11:12:34']
        ];
    }

    /**
     * @dataProvider interimStartDateProvider
     */
    public function testRenderWithInterim($interimStartDate)
    {
        $bookmark = new DateFrom();
        $bookmark->setData(
            [
                [
                    'specifiedDate' => new \DateTime('2014-02-03 11:12:34')
                ],
                [
                    'interimStatus' => [
                        'id' => Application::INTERIM_STATUS_INFORCE
                    ],
                    'interimStart' => $interimStartDate
                ]
            ]
        );

        $this->assertEquals(
            '01/01/2011',
            $bookmark->render()
        );
    }

    public function interimStartDateProvider()
    {
        return [
            [new \DateTime('2011-01-01 10:10:10')],
            ['2011-01-01 10:10:10']
        ];
    }
}
