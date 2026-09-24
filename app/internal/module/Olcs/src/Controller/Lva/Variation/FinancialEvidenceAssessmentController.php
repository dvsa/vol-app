<?php

namespace Olcs\Controller\Lva\Variation;

use Olcs\Controller\Interfaces\VariationControllerInterface;
use Olcs\Controller\Lva\AbstractFinancialEvidenceAssessmentController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;

/**
 * Financial evidence assessment in the context of a variation.
 *
 * A variation is an application in the API, so analyses are scoped to this variation only.
 */
class FinancialEvidenceAssessmentController extends AbstractFinancialEvidenceAssessmentController implements
    VariationControllerInterface
{
    use VariationControllerTrait;

    protected $lva = 'variation';
}
