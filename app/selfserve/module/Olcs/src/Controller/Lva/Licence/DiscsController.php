<?php

/**
 * External Licence Discs Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;

/**
 * External Licence Discs Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DiscsController extends Lva\AbstractDiscsController
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'external';
}
