<?php

/**
 * INTERNAL Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\Controller\AbstractInternalController;
use Common\Controller\Traits\Lva\ApplicationControllerTrait;

/**
 * INTERNAL Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AbstractApplicationController extends AbstractInternalController
{
    use ApplicationControllerTrait;
}
