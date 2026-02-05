<?php

/**
 * Variation Operating Centres Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

/**
 * Variation Operating Centres Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationOperatingCentresReviewService extends AbstractOperatingCentresReviewService
{
    /**
     * Create service instance
     *
     * @param AbstractReviewServiceServices $abstractReviewServiceServices
     * @param PsvOperatingCentreReviewService $psvOperatingCentreReviewService
     * @param VariationPsvOcTotalAuthReviewService $variationPsvOcTotalAuthReviewService
     * @param GoodsOperatingCentreReviewService $goodsOperatingCentreReviewService
     * @param VariationGoodsOcTotalAuthReviewService $variationGoodsOcTotalAuthReviewService
     *
     * @return VariationOperatingCentresReviewService
     */
    public function __construct(
        AbstractReviewServiceServices $abstractReviewServiceServices,
        private readonly PsvOperatingCentreReviewService $psvOperatingCentreReviewService,
        private readonly VariationPsvOcTotalAuthReviewService $variationPsvOcTotalAuthReviewService,
        private readonly GoodsOperatingCentreReviewService $goodsOperatingCentreReviewService,
        private readonly VariationGoodsOcTotalAuthReviewService $variationGoodsOcTotalAuthReviewService
    ) {
        parent::__construct($abstractReviewServiceServices);
    }

    /**
     * Format the readonly config from the given data
     *
     * @param array $data
     * @return array
     */
    public function getConfigFromData(array $data = [])
    {
        $config = ['subSections' => []];

        $isPsv = $this->isPsv($data);

        if ($isPsv) {
            $ocService = $this->psvOperatingCentreReviewService;
            $authService = $this->variationPsvOcTotalAuthReviewService;
        } else {
            $ocService = $this->goodsOperatingCentreReviewService;
            $authService = $this->variationGoodsOcTotalAuthReviewService;
        }

        $added = $updated = $deleted = [];

        foreach ($data['operatingCentres'] as $operatingCentre) {
            switch ($operatingCentre['action']) {
                case 'A':
                    $added[] = $ocService->getConfigFromData($operatingCentre);
                    break;
                case 'U':
                    $updated[] = $ocService->getConfigFromData($operatingCentre);
                    break;
                case 'D':
                    $deleted[] = $ocService->getConfigFromData($operatingCentre);
            }
        }

        if (!empty($added)) {
            $config['subSections'][] = [
                'title' => 'variation-review-operating-centres-added-title',
                'mainItems' => $added
            ];
        }

        if (!empty($updated)) {
            $config['subSections'][] = [
                'title' => 'variation-review-operating-centres-updated-title',
                'mainItems' => $updated
            ];
        }

        if (!empty($deleted)) {
            $config['subSections'][] = [
                'title' => 'variation-review-operating-centres-deleted-title',
                'mainItems' => $deleted
            ];
        }

        $config['subSections'][] = [
            'mainItems' => [
                $authService->getConfigFromData($data)
            ]
        ];

        return $config;
    }
}
