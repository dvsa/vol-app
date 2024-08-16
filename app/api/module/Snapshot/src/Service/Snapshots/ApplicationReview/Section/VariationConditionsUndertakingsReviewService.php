<?php

/**
 * Variation Conditions Undertakings Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

/**
 * Variation Conditions Undertakings Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationConditionsUndertakingsReviewService extends AbstractReviewService
{
    /** @var ConditionsUndertakingsReviewService */
    protected $helper;

    /**
     * Create service instance
     *
     * @param AbstractReviewServiceServices $abstractReviewServiceServices
     * @param ConditionsUndertakingsReviewService $helper
     *
     * @return VariationConditionsUndertakingsReviewService
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
            $this->processLicenceConditionsSections($licConds),
            $this->processLicenceUndertakingsSections($licUnds),
            $this->processOcConditionsSections($ocConds),
            $this->processOcUndertakingsSections($ocUnds)
        );

        if (empty($subSections)) {
            return ['freetext' => $this->translate('review-none-added')];
        }

        return ['subSections' => $subSections];
    }

    private function processLicenceConditionsSections($licConds)
    {
        $subSections = [];

        if (!empty($licConds['A'])) {
            $subSections[] = $this->helper->formatLicenceSubSection($licConds['A'], 'variation', 'conditions', 'added');
        }

        if (!empty($licConds['U'])) {
            $subSections[] = $this->helper
                ->formatLicenceSubSection($licConds['U'], 'variation', 'conditions', 'updated');
        }

        if (!empty($licConds['D'])) {
            $subSections[] = $this->helper
                ->formatLicenceSubSection($licConds['D'], 'variation', 'conditions', 'deleted');
        }

        return $subSections;
    }

    private function processLicenceUndertakingsSections($licUnds)
    {
        $subSections = [];

        if (!empty($licUnds['A'])) {
            $subSections[] = $this->helper
                ->formatLicenceSubSection($licUnds['A'], 'variation', 'undertakings', 'added');
        }

        if (!empty($licUnds['U'])) {
            $subSections[] = $this->helper
                ->formatLicenceSubSection($licUnds['U'], 'variation', 'undertakings', 'updated');
        }

        if (!empty($licUnds['D'])) {
            $subSections[] = $this->helper
                ->formatLicenceSubSection($licUnds['D'], 'variation', 'undertakings', 'deleted');
        }

        return $subSections;
    }

    private function processOcConditionsSections($ocConds)
    {
        $subSections = [];

        if (!empty($ocConds['A'])) {
            $subSections[] = $this->helper->formatOcSubSection($ocConds['A'], 'variation', 'conditions', 'added');
        }

        if (!empty($ocConds['U'])) {
            $subSections[] = $this->helper->formatOcSubSection($ocConds['U'], 'variation', 'conditions', 'updated');
        }

        if (!empty($ocConds['D'])) {
            $subSections[] = $this->helper->formatOcSubSection($ocConds['D'], 'variation', 'conditions', 'deleted');
        }

        return $subSections;
    }

    private function processOcUndertakingsSections($ocUnds)
    {
        $subSections = [];

        if (!empty($ocUnds['A'])) {
            $subSections[] = $this->helper->formatOcSubSection($ocUnds['A'], 'variation', 'undertakings', 'added');
        }

        if (!empty($ocUnds['U'])) {
            $subSections[] = $this->helper->formatOcSubSection($ocUnds['U'], 'variation', 'undertakings', 'updated');
        }

        if (!empty($ocUnds['D'])) {
            $subSections[] = $this->helper->formatOcSubSection($ocUnds['D'], 'variation', 'undertakings', 'deleted');
        }

        return $subSections;
    }
}
