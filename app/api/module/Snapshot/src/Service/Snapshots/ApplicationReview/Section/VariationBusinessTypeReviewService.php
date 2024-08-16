<?php

/**
 * Variation Business Type Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

/**
 * Variation Business Type Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationBusinessTypeReviewService extends AbstractReviewService
{
    /**
     * Format the readonly config from the given data
     *
     * @param array $data
     * @return array
     */
    public function getConfigFromData(array $data = [])
    {
        return ['freetext' => $this->translate('variation-review-business-type-change')];
    }
}
