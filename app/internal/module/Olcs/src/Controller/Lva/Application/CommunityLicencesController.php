<?php

/**
 * Internal Application Community Licences Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * Internal Application Community Licences Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CommunityLicencesController extends Lva\AbstractCommunityLicencesController implements
    ApplicationControllerInterface
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'internal';
}
