<?php

/**
 * External Application Licence History Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * External Application Licence History Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceHistoryController extends Lva\AbstractLicenceHistoryController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';
}
