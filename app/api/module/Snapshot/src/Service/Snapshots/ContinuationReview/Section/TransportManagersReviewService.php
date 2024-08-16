<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section;

use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Transport Managers Continuation Review Service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagersReviewService extends AbstractReviewService
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
        $tmLicences = $continuationDetail->getLicence()->getTmLicences();

        $header = [
            [
                ['value' => 'continuations.tm-section.table.name', 'header' => true],
                ['value' => 'continuations.tm-section.table.dob', 'header' => true],
            ]
        ];

        $config = [];
        /** @var TransportManagerLicence $tmLicence */
        foreach ($tmLicences as $tmLicence) {
            /** @var Person $person */
            $person = $tmLicence->getTransportManager()->getHomeCd()->getPerson();
            $birthDate = $person->getBirthDate(true);
            $row = [
                [
                    'value' => trim(
                        implode(
                            ' ',
                            [
                                $person->getTitle() !== null ? $person->getTitle()->getDescription() : '',
                                $person->getForename(),
                                $person->getFamilyName()
                            ]
                        )
                    )
                ],
                ['value' => $birthDate !== null ? $birthDate->format('d/m/Y') : '']
            ];
            $config[] = $row;
        }

        usort(
            $config,
            fn($a, $b) => strcmp($a[0]['value'], $b[0]['value'])
        );

        return (count($config) === 0)
            ? ['emptyTableMessage' => $this->translate('There are no transport managers recorded on your licence')]
            : array_merge($header, $config);
    }
}
