<?php

/**
 * Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Variation;

use Common\Controller\Traits\Lva;

/**
 * Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends AbstractVariationController
{
    use Lva\TypeOfLicenceTrait,
        Lva\VariationTypeOfLicenceTrait;
}
