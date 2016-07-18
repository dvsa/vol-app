<?php

namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva\AbstractGoodsVehiclesController;
use Dvsa\Olcs\Transfer\Query;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;

/**
 * External Licence Vehicles Goods Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclesController extends AbstractGoodsVehiclesController
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'external';

    protected static $exportDataMap = [
        'licence' => Query\Licence\GoodsVehiclesExport::class,
        'variation' => Query\Variation\GoodsVehiclesExport::class,
        'application' => Query\Application\GoodsVehiclesExport::class,
    ];

    /**
     * Specific functionality for CRUD actions
     *
     * @param string $action Crud Action
     *
     * @return \Zend\Http\Response|null
     */
    protected function checkForAlternativeCrudAction($action)
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if ($action === 'export') {
            // reset page and limit
            $query = $request->getPost('query');
            unset(
                $query['page'],
                $query['limit']
            );
            $request->getPost()->set('query', $query);

            return $this->getServiceLocator()
                ->get('Helper\Response')
                ->tableToCsv(
                    $this->getResponse(),
                    $this->getTable($this->getExportData(), $this->getFilters()),
                    'vehicles'
                );
        }

        return null;
    }

    /**
     * Request vehicle data for export
     *
     * @return array
     */
    private function getExportData()
    {
        $dtoData = $this->getFilters();
        $dtoData['id'] = $this->getIdentifier();

        $dtoClass = self::$exportDataMap[$this->lva];

        $response = $this->handleQuery($dtoClass::create($dtoData));

        return [
            'licenceVehicles' => $response->getResult(),
        ];
    }
}
