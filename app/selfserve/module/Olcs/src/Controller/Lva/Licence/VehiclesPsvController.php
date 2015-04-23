<?php

/**
 * External Licence Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Olcs\Controller\Lva\AbstractGenericVehiclesPsvController;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Common\Controller\Lva\Traits\PsvLicenceControllerTrait;

/**
 * External Licence Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VehiclesPsvController extends AbstractGenericVehiclesPsvController
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

            return $this->getServiceLocator()
                ->get('Helper\Response')
                ->tableToCsv(
                    $this->getResponse(),
                    $this->getTable($type),
                    $type . '-vehicles'
                );
        }

        return parent::checkForAlternativeCrudAction($action);
    }
}
