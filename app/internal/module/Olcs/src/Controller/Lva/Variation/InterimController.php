<?php

/**
 * Internal Variation Interim Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Olcs\Controller\Lva\AbstractInterimController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Olcs\Controller\Interfaces\VariationControllerInterface;
use Dvsa\Olcs\Transfer\Command\Variation\UpdateInterim;

/**
 * Internal Variation Interim Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InterimController extends AbstractInterimController implements VariationControllerInterface
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'internal';
    protected $updateInterimCommand = UpdateInterim::class;
}
