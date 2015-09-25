<?php

/**
 * Internal Variation Undertakings Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva\Variation;

use Olcs\Controller\Lva\AbstractUndertakingsController as InternalAbstractUndertakingsController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Olcs\Controller\Interfaces\VariationControllerInterface;

/**
* Internal Variation Undertakings Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class UndertakingsController extends InternalAbstractUndertakingsController implements VariationControllerInterface
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'internal';
}
