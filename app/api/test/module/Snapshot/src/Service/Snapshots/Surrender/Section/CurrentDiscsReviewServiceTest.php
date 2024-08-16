<?php

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\Surrender\Section;

use Dvsa\Olcs\Api\Entity\Surrender;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\CurrentDiscsReviewService;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Laminas\I18n\Translator\TranslatorInterface;

class CurrentDiscsReviewServiceTest extends MockeryTestCase
{
    /** @var CurrentDiscsReviewService review service */
    protected $sut;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->sut = new CurrentDiscsReviewService($abstractReviewServiceServices);
    }

    /**
     * @param $destoryedDiscs
     * @param $discsLost
     * @param $discsLostInfo
     * @param $discsStolen
     * @param $discsStolenInfo
     * @param $expected
     *
     * @dataProvider dpTestGetConfigFromData
     */
    public function testGetConfigFromData(
        $destroyedDiscs,
        $discsLost,
        $discsLostInfo,
        $discsStolen,
        $discsStolenInfo,
        $expected
    ) {
        $mockEntity = m::mock(Surrender::class);

        $mockEntity->shouldReceive('getDiscDestroyed')->andReturn($destroyedDiscs);
        $mockEntity->shouldReceive('getDiscLost')->andReturn($discsLost);
        $mockEntity->shouldReceive('getDiscLostInfo')->andReturn($discsLostInfo);
        $mockEntity->shouldReceive('getDiscStolen')->andReturn($discsStolen);
        $mockEntity->shouldReceive('getDiscStolenInfo')->andReturn($discsStolenInfo);

        $this->assertEquals($expected, $this->sut->getConfigFromData($mockEntity));
    }

    public function dpTestGetConfigFromData()
    {
        return [
            [
                'destroyedDiscs' => 10,
                'discsLost' => null,
                'discsLostInfo' => null,
                'discsStolen' => 12,
                'discsStolenInfo' => 'discs were stolen',
                'expectedResult' => [
                    'multiItems' => [
                        [
                            [
                                'label' => 'surrender-review-current-discs-destroyed',
                                'value' => 10
                            ],
                            [
                                'label' => 'surrender-review-current-discs-lost',
                                'value' => 0
                            ],
                            [
                                'label' => 'surrender-review-current-discs-stolen',
                                'value' => '12'
                            ],
                            [
                                'label' => 'surrender-review-additional-information',
                                'value' => 'discs were stolen'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'destroyedDiscs' => null,
                'discsLost' => 15,
                'discsLostInfo' => '15 discs were lost',
                'discsStolen' => null,
                'discsStolenInfo' => null,
                'expectedResult' => [
                    'multiItems' => [
                        [
                            [
                                'label' => 'surrender-review-current-discs-destroyed',
                                'value' => 0
                            ],
                            [
                                'label' => 'surrender-review-current-discs-lost',
                                'value' => 15
                            ],
                            [
                                'label' => 'surrender-review-additional-information',
                                'value' => '15 discs were lost'
                            ],
                            [
                                'label' => 'surrender-review-current-discs-stolen',
                                'value' => 0
                            ],
                        ]
                    ]
                ]
            ],
            [
                'destroyedDiscs' => 23,
                'discsLost' => 19,
                'discsLostInfo' => 'lost them',
                'discsStolen' => 2,
                'discsStolenInfo' => 'stolen',
                'expectedResult' => [
                    'multiItems' => [
                        [
                            [
                                'label' => 'surrender-review-current-discs-destroyed',
                                'value' => 23
                            ],
                            [
                                'label' => 'surrender-review-current-discs-lost',
                                'value' => 19
                            ],
                            [
                                'label' => 'surrender-review-additional-information',
                                'value' => 'lost them'
                            ],
                            [
                                'label' => 'surrender-review-current-discs-stolen',
                                'value' => 2
                            ],
                            [
                                'label' => 'surrender-review-additional-information',
                                'value' => 'stolen'
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }
}
