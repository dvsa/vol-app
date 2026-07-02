<?php

namespace CommonTest\Data\Mapper\Licence\Surrender;

use Common\Data\Mapper\Licence\Surrender\OperatorLicence;
use Common\RefData;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class OperatorLicenceTest extends MockeryTestCase
{
    private $operatorLicence;

    #[\Override]
    protected function setUp(): void
    {
        $this->operatorLicence = new OperatorLicence();
    }

    /**
     * @dataProvider dpTestMapFromForm
     */
    public function testMapFromForm($formData, $mappedData): void
    {
        static::assertEquals(
            $mappedData,
            $this->operatorLicence->mapFromForm($formData)
        );
    }

    /**
     * @dataProvider dpTestMapFromResult
     */
    public function testMapFromResult($mappedApiData, $apiData): void
    {
        static::assertEquals(
            $mappedApiData,
            $this->operatorLicence->mapFromResult($apiData)
        );
    }

    /**
     * @return ((string|string[])[]|null|string)[][][]
     *
     * @psalm-return array{case_01: array{form_data: array{operatorLicenceDocument: array{operatorLicenceDocument: 'lost', lostContent: array{details: 'lost info'}, stolenContent: array{details: ''}}}, mapped_form_data: array{licenceDocumentStatus: 'doc_sts_lost', licenceDocumentInfo: 'lost info'}}, case_02: array{form_data: array{operatorLicenceDocument: array{operatorLicenceDocument: 'stolen', stolenContent: array{details: 'stolen info'}, lostContent: array{details: ''}}}, mapped_form_data: array{licenceDocumentStatus: 'doc_sts_stolen', licenceDocumentInfo: 'stolen info'}}, case_03: array{form_data: array{operatorLicenceDocument: array{operatorLicenceDocument: 'possession', lostContent: array{details: 'lost info'}, stolenContent: array{details: ''}}}, mapped_form_data: array{licenceDocumentStatus: 'doc_sts_destroyed', licenceDocumentInfo: null}}, case_04: array{form_data: array{operatorLicenceDocument: array{operatorLicenceDocument: 'possession'}}, mapped_form_data: array{licenceDocumentStatus: 'doc_sts_destroyed', licenceDocumentInfo: null}}}
     */
    public function dpTestMapFromForm(): array
    {
        return [
            'case_01' =>
                [
                    'form_data' =>
                        [
                            'operatorLicenceDocument' =>
                                [
                                    'operatorLicenceDocument' => 'lost',
                                    'lostContent' =>
                                        [
                                            'details' => 'lost info'
                                        ],
                                    'stolenContent' =>
                                        [
                                            'details' => ''
                                        ],
                                ],
                        ],
                    'mapped_form_data' =>
                        [
                            'licenceDocumentStatus' => RefData::SURRENDER_DOC_STATUS_LOST,
                            'licenceDocumentInfo' => 'lost info'
                        ],
                ],
            'case_02' =>
                [
                    'form_data' =>
                        [
                            'operatorLicenceDocument' =>
                                [
                                    'operatorLicenceDocument' => 'stolen',
                                    'stolenContent' =>
                                        [
                                            'details' => 'stolen info'
                                        ],
                                    'lostContent' =>
                                        [
                                            'details' => ''
                                        ],
                                ],
                        ],
                    'mapped_form_data' =>
                        [
                            'licenceDocumentStatus' => RefData::SURRENDER_DOC_STATUS_STOLEN,
                            'licenceDocumentInfo' => 'stolen info'
                        ],
                ],
            'case_03' =>
                [
                    'form_data' =>
                        [
                            'operatorLicenceDocument' =>
                                [
                                    'operatorLicenceDocument' => 'possession',
                                    'lostContent' =>
                                        [
                                            'details' => 'lost info'
                                        ],
                                    'stolenContent' =>
                                        [
                                            'details' => ''
                                        ],
                                ],

                        ],
                    'mapped_form_data' =>
                        [
                            'licenceDocumentStatus' => RefData::SURRENDER_DOC_STATUS_DESTROYED,
                            'licenceDocumentInfo' => null
                        ],
                ],
                'case_04' =>
                [
                    'form_data' =>
                        [
                            'operatorLicenceDocument' =>
                                [
                                    'operatorLicenceDocument' => 'possession'
                                ],

                        ],
                    'mapped_form_data' =>
                        [
                            'licenceDocumentStatus' => RefData::SURRENDER_DOC_STATUS_DESTROYED,
                            'licenceDocumentInfo' => null
                        ],
                ]
        ];
    }

    /**
     * @return ((string|string[])[]|null|string)[][][]
     *
     * @psalm-return array{case_01: array{mapped_api_data: array{operatorLicenceDocument: array{operatorLicenceDocument: 'lost', lostContent: array{details: 'lost info'}}}, api_data: array{licenceDocumentStatus: array{id: 'doc_sts_lost'}, licenceDocumentInfo: 'lost info'}}, case_02: array{mapped_api_data: array{operatorLicenceDocument: array{operatorLicenceDocument: 'stolen', stolenContent: array{details: 'stolen info'}}}, api_data: array{licenceDocumentStatus: array{id: 'doc_sts_stolen'}, licenceDocumentInfo: 'stolen info'}}, case_03: array{mapped_api_data: array{operatorLicenceDocument: array{operatorLicenceDocument: 'possession'}}, api_data: array{licenceDocumentStatus: array{id: 'doc_sts_destroyed'}, licenceDocumentInfo: null}}}
     */
    public function dpTestMapFromResult(): array
    {
        return [
            'case_01' =>
                [
                    'mapped_api_data' =>
                        [
                            'operatorLicenceDocument' =>
                                [
                                    'operatorLicenceDocument' => 'lost',
                                    'lostContent' =>
                                        [
                                            'details' => 'lost info'
                                        ]
                                ],
                        ],
                    'api_data' => [
                        'licenceDocumentStatus' =>
                            [
                                'id' => RefData::SURRENDER_DOC_STATUS_LOST,
                            ],
                        'licenceDocumentInfo' => 'lost info'
                    ],
                ],
            'case_02' =>
                [
                    'mapped_api_data' =>
                        [
                            'operatorLicenceDocument' =>
                                [
                                    'operatorLicenceDocument' => 'stolen',
                                    'stolenContent' =>
                                        [
                                            'details' => 'stolen info'
                                        ]
                                ],
                        ],
                    'api_data' =>
                        [
                            'licenceDocumentStatus' =>
                                [
                                    'id' => RefData::SURRENDER_DOC_STATUS_STOLEN,
                                ],
                            'licenceDocumentInfo' => 'stolen info'
                        ],
                ],
            'case_03' =>
                [
                    'mapped_api_data' =>
                        [
                            'operatorLicenceDocument' =>
                                [
                                    'operatorLicenceDocument' => 'possession',
                                ],
                        ],
                    'api_data' =>
                        [
                            'licenceDocumentStatus' =>
                                [
                                    'id' => RefData::SURRENDER_DOC_STATUS_DESTROYED,
                                ],
                            'licenceDocumentInfo' => null
                        ],
                ]
        ];
    }
}
