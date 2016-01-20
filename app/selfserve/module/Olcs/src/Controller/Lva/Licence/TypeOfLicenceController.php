<?php

/**
 * External Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva\Licence\AbstractTypeOfLicenceController;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;

/**
 * External Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends AbstractTypeOfLicenceController
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'external';
}
