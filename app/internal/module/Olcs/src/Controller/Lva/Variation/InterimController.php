<?php

/**
 * Internal Variation Interim Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Olcs\Controller\Lva\AbstractInterimController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;

/**
 * Internal Variation Interim Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InterimController extends AbstractInterimController implements ApplicationControllerInterface
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'internal';
}
