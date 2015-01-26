<?php

/**
 * External Variation Convictions and penalties Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;

/**
 * External Variation Convictions and penalties Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConvictionsPenaltiesController extends Lva\AbstractConvictionsPenaltiesController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'external';
}
