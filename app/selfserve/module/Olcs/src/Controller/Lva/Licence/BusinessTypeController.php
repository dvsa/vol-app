<?php

/**
 * External Licence Business Type Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva\AbstractBusinessTypeController;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;

/**
 * External Licence Business Type Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusinessTypeController extends AbstractBusinessTypeController
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'external';
}
