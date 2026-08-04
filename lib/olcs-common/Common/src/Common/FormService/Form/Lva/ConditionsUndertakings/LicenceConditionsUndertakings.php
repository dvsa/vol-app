<?php

namespace Common\FormService\Form\Lva\ConditionsUndertakings;

use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Licence Conditions Undertakings
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceConditionsUndertakings extends AbstractConditionsUndertakings
{
    public function __construct(protected FormHelperService $formHelper, protected AuthorizationService $authService)
    {
    }

    #[\Override]
    protected function alterForm($form)
    {
        parent::alterForm($form);

        $this->removeStandardFormActions($form);

        return $form;
    }
}
