<?php

declare(strict_types=1);

namespace Common\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;

class VehiclesDeclarationsPsvOperateLarge
{
    public function __construct(protected FormHelperService $formHelper)
    {
    }

    public function getForm()
    {
        $form = $this->formHelper->createForm('Lva\VehiclesDeclarationsPsvOperateLarge');

        $this->alterForm($form);

        return $form;
    }

    protected function alterForm($form)
    {
        return $form;
    }
}
