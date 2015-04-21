<?php

/**
 * External Licence Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Olcs\Controller\Lva\AbstractGenericVehiclesGoodsController;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Common\Service\Table\TableBuilder;

/**
 * External Licence Vehicles Controller
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
            $query = array_merge(
                $this->getRequest()->getPost('query'),
                [
                    'page' => 1,
                    'limit' => 'all'
                ]
            );
            $this->getRequest()->getPost()->set('query', $query);

            $table = $this->getTable();
            $table->setContentType(TableBuilder::CONTENT_TYPE_CSV);
            $table->removeColumn('action');

            $body = $table->render();

            $response = $this->getResponse();
            $response->getHeaders()
                ->addHeaderLine('Content-Type', 'text/csv')
                ->addHeaderLine('Content-Disposition', 'attachment; filename="vehicles.csv"')
                ->addHeaderLine('Content-Length', strlen($body));

            $response->setContent($body);

            return $response;
        }

        return parent::checkForAlternativeCrudAction($action);
    }
}
