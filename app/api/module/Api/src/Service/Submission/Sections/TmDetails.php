<?php

namespace Dvsa\Olcs\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;

/**
 * Class TmDetails
 * @package Dvsa\Olcs\Api\Service\Submission\Section\TmDetails
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
final class TmDetails extends AbstractSection
{
    /**
     * Generate TmDetails Submission Section
     *
     * @param CasesEntity $case Case relating to the submission
     *
     * @return array Data array containing information for the submission section
     */
    public function generateSection(CasesEntity $case)
    {
        $data = [];
        $data['id'] = '';
        $data['title'] = '';
        $data['forename'] = '';
        $data['familyName'] = '';
        $data['emailAddress'] = '';
        $data['dob'] = '';
        $data['placeOfBirth'] = '';
        $data['tmType'] = '';
        $data['homeAddress'] = [];
        $data['workAddress'] = [];

        if (!empty($case->getTransportManager())) {
            $tmData = $case->getTransportManager();
            $person = $this->extractPerson($tmData->getHomeCd());

            $data['id'] = $tmData->getId();
            $data['title'] = $person['title'];
            $data['forename'] = $person['forename'];
            $data['familyName'] = $person['familyName'];
            $data['dob'] = $person['birthDate'];
            $data['placeOfBirth'] = $person['birthPlace'];
            $data['tmType'] = !empty($tmData->getTmType()) ? $tmData->getTmType()->getDescription() : '';
            $homeCd = $tmData->getHomeCd();
            $workCd = $tmData->getWorkCd();
            if (!empty($homeCd)) {
                $data['emailAddress'] = $homeCd->getEmailAddress();
                $data['homeAddress'] = !empty($homeCd->getAddress()) ?
                    $homeCd->getAddress()->toArray() : [];
            }
            if (!empty($workCd)) {
                $data['workAddress'] = !empty($workCd->getAddress()) ?
                    $workCd->getAddress()->toArray() : [];
            }
        }

        return ['data' => ['overview' => $data]];
    }
}
