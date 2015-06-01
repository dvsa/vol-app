<?php

/**
 * Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva\Variation\AbstractTypeOfLicenceController;
use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;

/**
 * Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends AbstractTypeOfLicenceController
{
    use VariationControllerTrait;

    protected $location = 'external';
    protected $lva = 'variation';
}
