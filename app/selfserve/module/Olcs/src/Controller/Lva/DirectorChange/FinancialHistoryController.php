<?php

/**
 * External Director Change Variation Financial History Controller
 */

namespace Olcs\Controller\Lva\DirectorChange;

use Common\Controller\Lva\AbstractFinancialHistoryController;
use Common\RefData;
use Olcs\Controller\Lva\Traits\VariationWizardPageWithSubsequentPageControllerTrait;

/**
 * External Director Change Variation Financial History Controller
 */
class FinancialHistoryController extends AbstractFinancialHistoryController
{
    use VariationWizardPageWithSubsequentPageControllerTrait;

    protected $location = 'external';
    protected $lva = 'variation';

    protected function getVariationType()
    {
        return RefData::VARIATION_TYPE_DIRECTOR_CHANGE;
    }

    protected function getNextPageRouteName()
    {
        return 'lva-director_change/financial_history';
    }

    protected function getFinancialHistoryForm(array $data = [])
    {
        $data['variationType'] = $this->getVariationType();
        $data['organisationType'] = $this->fetchDataForLva()['licence']['organisation']['type']['id'];
        return parent::getFinancialHistoryForm($data);
    }
}
