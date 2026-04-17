<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\Surrender\Section;

use Dvsa\Olcs\Api\Entity\Surrender;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\CommunityLicenceReviewService;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\I18n\Translator\TranslatorInterface;

class CommunityLicenceReveiwServiceTest extends MockeryTestCase
{
    /** @var CommunityLicenceReviewService review service */
    protected $sut;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->sut = new CommunityLicenceReviewService($abstractReviewServiceServices);
    }

    /**
     * @param $args
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetConfigFromData')]
    public function testGetConfigFromData(
        mixed $args,
        mixed $expected
    ): void {
        $mockEntity = m::mock(Surrender::class);
        $mockEntity->shouldReceive('getCommunityLicenceDocumentStatus->getDescription')->once()->andReturn($args['commLicDescription']);
        $mockEntity->shouldReceive('getCommunityLicenceDocumentStatus->getId')->once()->andReturn($args['commLicStatus']);
        if ($this->dataName() !== 'community_licence_destroyed') {
            $mockEntity->shouldReceive('getCommunityLicenceDocumentInfo')->once()->andReturn($args['commLicInfo']);
        }
        $this->assertEquals($expected, $this->sut->getConfigFromData($mockEntity));
    }

    public static function dpTestGetConfigFromData(): array
    {
        return [
            'stolen_community_licence' => [
                [
                    'licType' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    'commLicDescription' => 'Document stolen',
                    'commLicStatus' => RefData::SURRENDER_DOC_STATUS_STOLEN,
                    'commLicInfo' => 'Document stolen',
                ],
                [

                    'multiItems' => [
                        [
                            [
                                'label' => 'surrender-review-documentation-community-licence',
                                'value' => 'Document stolen'
                            ],
                            [
                                'label' => 'surrender-review-additional-information',
                                'value' => 'Document stolen'
                            ]
                        ]
                    ]
                ]
            ],
            'community_licence_destroyed' => [
                [

                    'licType' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    'commLicDescription' => 'Document destroyed',
                    'commLicStatus' => RefData::SURRENDER_DOC_STATUS_DESTROYED,
                    'commLicInfo' => null,
                ],
                [

                    'multiItems' => [
                        [

                            [
                                'label' => 'surrender-review-documentation-community-licence',
                                'value' => 'Document destroyed'
                            ],
                        ]
                    ]
                ]

            ]
        ];
    }
}
