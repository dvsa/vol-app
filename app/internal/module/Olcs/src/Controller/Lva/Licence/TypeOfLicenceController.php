<?php

/**
 * Internal Licence Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Lva\Traits;

/**
 * Internal Licence Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends Lva\AbstractTypeOfLicenceController implements
    LicenceControllerInterface
{
    use Traits\LicenceControllerTrait;

    protected $location = 'internal';
    protected $lva = 'licence';
}
