<?php

namespace Common\FormService\Form\Lva\TransportManager;

use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application Transport Manager
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationTransportManager extends AbstractTransportManager
{
    public function __construct(protected FormHelperService $formHelper, protected AuthorizationService $authService)
    {
    }
}
