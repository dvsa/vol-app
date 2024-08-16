<?php

namespace Dvsa\Olcs\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;

/**
 * Class ConditionsAndUndertakings
 *
 * @package Dvsa\Olcs\Api\Service\Submission\Sections
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
final class ConditionsAndUndertakings extends AbstractSection
{
    /**
     * Generate C's and U's attached to both licence and application. Sorted.
     *
     * @param CasesEntity $case Case relating to the submission
     *
     * @return array Data array containing information for the submission section
     */
    public function generateSection(CasesEntity $case)
    {
        $tables = ['undertakings' => [], 'conditions' => []];

        $licence = $case->getLicence();

        // CUs attached to the case
        $caseCus = $case->getConditionUndertakingsAddedViaCase();
        if (!empty($caseCus)) {
            /** @var ConditionUndertaking $entity */
            foreach ($caseCus as $entity) {
                $tables[$this->determineTableName($entity)][] =
                    $this->generateTableRow($entity, $entity->getCase()->getId());
            }
        }

        // CUs attached to the any other applications for this licence
        $applications = $licence->getApplications();
        if (!empty($applications)) {
            /** @var Application $application */
            foreach ($applications as $application) {
                // Only show application conditions when the application is Under Consideration
                if ($application->getStatus() != Application::APPLICATION_STATUS_UNDER_CONSIDERATION) {
                    continue;
                }
                /** @var ConditionUndertaking $entity */
                foreach ($application->getConditionUndertakings() as $entity) {
                    $tables[$this->determineTableName($entity)][] =
                        $this->generateTableRow($entity, $application->getId());
                }
            }
        }

        // CUs attached to the licence
        $licCus = $licence->getConditionUndertakingsAddedViaLicence();
        foreach ($licCus as $entity) {
            $tables[$this->determineTableName($entity)][] =
                $this->generateTableRow($entity, $licence->getLicNo());
        }

        // import fix - CUs imported as ViaApp, but ApplicationId not specified (instead specified LicenceId)
        $licCus = $licence->getConditionUndertakingsAddedViaImport();
        foreach ($licCus as $entity) {
            $tables[$this->determineTableName($entity)][] = $this->generateTableRow($entity);
        }

        usort(
            $tables['undertakings'],
            fn($a, $b) => ((int) $a['id'] > (int) $b['id']) ? +1 : -1
        );
        usort(
            $tables['conditions'],
            fn($a, $b) => ((int) $a['id'] > (int) $b['id']) ? +1 : -1
        );

        $dataToReturnArray = [
            'data' => [
                'tables' => $tables
            ]
        ];

        return $dataToReturnArray;
    }

    /**
     * Determine table name
     *
     * @param ConditionUndertaking $entity ConditionUndertaking entity being processed
     *
     * @return string
     */
    private function determineTableName(ConditionUndertaking $entity)
    {
        return (
            $entity->getConditionType()->getId() == ConditionUndertaking::TYPE_UNDERTAKING
            ? 'undertakings'
            : 'conditions'
        );
    }

    /**
     * Generate a table row for this section
     *
     * @param ConditionUndertaking $entity   Entity being processed
     * @param string               $parentId Parent Id
     *
     * @return array
     */
    private function generateTableRow(ConditionUndertaking $entity, $parentId = '')
    {
        /** @var ConditionUndertaking $entity */
        $thisEntity = [];
        $thisEntity['id'] = $entity->getId();
        $thisEntity['version'] = $entity->getVersion();
        $thisEntity['createdOn'] = $this->formatDate($entity->getCreatedOn());
        $thisEntity['parentId'] = $parentId;
        $thisEntity['addedVia'] = $entity->getAddedVia()->getDescription();
        $thisEntity['isFulfilled'] = $entity->getIsFulfilled();
        $thisEntity['isDraft'] = $entity->getIsDraft();
        $thisEntity['attachedTo'] = $entity->getAttachedTo()->getDescription();
        $thisEntity['notes'] = $entity->getNotes();

        if (empty($entity->getOperatingCentre())) {
            $thisEntity['OcAddress'] = [];
        } else {
            $thisEntity['OcAddress'] = $entity->getOperatingCentre()->getAddress()->toArray();
        }

        return $thisEntity;
    }
}
