<?php

namespace CommonTest\Common\Data\Mapper\Licence\Surrender\Sections;

use Common\Data\Mapper\Licence\Surrender\ReviewDetails;
use Common\Service\Helper\TranslationHelperService;
use CommonTest\Common\Data\Mapper\Licence\Surrender\ReviewContactDetailsMocksAndExpectationsTrait;
use PHPUnit\Framework\TestCase;
use Laminas\Mvc\Controller\Plugin\Url;

class ReviewDetailsTest extends TestCase
{
    use ReviewContactDetailsMocksAndExpectationsTrait;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new ReviewDetails();
    }

    /**
     * @dataProvider  dpReviewDetails
     */
    public function testMakeSectionsLicence($licence, $surrender): void
    {
        $mockTranslator = \Mockery::mock(TranslationHelperService::class);
        $mockUrlHelper = \Mockery::mock(Url::class);
        $mockLicence = $licence[0];
        $mockSurrender = $surrender[0];
        $this->mockTranslatorForLicenceDetails($mockTranslator);
        $this->mockTranslatorForCurrentDiscs($mockTranslator);
        $this->mockTranslatorForOperatorLicence($mockTranslator);

        $this->mockUrlHelperFromRoute($mockUrlHelper, 'licence/surrender/current-discs/review/GET', 4);
        $this->mockUrlHelperFromRoute($mockUrlHelper, 'licence/surrender/operator-licence/review/GET', 2);

        $typeOfLicence = $this->dataName();

        $expected = [
            $this->expectedForLicenceDetails(),
            $this->expectedDiscs(),
            $this->expectedOperatorLicence()

        ];

        if ($typeOfLicence === 'StandardInternational') {
            $mockSurrender['surrender']['isInternationalLicence'] = true;
            $this->mockUrlHelperFromRoute($mockUrlHelper, 'licence/surrender/community-licence/review/GET', 2);
            $this->mockTranslatorForCommunityLicence($mockTranslator);
            $expected[] = $this->expectedCommunityLicence();
        }

        $sections = ReviewDetails::makeSections($mockLicence, $mockUrlHelper, $mockTranslator, $mockSurrender);

        $this->assertSame($expected, $sections);
    }

    /**
     * @return array[][][]
     *
     * @psalm-return array{StandardLicence: list{list{array}, list{array}}, StandardInternational: list{list{array}, list{array}}}
     */
    public function dpReviewDetails(): array
    {
        return [
            'StandardLicence' => [
                [
                    $this->mockLicence()
                ],
                [
                    $this->surrender()
                ]
            ],
            'StandardInternational' => [
                [
                    $this->mockLicence()
                ],
                [
                    $this->surrender()
                ]
            ]

        ];
    }

    public function surrender(): array
    {

        return [
            'surrender' => [
                'version' => '1',
                'licence' => $this->mockLicence(),
                'discDestroyed' => '1',
                'discLost' => null,
                'discStolen' => null,
                'discLostInfo' => '',
                'discStoleninfo' => '',
                'id' => '1',
                'licenceDocumentStatus' => ['id' => 'doc_sts_destroyed'],
                'licenceDocumentInfo' => null,
                'communityLicenceDocumentStatus' => ['id' => 'doc_sts_destroyed'],
                'communityLicenceDocumentInfo' => null,
                'isInternationalLicence' => false
            ]
        ];
    }

    /**
     * @return (((string|string[]|true)[]|string)[]|string|true)[]
     *
     * @psalm-return array{sectionHeading: 'DiscHeading', changeLinkInHeading: true, change: array{sectionLink: 'licence/surrender/current-discs/review/GET'}, questions: list{array{label: 'Number to be destroyed', answer: '1', changeLinkInHeading: true, change: array{sectionLink: 'licence/surrender/current-discs/review/GET'}}, array{label: 'Number lost', answer: '0', changeLinkInHeading: true, change: array{sectionLink: 'licence/surrender/current-discs/review/GET'}}, array{label: 'Number stolen', answer: '0', changeLinkInHeading: true, change: array{sectionLink: 'licence/surrender/current-discs/review/GET'}}}}
     */
    public function expectedDiscs(): array
    {
        return
            [
                'sectionHeading' => 'DiscHeading',
                'changeLinkInHeading' => true,
                'change' => [
                    'sectionLink' => 'licence/surrender/current-discs/review/GET'
                ],
                'questions' => [
                    [
                        'label' => 'Number to be destroyed',
                        'answer' => '1',
                        'changeLinkInHeading' => true,
                        'change' =>
                            [
                                'sectionLink' => 'licence/surrender/current-discs/review/GET',
                            ]
                    ],
                    [
                        'label' => 'Number lost',
                        'answer' => '0',
                        'changeLinkInHeading' => true,
                        'change' =>
                            [
                                'sectionLink' => 'licence/surrender/current-discs/review/GET',
                            ]
                    ]
                    ,
                    [

                        'label' => 'Number stolen',
                        'answer' => '0',
                        'changeLinkInHeading' => true,
                        'change' =>
                            [
                                'sectionLink' => 'licence/surrender/current-discs/review/GET',
                            ]

                    ]
                ]
            ];
    }

    /**
     * @param \Mockery\LegacyMockInterface&\Mockery\MockInterface&TranslationHelperService $mockTranslator
     */
    private function mockTranslatorForCurrentDiscs(TranslationHelperService $mockTranslator): void
    {
        $mockTranslator->shouldReceive('translate')->with('licence.surrender.review.discs.heading')->once()->andReturn('DiscHeading');
        $mockTranslator->shouldReceive('translate')->with('licence.surrender.review.label.discs.possession')->once()->andReturn('Number to be destroyed');
        $mockTranslator->shouldReceive('translate')->with('licence.surrender.review.label.discs.lost')->once()->andReturn('Number lost');
        $mockTranslator->shouldReceive('translate')->with('licence.surrender.review.label.discs.stolen')->once()->andReturn('Number stolen');
    }

    /**
     * @param \Mockery\LegacyMockInterface&\Mockery\MockInterface&TranslationHelperService $mockTranslator
     */
    private function mockTranslatorForOperatorLicence(TranslationHelperService $mockTranslator): void
    {
        $mockTranslator->shouldReceive('translate')->with('surrender.review.label.documents.operatorLicenceDocument')->once()->andReturn('OperatorLicenceLabel');
        $mockTranslator->shouldReceive('translate')->with('licence.surrender.review.label.documents.answerpossession')->once()->andReturn('OperatorLicenceAnswer');
        $mockTranslator->shouldReceive('translate')->with('licence.surrender.review.documents.operatorlicence.heading')->once()->andReturn('OperatorLicenceHeading');
    }

    /**
     * @param \Mockery\LegacyMockInterface&\Mockery\MockInterface&TranslationHelperService $mockTranslator
     */
    private function mockTranslatorForCommunityLicence(TranslationHelperService $mockTranslator): void
    {
        $mockTranslator->shouldReceive('translate')->with('surrender.review.label.documents.communityLicenceDocument')->once()->andReturn('CommunityLicenceLabel');
        $mockTranslator->shouldReceive('translate')->with('licence.surrender.review.label.documents.answerpossession')->once()->andReturn('CommunityLicenceAnswer');
        $mockTranslator->shouldReceive('translate')->with('licence.surrender.review.documents.communitylicence.heading')->once()->andReturn('CommunityLicenceHeading');
    }

    /**
     * @return (((string|string[]|true)[]|string)[]|string|true)[]
     *
     * @psalm-return array{sectionHeading: 'OperatorLicenceHeading', changeLinkInHeading: true, change: array{sectionLink: 'licence/surrender/operator-licence/review/GET'}, questions: list{array{label: 'OperatorLicenceLabel', answer: 'OperatorLicenceAnswer', changeLinkInHeading: true, change: array{sectionLink: 'licence/surrender/operator-licence/review/GET'}}}}
     */
    private function expectedOperatorLicence(): array
    {
        return [

            'sectionHeading' => 'OperatorLicenceHeading',
            'changeLinkInHeading' => true,
            'change' => [
                'sectionLink' => 'licence/surrender/operator-licence/review/GET'
            ],
            'questions' =>
                [
                    [
                        'label' => 'OperatorLicenceLabel',
                        'answer' => 'OperatorLicenceAnswer',
                        'changeLinkInHeading' => true,
                        'change' => [
                            'sectionLink' => 'licence/surrender/operator-licence/review/GET'
                        ]
                    ]
                ]
        ];
    }

    /**
     * @return (((string|string[]|true)[]|string)[]|string|true)[]
     *
     * @psalm-return array{sectionHeading: 'CommunityLicenceHeading', changeLinkInHeading: true, change: array{sectionLink: 'licence/surrender/community-licence/review/GET'}, questions: list{array{label: 'CommunityLicenceLabel', answer: 'CommunityLicenceAnswer', changeLinkInHeading: true, change: array{sectionLink: 'licence/surrender/community-licence/review/GET'}}}}
     */
    private function expectedCommunityLicence(): array
    {

        return [
            'sectionHeading' => 'CommunityLicenceHeading',
            'changeLinkInHeading' => true,
            'change' => [
                'sectionLink' => 'licence/surrender/community-licence/review/GET'
            ],
            'questions' =>
                [
                    [
                        'label' => 'CommunityLicenceLabel',
                        'answer' => 'CommunityLicenceAnswer',
                        'changeLinkInHeading' => true,
                        'change' => [
                            'sectionLink' => 'licence/surrender/community-licence/review/GET'
                        ]
                    ]
                ]
        ];
    }
}
