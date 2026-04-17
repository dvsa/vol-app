<?php

declare(strict_types=1);

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
    public function testGetQuery(): void
    {
        $bookmark = new DateFrom();
        $query = $bookmark->getQuery(['communityLic' => 123, 'application' => 456]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query[0]);
        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query[1]);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('specifiedDateProvider')]
    public function testRender(mixed $specifiedDate): void
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

    public static function specifiedDateProvider(): array
    {
        return [
            [new \DateTime('2014-02-03 11:12:34')],
            ['2014-02-03 11:12:34']
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('interimStartDateProvider')]
    public function testRenderWithInterim(mixed $interimStartDate): void
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

    public static function interimStartDateProvider(): array
    {
        return [
            [new \DateTime('2011-01-01 10:10:10')],
            ['2011-01-01 10:10:10']
        ];
    }
}
