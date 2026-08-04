<?php

namespace Common\Data\Mapper\Licence\Surrender;

use Common\Data\Mapper\MapperInterface;
use Common\RefData;

class OperatorLicence implements MapperInterface
{
    /**
     * Map data from form to DTO
     *
     * @param array $formData Form data
     */
    public static function mapFromForm(array $formData): array
    {
        $mappedData = [
            'possession' => [
                'licenceDocumentStatus' => RefData::SURRENDER_DOC_STATUS_DESTROYED,
                'licenceDocumentInfo' => null
            ],
            'lost' => [
                'licenceDocumentStatus' => RefData::SURRENDER_DOC_STATUS_LOST,
                'licenceDocumentInfo' => $formData['operatorLicenceDocument']['lostContent']['details'] ?? null
            ],
            'stolen' => [
                'licenceDocumentStatus' => RefData::SURRENDER_DOC_STATUS_STOLEN,
                'licenceDocumentInfo' => $formData['operatorLicenceDocument']['stolenContent']['details'] ?? null
            ],
        ];
        return $mappedData[$formData['operatorLicenceDocument']['operatorLicenceDocument']];
    }

    /**
     * @return (array|string)[][]
     *
     * @psalm-return array{operatorLicenceDocument?: array{operatorLicenceDocument: 'lost'|'possession'|'stolen', stolenContent?: array{details: mixed}, lostContent?: array{details: mixed}}}
     */
    #[\Override]
    public static function mapFromResult(array $data)
    {
        $licenceDocumentStatus = $data["licenceDocumentStatus"]["id"] ?? null;

        if (is_null($licenceDocumentStatus)) {
            return [];
        }

        $formData = [
            RefData::SURRENDER_DOC_STATUS_DESTROYED =>
                [
                    'operatorLicenceDocument' => [
                        'operatorLicenceDocument' => 'possession'
                    ]
                ],
            RefData::SURRENDER_DOC_STATUS_LOST =>
                [
                    'operatorLicenceDocument' => [
                        'operatorLicenceDocument' => 'lost',
                        'lostContent' => [
                            'details' => $data["licenceDocumentInfo"]
                        ]
                    ]
                ],
            RefData::SURRENDER_DOC_STATUS_STOLEN =>
                [
                    'operatorLicenceDocument' => [
                        'operatorLicenceDocument' => 'stolen',
                        'stolenContent' => [
                            'details' => $data["licenceDocumentInfo"]
                        ]
                    ]
                ],
        ];

        return $formData[$licenceDocumentStatus];
    }
}
