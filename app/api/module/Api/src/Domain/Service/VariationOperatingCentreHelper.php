<?php

/**
 * Variation Operating Centre Helper
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Service;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Dvsa\Olcs\Api\Domain\Repository\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Domain\Repository\LicenceOperatingCentre;
use Psr\Container\ContainerInterface;

/**
 * Variation Operating Centre Helper
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationOperatingCentreHelper implements FactoryInterface
{
    /**
     * @var ApplicationOperatingCentre
     */
    protected $aocRepo;

    /**
     * @var LicenceOperatingCentre
     */
    protected $locRepo;

    public function getListDataForApplication(Application $application, $query = null)
    {
        $aocs = $this->aocRepo->fetchByApplicationIdForOperatingCentres($application->getId());
        $locs = $this->locRepo->fetchByLicenceIdForOperatingCentres($application->getLicence()->getId());

        $aocData = [];
        $locData = [];

        $sort = null;
        $order = 'ASC';
        if (!is_null($query)) {
            $sort = $query->getSort();
            $order = $query->getOrder();
        }

        foreach ($aocs as $aoc) {
            $aoc['source'] = 'A';
            $aoc['id'] = 'A' . $aoc['id'];
            $aoc['sort'] = $this->addCustomSort($aoc, $sort);

            $aocData[$aoc['operatingCentre']['id']] = $aoc;
        }

        foreach ($locs as $loc) {
            $loc['source'] = 'L';
            $loc['id'] = 'L' . $loc['id'];
            $loc['sort'] = $this->addCustomSort($loc, $sort);

            $locData[$loc['operatingCentre']['id']] = $loc;
        }
        return $this->updateAndFilterTableData($locData, $aocData, $order);
    }

    private function updateAndFilterTableData($licenceData, $applicationData, $order)
    {
        $data = [];

        foreach ($licenceData as $ocId => $row) {
            if (!isset($applicationData[$ocId])) {
                $row['action'] = 'E';
                $data[] = $row;
            } elseif ($applicationData[$ocId]['action'] === 'U') {
                $row['action'] = 'C';
                $data[] = $row;
            }
        }

        $mergedData = array_merge($data, $applicationData);
        if ($order === 'ASC') {
            usort(
                $mergedData,
                fn($val1, $val2) => strcmp((string) $val1['sort'], (string) $val2['sort'])
            );
        } else {
            usort(
                $mergedData,
                fn($val1, $val2) => strcmp((string) $val2['sort'], (string) $val1['sort'])
            );
        }

        return $mergedData;
    }

    /**
     * Custom sorting for variation operating centres
     *
     * @param array $oc
     * @param $sort
     * @return array
     */
    private function addCustomSort($oc, $sort)
    {
        $value = match ($sort) {
            'noOfVehiclesRequired', 'noOfTrailersRequired' => (int) $oc[$sort],
            'createdOn' => $oc[$sort],
            'adr' => strtolower(
                $oc['operatingCentre']['address']['addressLine1'] .
                $oc['operatingCentre']['address']['addressLine2'] .
                $oc['operatingCentre']['address']['addressLine3'] .
                $oc['operatingCentre']['address']['addressLine4'] .
                $oc['operatingCentre']['address']['town']
            ),
            'lastModifiedOn' => is_null($oc['lastModifiedOn']) ? $oc['createdOn'] : $oc['lastModifiedOn'],
            default => $oc['operatingCentre']['id'],
        };
        return $value;
    }
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $repoSm = $container->get('RepositoryServiceManager');
        $this->aocRepo = $repoSm->get('ApplicationOperatingCentre');
        $this->locRepo = $repoSm->get('LicenceOperatingCentre');
        return $this;
    }
}
