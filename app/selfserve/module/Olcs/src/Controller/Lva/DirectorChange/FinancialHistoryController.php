<?php

/**
 * External Director Change Variation Financial History Controller
 */

namespace Olcs\Controller\Lva\DirectorChange;

use Common\Controller\Lva\AbstractFinancialHistoryController;
use Common\RefData;
use Olcs\Controller\Lva\Traits\VariationWizardPageFormActionsTrait;
use Olcs\Controller\Lva\Traits\VariationWizardPageWithSubsequentPageControllerTrait;

/**
 * External Director Change Variation Financial History Controller
 */
class FinancialHistoryController extends AbstractFinancialHistoryController
{
    use VariationWizardPageWithSubsequentPageControllerTrait;
    use VariationWizardPageFormActionsTrait;

    protected $location = 'external';
    protected $lva = 'variation';

    protected function getVariationType()
    {
        return RefData::VARIATION_TYPE_DIRECTOR_CHANGE;
    }

    public function getSubmitActionText()
    {
        return 'Continue to licence history';
    }

    protected function getNextPageRouteName()
    {
        # This will need to change to something like 'lva-director_change/licence_history' once that controller exists
        return 'lva-director_change/financial_history';
    }

    protected function getFinancialHistoryForm(array $data = [])
    {
        $data['variationType'] = $this->getVariationType();
        $data['organisationType'] = $this->fetchDataForLva()['licence']['organisation']['type']['id'];
        return parent::getFinancialHistoryForm($data);
    }
}
