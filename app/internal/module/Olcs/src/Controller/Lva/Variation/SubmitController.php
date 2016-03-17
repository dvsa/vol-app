<?php

/**
 * Variation Submit Controller
 *
 * @author Alex Peshkov <alex.pehkov@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Olcs\Controller\Lva\AbstractSubmitController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Olcs\Controller\Interfaces\VariationControllerInterface;

/**
 * Variation Submit Controller
 *
 * @author Alex Peshkov <alex.pehkov@valtech.co.uk>
 */
class SubmitController extends AbstractSubmitController implements VariationControllerInterface
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'internal';
}
