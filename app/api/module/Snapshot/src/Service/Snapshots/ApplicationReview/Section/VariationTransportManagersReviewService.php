<?php

/**
 * Variation TransportManagers Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

/**
 * Variation TransportManagers Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationTransportManagersReviewService extends AbstractReviewService
{
    /**
     * Create service instance
     *
     * @param AbstractReviewServiceServices $abstractReviewServiceServices
     * @param TransportManagersReviewService $transportManagersReviewService
     *
     * @return VariationTransportManagersReviewService
     */
    public function __construct(
        AbstractReviewServiceServices $abstractReviewServiceServices,
        private readonly TransportManagersReviewService $transportManagersReviewService
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
        $actions = [];

        foreach ($data['transportManagers'] as $transportManagerApplication) {
            $actions[$transportManagerApplication['action']][] = $transportManagerApplication;
        }

        $subSections = [];

        if (isset($actions['A'])) {
            $subSections[] = [
                'title' => 'review-transport-manager-added-title',
                'mainItems' => $this->transportManagersReviewService->getConfigFromData($actions['A'])
            ];
        }

        if (isset($actions['U'])) {
            $subSections[] = [
                'title' => 'review-transport-manager-updated-title',
                'mainItems' => $this->transportManagersReviewService->getConfigFromData($actions['U'])
            ];
        }

        if (isset($actions['D'])) {
            $subSections[] = [
                'title' => 'review-transport-manager-deleted-title',
                'mainItems' => $this->transportManagersReviewService->getConfigFromData($actions['D'])
            ];
        }

        return [
            'subSections' => $subSections
        ];
    }
}
