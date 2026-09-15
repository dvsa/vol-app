<?php

namespace Common\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Variation Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Variation extends AbstractLvaFormService
{
    public function __construct(protected FormHelperService $formHelper, protected AuthorizationService $authService)
    {
    }

    public function alterForm($form): void
    {
        $this->removeFormAction($form, 'saveAndContinue');
        $this->setPrimaryAction($form, 'save');
    }
}
