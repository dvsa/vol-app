<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;

/**
 * Goods Operating Centre Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GoodsOperatingCentreReviewService extends AbstractReviewService
{
    /**
     * Create service instance
     *
     * @param AbstractReviewServiceServices $abstractReviewServiceServices
     * @param PsvOperatingCentreReviewService $psvOperatingCentreReviewService
     *
     * @return GoodsOperatingCentreReviewService
     */
    public function __construct(
        AbstractReviewServiceServices $abstractReviewServiceServices,
        private readonly PsvOperatingCentreReviewService $psvOperatingCentreReviewService
    ) {
        parent::__construct($abstractReviewServiceServices);
    }

    /**
     * Format the OC config
     *
     * @param array $data Data from API
     *
     * @return array
     */
    public function getConfigFromData(array $data = [])
    {
        $config = $this->psvOperatingCentreReviewService->getConfigFromData($data);

        $useHgvLabel = !is_null($data['application']['totAuthLgvVehicles']);

        $config['multiItems']['vehicles+trailers'] = [
            [
                'label' => $useHgvLabel ? 'review-operating-centre-total-vehicles-hgv' : 'review-operating-centre-total-vehicles',
                'value' => $data['noOfVehiclesRequired']
            ],
            [
                'label' => 'review-operating-centre-total-trailers',
                'value' => $data['noOfTrailersRequired'],
            ],
        ];

        $adPlacedValue = [
            ApplicationOperatingCentre::AD_POST => 'review-operating-centre-advertisement-post',
            ApplicationOperatingCentre::AD_UPLOAD_NOW => 'review-operating-centre-advertisement-upload-now',
            ApplicationOperatingCentre::AD_UPLOAD_LATER => 'review-operating-centre-advertisement-upload-later'
        ];
        // Add the advertisements fields
        $config['multiItems']['advertisements'] = [
            [
                'label' => 'review-operating-centre-advertisement-ad-placed',
                'value' => $this->translate($adPlacedValue[$data['adPlaced']])
            ]
        ];

        if ($data['adPlaced'] === ApplicationOperatingCentre::AD_UPLOAD_NOW) {
            $config['multiItems']['advertisements'] = array_merge(
                $config['multiItems']['advertisements'],
                [
                    [
                        'label' => 'review-operating-centre-advertisement-newspaper',
                        'value' => $data['adPlacedIn']
                    ],
                    [
                        'label' => 'review-operating-centre-advertisement-date',
                        'value' => $this->formatDate($data['adPlacedDate'])
                    ],
                    [
                        'label' => 'review-operating-centre-advertisement-file',
                        'noEscape' => true,
                        'value' => $this->formatAdDocumentList($data)
                    ]
                ]
            );
        }

        return $config;
    }

    /**
     * Format ad document list
     *
     * @param array $data data from API
     *
     * @return string
     */
    private function formatAdDocumentList($data)
    {
        $files = [];

        foreach ($data['operatingCentre']['adDocuments'] as $document) {
            if ($document['application']['id'] == $data['application']['id']) {
                $files[] = $document['description'];
            }
        }

        if (empty($files)) {
            return $this->translate('no-files-uploaded');
        }

        return implode('<br>', $files);
    }
}
