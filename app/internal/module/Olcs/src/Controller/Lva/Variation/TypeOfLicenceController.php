<?php

/**
 * Internal Variation Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva\Variation\AbstractTypeOfLicenceController;
use Olcs\Controller\Lva\Traits;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;

/**
 * Internal Variation Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends AbstractTypeOfLicenceController implements ApplicationControllerInterface
{
    use Traits\VariationControllerTrait;

    protected $location = 'internal';
    protected $lva = 'variation';
}
