<?php

/**
 * External Application Business Details Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva\AbstractBusinessDetailsController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * External Application Business Details Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusinessDetailsController extends AbstractBusinessDetailsController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';
}
