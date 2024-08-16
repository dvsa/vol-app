<?php

/**
 * Variation Financial Evidence Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

/**
 * Variation Financial Evidence Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationFinancialEvidenceReviewService extends AbstractReviewService
{
    /**
     * Create service instance
     *
     * @param AbstractReviewServiceServices $abstractReviewServiceServices
     * @param ApplicationFinancialEvidenceReviewService $applicationFinancialEvidenceReviewService
     *
     * @return VariationFinancialEvidenceReviewService
     */
    public function __construct(
        AbstractReviewServiceServices $abstractReviewServiceServices,
        private readonly ApplicationFinancialEvidenceReviewService $applicationFinancialEvidenceReviewService
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
        return $this->applicationFinancialEvidenceReviewService->getConfigFromData($data);
    }
}
