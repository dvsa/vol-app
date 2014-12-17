<?php

/**
 * External Variation Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Olcs\Controller\Lva\AbstractGenericVehiclesPsvController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Common\Controller\Lva\Traits;
use Zend\Form\Form;

/**
 * External Variation Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VehiclesPsvController extends AbstractGenericVehiclesPsvController
{
    use VariationControllerTrait,
        Traits\PsvLicenceControllerTrait,
        // @NOTE: AC says variations behave exactly as per licences, so...
        Traits\LicenceGenericVehiclesControllerTrait;

    use Traits\PsvGoodsLicenceVariationControllerTrait {
            Traits\PsvGoodsLicenceVariationControllerTrait::alterFormForLva as traitAlterFormForLva;
        }

    protected $lva = 'variation';
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
