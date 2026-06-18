<?php

namespace Common\FormService\Form\Lva\People;

use Common\Form\Form;
use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Licence People
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicencePeople extends AbstractPeople
{
    public function __construct(protected FormHelperService $formHelper, protected AuthorizationService $authService)
    {
    }

    /**
     * Alter licence people form
     *
     * @param Form  $form   Form
     * @param array $params Parameters / options for form
     *
     * @return Form
     */
    #[\Override]
    protected function alterForm(Form $form, array $params = [])
    {
        $form = parent::alterForm($form, $params);

        $this->removeStandardFormActions($form);

        return $form;
    }
}
