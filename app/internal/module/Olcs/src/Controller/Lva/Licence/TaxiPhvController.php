<?php

/**
 * Internal Licence Taxi PHV Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;

/**
 * Internal Licence Taxi PHV Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TaxiPhvController extends Lva\AbstractTaxiPhvController implements LicenceControllerInterface
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'internal';
}
