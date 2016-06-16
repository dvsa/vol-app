<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\PsvVehicles as CommonPsvVehicles;
use Common\Service\Helper\FormHelperService;

/**
 * Licence Psv Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicencePsvVehicles extends CommonPsvVehicles
{
    /**
     * Make form alterations
     *
     * @param \Zend\Form\Form $form
     * @return \Zend\Form\Form
     */
    protected function alterForm($form)
    {
        $this->showShareInfo = true;
        parent::alterForm($form);

        $saveButton = $form->get('form-actions')->get('save');
        $this->setPrimaryAction($form, 'save');
        $this->getFormHelper()->alterElementLabel($saveButton, 'internal.', FormHelperService::ALTER_LABEL_PREPEND);

        return $form;
    }
}
