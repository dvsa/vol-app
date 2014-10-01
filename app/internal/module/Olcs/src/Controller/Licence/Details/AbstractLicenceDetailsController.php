<?php

/**
 * Abstract LicenceDetails Controller
 */
namespace Olcs\Controller\Licence\Details;

use Common\Controller\Licence\Details\AbstractLicenceDetailsController as CommonAbstractLicenceDetailsController;
use Olcs\Controller\Traits\LicenceControllerTrait;

/**
 * Abstract LicenceDetails Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractLicenceDetailsController extends CommonAbstractLicenceDetailsController
{
    use LicenceControllerTrait;
}
