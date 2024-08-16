<?php

/**
 * Application People Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

/**
 * Application People Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationPeopleReviewService extends AbstractReviewService
{
    /**
     * Create service instance
     *
     * @param AbstractReviewServiceServices $abstractReviewServiceServices
     * @param PeopleReviewService $peopleReviewService
     *
     * @return ApplicationPeopleReviewService
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
        $mainItems = [];

        $people = $this->consolidatePeople(
            $data['applicationOrganisationPersons'],
            $data['licence']['organisation']['organisationPersons']
        );

        $showPosition = $this->peopleReviewService->shouldShowPosition($data);

        foreach ($people as $person) {
            $mainItems[] = $this->peopleReviewService->getConfigFromData($person, $showPosition);
        }

        return [
            'subSections' => [
                [
                    'mainItems' => $mainItems
                ]
            ]
        ];
    }

    /**
     * Get the current state of all people, ignoring previous states and deleted people
     *
     * @param array $applicationPeople
     * @param array $licencePeople
     * @return array
     */
    private function consolidatePeople($applicationPeople, $licencePeople)
    {
        $people = [];
        $ignore = [];

        foreach ($applicationPeople as $person) {
            switch ($person['action']) {
                case 'A':
                    $people[] = $person;
                    break;
                // If we have updated a person on the application
                // The updated person is stored in the 'originalPerson' child
                // The newly updated person is stored in the 'person' child
                case 'U':
                    $people[] = $person;
                    $ignore[] = $person['originalPerson']['id'];
                    break;
                // If we have deleted a person on the application
                // The deleted person is stored in the 'person' child
                case 'D':
                    $ignore[] = $person['person']['id'];
                    break;
            }
        }

        foreach ($licencePeople as $person) {
            if (!in_array($person['person']['id'], $ignore)) {
                $people[] = $person;
            }
        }

        return $people;
    }
}
