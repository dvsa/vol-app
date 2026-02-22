<?php

declare(strict_types=1);

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\VehiclesDeclarationsEvidenceLarge as CommonVehiclesDeclarationsEvidenceLarge;

class VehiclesDeclarationsEvidenceLarge extends CommonVehiclesDeclarationsEvidenceLarge
{
    /**
     * Make form alterations
     *
     * @param \Laminas\Form\Form $form
     * @return \Laminas\Form\Form
     */
    #[\Override]
    protected function alterForm($form)
    {
        parent::alterForm($form);

        //don't show the guidance link on internal
        $form->get('largeEvidenceText')->setValue('markup-psv-large-evidence-form-no-guidance');
        $form->get('form-actions')->get('save')->setLabel('internal.save.button');

        return $form;
    }
}
