<?php

namespace Common\Service\Review;

use Common\Service\Table\Formatter\Address;

/**
 * Licence Conditions Undertakings Review Service
 *
 * @NOTE There is not such thing as a Licence Review Section, however our external licence lva version of this page is
 * a read only page with identical config to the review service, so it makes sense to re-use this code
 */
class LicenceConditionsUndertakingsReviewService extends AbstractReviewService
{
    public function __construct(
        AbstractReviewServiceServices $abstractReviewServiceServices,
        protected ConditionsUndertakingsReviewService $helper,
        Address $addressFormatter
    ) {
        parent::__construct($abstractReviewServiceServices, $addressFormatter);
    }

    /**
     * Format the readonly config from the given data
     *
     * @return array
     */
    #[\Override]
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

    /**
     * @psalm-return list{0?: mixed, 1?: mixed}
     */
    private function processLicenceSections($licConds, $licUnds): array
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

    /**
     * @psalm-return list{0?: mixed, 1?: mixed}
     */
    private function processOcSections($ocConds, $ocUnds): array
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
