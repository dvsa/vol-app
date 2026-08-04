<?php

declare(strict_types=1);

namespace CommonTest\Data\Mapper\Licence\Surrender;

use Common\Data\Mapper\Licence\Surrender\OperatorLicence;
use Common\RefData;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class OperatorLicenceTest extends MockeryTestCase
{
    private $operatorLicence;

    #[\Override]
    protected function setUp(): void
    {
        $this->operatorLicence = new OperatorLicence();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestMapFromForm')]
    public function testMapFromForm($formData, $mappedData): void
    {
        $this->assertEquals($mappedData, $this->operatorLicence->mapFromForm($formData));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestMapFromResult')]
    public function testMapFromResult($mappedApiData, $apiData): void
    {
        $this->assertEquals($mappedApiData, $this->operatorLicence->mapFromResult($apiData));
    }

    /**
     * @return \Iterator<(int | string), array<array<(array<(array<string> | string)> | string | null)>>>
     *
     * @psalm-return array{case_01: array{form_data: array{operatorLicenceDocument: array{operatorLicenceDocument: 'lost', lostContent: array{details: 'lost info'}, stolenContent: array{details: ''}}}, mapped_form_data: array{licenceDocumentStatus: 'doc_sts_lost', licenceDocumentInfo: 'lost info'}}, case_02: array{form_data: array{operatorLicenceDocument: array{operatorLicenceDocument: 'stolen', stolenContent: array{details: 'stolen info'}, lostContent: array{details: ''}}}, mapped_form_data: array{licenceDocumentStatus: 'doc_sts_stolen', licenceDocumentInfo: 'stolen info'}}, case_03: array{form_data: array{operatorLicenceDocument: array{operatorLicenceDocument: 'possession', lostContent: array{details: 'lost info'}, stolenContent: array{details: ''}}}, mapped_form_data: array{licenceDocumentStatus: 'doc_sts_destroyed', licenceDocumentInfo: null}}, case_04: array{form_data: array{operatorLicenceDocument: array{operatorLicenceDocument: 'possession'}}, mapped_form_data: array{licenceDocumentStatus: 'doc_sts_destroyed', licenceDocumentInfo: null}}}
     */
    public static function dpTestMapFromForm(): \Iterator
    {
        yield 'case_01' => [
            'formData' =>
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
            'mappedData' =>
                [
                    'licenceDocumentStatus' => RefData::SURRENDER_DOC_STATUS_LOST,
                    'licenceDocumentInfo' => 'lost info'
                ],
        ];
        yield 'case_02' => [
            'formData' =>
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
            'mappedData' =>
                [
                    'licenceDocumentStatus' => RefData::SURRENDER_DOC_STATUS_STOLEN,
                    'licenceDocumentInfo' => 'stolen info'
                ],
        ];
        yield 'case_03' => [
            'formData' =>
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
            'mappedData' =>
                [
                    'licenceDocumentStatus' => RefData::SURRENDER_DOC_STATUS_DESTROYED,
                    'licenceDocumentInfo' => null
                ],
        ];
        yield 'case_04' => [
            'formData' =>
                [
                    'operatorLicenceDocument' =>
                        [
                            'operatorLicenceDocument' => 'possession'
                        ],

                ],
            'mappedData' =>
                [
                    'licenceDocumentStatus' => RefData::SURRENDER_DOC_STATUS_DESTROYED,
                    'licenceDocumentInfo' => null
                ],
        ];
    }

    /**
     * @return \Iterator<(int | string), array<array<(array<(array<string> | string)> | string | null)>>>
     *
     * @psalm-return array{case_01: array{mapped_api_data: array{operatorLicenceDocument: array{operatorLicenceDocument: 'lost', lostContent: array{details: 'lost info'}}}, api_data: array{licenceDocumentStatus: array{id: 'doc_sts_lost'}, licenceDocumentInfo: 'lost info'}}, case_02: array{mapped_api_data: array{operatorLicenceDocument: array{operatorLicenceDocument: 'stolen', stolenContent: array{details: 'stolen info'}}}, api_data: array{licenceDocumentStatus: array{id: 'doc_sts_stolen'}, licenceDocumentInfo: 'stolen info'}}, case_03: array{mapped_api_data: array{operatorLicenceDocument: array{operatorLicenceDocument: 'possession'}}, api_data: array{licenceDocumentStatus: array{id: 'doc_sts_destroyed'}, licenceDocumentInfo: null}}}
     */
    public static function dpTestMapFromResult(): \Iterator
    {
        yield 'case_01' => [
            'mappedApiData' =>
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
            'apiData' => [
                'licenceDocumentStatus' =>
                    [
                        'id' => RefData::SURRENDER_DOC_STATUS_LOST,
                    ],
                'licenceDocumentInfo' => 'lost info'
            ],
        ];
        yield 'case_02' => [
            'mappedApiData' =>
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
            'apiData' =>
                [
                    'licenceDocumentStatus' =>
                        [
                            'id' => RefData::SURRENDER_DOC_STATUS_STOLEN,
                        ],
                    'licenceDocumentInfo' => 'stolen info'
                ],
        ];
        yield 'case_03' => [
            'mappedApiData' =>
                [
                    'operatorLicenceDocument' =>
                        [
                            'operatorLicenceDocument' => 'possession',
                        ],
                ],
            'apiData' =>
                [
                    'licenceDocumentStatus' =>
                        [
                            'id' => RefData::SURRENDER_DOC_STATUS_DESTROYED,
                        ],
                    'licenceDocumentInfo' => null
                ],
        ];
    }
}
