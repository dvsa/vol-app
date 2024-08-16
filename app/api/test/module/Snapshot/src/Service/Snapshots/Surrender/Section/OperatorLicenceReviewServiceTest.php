<?php

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\Surrender\Section;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Surrender;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\OperatorLicenceReviewService;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Laminas\I18n\Translator\TranslatorInterface;

class OperatorLicenceReviewServiceTest extends MockeryTestCase
{
    /** @var OperatorLicenceReviewService review service */
    protected $sut;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->sut = new OperatorLicenceReviewService($abstractReviewServiceServices);
    }

    /**
     * @dataProvider dpTestGetConfigFromData
     *
     * @param $args
     */
    public function testGetConfigFromData(
        $args,
        $expected
    ) {
        $mockEntity = m::mock(Surrender::class);

        $mockEntity->shouldReceive('getLicenceDocumentStatus->getDescription')->andReturn($args['licDocDescription']);
        $mockEntity->shouldReceive('getLicenceDocumentStatus->getId')->andReturn($args['licDocStatus']);
        $mockEntity->shouldReceive('getLicenceDocumentInfo')->andReturn($args['licDocInfo']);
        $mockEntity->shouldReceive('getLicence->getLicenceType->getId')->andReturn($args['licType']);

        $this->assertEquals($expected, $this->sut->getConfigFromData($mockEntity));
    }

    public function dpTestGetConfigFromData()
    {
        return [

            0 => [
                [
                    'licDocDescription' => 'Document lost',
                    'licDocStatus' => RefData::SURRENDER_DOC_STATUS_LOST,
                    'licDocInfo' => 'Document lost',
                    'licType' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                ],
                [

                    'multiItems' => [
                        [
                            [
                                'label' => 'surrender-review-documentation-operator-licence',
                                'value' => 'Document lost'
                            ],
                            [
                                'label' => 'surrender-review-additional-information',
                                'value' => 'Document lost'
                            ],
                        ]
                    ]
                ]
            ],
            1 => [
                [
                    'licDocDescription' => 'Document destroyed',
                    'licDocStatus' => RefData::SURRENDER_DOC_STATUS_DESTROYED,
                    'licDocInfo' => null,
                    'licType' => Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                    'commLicDescription' => null,
                    'commLicStatus' => null,
                    'commLicInfo' => null,

                ],
                [

                    'multiItems' => [
                        [
                            [
                                'label' => 'surrender-review-documentation-operator-licence',
                                'value' => 'Document destroyed'
                            ]
                        ]
                    ]
                ]
            ],
            2 => [
                [
                    'licDocDescription' => 'Document destroyed',
                    'licDocStatus' => RefData::SURRENDER_DOC_STATUS_DESTROYED,
                    'licDocInfo' => null,
                    'licType' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                ],
                [

                    'multiItems' => [
                        [
                            [
                                'label' => 'surrender-review-documentation-operator-licence',
                                'value' => 'Document destroyed'
                            ]
                        ]
                    ]
                ]

            ]
        ];
    }
}
