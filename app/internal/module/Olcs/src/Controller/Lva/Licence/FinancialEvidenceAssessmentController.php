<?php

namespace Olcs\Controller\Lva\Licence;

use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Lva\AbstractFinancialEvidenceAssessmentController;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;

/**
 * Financial evidence assessment in the context of a licence.
 *
 * Shows successful analyses across the licence: documents linked to the licence, or to any of
 * its applications (new or variation).
 */
class FinancialEvidenceAssessmentController extends AbstractFinancialEvidenceAssessmentController implements
    LicenceControllerInterface
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
}
