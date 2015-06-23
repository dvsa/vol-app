<?php

/**
 * Goods Vehicles Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\AbstractFormService;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Goods Vehicles Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 *
 * @todo remove me
 */
class GoodsVehiclesVehicle extends AbstractFormService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function alterForm($form, $params)
    {
        // We never want to see the vehicle history table on add
        if ($params['mode'] === 'add') {
            $form->remove('vehicle-history-table');
            return;
        }

        $this->getFormHelper()->populateFormTable(
            $form->get('vehicle-history-table'),
            $this->getHistoryTable($params['id'])
        );
    }

    protected function getHistoryTable($id)
    {
        return $this->getServiceLocator()->get('Table')
            ->prepareTable('lva-vehicles-history', $this->getHistoryTableData($id));
    }

    protected function getHistoryTableData($id)
    {
        $vrm = $this->getServiceLocator()->get('Entity\LicenceVehicle')->getVrm($id);

        return $this->getServiceLocator()->get('Entity\VehicleHistoryView')->getDataForVrm($vrm);
    }
}
