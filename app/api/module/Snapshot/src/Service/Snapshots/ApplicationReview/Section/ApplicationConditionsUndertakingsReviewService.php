<?php

/**
 * Application Conditions Undertakings Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

/**
 * Application Conditions Undertakings Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationConditionsUndertakingsReviewService extends AbstractReviewService
{
    /** @var ConditionsUndertakingsReviewService */
    protected $helper;

    /**
     * Create service instance
     *
     * @param AbstractReviewServiceServices $abstractReviewServiceServices
     * @param ConditionsUndertakingsReviewService $helper
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
        [$licConds, $licUnds, $ocConds, $ocUnds] = $this->helper->splitUpConditionsAndUndertakings($data);

        $subSections = array_merge(
            [],
            $this->processLicenceSections($licConds, $licUnds),
            $this->processOcSections($ocConds, $ocUnds)
        );

        if (empty($subSections)) {
            return ['freetext' => $this->translate('review-none-added')];
        }

        return ['subSections' => $subSections];
    }

    private function processLicenceSections($licConds, $licUnds)
    {
        $subSections = [];

        if (!empty($licConds['A'])) {
            $subSections[] = $this->helper
                ->formatLicenceSubSection($licConds['A'], 'application', 'conditions', 'added');
        }

        if (!empty($licUnds['A'])) {
            $subSections[] = $this->helper
                ->formatLicenceSubSection($licUnds['A'], 'application', 'undertakings', 'added');
        }

        return $subSections;
    }

    private function processOcSections($ocConds, $ocUnds)
    {
        $subSections = [];

        if (!empty($ocConds['A'])) {
            $subSections[] = $this->helper->formatOcSubSection($ocConds['A'], 'application', 'conditions', 'added');
        }

        if (!empty($ocUnds['A'])) {
            $subSections[] = $this->helper->formatOcSubSection($ocUnds['A'], 'application', 'undertakings', 'added');
        }

        return $subSections;
    }
}
