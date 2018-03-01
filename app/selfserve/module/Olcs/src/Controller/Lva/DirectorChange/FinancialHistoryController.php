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
     * Get the required previous sections
     *
     * @return array required previous sections or return empty array
     */
    protected function getRequiredSections()
    {
        return ['peopleStatus'];
    }

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
     * Get the previous wizard page location
     *
     * @see consuming class to provide implementation
     *
     * @return array route definition
     */
    protected function getPreviousPageRoute()
    {
        return ['name' => 'lva-director_change/people', 'params' => ['application' => $this->getIdentifier()]];
    }

    /**
     * Provide the route for the next page in the wizard
     *
     * @return array route definition
     */
    protected function getNextPageRoute()
    {
        return [
            'name' => 'lva-director_change/convictions_penalties',
            'params' => ['application' => $this->getIdentifier()]
        ];
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
        return ['name' => 'lva-licence/people', 'params' => ['licence' => $licenceId]];
    }
}
