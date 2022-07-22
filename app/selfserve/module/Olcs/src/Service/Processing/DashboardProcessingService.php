<?php

namespace Olcs\Service\Processing;

use Common\Service\Table\TableFactory;

/**
 * Dashboard presentation logic
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DashboardProcessingService
{
    /** @var TableFactory */
    protected $tableService;

    /**
     * Create service instance
     *
     * @param TableFactory $tableService
     *
     * @return DashboardProcessingService
     */
    public function __construct(
        TableFactory $tableService
    ) {
        $this->tableService = $tableService;
    }

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

        return [
            'licences' => $this->tableService->buildTable('dashboard-licences', $licences),
            'variations' => $this->tableService->buildTable('dashboard-variations', $variations),
            'applications' => $this->tableService->buildTable('dashboard-applications', $applications),
        ];
    }
}
