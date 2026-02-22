<?php

namespace Olcs\Controller\Lva\DirectorChange;

use Common\Controller\Lva\AbstractController;
use Common\Controller\Lva\AbstractConvictionsPenaltiesController;
use Common\FormService\FormServiceManager;
use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Variation\GrantDirectorChange;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Http\Response;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Olcs\Controller\Lva\Traits\VariationWizardFinalPageControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * @see ConvictionsPenaltiesControllerFactory
 * @see \OlcsTest\Controller\Lva\DirectorChange\ConvictionsPenaltiesControllerTest
 */
class ConvictionsPenaltiesController extends AbstractConvictionsPenaltiesController
{
    use VariationWizardFinalPageControllerTrait;

    /**
     * @var string
     */
    protected string $location = 'external';

    /**
     * @var string
     */
    protected $lva = self::LVA_VAR;

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        FormServiceManager $formServiceManager,
        TableFactory $tableFactory,
        private TranslationHelperService $translationHelper,
        ScriptFactory $scriptFactory,
        protected FlashMessenger $flashMessengerPlugin
    ) {
        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $flashMessengerHelper,
            $formServiceManager,
            $tableFactory,
            $scriptFactory
        );
    }

    /**
     * Get the required previous sections
     *
     * @return array required previous sections;
     */
    protected function getRequiredSections()
    {
        return ['peopleStatus', 'financialHistoryStatus', 'licenceHistoryStatus'];
    }


    /**
     * Return the route the wizard lives under
     *
     * @return null|string
     */
    protected function getBaseRoute()
    {
        return 'lva-director_change/convictions_penalties';
    }

    /**
     * get the variation type
     *
     * @return string variation type
     */
    protected function getVariationType()
    {
        return RefData::VARIATION_TYPE_DIRECTOR_CHANGE;
    }

    /**
     * Method to complete the process
     *
     * @return Response
     */
    protected function submit()
    {
        $response = $this->handleCommand(GrantDirectorChange::create(['id' => $this->getIdentifier()]));

        $responseContent = json_decode($response->getBody());
        $createdPersonIDs = $responseContent->id->createdPerson;

        if (!is_array($createdPersonIDs)) {
            $createdPersonIDs = [$createdPersonIDs];
        }

        $createdPeopleCount = count($createdPersonIDs);

        if ($response->isClientError() || $response->isServerError()) {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
        }

        if ($createdPeopleCount > 0) {
            $messageKey = 'selfserve-app-subSection-your-business-people.message.created.success.' . ($createdPeopleCount === 1 ? 'singular' : 'plural');
            $message = sprintf($this->translationHelper->translate($messageKey), $createdPeopleCount);
            $this->flashMessengerHelper->addSuccessMessage($message);
        }

        foreach ($createdPersonIDs as $createdPersonID) {
            $this->flashMessengerPlugin->addMessage($createdPersonID, AbstractController::FLASH_MESSENGER_CREATED_PERSON_NAMESPACE);
        }

        return $this->redirectToStartRoute();
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
     * Get the previous wizard page location
     *
     * @see consuming class to provide implementation
     *
     * @return array route definition
     */
    protected function getPreviousPageRoute()
    {
        return ['name' => 'lva-director_change/licence_history', 'params' => ['application' => $this->getIdentifier()]];
    }

    /**
     * Get the form
     *
     * @param array $data   form data
     * @param array $params parameters for form
     *
     * @return mixed]
     */
    #[\Override]
    protected function getConvictionsPenaltiesForm($data, $params = [])
    {
        $params['variationType'] = $this->getVariationType();
        $params['organisationType'] = $this->fetchDataForLva()['licence']['organisation']['type']['id'];
        return parent::getConvictionsPenaltiesForm($data, $params);
    }

    /**
     * Alter the table button
     *
     * @param array $data data
     *
     * @return mixed
     */
    #[\Override]
    protected function getConvictionsPenaltiesTable($data)
    {
        /**
         * @var TableBuilder table
         */
        $table = parent::getConvictionsPenaltiesTable($data);
        $table->setSetting(
            'crud',
            [
                'actions' => [
                    'add' => [
                        'label' => 'Add a conviction'
                    ]
                ]
            ]
        );
        $table->setVariable(
            'empty_message',
            'selfserve-app-subSection-previous-history-criminal-conviction-hasConv-hint-director-change'
        );
        return $table;
    }
}
