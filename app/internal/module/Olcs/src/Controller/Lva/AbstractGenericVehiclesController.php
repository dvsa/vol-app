<?php

/**
 * Abstract Generic Vehicles Goods Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractGoodsVehiclesController;
use Dvsa\Olcs\Transfer\Command\Application\CreateVehicleListDocument as ApplicationCreateDocument;
use Dvsa\Olcs\Transfer\Command\Licence\CreateVehicleListDocument as LicenceCreateDocument;
use Dvsa\Olcs\Transfer\Query;

/**
 * Abstract Generic Vehicles Goods Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractGenericVehiclesController extends AbstractGoodsVehiclesController
{
    protected $docGenerationMap = [
        'licence' => LicenceCreateDocument::class,
        'variation' => ApplicationCreateDocument::class,
        'application' => ApplicationCreateDocument::class
    ];

    protected static $exportDataMap = [
        'licence' => Query\Licence\GoodsVehiclesExport::class,
        'variation' => Query\Variation\GoodsVehiclesExport::class,
        'application' => Query\Application\GoodsVehiclesExport::class,
    ];

    /**
     * Print vehicles action
     *
     * @return \Zend\Http\Response
     */
    public function printVehiclesAction()
    {
        $dtoClass = $this->docGenerationMap[$this->lva];
        $response = $this->handleCommand($dtoClass::create(['id' => $this->getIdentifier()]));

        $fm = $this->getServiceLocator()->get('Helper\FlashMessenger');

        if ($response->isOk()) {
            $fm->addSuccessMessage('vehicle-list-printed');
        } else {
            $fm->addErrorMessage('vehicle-list-print-failed');
        }

        return $this->redirect()->toRoute($this->getBaseRoute(), ['action' => null], [], true);
    }

    /**
     * Export vehicles action
     *
     * @return \Zend\Http\Response
     */
    public function exportAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

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
