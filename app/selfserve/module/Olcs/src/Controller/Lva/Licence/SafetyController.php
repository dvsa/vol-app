<?php

/**
 * External Licence Safety Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Common\Controller\Lva\Traits\LicenceSafetyControllerTrait;

/**
 * External Licence Safety Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SafetyController extends Lva\AbstractSafetyController
{
    use LicenceSafetyControllerTrait;

    protected $lva = 'licence';
    protected $location = 'external';
}
