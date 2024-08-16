<?php

/**
 * Variation People Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Entity\Organisation\Organisation;

/**
 * Variation People Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationPeopleReviewService extends AbstractReviewService
{
    /**
     * Create service instance
     *
     * @param AbstractReviewServiceServices $abstractReviewServiceServices
     * @param PeopleReviewService $peopleReviewService
     *
     * @return VariationPeopleReviewService
     */
    public function __construct(
        AbstractReviewServiceServices $abstractReviewServiceServices,
        private readonly PeopleReviewService $peopleReviewService
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
        if ($this->shouldShowSimpleUpgradeText($data)) {
            return ['freetext' => $this->translate('variation-review-people-change')];
        }

        $peopleList = $this->splitPeopleUp($data);

        $showPosition = $this->peopleReviewService->shouldShowPosition($data);

        $sections = [];

        foreach ($peopleList as $action => $people) {
            if (!empty($people)) {
                $sections[] = $this->formatSection($people, $action, $showPosition);
            }
        }

        return ['subSections' => $sections];
    }

    /**
     * If we are a Sole trader or partnership, we can just return a simple message
     *
     * @param array $data
     * @return boolean
     */
    private function shouldShowSimpleUpgradeText($data)
    {
        return in_array(
            $data['licence']['organisation']['type']['id'],
            [
                Organisation::ORG_TYPE_SOLE_TRADER,
                Organisation::ORG_TYPE_PARTNERSHIP
            ]
        );
    }

    /**
     * Split people into A, U and D lists
     *
     * @param array $data
     * @return array
     */
    private function splitPeopleUp($data)
    {
        $peopleList = ['A' => [], 'U' => [], 'D' => []];
        foreach ($data['applicationOrganisationPersons'] as $person) {
            $peopleList[$person['action']][] = $person;
        }

        return $peopleList;
    }

    private function formatSection($people, $action, $showPosition)
    {
        $mainItems = [];
        foreach ($people as $person) {
            $mainItems[] = $this->peopleReviewService->getConfigFromData($person, $showPosition);
        }

        return [
            'title' => 'variation-review-people-' . $action . '-title',
            'mainItems' => $mainItems
        ];
    }
}
