<?php

/**
 * Internal Licencing Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Olcs\Controller\Interfaces\LicenceControllerInterface;

/**
 * Internal Licencing Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatingCentresController extends Lva\AbstractOperatingCentresController implements LicenceControllerInterface
{
    use LicenceControllerTrait,
        Lva\Traits\LicenceOperatingCentresControllerTrait;

    protected $lva = 'licence';
    protected $location = 'internal';
}
