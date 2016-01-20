<?php

/**
 * Variation Financial Evidence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\VariationFinancialEvidence as CommonFinancialEvidence;
use Olcs\FormService\Form\Lva\Traits\FinancialEvidenceAlterations;

/**
 * Variation Financial Evidence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationFinancialEvidence extends CommonFinancialEvidence
{
    use FinancialEvidenceAlterations;
}
