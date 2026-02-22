<?php

/**
 * Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\Repository\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Domain\Repository\Document;
use Dvsa\Olcs\Api\Domain\Repository\TrafficArea;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficAreaEnforcementArea;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Query\Application\OperatingCentres as OperatingCentresQuery;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Psr\Container\ContainerInterface;
use Dvsa\Olcs\Api\Domain\Service\VariationOperatingCentreHelper;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatingCentres extends AbstractQueryHandler
{
    protected $repoServiceName = 'Application';

    protected $extraRepos = ['ApplicationOperatingCentre', 'TrafficArea', 'Document'];

    /**
     * @var VariationOperatingCentreHelper
     */
    private $variationHelper;

    /**
     * Handle the query
     *
     * @param QueryInterface|OperatingCentresQuery $query query
     *
     * @return Result
     * @throws RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        /* @var ApplicationEntity $application */
        $application = $this->getRepo()->fetchUsingId($query);
        $documents = null;
        if (!$this->isReadOnlyInternalUser()) {
            /** @var Document $documentRepository */
            $documentRepository = $this->getRepo('Document');
            $documents = $this->resultList(
                $documentRepository->fetchUnlinkedOcDocumentsForEntity($application),
                ['application']
            );
        }

        return $this->result(
            $application,
            [
                'licence' => [
                    'trafficArea',
                    'enforcementArea'
                ],
            ],
            [
                'requiresVariation' => false,
                'operatingCentres' => $this->getAocData($application, $query),
                'totCommunityLicences' => $this->getTotCommunityLicences($application),
                'isPsv' => $application->isPsv(),
                'canHaveCommunityLicences' => $application->canHaveCommunityLicences(),
                'canHaveSchedule41' => $this->canHaveSchedule41($application),
                'possibleEnforcementAreas' => $this->getPossibleEnforcementAreas($application),
                'possibleTrafficAreas' => $this->getPossibleTrafficAreas($application),
                // Vars used for add form
                'canAddAnother' => $this->canAddAnother($application),
                'documents' => $documents
            ]
        );
    }

    /**
     * Can add another?
     *
     * @param ApplicationEntity $application application
     *
     * @return bool
     */
    protected function canAddAnother(ApplicationEntity $application)
    {
        return !($application->isNew()
            && $application->getNiFlag() === 'N'
            && $application->getLicence()->getTrafficArea() === null
        );
    }

    /**
     * Can haev schedule 41?
     *
     * @param ApplicationEntity $application application
     *
     * @return bool
     */
    protected function canHaveSchedule41(ApplicationEntity $application)
    {
        if ($this->isGranted(Permission::SELFSERVE_USER)) {
            return false;
        }

        if ($application->isPsv()) {
            return false;
        }

        if ($application->getStatus()->getId() !== ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION) {
            return false;
        }

        if (count($application->getActiveS4s()) > 0) {
            return false;
        }

        return true;
    }

    /**
     * get possible traffic areas
     *
     * @param ApplicationEntity $application application
     *
     * @return array
     * @throws RuntimeException
     */
    protected function getPossibleTrafficAreas(ApplicationEntity $application)
    {
        /** @var TrafficArea $repository */
        $repository = $this->getRepo('TrafficArea');
        return $repository->getValueOptions(
            $application->getLicence()->getOrganisation()->getAllowedOperatorLocation()
        );
    }

    /**
     * get possible enforcement areas
     *
     * @param ApplicationEntity $application application
     *
     * @return array
     */
    protected function getPossibleEnforcementAreas(ApplicationEntity $application)
    {
        if ($application->getLicence()->getTrafficArea() === null) {
            return [];
        }

        /** @var TrafficAreaEnforcementArea[] $tas */
        $tas = $application->getLicence()->getTrafficArea()->getTrafficAreaEnforcementAreas();

        $options = [];
        foreach ($tas as $ta) {
            $options[$ta->getEnforcementArea()->getId()] = $ta->getEnforcementArea()->getName();
        }

        return $options;
    }

    /**
     * Get tot community licences
     *
     * @param ApplicationEntity $application application
     *
     * @return int
     */
    protected function getTotCommunityLicences(ApplicationEntity $application)
    {
        if ($application->isVariation()) {
            return $application->getLicence()->getTotCommunityLicences();
        }

        return $application->getTotCommunityLicences();
    }

    /**
     * get AOC data
     *
     * @param ApplicationEntity $application application
     * @param QueryInterface    $query       query
     *
     * @return array
     * @throws RuntimeException
     */
    protected function getAocData(ApplicationEntity $application, $query)
    {
        if (!$application->isVariation()) {
            /** @var ApplicationOperatingCentre $repository */
            $repository = $this->getRepo('ApplicationOperatingCentre');
            return $repository
                ->fetchByApplicationIdForOperatingCentres($application->getId(), $query);
        }

        return $this->variationHelper->getListDataForApplication($application, $query);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return OperatingCentres
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->variationHelper = $container->get('VariationOperatingCentreHelper');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
