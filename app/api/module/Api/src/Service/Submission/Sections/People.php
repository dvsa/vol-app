<?php

namespace Dvsa\Olcs\Api\Service\Submission\Sections;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Person\Person as PersonEntity;
use Dvsa\Olcs\Api\Entity\System\RefData as RefdataEntity;

/**
 * Class People
 * @package Dvsa\Olcs\Api\Service\Submission\Sections
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
final class People extends AbstractSection
{
    /**
     * Generate People section of submission
     *
     * @param CasesEntity $case Case relating to the submission
     *
     * @return array Data array containing information for the submission section
     */
    public function generateSection(CasesEntity $case)
    {
        // get all other persons associated with the licence
        $licence = $case->getLicence();
        $organisation = !empty($licence) ? $licence->getOrganisation() : null;
        $organisationPersons = !empty($organisation) ? $organisation->getOrganisationPersons() : new ArrayCollection();

        $persons = new ArrayCollection($organisationPersons->toArray());

        // If case type is application, add application persons to list
        if ($case->getCaseType()->getId() === CasesEntity::APP_CASE_TYPE) {
            // get Application Persons
            /** @var Application $caseApplication */
            $caseApplication = $case->getApplication();

            if ($caseApplication instanceof Application) {
                $applicationPersons = $caseApplication->getApplicationOrganisationPersons();
                $persons = new ArrayCollection(
                    array_merge($applicationPersons->toArray(), $organisationPersons->toArray())
                );
            }
        }

        $data = [];
        for ($i = 0; $i < count($persons); $i++) {
            /** @var PersonEntity $personEntity */
            $personEntity = $persons->current()->getPerson();

            $personId = $personEntity->getId();
            $data[$personId]['id'] = $personEntity->getId();
            $data[$personId]['title'] = '';
            if ($personEntity->getTitle() instanceof RefdataEntity) {
                $data[$personId]['title'] = $personEntity->getTitle()->getDescription();
            }
            $data[$personId]['familyName'] = $personEntity->getFamilyName();
            $data[$personId]['forename'] = $personEntity->getForename();
            $data[$personId]['disqualificationStatus'] = $personEntity->getDisqualificationStatus();
            $data[$personId]['birthDate'] = $this->formatDate($personEntity->getBirthDate());

            $persons->next();
        }

        return [
            'data' => [
                'tables' => [
                    'people' => $data
                ]
            ]
        ];
    }
}
