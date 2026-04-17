<?php

declare(strict_types=1);

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\VehiclesDeclarationsNovelty;
use Common\Service\Helper\FormHelperService;
use Laminas\Form\Form;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;

class VariationVehiclesDeclarationsPsvOperateNovelty extends VehiclesDeclarationsNovelty
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
    #[\Override]
    protected function alterForm($form)
    {
        parent::alterForm($form);
        $this->alterButtons($form);

        //as this is the last psv/restricted question on variation journey, we want users to return to overview
        $this->formHelper->remove($form, 'form-actions->saveAndContinue');
        $form->get('form-actions')->get('save')->removeAttribute('class');
        $form->get('form-actions')->get('save')->setAttribute('class', 'govuk-button govuk-button--primary');

        return $form;
    }
}
