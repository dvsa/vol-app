<?php

/**
 * External Application Business Type Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva\AbstractBusinessTypeController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * External Application Business Type Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusinessTypeController extends AbstractBusinessTypeController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';
}
