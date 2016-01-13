<?php

/**
 * External Application Transport Managers Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Lva\AbstractTransportManagersController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * External Application Transport Managers Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagersController extends AbstractTransportManagersController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';
}
