<?php

/**
 * External Director Change Variation Licence History Controller
 */

namespace Olcs\Controller\Lva\DirectorChange;

use Common\Controller\Lva\AbstractLicenceHistoryController;
use Common\FormService\FormServiceManager;
use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\Traits\VariationWizardPageFormActionsTrait;
use Olcs\Controller\Lva\Traits\VariationWizardPageWithSubsequentPageControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * External Director Change Variation Licence History Controller
 */
class LicenceHistoryController extends AbstractLicenceHistoryController
{
    use VariationWizardPageWithSubsequentPageControllerTrait;
    use VariationWizardPageFormActionsTrait;

    protected string $location = 'external';
    protected $lva = 'variation';

    private TranslationHelperService $translationHelper;

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FlashMessengerHelperService $flashMessengerHelper,
        FormServiceManager $formServiceManager,
        ScriptFactory $scriptFactory,
        StringHelperService $stringHelper,
        TableFactory $tableFactory,
        FormHelperService $formHelper,
        TranslationHelperService $translationHelper
    ) {
        $this->translationHelper = $translationHelper;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $formServiceManager,
            $scriptFactory,
            $stringHelper,
            $tableFactory,
            $formHelper
        );
    }

    /**
     * Get the required previous sections
     *
     * @return array required previous sections or return empty array
     */
    protected function getRequiredSections()
    {
        return ['peopleStatus', 'financialHistoryStatus'];
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
        return ['name' => 'lva-director_change/financial_history', 'params' => ['application' => $this->getIdentifier()]];
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
     * Get the start of the start of the wizard
     *
     * @return array
     */
    public function getStartRoute()
    {
        $licenceId = $this->getLicenceId($this->getApplicationId());
        return ['name' => 'lva-licence/people', 'params' => ['licence' => $licenceId]];
    }

    /**
     * Return the route the wizard lives under
     *
     * @return null|string
     */
    protected function getBaseRoute()
    {
        return 'lva-director_change/licence_history';
    }
}
