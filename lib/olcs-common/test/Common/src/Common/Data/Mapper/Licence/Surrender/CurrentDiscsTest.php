<?php

namespace CommonTest\Data\Mapper\Licence\Surrender;

use Common\Data\Mapper\Licence\Surrender\CurrentDiscs;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CurrentDiscsTest extends MockeryTestCase
{
    /**
     * @dataProvider dpMapFromResult
     */
    public function testMapFromResult($apiData, $formData): void
    {
        static::assertEquals(
            $formData,
            CurrentDiscs::mapFromResult($apiData)
        );
    }

    /**
     * @dataProvider dpMapFromForm
     */
    public function testMapFromForm($apiData, $formData): void
    {
        static::assertEquals(
            $apiData,
            CurrentDiscs::mapFromForm($formData)
        );
    }

    /**
     * @return (((int|null|string)[]|string)[]|int|null|string)[][][]
     *
     * @psalm-return array{case_01: array{apiData: array{id: 5, licence_id: 70, status: 'surr_sts_start', discDestroyed: 1, discLost: 2, discStolen: 3, discLostInfo: 'it was lost', discStolenInfo: 'someone stole it', licenceDocumentStatus: null, communityLicenceDocumentStatus: null, digitalSignatureId: null, signatureType: null, createdBy: 542, lastModifiedBy: 542, createdOn: '2018-12-17 12 =>20 =>17', lastModifiedOn: null, version: 1}, formData: array{version: 1, possessionSection: array{inPossession: 'Y', info: array{number: 1}}, lostSection: array{lost: 'Y', info: array{number: 2, details: 'it was lost'}}, stolenSection: array{stolen: 'Y', info: array{number: 3, details: 'someone stole it'}}}}, case_02: array{apiData: array{id: 5, licence_id: 70, status: 'surr_sts_start', discDestroyed: null, discLost: null, discStolen: null, discLostInfo: null, discStolenInfo: null, licenceDocumentStatus: null, communityLicenceDocumentStatus: null, digitalSignatureId: null, signatureType: null, createdBy: 542, lastModifiedBy: 542, createdOn: '2018-12-17 12 =>20 =>17', lastModifiedOn: null, version: 1}, formData: array{version: 1, possessionSection: array{inPossession: 'N', info: array{number: null}}, lostSection: array{lost: 'N', info: array{number: null, details: null}}, stolenSection: array{stolen: 'N', info: array{number: null, details: null}}}}}
     */
    public function dpMapFromResult(): array
    {
        return [
            'case_01' => [
                'apiData' => [
                    "id" => 5,
                    "licence_id" => 70,
                    "status" => "surr_sts_start",
                    "discDestroyed" => 1,
                    "discLost" => 2,
                    "discStolen" => 3,
                    "discLostInfo" => 'it was lost',
                    "discStolenInfo" => 'someone stole it',
                    "licenceDocumentStatus" => null,
                    "communityLicenceDocumentStatus" => null,
                    "digitalSignatureId" => null,
                    "signatureType" => null,
                    "createdBy" => 542,
                    "lastModifiedBy" => 542,
                    "createdOn" => "2018-12-17 12 =>20 =>17",
                    "lastModifiedOn" => null,
                    "version" => 1
                ],
                'formData' => [
                    'version' => 1,
                    'possessionSection' => [
                        'inPossession' => 'Y',
                        'info' => [
                            'number' => 1,
                        ],
                    ],
                    'lostSection' => [
                        'lost' => 'Y',
                        'info' => [
                            'number' => 2,
                            'details' => 'it was lost',
                        ],
                    ],
                    'stolenSection' => [
                        'stolen' => 'Y',
                        'info' => [
                            'number' => 3,
                            'details' => 'someone stole it',
                        ],
                    ],
                ]
            ],
            'case_02' => [
                'apiData' => [
                    "id" => 5,
                    "licence_id" => 70,
                    "status" => "surr_sts_start",
                    "discDestroyed" => null,
                    "discLost" => null,
                    "discStolen" => null,
                    "discLostInfo" => null,
                    "discStolenInfo" => null,
                    "licenceDocumentStatus" => null,
                    "communityLicenceDocumentStatus" => null,
                    "digitalSignatureId" => null,
                    "signatureType" => null,
                    "createdBy" => 542,
                    "lastModifiedBy" => 542,
                    "createdOn" => "2018-12-17 12 =>20 =>17",
                    "lastModifiedOn" => null,
                    "version" => 1
                ],
                'formData' => [
                    'version' => 1,
                    'possessionSection' => [
                        'inPossession' => 'N',
                        'info' => [
                            'number' => null,
                        ],
                    ],
                    'lostSection' => [
                        'lost' => 'N',
                        'info' => [
                            'number' => null,
                            'details' => null,
                        ],
                    ],
                    'stolenSection' => [
                        'stolen' => 'N',
                        'info' => [
                            'number' => null,
                            'details' => null,
                        ],
                    ],
                ]
            ]
        ];
    }

    /**
     * @return (((int|null|string)[]|string)[]|int|null|string)[][][]
     *
     * @psalm-return array{case_01: array{apiData: array{discDestroyed: 1, discLost: 2, discStolen: 3, discLostInfo: 'it was lost', discStolenInfo: 'someone stole it'}, formData: array{version: 1, possessionSection: array{inPossession: 'Y', info: array{number: 1}}, lostSection: array{lost: 'Y', info: array{number: 2, details: 'it was lost'}}, stolenSection: array{stolen: 'Y', info: array{number: 3, details: 'someone stole it'}}}}, case_02: array{apiData: array{discDestroyed: null, discLost: null, discLostInfo: null, discStolen: null, discStolenInfo: null}, formData: array{version: 1, possessionSection: array{inPossession: 'N', info: array{number: null}}, lostSection: array{lost: 'N', info: array{number: null, details: null}}, stolenSection: array{stolen: 'N', info: array{number: null, details: null}}}}, case_03: array{apiData: array{discDestroyed: null, discLost: 4, discLostInfo: 'it was lost', discStolen: null, discStolenInfo: null}, formData: array{version: 1, possessionSection: array{inPossession: 'N', info: array{number: 1}}, lostSection: array{lost: 'Y', info: array{number: 4, details: 'it was lost'}}, stolenSection: array{stolen: 'N', info: array{number: null, details: null}}}}, case_04: array{apiData: array{discDestroyed: null, discLost: null, discLostInfo: null, discStolen: 3, discStolenInfo: 'someone stole it'}, formData: array{version: 1, possessionSection: array{inPossession: 'N', info: array{number: 1}}, lostSection: array{lost: 'N', info: array{number: 4, details: 'it was lost'}}, stolenSection: array{stolen: 'Y', info: array{number: 3, details: 'someone stole it'}}}}}
     */
    public function dpMapFromForm(): array
    {
        return [
            'case_01' => [
                'apiData' => [
                    "discDestroyed" => 1,
                    "discLost" => 2,
                    "discStolen" => 3,
                    "discLostInfo" => 'it was lost',
                    "discStolenInfo" => 'someone stole it',
                ],
                'formData' => [
                    'version' => 1,
                    'possessionSection' => [
                        'inPossession' => 'Y',
                        'info' => [
                            'number' => 1,
                        ],
                    ],
                    'lostSection' => [
                        'lost' => 'Y',
                        'info' => [
                            'number' => 2,
                            'details' => 'it was lost',
                        ],
                    ],
                    'stolenSection' => [
                        'stolen' => 'Y',
                        'info' => [
                            'number' => 3,
                            'details' => 'someone stole it',
                        ],
                    ],
                ]
            ],
            'case_02' => [
                'apiData' => [
                    'discDestroyed' => null,
                    'discLost' => null,
                    'discLostInfo' => null,
                    'discStolen' => null,
                    'discStolenInfo' => null,
                ],
                'formData' => [
                    'version' => 1,
                    'possessionSection' => [
                        'inPossession' => 'N',
                        'info' => [
                            'number' => null,
                        ],
                    ],
                    'lostSection' => [
                        'lost' => 'N',
                        'info' => [
                            'number' => null,
                            'details' => null,
                        ],
                    ],
                    'stolenSection' => [
                        'stolen' => 'N',
                        'info' => [
                            'number' => null,
                            'details' => null,
                        ],
                    ],
                ]
            ],
            'case_03' => [
                'apiData' => [
                    'discDestroyed' => null,
                    'discLost' => 4,
                    'discLostInfo' => 'it was lost',
                    'discStolen' => null,
                    'discStolenInfo' => null,
                ],
                'formData' => [
                    'version' => 1,
                    'possessionSection' => [
                        'inPossession' => 'N',
                        'info' => [
                            'number' => 1,
                        ],
                    ],
                    'lostSection' => [
                        'lost' => 'Y',
                        'info' => [
                            'number' => 4,
                            'details' => 'it was lost',
                        ],
                    ],
                    'stolenSection' => [
                        'stolen' => 'N',
                        'info' => [
                            'number' => null,
                            'details' => null,
                        ],
                    ],
                ]
            ],
            'case_04' => [
                'apiData' => [
                    'discDestroyed' => null,
                    'discLost' => null,
                    'discLostInfo' => null,
                    'discStolen' => 3,
                    'discStolenInfo' => 'someone stole it',
                ],
                'formData' => [
                    'version' => 1,
                    'possessionSection' => [
                        'inPossession' => 'N',
                        'info' => [
                            'number' => 1,
                        ],
                    ],
                    'lostSection' => [
                        'lost' => 'N',
                        'info' => [
                            'number' => 4,
                            'details' => 'it was lost',
                        ],
                    ],
                    'stolenSection' => [
                        'stolen' => 'Y',
                        'info' => [
                            'number' => 3,
                            'details' => 'someone stole it',
                        ],
                    ],
                ]
            ]
        ];
    }
}
