<?php

/**
 * External Licence Vehicles Goods Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Olcs\Controller\Lva\AbstractGenericVehiclesGoodsController;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;

/**
 * External Licence Vehicles Goods Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclesController extends AbstractGenericVehiclesGoodsController
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'external';

    protected function alterTable($table)
    {
        $table->addAction(
            'export',
            [
                'requireRows' => true,
                'class' => 'secondary js-disable-crud'
            ]
        );
        return parent::alterTable($table);
    }

    protected function checkForAlternativeCrudAction($action)
    {
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
                $this->getRequest()->getPost('query'),
                [
                    'page' => 1,
                    'limit' => 'all'
                ]
            );
            $this->getRequest()->getPost()->set('query', $query);

            return $this->getServiceLocator()
                ->get('Helper\Response')
                ->tableToCsv(
                    $this->getResponse(),
                    $this->getTable(),
                    'vehicles'
                );
        }

        return parent::checkForAlternativeCrudAction($action);
    }
}
