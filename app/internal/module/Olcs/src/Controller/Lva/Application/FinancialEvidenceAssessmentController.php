<?php

namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Lva\AbstractFinancialEvidenceAssessmentController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * Financial evidence assessment in the context of a new application.
 */
class FinancialEvidenceAssessmentController extends AbstractFinancialEvidenceAssessmentController implements
    ApplicationControllerInterface
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
}
