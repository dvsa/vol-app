<?php

/**
 * FinancialEvidence Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\FinancialEvidence as CommonFinancialEvidence;
use Olcs\FormService\Form\Lva\Traits\FinancialEvidenceAlterations;

/**
 * FinancialEvidence Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FinancialEvidence extends CommonFinancialEvidence
{
    use FinancialEvidenceAlterations;
}
