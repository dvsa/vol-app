<?php

namespace Common\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Application extends AbstractLvaFormService
{
    public function __construct(protected FormHelperService $formHelper, protected AuthorizationService $authService)
    {
    }

    public function alterForm($form): void
    {
        // No op
    }
}
