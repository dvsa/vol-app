<?php

/**
 * Application Operating Centres Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

/**
 * Application Operating Centres Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationOperatingCentresReviewService extends AbstractOperatingCentresReviewService
{
    /**
     * Create service instance
     *
     * @param AbstractReviewServiceServices $abstractReviewServiceServices
     * @param PsvOperatingCentreReviewService $psvOperatingCentreReviewService
     * @param ApplicationPsvOcTotalAuthReviewService $applicationPsvOcTotalAuthReviewService
     * @param GoodsOperatingCentreReviewService $goodsOperatingCentreReviewService
     * @param ApplicationGoodsOcTotalAuthReviewService $applicationGoodsOcTotalAuthReviewService
     * @param TrafficAreaReviewService $trafficAreaReviewService
     *
     * @return ApplicationOperatingCentresReviewService
     */
    public function __construct(
        AbstractReviewServiceServices $abstractReviewServiceServices,
        private readonly PsvOperatingCentreReviewService $psvOperatingCentreReviewService,
        private readonly ApplicationPsvOcTotalAuthReviewService $applicationPsvOcTotalAuthReviewService,
        private readonly GoodsOperatingCentreReviewService $goodsOperatingCentreReviewService,
        private readonly ApplicationGoodsOcTotalAuthReviewService $applicationGoodsOcTotalAuthReviewService,
        private readonly TrafficAreaReviewService $trafficAreaReviewService
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
            $authService = $this->applicationPsvOcTotalAuthReviewService;
        } else {
            $ocService = $this->goodsOperatingCentreReviewService;
            $authService = $this->applicationGoodsOcTotalAuthReviewService;
        }

        $added = [];

        foreach ($data['operatingCentres'] as $operatingCentre) {
            $added[] = $ocService->getConfigFromData($operatingCentre);
        }

        if (!empty($added)) {
            $config['subSections'][] = ['mainItems' => $added];
        }

        $config['subSections'][] = [
            'mainItems' => [
                $this->trafficAreaReviewService->getConfigFromData($data),
                $authService->getConfigFromData($data)
            ]
        ];

        return $config;
    }
}
