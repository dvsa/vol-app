<?php

namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva\AbstractGoodsVehiclesController;
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
            /**
             * The Goods vehicle form is configured to submit certain
             * variables present in the query string so that the export
             * represents the user's current filtered view of things
             *
             * However, one caveat is that we always want to get ALL
             * results for a given filter, so we have to make sure we
             * reset page and limit
             */
            $query = array_merge(
                $request->getPost('query'),
                [
                    'page' => 1,
                    'limit' => 0,
                ]
            );
            $request->getPost()->set('query', $query);

            return $this->getServiceLocator()
                ->get('Helper\Response')
                ->tableToCsv(
                    $this->getResponse(),
                    $this->getTable($this->getHeaderData(false), $this->getFilters()),
                    'vehicles'
                );
        }

        return null;
    }
}
