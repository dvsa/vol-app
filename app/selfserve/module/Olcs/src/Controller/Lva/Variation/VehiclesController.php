<?php

/**
 * External Variation Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Olcs\Controller\Lva\AbstractGenericVehiclesGoodsController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Common\Controller\Lva\Traits;
use Zend\Form\Form;

/**
 * External Variation Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclesController extends AbstractGenericVehiclesGoodsController
{
    use VariationControllerTrait,
        Traits\ApplicationGenericVehiclesControllerTrait,
        Traits\PsvGoodsLicenceVariationControllerTrait,
        Traits\ApplicationGoodsVehiclesControllerTrait {
            Traits\ApplicationGoodsVehiclesControllerTrait::alterTable as traitAlterTable;
            Traits\PsvGoodsLicenceVariationControllerTrait::alterFormForLva as traitAlterFormForLva;
        }

    protected $lva = 'variation';
    protected $location = 'external';

    /**
     * This method handles calling both the trait's alterTable method, and it's parents
     */
    protected function alterTable($table)
    {
        return parent::alterTable($this->traitAlterTable($table));
    }

    /**
     * This method handles calling both the trait's alterFormForLva method, and it's parents
     *
     * @param Zend\Form\Form $form
     * @return $form
     */
    protected function alterFormForLva(Form $form)
    {
        return parent::alterFormForLva($this->traitAlterFormForLva($form));
    }
}
