<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section;

use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * People Continuation Review Service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PeopleReviewService extends AbstractReviewService
{
    /**
     * Format the readonly config from the given data
     *
     * @param ContinuationDetail $continuationDetail continuation detail
     *
     * @return array
     */
    public function getConfigFromData(ContinuationDetail $continuationDetail)
    {
        $organisation = $continuationDetail->getLicence()->getOrganisation();
        $organisationPersons = $organisation->getOrganisationPersons();

        $header[] = [
            ['value' => 'continuations.people-section.table.name', 'header' => true],
            ['value' => 'continuations.people-section.table.date-of-birth', 'header' => true]
        ];
        $config = [];
        /** @var OrganisationPerson $op */
        foreach ($organisationPersons as $op) {
            $person = $op->getPerson();
            $title = $person->getTitle();
            $birthDate = $person->getBirthDate(true);
            $config[] = [
                [
                    'value' => implode(
                        ' ',
                        [
                            $title !== null ? $title->getDescription() : '',
                            $person->getForename(),
                            $person->getFamilyName(),
                        ]
                    )
                ],
                ['value' => $birthDate !== null ? $birthDate->format('d/m/Y') : '']
            ];
        }
        usort(
            $config,
            fn($a, $b) => strcmp($a[0]['value'], $b[0]['value'])
        );

        return (count($config) === 0)
            ? [
                'emptyTableMessage' =>
                    $this->translate(
                        'continuations.people-empty-table-message.' . $organisation->getType()->getId()
                    )
            ]
            : array_merge($header, $config);
    }
}
