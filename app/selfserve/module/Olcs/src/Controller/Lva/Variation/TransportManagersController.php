<?php

/**
 * External Variation Transport Managers Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Olcs\Controller\Lva\AbstractTransportManagersController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;

/**
 * External Variation Transport Managers Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagersController extends AbstractTransportManagersController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'external';
}
