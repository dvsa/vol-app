<?php

/**
 * External Licence Business Details Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva\AbstractBusinessDetailsController;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;

/**
 * External Licence Business Details Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusinessDetailsController extends AbstractBusinessDetailsController
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'external';
}
