<?php

/**
 * External Variation Summary Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Olcs\Controller\Lva\AbstractSummaryController;

/**
 * External Variation Summary Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SummaryController extends AbstractSummaryController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
}
