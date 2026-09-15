<?php

namespace Common\FormService\Form\Lva\People;

use Common\Form\Form;
use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Variation People
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationPeople extends AbstractPeople
{
    public function __construct(protected FormHelperService $formHelper, protected AuthorizationService $authService)
    {
    }

    /**
     * Alter variation form
     *
     * @param Form  $form   Form class
     * @param array $params Parameters for form
     *
     * @return void
     */
    #[\Override]
    protected function alterForm(Form $form, array $params = [])
    {
        parent::alterForm($form, $params);

        $this->removeStandardFormActions($form);
    }
}
