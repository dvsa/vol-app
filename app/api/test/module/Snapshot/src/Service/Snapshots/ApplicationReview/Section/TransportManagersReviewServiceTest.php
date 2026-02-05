<?php

declare(strict_types=1);

/**
 * Transport Managers Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\TransportManagersReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Transport Managers Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagersReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    /** @var TranslatorInterface */
    protected $mockTranslator;

    public function setUp(): void
    {
        $this->mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($this->mockTranslator);

        $this->sut = new TransportManagersReviewService($abstractReviewServiceServices);
    }

    public function testGetConfigFromData(): void
    {
        $data = [
            [
                'transportManager' => [
                    'homeCd' => [
                        'emailAddress' => 'foo@bar.com',
                        'person' => [
                            'birthDate' => '1989-08-23',
                            'forename' => 'foo',
                            'familyName' => 'bar',
                            'title' => [
                                'description' => 'Mr'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'transportManager' => [
                    'homeCd' => [
                        'emailAddress' => 'bar@foo.com',
                        'person' => [
                            'birthDate' => '1991-08-23',
                            'forename' => 'bar',
                            'familyName' => 'foo',
                            'title' => [
                                'description' => 'Mrs'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $expected = [
            [
                'header' => 'Mr foo bar',
                'multiItems' => [
                    [
                        [
                            'label' => 'review-transport-manager-email',
                            'value' => 'foo@bar.com'
                        ],
                        [
                            'label' => 'review-transport-manager-dob',
                            'value' => '23 Aug 1989'
                        ]
                    ]
                ]
            ],
            [
                'header' => 'Mrs bar foo',
                'multiItems' => [
                    [
                        [
                            'label' => 'review-transport-manager-email',
                            'value' => 'bar@foo.com'
                        ],
                        [
                            'label' => 'review-transport-manager-dob',
                            'value' => '23 Aug 1991'
                        ]
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }
}
