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
        $table->addAction('export', ['requireRows' => true]);
        return $table;
    }

    protected function checkForAlternativeCrudAction($action)
    {
    }
}
