<?php

/**
 * External Director Change Variation Financial History Controller
 */

namespace Olcs\Controller\Lva\DirectorChange;

use Common\Controller\Lva\AbstractFinancialHistoryController;
use Common\FormService\FormServiceManager;
use Common\RefData;
use Common\Service\Helper\DataHelperService;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\FormInterface;
use Olcs\Controller\Lva\Traits\VariationWizardPageFormActionsTrait;
use Olcs\Controller\Lva\Traits\VariationWizardPageWithSubsequentPageControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * External Director Change Variation Financial History Controller
 */
class FinancialHistoryController extends AbstractFinancialHistoryController
{
    use VariationWizardPageWithSubsequentPageControllerTrait;
    use VariationWizardPageFormActionsTrait;

    protected string $location = 'external';
    protected $lva = 'variation';

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param FormServiceManager $formServiceManager
     * @param ScriptFactory $scriptFactory
     * @param DataHelperService $dataHelper
     * @param FileUploadHelperService $uploadHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FlashMessengerHelperService $flashMessengerHelper,
        FormServiceManager $formServiceManager,
        ScriptFactory $scriptFactory,
        DataHelperService $dataHelper,
        FileUploadHelperService $uploadHelper
    ) {
        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $formServiceManager,
            $scriptFactory,
            $dataHelper,
            $uploadHelper
        );
    }
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
        return 'Continue to Licence History';
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
            'name' => 'lva-director_change/licence_history',
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
    #[\Override]
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
