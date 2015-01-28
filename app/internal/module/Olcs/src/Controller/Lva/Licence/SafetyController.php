<?php

/**
 * Internal Licence Safety Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Common\Controller\Lva\Traits\LicenceSafetyControllerTrait;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;

/**
 * Internal Licence Safety Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SafetyController extends Lva\AbstractSafetyController
    implements LicenceControllerInterface
{
    use LicenceSafetyControllerTrait,
        LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'internal';
}
