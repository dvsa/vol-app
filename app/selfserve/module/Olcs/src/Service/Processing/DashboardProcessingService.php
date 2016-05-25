<?php

/**
 * Dashboard presentation logic
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace Olcs\Service\Processing;

use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Common\RefData;

/**
 * Dashboard presentation logic
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DashboardProcessingService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * Get the three tables for display on the dashboard
     *
     * @param array $data organisation dashboard data
     * @return array ['licences', 'applications', 'variations'] all containing a table
     */
    public function getTables($data)
    {
        $licences = $data['licences'];
        $applications = $data['applications'];
        $variations = $data['variations'];

        foreach ($licences as &$licence) {
            $licence['type'] = $licence['licenceType']['id'];
            $licence['trafficArea'] = isset($licence['trafficArea']['name']) ? $licence['trafficArea']['name'] : '';
        }

        foreach ($applications as &$application) {
            $application['licNo'] = $application['licence']['licNo'];
            $application['type'] = $application['licenceType']['id'];
        }

        foreach ($variations as &$variation) {
            $variation['licNo'] = $variation['licence']['licNo'];
            $variation['type'] = $variation['licenceType']['id'];
        }

        krsort($licences);
        krsort($variations);
        krsort($applications);

        $tableService = $this->getServiceLocator()->get('Table');

        return [
            'licences' => $tableService->buildTable('dashboard-licences', $licences),
            'variations' => $tableService->buildTable('dashboard-variations', $variations),
            'applications' => $tableService->buildTable('dashboard-applications', $applications),
        ];
    }
}
