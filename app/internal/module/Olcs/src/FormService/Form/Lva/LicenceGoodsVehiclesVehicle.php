<?php

/**
 * Internal Licence Goods Vehicles Vehicle
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\LicenceGoodsVehiclesVehicle as CommonLicenceGoodsVehiclesVehicle;

/**
 * Internal Licence Goods Vehicles Vehicle
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceGoodsVehiclesVehicle extends CommonLicenceGoodsVehiclesVehicle
{
    public function alterForm($form, $params)
    {
        $this->removeSubmitButton = false;
        parent::alterForm($form, $params);

        if ($params['mode'] === 'edit') {
            if ($params['isRemoved']) {
                $removalDate = $form->get('licence-vehicle')->get('removalDate');
                $removalDate->getDayElement()->removeAttribute('disabled');
                $removalDate->getMonthElement()->removeAttribute('disabled');
                $removalDate->getYearElement()->removeAttribute('disabled');
                $form->get('form-actions')->get('submit')->removeAttribute('disabled');
                $form->get('security')->removeAttribute('disabled');
                $form->get('licence-vehicle')->get('id')->removeAttribute('disabled');
                $form->get('licence-vehicle')->get('version')->removeAttribute('disabled');
                $form = $this->getFormHelper()->disableEmptyValidation($form);
            } else {
                $this->getFormHelper()->disableElement($form, 'licence-vehicle->removalDate');
            }
        }
        return $form;
    }
}
