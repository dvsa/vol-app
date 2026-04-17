<?php

declare(strict_types=1);

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\VehiclesDeclarationsNovelty as CommonVehiclesDeclarationsNovelty;

class ApplicationVehiclesDeclarationsPsvOperateNovelty extends CommonVehiclesDeclarationsNovelty
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

        $form->get('form-actions')->get('save')->setLabel('internal.save.button');

        return $form;
    }
}
