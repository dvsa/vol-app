<?php

/**
 * Internal Licence Community Licences Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;

/**
 * Internal Licence Community Licences Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CommunityLicencesController extends Lva\AbstractCommunityLicencesController
    implements LicenceControllerInterface
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'internal';
}
