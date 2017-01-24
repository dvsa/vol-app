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

    /**
     * Print vehicles action
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
}
