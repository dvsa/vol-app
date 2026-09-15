<?php

namespace Common\FormService\Form\Lva\People;

use Common\Form\Form;
use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

class ApplicationPeople extends AbstractPeople
{
    protected $lva = 'application';

    public function __construct(protected FormHelperService $formHelper, protected AuthorizationService $authService)
    {
    }

    /**
     * Alter form
     *
     * @param Form  $form   Form class
     * @param array $params Parameters for form
     *
     * @return Form
     */
    #[\Override]
    protected function alterForm(Form $form, array $params = [])
    {
        parent::alterForm($form, $params);

        $this->removeFormAction($form, 'cancel');

        return $form;
    }
}
