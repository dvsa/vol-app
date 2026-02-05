<?php

declare(strict_types=1);

/**
 * People Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\PeopleReviewService;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * People Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PeopleReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->sut = new PeopleReviewService($abstractReviewServiceServices);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testGetConfigFromData(mixed $data, mixed $showPosition, mixed $expected): void
    {
        $this->assertEquals($expected, $this->sut->getConfigFromData($data, $showPosition));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('providerShouldShowPosition')]
    public function testShouldShowPosition(mixed $orgType, mixed $expected): void
    {
        $data = [
            'licence' => [
                'organisation' => [
                    'type' => [
                        'id' => $orgType
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $this->sut->shouldShowPosition($data));
    }

    public static function providerShouldShowPosition(): array
    {
        return [
            [
                Organisation::ORG_TYPE_OTHER,
                true
            ],
            [
                Organisation::ORG_TYPE_SOLE_TRADER,
                false
            ],
            [
                Organisation::ORG_TYPE_PARTNERSHIP,
                false
            ],
            [
                Organisation::ORG_TYPE_LLP,
                false
            ],
            [
                Organisation::ORG_TYPE_REGISTERED_COMPANY,
                false
            ]
        ];
    }

    public static function provider(): array
    {
        return [
            [
                [
                    'position' => 'The boss',
                    'person' => [
                        'forename' => 'Bob',
                        'familyName' => 'Smith',
                        'title' => [
                            'description' => 'Mr'
                        ],
                        'otherName' => 'Robert',
                        'birthDate' => '1989-08-23'
                    ]
                ],
                false,
                [
                    'header' => 'Bob Smith',
                    'multiItems' => [
                        [
                            [
                                'label' => 'application-review-people-person-title',
                                'value' => 'Mr'
                            ],
                            [
                                'label' => 'application-review-people-person-firstname',
                                'value' => 'Bob'
                            ],
                            [
                                'label' => 'application-review-people-person-lastname',
                                'value' => 'Smith'
                            ],
                            [
                                'label' => 'application-review-people-person-othername',
                                'value' => 'Robert'
                            ],
                            [
                                'label' => 'application-review-people-person-dob',
                                'value' => '23 Aug 1989'
                            ]
                        ]
                    ]
                ]
            ],
            [
                [
                    'position' => 'The boss',
                    'person' => [
                        'forename' => 'Bob',
                        'familyName' => 'Smith',
                        'title' => [
                            'description' => 'Mr'
                        ],
                        'otherName' => 'Robert',
                        'birthDate' => '1989-08-23'
                    ]
                ],
                true,
                [
                    'header' => 'Bob Smith',
                    'multiItems' => [
                        [
                            [
                                'label' => 'application-review-people-person-title',
                                'value' => 'Mr'
                            ],
                            [
                                'label' => 'application-review-people-person-firstname',
                                'value' => 'Bob'
                            ],
                            [
                                'label' => 'application-review-people-person-lastname',
                                'value' => 'Smith'
                            ],
                            [
                                'label' => 'application-review-people-person-othername',
                                'value' => 'Robert'
                            ],
                            [
                                'label' => 'application-review-people-person-dob',
                                'value' => '23 Aug 1989'
                            ],
                            [
                                'label' => 'application-review-people-person-position',
                                'value' => 'The boss'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
