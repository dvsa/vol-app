<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\VehiclesDeclarationsPsvOperateSmall;
use Common\Service\Helper\FormHelperService;
use Laminas\Form\Form;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;

class ApplicationVehiclesDeclarationsSmall extends VehiclesDeclarationsPsvOperateSmall
{
    use ButtonsAlterations;

    protected FormHelperService $formHelper;

    public function __construct(FormHelperService $formHelper)
    {
        parent::__construct($formHelper);
    }

    /**
     * Make form alterations
     *
     * @param Form $form form
     *
     * @return Form
     */
    protected function alterForm($form)
    {
        parent::alterForm($form);
        $this->alterButtons($form);

        return $form;
    }
}
