<?php

/**
 * Internal Licence Business Details Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva\AbstractBusinessDetailsController;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;

/**
 * Internal Licence Business Details Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusinessDetailsController extends AbstractBusinessDetailsController implements LicenceControllerInterface
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'internal';
}
