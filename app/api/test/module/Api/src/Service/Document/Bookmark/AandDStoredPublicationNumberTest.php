<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\AandDStoredPublicationNumber;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * AandDStoredPublicationNumber bookmark test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class AandDStoredPublicationNumberTest extends MockeryTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new AandDStoredPublicationNumber();
    }

    public function testGetQuery(): void
    {
        $query = $this->sut->getQuery(['application' => 1]);
        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('publicationsProvider')]
    public function testRender(mixed $data, mixed $result): void
    {
        $this->sut->setData($data);
        $this->assertEquals($result, $this->sut->render());
    }

    public static function publicationsProvider(): array
    {
        return [
            [
                [
                    'publicationLinks' => [
                        [
                            'publication' => [
                                'pubDate' => '2015-10-31',
                                'id' => 2,
                                'publicationNo' => '4'
                            ],
                            'publicationSection' => [
                                'id' => 1
                            ]
                        ],
                        [
                            'publication' => [
                                'pubDate' => '2015-10-29',
                                'id' => 3,
                                'publicationNo' => '1'
                            ],
                            'publicationSection' => [
                                'id' => 1
                            ]
                        ],
                        [
                            'publication' => [
                                'pubDate' => '2015-10-30',
                                'id' => 1,
                                'publicationNo' => '2'
                            ],
                            'publicationSection' => [
                                'id' => 1
                            ]
                        ],
                        [
                            'publication' => [
                                'pubDate' => '2015-10-30',
                                'id' => 2,
                                'publicationNo' => '3'
                            ],
                            'publicationSection' => [
                                'id' => 1
                            ]
                        ],
                        [
                            'publication' => [
                                'pubDate' => '2015-10-30',
                                'id' => 2,
                                'publicationNo' => '3'
                            ],
                            'publicationSection' => [
                                'id' => 1
                            ]
                        ],
                        [
                            'publication' => [
                                'pubDate' => '2015-10-30',
                                'id' => 1,
                                'publicationNo' => '5'
                            ],
                            'publicationSection' => [
                                'id' => 1
                            ]
                        ]
                    ],
                ],
                '4'
            ],
            [
                ['publicationLinks' => []],
                AandDStoredPublicationNumber::APP_NO_PUBLISHED
            ],
            [
                [],
                ''
            ]
        ];
    }
}
