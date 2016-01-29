<?php

/**
 * Internal Application Safety Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Common\Controller\Lva\Traits\ApplicationSafetyControllerTrait;

/**
 * Internal Application Safety Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SafetyController extends Lva\AbstractSafetyController implements ApplicationControllerInterface
{
    use ApplicationControllerTrait,
        ApplicationSafetyControllerTrait;

    protected $lva = 'application';
    protected $location = 'internal';
}
