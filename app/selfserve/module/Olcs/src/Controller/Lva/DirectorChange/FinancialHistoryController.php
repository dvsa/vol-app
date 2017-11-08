<?php

/**
 * External Director Change Variation Financial History Controller
 */

namespace Olcs\Controller\Lva\DirectorChange;

use Common\Controller\Lva\AbstractFinancialHistoryController;
use Common\RefData;
use Olcs\Controller\Lva\Traits\VariationWizardPageFormActionsTrait;
use Olcs\Controller\Lva\Traits\VariationWizardPageWithSubsequentPageControllerTrait;
use Zend\Form\FormInterface;

/**
 * External Director Change Variation Financial History Controller
 */
class FinancialHistoryController extends AbstractFinancialHistoryController
{
    use VariationWizardPageWithSubsequentPageControllerTrait;
    use VariationWizardPageFormActionsTrait;

    protected $location = 'external';
    protected $lva = 'variation';

    /**
     * Get the variation type upon which this controller can operate
     *
     * @return string
     */
    protected function getVariationType()
    {
        return RefData::VARIATION_TYPE_DIRECTOR_CHANGE;
    }

    /**
     * Provide the text (or translation string) for the saveAndContinue button
     *
     * @return string
     */
    public function getSubmitActionText()
    {
        return 'Continue to Convictions and Penalties';
    }

    /**
     * Provide the route name for the next page in the wizard
     *
     * @return string
     */
    protected function getNextPageRouteName()
    {
        # This will need to change to something like 'lva-director_change/licence_history' once that controller exists
        return 'lva-director_change/convictions_penalties';
    }

    /**
     * Get Financial History Form (extra data is required for this version of the form)
     *
     * @param array $data the form data
     *
     * @return FormInterface
     */
    protected function getFinancialHistoryForm(array $data = [])
    {
        $data['variationType'] = $this->getVariationType();
        $data['organisationType'] = $this->fetchDataForLva()['licence']['organisation']['type']['id'];
        return parent::getFinancialHistoryForm($data);
    }

    /**
     * Get the start of the start of the wizard
     *
     * @return array
     */
    public function getStartRoute()
    {
        $licenceId = $this->getLicenceId($this->getApplicationId());
        return ['name'=>'lva-licence/people', 'params'=>['licence' =>$licenceId]];
    }
}
