<?php

/**
 * Internal Variation Financial History Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;

/**
 * Internal Variation Financial History Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FinancialHistoryController extends Lva\AbstractFinancialHistoryController implements
    ApplicationControllerInterface
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'internal';
}
