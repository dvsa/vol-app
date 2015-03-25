<?php

/**
 * Variation Refuse Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva\Variation;

use Olcs\Controller\Lva\AbstractRefuseController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;

/**
 * Variation Refuse Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class RefuseController extends AbstractRefuseController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'internal';
}
