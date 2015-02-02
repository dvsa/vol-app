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
use Common\Controller\Lva\Traits\LicenceGenericVehiclesControllerTrait;
use Common\Controller\Lva\Traits\PsvGoodsLicenceVariationControllerTrait;
use Zend\Form\Form;

/**
 * External Licence Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VehiclesPsvController extends AbstractGenericVehiclesPsvController
{
    use LicenceControllerTrait,
        PsvLicenceControllerTrait,
        LicenceGenericVehiclesControllerTrait;

    use PsvGoodsLicenceVariationControllerTrait {
            PsvGoodsLicenceVariationControllerTrait::alterFormForLva as traitAlterFormForLva;
        }

    protected $lva = 'licence';
    protected $location = 'external';

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
