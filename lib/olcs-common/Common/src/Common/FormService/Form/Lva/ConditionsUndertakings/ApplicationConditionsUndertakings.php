<?php

namespace Common\FormService\Form\Lva\ConditionsUndertakings;

use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application Conditions Undertakings
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationConditionsUndertakings extends AbstractConditionsUndertakings
{
    public function __construct(protected FormHelperService $formHelper, protected AuthorizationService $authService)
    {
    }

    #[\Override]
    protected function alterForm($form)
    {
        parent::alterForm($form);

        $this->removeFormAction($form, 'save');
        $this->removeFormAction($form, 'cancel');

        return $form;
    }
}
