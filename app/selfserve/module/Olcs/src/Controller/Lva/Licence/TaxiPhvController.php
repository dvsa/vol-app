<?php

/**
 * External Licence Taxi PHV Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;

/**
 * External Licence Taxi PHV Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TaxiPhvController extends Lva\AbstractTaxiPhvController
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'external';
}
