<?php

/**
 * Internal Variation Discs Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;

/**
 * Internal Variation Discs Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DiscsController extends Lva\AbstractDiscsController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'internal';
}
