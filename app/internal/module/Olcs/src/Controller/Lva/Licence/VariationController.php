<?php

/**
 * Licence Variation Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Common\Controller\Lva\AbstractVariationController;
use Olcs\Controller\Interfaces\LicenceControllerInterface;

/**
 * Licence Variation Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationController extends AbstractVariationController implements LicenceControllerInterface
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'internal';
}
