<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\ApplicantName;

/**
 * Applicant name test
 */
final class ApplicantNameTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new ApplicantName();

        $this->assertNull($bookmark->getQuery([]));

        $query = $bookmark->getQuery(['opposition' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('renderDataProvider')]
    public function testRender(mixed $data, mixed $expected): void
    {
        $bookmark = new ApplicantName();
        $bookmark->setData($data);

        $this->assertEquals($expected, $bookmark->render());
    }

    public static function renderDataProvider(): \Iterator
    {
        // no results
        yield [
            [],
            ''
        ];
        // organisation without any trading names
        yield [
            [
                'licence' => [
                    'organisation' => [
                        'name' => 'An Org',
                        'tradingNames' => []
                    ]
                ]
            ],
            'An Org'
        ];
        // organisation with single trading name
        yield [
            [
                'licence' => [
                    'organisation' => [
                        'name' => 'An Org',
                        'tradingNames' => [
                            [
                                'name' => 'TN 1',
                                'createdOn' => '2015-04-01 11:00:00'
                            ]
                        ]
                    ]
                ]
            ],
            'An Org T/A TN 1'
        ];
        // organisation with multiple trading names
        yield [
            [
                'licence' => [
                    'organisation' => [
                        'name' => 'An Org',
                        'tradingNames' => [
                            [
                                'name' => 'TN 1',
                                'createdOn' => '2015-04-01 11:00:00'
                            ],
                            [
                                'name' => 'TN 2',
                                'createdOn' => '2014-04-01 11:00:00'
                            ]
                        ]
                    ]
                ]
            ],
            'An Org T/A TN 2'
        ];
    }
}
