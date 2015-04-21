<?php

/**
 * External Licence Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva\AbstractVehiclesPsvController;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Common\Controller\Lva\Traits\PsvLicenceControllerTrait;
use Common\Service\Table\TableBuilder;

/**
 * External Licence Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VehiclesPsvController extends AbstractVehiclesPsvController
{
    use LicenceControllerTrait,
        PsvLicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'external';

    /**
     * Pre save vehicle
     *
     * @param array $data
     * @param string $mode
     * @return mixed
     */
    protected function preSaveVehicle($data, $mode)
    {
        if ($mode === 'add') {
            $data['licence-vehicle']['specifiedDate'] = date('Y-m-d');
        }

        return $data;
    }

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
            $type = $this->getType();

            $table = $this->getTable($type);
            $table->setContentType(TableBuilder::CONTENT_TYPE_CSV);
            $table->removeColumn('action');

            $body = $table->render();

            $response = $this->getResponse();
            $response->getHeaders()
                ->addHeaderLine('Content-Type', 'text/csv')
                ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $type . '-vehicles.csv"')
                ->addHeaderLine('Content-Length', strlen($body));

            $response->setContent($body);

            return $response;
        }

        return parent::checkForAlternativeCrudAction($action);
    }
}
