<?php

namespace Common\FormService\Form\Lva\People;

use Common\Form\Form;
use Common\Form\Model\Form\Licence\AddPerson;
use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Licence People
 */
class LicenceAddPerson extends AbstractPeople
{
    public function __construct(protected FormHelperService $formHelper, protected AuthorizationService $authService)
    {
    }

    /**
     * Get the form
     *
     * @param array $params params
     *
     * @return Form $form form
     */
    #[\Override]
    public function getForm(array $params = [])
    {
        return $this->formHelper->createForm(AddPerson::class);
    }
}
