<?php

/**
 * Licence Conditions Undertakings Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

/**
 * Licence Conditions Undertakings Review Service
 *
 * @NOTE There is no such thing as a Licence Review Section, however our external licence lva version of this page is
 * a read only page with identical config to the review service, so it makes sense to re-use this code
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceConditionsUndertakingsReviewService extends AbstractReviewService
{
    protected $helper;

    /**
     * Create service instance
     *
     * @param AbstractReviewServiceServices $abstractReviewServiceServices
     * @param ConditionsUndertakingsReviewService $helper
     *
     * @return LicenceConditionsUndertakingsReviewService
     */
    public function __construct(
        AbstractReviewServiceServices $abstractReviewServiceServices,
        ConditionsUndertakingsReviewService $helper
    ) {
        parent::__construct($abstractReviewServiceServices);
        $this->helper = $helper;
    }

    /**
     * Format the readonly config from the given data
     *
     * @param array $data
     * @return array
     */
    public function getConfigFromData(array $data = [])
    {
        [$licConds, $licUnds, $ocConds, $ocUnds] = $this->helper
            ->splitUpConditionsAndUndertakings($data, false);

        $subSections = array_merge(
            [],
            $this->processLicenceSections($licConds, $licUnds),
            $this->processOcSections($ocConds, $ocUnds)
        );

        return ['subSections' => $subSections];
    }

    private function processLicenceSections($licConds, $licUnds)
    {
        $subSections = [];

        if (!empty($licConds['list'])) {
            $subSections[] = $this->helper
                ->formatLicenceSubSection($licConds['list'], 'application', 'conditions', 'added');
        }

        if (!empty($licUnds['list'])) {
            $subSections[] = $this->helper
                ->formatLicenceSubSection($licUnds['list'], 'application', 'undertakings', 'added');
        }

        return $subSections;
    }

    private function processOcSections($ocConds, $ocUnds)
    {
        $subSections = [];

        if (!empty($ocConds['list'])) {
            $subSections[] = $this->helper->formatOcSubSection($ocConds['list'], 'application', 'conditions', 'added');
        }

        if (!empty($ocUnds['list'])) {
            $subSections[] = $this->helper->formatOcSubSection($ocUnds['list'], 'application', 'undertakings', 'added');
        }

        return $subSections;
    }
}
