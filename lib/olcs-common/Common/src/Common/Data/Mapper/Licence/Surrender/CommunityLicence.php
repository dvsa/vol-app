<?php

namespace Common\Data\Mapper\Licence\Surrender;

use Common\Data\Mapper\MapperInterface;
use Common\RefData;

class CommunityLicence implements MapperInterface
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
                'communityLicenceDocumentStatus' => RefData::SURRENDER_DOC_STATUS_DESTROYED,
                'communityLicenceDocumentInfo' => null
            ],
            'lost' => [
                'communityLicenceDocumentStatus' => RefData::SURRENDER_DOC_STATUS_LOST,
                'communityLicenceDocumentInfo' => $formData['communityLicenceDocument']['lostContent']['details'] ?? null
            ],
            'stolen' => [
                'communityLicenceDocumentStatus' => RefData::SURRENDER_DOC_STATUS_STOLEN,
                'communityLicenceDocumentInfo' => $formData['communityLicenceDocument']['stolenContent']['details'] ?? null
            ],
        ];
        return $mappedData[$formData['communityLicenceDocument']['communityLicenceDocument']];
    }

    /**
     * @return (array|string)[][]
     *
     * @psalm-return array{communityLicenceDocument?: array{communityLicenceDocument: 'lost'|'possession'|'stolen', stolenContent?: array{details: mixed}, lostContent?: array{details: mixed}}}
     */
    #[\Override]
    public static function mapFromResult(array $data)
    {
        $licenceDocumentStatus = $data["communityLicenceDocumentStatus"]["id"] ?? null;

        if (is_null($licenceDocumentStatus)) {
            return [];
        }

        $formData = [
            RefData::SURRENDER_DOC_STATUS_DESTROYED =>
                [
                    'communityLicenceDocument' => [
                        'communityLicenceDocument' => 'possession'
                    ]
                ],
            RefData::SURRENDER_DOC_STATUS_LOST =>
                [
                    'communityLicenceDocument' => [
                        'communityLicenceDocument' => 'lost',
                        'lostContent' => [
                            'details' => $data["communityLicenceDocumentInfo"]
                        ]
                    ]
                ],
            RefData::SURRENDER_DOC_STATUS_STOLEN =>
                [
                    'communityLicenceDocument' => [
                        'communityLicenceDocument' => 'stolen',
                        'stolenContent' => [
                            'details' => $data["communityLicenceDocumentInfo"]
                        ]
                    ]
                ],
        ];

        return $formData[$licenceDocumentStatus];
    }
}
