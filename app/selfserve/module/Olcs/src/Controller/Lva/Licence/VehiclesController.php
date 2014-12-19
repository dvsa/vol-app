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
use Common\Controller\Lva\Traits;
use Zend\Form\Form;

/**
 * External Licence Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclesController extends AbstractGenericVehiclesGoodsController
{
    use LicenceControllerTrait,
        Traits\LicenceGenericVehiclesControllerTrait,
        Traits\LicenceGoodsVehiclesControllerTrait;

    use Traits\PsvGoodsLicenceVariationControllerTrait {
            Traits\PsvGoodsLicenceVariationControllerTrait::alterFormForLva as traitAlterFormForLva;
        }

    protected $lva = 'licence';
    protected $location = 'external';

    /**
     * This method is used to hook the trait's pre & post save methods into the parent save vehicle method
     *
     * @param array $data
     * @param string $mode
     */
    protected function saveVehicle($data, $mode)
    {
        $data = $this->preSaveVehicle($data, $mode);

        $licenceVehicleId = parent::saveVehicle($data, $mode);

        $this->postSaveVehicle($licenceVehicleId, $mode);
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
