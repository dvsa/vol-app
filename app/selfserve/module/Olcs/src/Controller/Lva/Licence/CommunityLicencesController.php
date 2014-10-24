<?php

/**
 * External Licence Community Licences Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;

/**
 * External Licence Community Licences Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CommunityLicencesController extends Lva\AbstractCommunityLicencesController
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'external';
}
