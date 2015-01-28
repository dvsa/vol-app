<?php

/**
 * Internal Licence Discs Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;

/**
 * Internal Licence Discs Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DiscsController extends Lva\AbstractDiscsController
    implements LicenceControllerInterface
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'internal';
}
