<?php

namespace Olcs\Controller\Lva\Variation;

use Olcs\Controller\Lva\Traits\VariationControllerTrait;

/**
* Internal Application Undertakings Controller
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DeclarationsInternalController extends \Olcs\Controller\Lva\AbstractDeclarationsInternalController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'internal';
}
