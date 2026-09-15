<?php

namespace Common\Service\Data;

/**
 * Class OcContextListDataService
 *
 * @package Olcs\Service\Data
 */
class OcContextListDataService implements ListDataInterface
{
    public function __construct(private LicenceOperatingCentre $licenceOperatingCentreDataService, private ApplicationOperatingCentre $applicationOperatingCentreDataService)
    {
    }

    /**
     * Calls either the LicenceOperatingCentre List data service or  the ApplicationOperatingCentre list data service
     * to return a list of OCs associated with either the licence or application
     *
     * @param array|string $context   Context
     * @param bool         $useGroups Use groups
     *
     * @return array
     */
    #[\Override]
    public function fetchListOptions($context, $useGroups = false)
    {
        if ($context == 'licence') {
            return $this->licenceOperatingCentreDataService->fetchListOptions($context, $useGroups);
        }
        if ($context == 'application') {
            return $this->applicationOperatingCentreDataService->fetchListOptions($context, $useGroups);
        }

        return [];
    }
}
